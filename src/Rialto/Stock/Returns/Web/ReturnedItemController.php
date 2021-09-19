<?php

namespace Rialto\Stock\Returns\Web;

use Craue\FormFlowBundle\Form\FormFlowInterface;
use DateTime;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\EntityList;
use Rialto\Email\MailerInterface;
use Rialto\Filetype\Postscript\InstructionLabel;
use Rialto\Printing\Job\PrintJob;
use Rialto\Printing\Job\PrintQueue;
use Rialto\Printing\Printer\LabelPrinter;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\Web\BinFilterForm;
use Rialto\Stock\Count\ByItemAccounting;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Returns\Problem\AdminResolveLimits;
use Rialto\Stock\Returns\Problem\CheckInEmail;
use Rialto\Stock\Returns\Problem\ItemResolution;
use Rialto\Stock\Returns\Problem\ProblemsEmail;
use Rialto\Stock\Returns\Problem\ReturnedItemResolver;
use Rialto\Stock\Returns\ReturnedItem;
use Rialto\Stock\Returns\ReturnedItemRepository;
use Rialto\Stock\Returns\ReturnedItems;
use Rialto\Stock\Returns\ReturnedItemService;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Controller for receiving parts that have been returned from a
 * manufacturer.
 */
class ReturnedItemController extends RialtoController
{
    /** @var ReturnedItemService */
    private $itemSvc;

    /** @var ReturnedItemResolver */
    private $resolver;

