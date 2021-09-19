<?php

namespace Rialto\Accounting\Balance\Web;

use Exception;
use Gumstix\Filetype\CsvFile;
use Rialto\Accounting\Balance\AccountBalance;
use Rialto\Accounting\Balance\BalanceUpdateList;
use Rialto\Accounting\Balance\Orm\AccountBalanceRepository;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Ledger\Entry\Orm\GLEntryRepository;
use Rialto\Accounting\Period\Orm\PeriodRepository;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Period\PeriodRange;
use Rialto\Accounting\Report\BalanceSheet;
use Rialto\Accounting\Report\BalanceSheetCsv;
use Rialto\Accounting\Report\ProfitAndLossCsv;
use Rialto\Accounting\Report\ProfitAndLossReport;
use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/Accounting/AccountBalance")
 */
class AccountBalanceController extends RialtoController
{
    /**
     * @var AccountBalanceRepository
     */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(AccountBalance::class);
    }

    /**
     * @Route("/", name="account_balance_list")
     * @Method("GET")
     * @Template("accounting/balance/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $list = new EntityList($this->repo, $form->getData());
        return [
            'list' => $list,
            'form' => $form->createView(),
        ];
    }

    /**
     * Shows the balance sheet for selected periods.
     *
     * @Route("/balanceSheet", name="Accounting_BalanceSheet")
     * @Template("accounting/balance/balanceSheet.html.twig")
     */
    public function balanceSheetAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        /* @var $periodRepo PeriodRepository */
        $periodRepo = $this->getRepository(Period::class);

        $numUnposted = $this->getNumUnpostedEntries();
        $periods = new PeriodRange($periodRepo);
        $form = $this->createForm(BalanceSheetType::class, $periods, [
            'num_unposted' => $numUnposted,
        ]);
        $form->handleRequest($request);

        $sheet = new BalanceSheet($periods->getPeriods());

        if ($form->get('postEntries')->isClicked()) {
            $this->dbm->beginTransaction();
            try {
                $numPosted = $this->repo->postUnpostedEntries(
                    $form->get('closedCutoffDate')->getData());
                $this->dbm->flushAndCommit();
                $this->logNotice("Posted $numPosted GL entries successfully.");
                return $this->redirectToRoute('gl_entry_list', [
                    'posted' => 'no'
                ]);
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }
        $sheet->loadBalances($this->repo);

        if ($form->get('csv')->isClicked()) {
            $csv = new BalanceSheetCsv($sheet);
            return $this->generateCsvResponse($csv, 'BalanceSheet');
        }

        return [
            'form' => $form->createView(),
            'sheet' => $sheet,

        ];
    }

    /** @return int The number of unposted GL entries. */
    private function getNumUnpostedEntries()
    {
        /** @var GLEntryRepository $repository */
        $repository = $this->getRepository(GLEntry::class);
        return $repository->countUnposted();
    }

    /** @return Response */
    private function generateCsvResponse(CsvFile $csv, $filename)
    {
        $data = $csv->toString();
        return FileResponse::fromData($data, "$filename.csv", 'text/csv');
    }

    /**
     * Update all account balances since the selected period.
     *
     * Any new GL entries since the last repost will be added to the account
     * balances.
     *
     * @Route("/repost", name="Accounting_AccountBalance_repost")
     * @Template("accounting/balance/repost.html.twig")
     */
    public function repostAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createFormBuilder()
            ->add('period', EntityType::class, [
                'class' => Period::class,
                'query_builder' => function (PeriodRepository $repo) {
                    return $repo->queryRecent();
                },
                'label' => 'Recalculate balances beginning from period',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $period = $data['period'];
            $balances = $this->repo->findAllSincePeriod($period);
            $updateFactory = new BalanceUpdateList();
            $updateFactory->setBefore($balances);

            $this->dbm->beginTransaction();
            try {
                $updatedBalances = $this->repo->repostBalancesFromPeriod($period);
                $updateFactory->setAfter($updatedBalances);

                if ($request->get('commit')) {
                    $this->dbm->flushAndCommit();
                    $this->logNotice('Balanced reposted successfully.');
                    $uri = $this->generateUrl('index');
                    return $this->redirect($uri);
                } else { /* preview */
                    $this->dbm->rollBack();
                    return [
                        'form' => $form->createView(),
                        'updates' => $updateFactory->getUpdates(),
                    ];
                }
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Show the profit and loss report for selected periods.
     *
     * @Route("/profitAndLoss/", name="Accounting_ProfitAndLoss")
     * @Template("accounting/balance/profitAndLoss.html.twig")
     */
    public function profitAndLossAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        /* @var $periodRepo PeriodRepository */
        $periodRepo = $this->getRepository(Period::class);
        $numUnposted = $this->getNumUnpostedEntries();
        $report = new ProfitAndLossReport($periodRepo);
        $form = $this->createForm(ProfitAndLossType::class, $report, [
            'num_unposted' => $numUnposted,
        ]);
        $form->handleRequest($request);

        $report->loadPeriods();

        if ($form->get('postEntries')->isClicked()) {
            $this->dbm->beginTransaction();
            try {
                $numPosted = $this->repo->postUnpostedEntries(
                    $form->get('closedCutoffDate')->getData());
                $this->dbm->flushAndCommit();
                $this->logNotice("Posted $numPosted GL entries successfully.");
                return $this->redirectToRoute('gl_entry_list', [
                    'posted' => 'no'
                ]);
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }
        $report->loadBalances($this->repo);

        if ($form->get('csv')->isClicked()) {
            $csv = new ProfitAndLossCsv($report);
            return $this->generateCsvResponse($csv, 'ProfitAndLoss');
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

}
