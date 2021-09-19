<?php

namespace Rialto\Stock\Count\Web;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Rialto\Database\Orm\EntityList;
use Rialto\Email\MailerInterface;
use Rialto\Security\Role\Role;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Rialto\Stock\Count\CsvStockCount;
use Rialto\Stock\Count\Email\StockCountEntryEmail;
use Rialto\Stock\Count\Email\StockCountRequestEmail;
use Rialto\Stock\Count\Orm\StockCountRepository;
use Rialto\Stock\Count\StockAdjustment;
use Rialto\Stock\Count\StockCount;
use Rialto\Stock\Count\StockCountVoter;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows the user to request and provide an inventory count.
 */
class StockCountController extends RialtoController
{
    /**
     * @var StockCountRepository
     */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(StockCount::class);
    }

    /**
     * @Route("/stock/stock-count/", name="stock_count_list")
     * @Method("GET")
     * @Template("stock/count/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $form = $this->createForm(CountListFilterType::class);
        $form->submit($request->query->all());
        $list = new EntityList($this->repo, $form->getData());
        return [
            'form' => $form->createView(),
            'list' => $list,
        ];
    }

    /**
     * @Route("/stock/stock-count/{id}/", name="stock_count_view")
     * @Method("GET")
     * @Template("stock/count/view.html.twig")
     */
    public function viewAction(StockCount $count)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        return ['entity' => $count];
    }

    /**
     * Where the administrator or stock manager requests that an inventory
     * count be done.
     *
     * @Route("/Stock/StockCount/", name="stockcount_request")
     * @Template("stock/count/request.html.twig")
     */
    public function requestAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $count = new StockCount($this->getCurrentUser());
        $form = $this->createForm(StockCountRequestType::class, $count);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->dbm->persist($count);
                $this->dbm->flush();

                if ($form->get('sendEmail')->getData()) {
                    $this->sendEmail($count);
                } else {
                    $this->logWarning("Email suppressed.");
                }
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Stock count requested successfully.");
            return $this->redirect($this->viewUrl($count));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function sendEmail(StockCount $count)
    {
        $email = new StockCountRequestEmail($count);
        $this->get(MailerInterface::class)->send($email);
    }

    private function viewUrl(StockCount $count)
    {
        return $this->generateUrl('stock_count_view', [
            'id' => $count->getId(),
        ]);
    }

    /**
     * Where the location manager enters the stock counts.
     *
     * @Route("/supplier/stockCount/{id}/", name="stockcount_entry")
     * @Template("stock/count/entry.html.twig")
     */
    public function entryAction(StockCount $count, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::SUPPLIER_ADVANCED]);
        $this->denyAccessUnlessGranted(StockCountVoter::ENTRY, $count);

        $returnUrl = $this->isGranted(Role::STOCK) ?
            $this->viewUrl($count) :
            $this->generateUrl('index');
        $form = $this->createForm(StockCountEntryType::class, $count);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $count->applySelectedAllocations();
                $this->dbm->flush();
                $user = $this->getCurrentUser();
                if (!$count->isRequestedBy($user)) {
                    $email = new StockCountEntryEmail($count, $user);
                    $this->get(MailerInterface::class)->send($email);
                }
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Stock counts entered successfully.");
            return $this->redirect($returnUrl);
        }
        return [
            'form' => $form->createView(),
            'count' => $count,
            'returnUrl' => $returnUrl,
        ];
    }

    /**
     * Where the administrator approves the stock counts entered by
     * the location manager. This is when the actual stock adjustments
     * occur.
     *
     * @Route("/Stock/StockCount/{id}/approve/", name="stockcount_approve")
     * @Template("stock/count/approve.html.twig")
     */
    public function approveAction(StockCount $count, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING_OVERRIDE);
        $count->loadStockMoveHistory($this->dbm);
        $returnUrl = $this->viewUrl($count);
        $form = $this->createForm(StockCountApprovalType::class, $count);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $adjustment = new StockAdjustment($count->getMemo());
                if (null !== ($date = $form->get('adjustmentDate')->getData())) {
                    $adjustment->setDate($date);
                }
                $adjustment->setEventDispatcher($this->dispatcher());
                $count->approve($adjustment);

                if ($adjustment->hasChanges()) {
                    $adjustment->adjust($this->dbm);
                } else {
                    $this->logWarning("No changes required.");
                }
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Stock count approved.");
            return $this->redirect($returnUrl);
        }

        return [
            'form' => $form->createView(),
            'count' => $count,
            'returnUrl' => $returnUrl,
        ];
    }

    /**
     * In which the stock manager notifies an admin that a stock count is
     * ready for approval.
     *
     * @Route("/stock/count/{id}/notify", name="stockcount_notify")
     * @Template("stock/count/notify.html.twig")
     */
    public function notifyAction(StockCount $count, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $options = [
            'action' => $this->getCurrentUri(),
            'attr' => ['class' => 'standard inline'],
        ];
        $form = $this->createFormBuilder(null, $options)
            ->add('recipient', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $repo) {
                    return $repo->queryMailableByRole(Role::ACCOUNTING_OVERRIDE);
                },
                'choice_label' => 'name',
                'label' => 'Notify administrator of completed count',
                'attr' => ['class' => 'checkbox_group'],
            ])
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $from = $this->getCurrentUser();
            $to = $form->get('recipient')->getData();
            $email = new StockCountEntryEmail($count, $from, $to);
            $this->get(MailerInterface::class)->send($email);

            $this->logNotice("Email sent to $to.");
            $url = $this->viewUrl($count);
            return JsonResponse::javascriptRedirect($url);
        }
        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Upload a .csv containing the count of all stock at a specific
     * location as of a particular date.
     *
     * @Route("/stock/count/upload/", name="stock_count_upload")
     */
    public function uploadAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $count = new CsvStockCount();
        /** @var $flow CsvStockCountFlow */
        $flow = $this->get(CsvStockCountFlow::class);
        $flow->bind($count);
        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);
            /** @var $em EntityManagerInterface */
            $em = $this->getDoctrine()->getManager();
            $count->loadLevels($em);
            $count->applyAdjustments();
            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                $this->dbm->beginTransaction();
                try {
                    $count->persistNewBins($this->dbm);
                    $this->dbm->flush();

                    $adjustment = $count->createStockAdjustment();
                    $adjustment->setEventDispatcher($this->dispatcher());
                    $trans = $adjustment->adjust($this->dbm);
                    $this->dbm->flushAndCommit();
                } catch (\Exception $ex) {
                    $this->dbm->rollback();
                    throw $ex;
                }
                $flow->reset();
                $this->logNotice("Adjustments from uploaded file applied.");
                return $this->redirectToRoute('accounting_transaction_list', [
                    'sysType' => $trans->getSystemType()->getId(),
                    'groupNo' => $trans->getSystemTypeNumber(),
                ]);
            }
        }

        $stepLabel = $flow->getCurrentStepLabel();
        $template = "stock/count/$stepLabel.html.twig";

        return $this->render($template, [
            'form' => $form->createView(),
            'flow' => $flow,
            'count' => $count,
            'levels' => $count->getLevels(),
        ]);
    }
}