    /** @var MailerInterface */
    private $mailer;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->itemSvc = $container->get(ReturnedItemService::class);
        $this->resolver = $container->get(ReturnedItemResolver::class);
        $this->mailer = $container->get(MailerInterface::class);
    }

    /**
     * In which the user enters which parts she has received back.
     *
     * @Route("/stock/returns/", name="stock_returns_enter")
     * @Route("/receiving/returns/", name="receiving_returns_enter")
     */
    public function returnAction()
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        $items = new ReturnedItems($this->getHeadquarters());

        $flow = $this->getFormFlow();
        $flow->bind($items);
        $form = $flow->createForm();
        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);
            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                return $this->saveResults($items);
            }
        }

        $template = $this->getTemplate($flow->getCurrentStepLabel());

        return $this->render($template, [
            'flow' => $flow,
            'form' => $form->createView(),
            'items' => $items,
        ]);
    }

    /** @return FormFlowInterface|object */
    private function getFormFlow()
    {
        return $this->get(ReturnedItemsFlow::class);
    }

    private function getTemplate($stepLabel)
    {
        return "stock/returns/$stepLabel.html.twig";
    }

    /**
     * This is a iframe source for search bar in @see returnAction()
     * we use iframe so that this search bar does not conflict with existing form
     *
     * @Route("/stock/returns/searchSku", name="search_bins_stock_returns")
     * @Method("GET")
     * @Template("stock/returns/searchBySku.html.twig")
     */
    public function searchStockBinsBySkuAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        $form = $this->createForm(BinFilterForm::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(StockBin::class);
        $results = new EntityList($repo, $form->getData());
        return [
            'searchForm' => $form->createView(),
            'bins' => $results,
        ];
    }

    private function saveResults(ReturnedItems $items)
    {
        $this->dbm->beginTransaction();
        try {
            $problems = $this->itemSvc->handleProblems($items);

            foreach ($items->getItems() as $returnItem) {
                $bin = $returnItem->getBin();

                $binQty = intval($bin->getQuantity());
                $returnedItemQty = $returnItem->getQuantity();

                if ($binQty != $returnedItemQty) {
                    $bin->setNewQty($returnItem->getQuantity());
                    $strategy = new ByItemAccounting();
                    $strategy->addBin($bin);

                    $transaction = new Transaction(
                        SystemType::fetchStockAdjustment($this->dbm),
                        $bin->getId());
                    $transaction->setMemo("update qty of $bin manually during return");
                    $bin->applyNewQty($transaction);
                    $strategy->addEntries($transaction);
                    $this->dbm->persist($transaction);
                }
            }
            $transfer = $this->itemSvc->transferItemsWithoutProblems($items);


            $this->notifyOfProblems($items->getSource(), $problems);
            $this->dbm->flush(); // make sure items have IDs
            $this->printInstructionLabels($problems);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        return $this->redirectToRoute('stock_returns_results', [
            'date' => date(DateTime::ISO8601),
            'transfer' => $transfer ? $transfer->getId() : null,
        ]);
    }

    /**
     * @param ReturnedItem[] $problems
     */
    private function notifyOfProblems(Facility $returnedFrom, array $problems)
    {
        if (count($problems) == 0) {
            return;
        }
        $currentUser = $this->getCurrentUser();
        $email = new ProblemsEmail($currentUser, $returnedFrom, $problems);
        $email->loadSubscribers($this->dbm);
        $this->mailer->send($email);
    }


    /**
     * @param ReturnedItem[] $items
     */
    private function printInstructionLabels(array $items)
    {
        /** @var $queue PrintQueue */
        $queue = $this->get(PrintQueue::class);
        static $printerID = 'instructions';
        foreach ($items as $item) {
            $label = $this->createLabel($item);
            $job = PrintJob::postscript($label->render());
            $job->setDescription($item);
            $queue->add($job, $printerID);
        }
    }

    /** @return InstructionLabel */
    private function createLabel(ReturnedItem $item)
    {
        return new InstructionLabel([
            sprintf('%s; stock code: %s; bin ID: %s',
                $item,
                $item->getSku() ?: 'unknown',
                $item->getBinId() ?: 'unknown')
        ]);
    }

    /**
     * @Route("/stock/returns/{id}/print/", name="stock_returns_print")
     * @Route("/receiving/returns/{id}/print",
     *   name="receiving_returns_print")
     * @Method("POST")
     */
    public function printLabelAction(ReturnedItem $item)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        $label = $this->createLabel($item);
        /** @var $printer LabelPrinter */
        $printer = LabelPrinter::get('instructions', $this->dbm);
        $printer->printLabel($label);
        return JsonResponse::fromMessages(['OK']);
    }

    /**
     * The user is redirected here after returnAction() above.
     *
     * @Route("/stock/returns/results/", name="stock_returns_results")
     * @Route("/receiving/returns/results", name="receiving_returns_results")
     * @Method("GET")
     * @Template("stock/returns/results.html.twig")
     */
    public function resultsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        /** @var $transfer Transfer|null */
        $transfer = $this->getEntityFromRequest(Transfer::class, 'transfer', $request);
        $date = new DateTime($request->get('date', 'now'));

        /** @var $repo ReturnedItemRepository */
        $repo = $this->getRepository(ReturnedItem::class);
        $items = $repo->findByDate($date);

        return [
            'items' => $items,
            'transfer' => $transfer,
        ];
    }

    /**
     * Show returned items that have not been checked in yet due to
     * unresolved problems.
     *
     * @Route("/stock/returns/outstanding/", name="stock_returns_outstanding")
     * @Route("/receiving/returns/outstanding",
     *   name="receiving_returns_outstanding")
     * @Template("stock/returns/outstanding.html.twig")
     */
    public function outstandingAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        /** @var $repo ReturnedItemRepository */
        $repo = $this->getRepository(ReturnedItem::class);
        $items = $repo->findAll();

        $form = $this->createForm(CheckInType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $from = $form->get('location')->getData();
            $to = $this->getHeadquarters();

            $this->dbm->beginTransaction();
            try {
                $transfer = $this->itemSvc->resolveOutstanding($from, $to);
                if ($transfer) {
                    $this->notifyOfCheckIn($transfer);
                }
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            if ($transfer) {
                $this->logNotice("Created $transfer.");
                return $this->redirectToRoute('stock_transfer_view', [
                    'transfer' => $transfer->getId(),
                ]);
            } else {
                $this->logWarning("No resolved items to check in.");
                return $this->redirectToRoute('stock_returns_outstanding');
            }
        }

        return [
            'items' => $items,
            'form' => $form->createView(),
        ];
    }

    private function notifyOfCheckIn(Transfer $transfer)
    {
        $currentUser = $this->getCurrentUser();
        $email = new CheckInEmail($currentUser, $transfer);
        $email->loadRecipients($this->dbm);
        $this->mailer->send($email);
    }

    /**
     * Help the user to understand the problems with an returned item.
     *
     * @Route("/stock/returns/{id}/diagnose/", name="stock_returns_diagnose")
     * @Route("/receiving/returns/{id}/diagnose",
     *   name="receiving_returns_diagnose")
     * @Template("stock/returns/diagnose.html.twig")
     */
    public function diagnoseAction(ReturnedItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $itemForm = $this->createForm(ReturnedItemType::class, $item, [
            'error_bubbling' => true,
        ]);
        $resolution = new ItemResolution($item, new AdminResolveLimits());
        $formBuilder = $this->createFormBuilder($resolution, [
            'translation_domain' => 'form',
        ]);
        $this->resolver->populateResolution($resolution, $formBuilder);

        $itemForm->handleRequest($request);
        if ($itemForm->isSubmitted()) {
            if ($itemForm->isValid()) {
                $this->dbm->flush();
                return $this->redirect($this->getCurrentUri());
            }
        } elseif ($resolution->hasSolutions()) {
            $resolutionForm = $resolution->getForm();
            $resolutionForm->handleRequest($request);
            if ($resolutionForm->isSubmitted() && $resolutionForm->isValid()) {
                $this->dbm->beginTransaction();
                try {
                    $this->resolver->resolveItem($resolution);
                    $this->dbm->flushAndCommit();
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }
                $this->logNotice("Resolved $item.");
                return $this->redirectToRoute('stock_returns_outstanding');
            }
        }

        return [
            'item' => $item,
            'itemForm' => $itemForm->createView(),
            'possibleBins' => $this->resolver->getPossibleBins($item),
            'transfer' => $item->getOutstandingTransfer(),
            'allocations' => $item->getAllocations(),
            'moves' => $this->resolver->findStockMoves($item),
            'resolution' => $resolution,
        ];
    }

    /**
     * Cancel a mistakenly-added return.
     *
     * @Route("/stock/returns/{id}/", name="stock_returns_delete")
     * @Method("DELETE")
     */
    public function deleteAction(ReturnedItem $item)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $msg = "Cancelled $item.";
        $this->dbm->remove($item);
        $this->dbm->flush();
        $this->logNotice($msg);
        return $this->redirectToRoute('stock_returns_outstanding');
    }
}
