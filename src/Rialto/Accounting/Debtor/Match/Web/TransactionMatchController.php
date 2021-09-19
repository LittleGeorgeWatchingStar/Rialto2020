<?php

namespace Rialto\Accounting\Debtor\Match\Web;

use DateTime;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Debtor\DebtorTransactionFactory;
use Rialto\Accounting\Debtor\Match\ManyToManyDebtorTransactionMatcher;
use Rialto\Accounting\Debtor\Match\OneToOneDebtorTransactionMatcher;
use Rialto\Accounting\Debtor\Match\TransactionMatch;
use Rialto\Accounting\Debtor\Orm\DebtorTransactionRepository;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * For matching off debtor credits against invoices.
 */
class TransactionMatchController extends RialtoController
{
    const ALLOCATE_MAX_RECORDS = 500;

    /**
     * For allocating credits against invoices en masse.
     *
     * @Route("/debtor/transaction-match/", name="debtor_transaction_match")
     * @Template("accounting/debtor/transaction/match.html.twig")
     */
    public function matchAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $queryForm = $this->createQueryForm();

        if ($request->get('filter')) {
            $queryForm->handleRequest($request);
        }

        $filters = $queryForm->getData();
        $transactions = $this->loadTransactions($filters);
        $matches = $this->findMatches($transactions, $filters);
        $postForm = $this->createPostForm($matches);

        $postForm->handleRequest($request);
        if ($postForm->isSubmitted() && $postForm->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->processPost($request, $matches);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'queryForm' => $queryForm->createView(),
            'matches' => $matches,
            'postForm' => $postForm->createView(),
        ];
    }

    private function createQueryForm(): FormInterface
    {
        $defaultData = [
            'since' => $this->getDefaultStartDate(),
            'exact' => false,
            'strategy' => 'many',
        ];

        return $this->createForm(MatchQueryType::class, $defaultData);
    }

    private function getDefaultStartDate(): DateTime
    {
        return new DateTime('-1 year');
    }

    private function loadTransactions(array $filters)
    {
        /* @var $repo DebtorTransactionRepository */
        $repo = $this->getRepository(DebtorTransaction::class);

        $customer = null;
        if (!empty($filters['customer'])) {
            $customer = $filters['customer'];
        }
        if (empty($filters['since'])) {
            $filters['since'] = $this->getDefaultStartDate();
        }

        $transactions = $repo->findForAllocationMatching(
            $filters['since'],
            $customer,
            self::ALLOCATE_MAX_RECORDS
        );

        if (count($transactions) == self::ALLOCATE_MAX_RECORDS) {
            $this->logWarning(sprintf(
                'Only the first %s matching transactions are shown.',
                number_format(self::ALLOCATE_MAX_RECORDS)
            ));
        }
        return $transactions;
    }

    /**
     * @return TransactionMatch[]
     */
    private function findMatches(array $transactions, array $filters)
    {
        if ($filters['strategy'] == 'one') {
            $strategy = new OneToOneDebtorTransactionMatcher();
        } else {
            $strategy = new ManyToManyDebtorTransactionMatcher();
        }

        $matches = $strategy->findMatches($transactions);
        if (!empty($filters['exact'])) {
            $matches = $strategy->filterExactMatches($matches);
        }
        return $matches;
    }

    /**
     * @param TransactionMatch[] $matches
     */
    private function createPostForm(array $matches): FormInterface
    {
        $container = new \stdClass();
        $container->matches = $matches;

        $options = ['error_bubbling' => true];

        return $this->createNamedBuilder(
            'allocation', $container, $options)
            ->add('matches', CollectionType::class, [
                'entry_type' => TransactionMatchType::class,
            ])
            ->getForm();
    }

    /**
     * @param TransactionMatch[] $matches
     */
    private function processPost(Request $request, array $matches)
    {
        if ($request->get('batchAllocate')) {
            $total = 0;
            foreach ($matches as $match) {
                if ($match->isSelected()) {
                    $total += $match->createAllocations();
                }
            }
            $this->logNotice(sprintf(
                'Allocated $%s in total.', number_format($total, 2)
            ));
        } elseif ($request->get('createTransferFee')) {
            $indexKey = $request->get('createTransferFee');
            $match = $matches[$indexKey];
            $fee = $match->createTransferFee();
            $factory = $this->get(DebtorTransactionFactory::class);
            $debtorTrans = $factory->createCredit($fee);
            $this->dbm->persist($debtorTrans);
            $this->logNotice(sprintf('Created %s for transfer fee.',
                $fee->getLabel()
            ));
        } elseif ($request->get('allocateNow')) {
            $indexKey = $request->get('allocateNow');
            $match = $matches[$indexKey];
            $amount = $match->createAllocations();
            $customer = $match->getCustomer();
            $this->logNotice(sprintf(
                'Allocated $%s for %s.',
                number_format($amount, 2),
                $customer->getName()
            ));
        }
    }

}
