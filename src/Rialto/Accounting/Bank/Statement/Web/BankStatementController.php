<?php

namespace Rialto\Accounting\Bank\Statement\Web;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;
use Exception;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Account\Repository\BankAccountRepository;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Statement\BankStatementPattern;
use Rialto\Accounting\Bank\Statement\Match\CustomerPaymentStrategy;
use Rialto\Accounting\Bank\Statement\Match\MatchStrategy;
use Rialto\Accounting\Bank\Statement\Match\NullStrategy;
use Rialto\Accounting\Bank\Statement\Orm\BankStatementRepository;
use Rialto\Accounting\Bank\Statement\Parser\ParseResult;
use Rialto\Accounting\Debtor\DebtorTransactionFactory;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Period\Orm\PeriodRepository;
use Rialto\Accounting\Period\Period;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Security\Role\Role;
use Rialto\Time\Web\DateType;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing bank statements.
 */
class BankStatementController extends RialtoController
{
    /**
     * @Route("/Accounting/BankStatement/",
     *   name="Accounting_BankStatement_list")
     * @Template("accounting/bankstatement/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $period = Period::fetchCurrent($this->dbm);
        $options = ['csrf_protection' => false];
        $filterForm = $this->createNamedBuilder(null, null, $options)
            ->add('period', EntityType::class, [
                'class' => Period::class,
                'query_builder' => function (PeriodRepository $repo) {
                    return $repo->createQueryBuilder('p')
                        ->orderBy('p.endDate', 'desc');
                },
                'data' => $period,
            ])
            ->add('bankAccount', EntityType::class, [
                'class' => BankAccount::class,
                'required' => true,
            ])
            ->getForm();

        $statements = [];
        if ($request->get('period')) {
            $filterForm->submit($request->query->all());
            $period = $filterForm->get('period')->getData();
            /** @var BankAccount $account */
            $account = $filterForm->get('bankAccount')->getData();
            $repo = $this->getStatementRepo();
            $statements = $repo->findByPeriod($period, $account);
        }

        if ($request->get('_format') == 'pdf') {
            $endDate = $period->getEndDate();
            /* @var $generator PdfGenerator */
            $generator = $this->get(PdfGenerator::class);
            $pdfData = $generator->render(
                'accounting/bankstatement/list.tex.twig', [
                'statements' => $statements,
                'periodEndDate' => $endDate,
            ]);
            return $this->getPdfResponse($pdfData, $endDate);
        }

        return [
            'statements' => $statements,
            'filterForm' => $filterForm->createView(),
        ];
    }

    /** @return BankStatementRepository|ObjectRepository */
    private function getStatementRepo()
    {
        return $this->getRepository(BankStatement::class);
    }

    private function getPdfResponse($pdfData, DateTime $endDate)
    {
        $endDateDashes = $endDate->format('m-d-Y');
        $filename = "bankReconciliationStatementEnding$endDateDashes";
        return PdfResponse::create($pdfData, $filename);
    }

    /**
     * @Route("/accounting/bank-statement/{id}/", name="bank_statement_view")
     * @Method("GET")
     * @Template("accounting/bankstatement/view.html.twig")
     */
    public function viewAction(BankStatement $statement)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        return ['entity' => $statement];
    }

    /**
     * Load bank statement lines from an uploaded CSV file.
     *
     * @Route("/Accounting/BankStatement/load/",
     *  name="Accounting_BankStatement_load")
     * @Template("accounting/bankstatement/load.html.twig")
     */
    public function loadAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $form = $this->createForm(BankStatementLoadType::class);

        $results = [];
        $form->handleRequest($request);
        $numAdded = $numSkipped = 0;
        if ($form->isSubmitted() && $form->isValid()) {
            $results = BankStatementLoadType::parse($form, $this->dbm);
            $this->dbm->flush();
            [$skipped, $added] = (new ArrayCollection($results))
                ->partition(function ($i, ParseResult $r) {
                    return $r->isSkipped();
                });
            $numAdded = count($added);
            $numSkipped = count($skipped);
        }

        return [
            'form' => $form->createView(),
            'results' => $results,
            'numAdded' => $numAdded,
            'numSkipped' => $numSkipped,
        ];
    }

    /**
     * Reconcile uploaded bank statements with their corresponding transactions.
     *
     * @Route("/Accounting/BankStatement/match",
     *   name="Accounting_BankStatement_match")
     * @Template("accounting/bankstatement/match.html.twig")
     */
    public function matchAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $filterForm = $this->createFilterForm();
        $filters = $this->getFilters($filterForm, $request);
        $statements = $this->findMatchingBankStatements($filters);
        $patterns = $this->getPatterns();

        $ourCompany = $this->getDefaultCompany();
        /** @var $debtorTransFactory DebtorTransactionFactory */
        $debtorTransFactory = $this->get(DebtorTransactionFactory::class);

        /** @var BankAccountRepository $bankAccountRepo */
        $bankAccountRepo = $this->get(BankAccountRepository::class);

        $strategies = [];
        /* @var $strategies MatchStrategy[] */
        foreach ($statements as $statement) {
            foreach ($patterns as $pattern) {
                if ($pattern->matchesStatement($statement)) {
                    $strategy = $pattern->createStrategy($statement, $this->dbm,
                        $bankAccountRepo);
                    if (! $strategy->hasMatchingRecords()) {
                        continue;
                    }
                    $strategy->setCompany($ourCompany);
                    if ($strategy instanceof CustomerPaymentStrategy) {
                        $strategy->setDebtorTransactionFactory($debtorTransFactory);
                    }
                    $strategies[$statement->getId()] = $strategy;

                    continue 2; /* go to next statement */
                }
            }
            $strategy = new NullStrategy($statement, $this->dbm,
                $this->get(BankAccountRepository::class));
            $strategies[$statement->getId()] = $strategy;
        }

        $updateForm = $this->createUpdateForm($strategies);

        if ($request->isMethod('POST')) {
            $updateForm->handleRequest($request);
            if ($request->get('applyUpdates') && $updateForm->isValid()) {
                $this->dbm->beginTransaction();
                try {
                    foreach ($strategies as $strategy) {
                        $strategy->save();
                    }
                    $this->dbm->flushAndCommit();
                    $this->logNotice('Success!');
                    return $this->redirect($this->getCurrentUri());
                } catch (Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }
            }
        }

        return [
            'filterForm' => $filterForm->createView(),
            'matches' => $strategies,
            'updateForm' => $updateForm->createView(),
        ];
    }

    /** @return BankStatementPattern[] */
    private function getPatterns()
    {
        $repo = $this->getRepository(BankStatementPattern::class);
        return $repo->findAll();
    }

    /** @return FormInterface */
    private function createFilterForm()
    {
        $options = ['csrf_protection' => false, 'method' => 'get'];
        $builder = $this->createNamedBuilder(null, null, $options);
        $builder->add('bankAccount', EntityType::class, [
            'class' => BankAccount::class,
            'required' => true,
        ]);
        $builder->add('since', DateType::class, [
            'label' => 'Show statements since',
        ]);
        $builder->add('sort', ChoiceType::class, [
            'choices' => [
                'newest first' => 'newest first',
                'oldest first' => 'oldest first',
                'description' => 'description',
                'amount' => 'amount',
            ],
            'label' => 'Sort by',
        ]);
        return $builder->getForm();
    }

    private function getFilters(FormInterface $filterForm, Request $request)
    {
        $since = new DateTime('-3 months');
        $defaults = [
            'since' => $since->format('Y-m-d'),
            'sort' => 'newest first',
            'bankAccount' => GLAccount::REGULAR_CHECKING_ACCOUNT
        ];
        $filterInputs = array_merge($defaults, $request->query->all());
        $filterForm->submit($filterInputs);
        return $filterForm->getData();
    }

    /** @return BankStatement[] */
    private function findMatchingBankStatements(array $filters)
    {
        $since = $filters['since'];
        $sort = $filters['sort'];
        $bankAccount = $filters['bankAccount'] ?? null;
        /** @var $repo BankStatementRepository */
        $repo = $this->getRepository(BankStatement::class);
        return $repo->findOutstandingSince($since, $sort, $bankAccount);
    }

    /**
     * @param $strategies MatchStrategy[]
     * @return FormInterface
     */
    private function createUpdateForm(array $strategies)
    {
        $container = new \stdClass();
        $container->strategies = $strategies;

        $builder = $this->createFormBuilder($container)
            ->add('strategies', CollectionType::class, [
                'entry_type' => MatchStrategyType::class,
                'label' => false,
            ]);
        return $builder->getForm();
    }

}
