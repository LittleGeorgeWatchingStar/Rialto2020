<?php

namespace Rialto\Accounting\PaymentTransaction\Web;

use DateTime;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Rialto\Accounting\Period\Period;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Common base class for Supplier- and DebtorTransactionController classes.
 */
abstract class PaymentTransactionController extends RialtoController
{
    /**
     * Implements the deletion steps that are common to both
     * supplier and debtor transactions.
     */
    protected function deleteHelper(PaymentTransaction $trans, $returnUri, Request $request)
    {
        $bankTrans = $trans->getBankTransactions();
        $glEntries = $trans->getGLEntries();

        if ($request->get('confirm')) {
            /* Capture the record IDs before they all get deleted. */
            $messages = [
                'transaction' => $trans->getId(),
                'bank transactions:' => $this->createIdList($bankTrans),
                'GL entries:' => $this->createIdList($glEntries),
            ];

            $this->dbm->beginTransaction();
            try {
                foreach ($bankTrans as $bt) {
                    $this->dbm->remove($bt);
                }
                foreach ($glEntries as $entry) {
                    $this->dbm->remove($entry);
                }
                $this->dbm->remove($trans);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            foreach ($messages as $type => $ids) {
                if ($ids) {
                    $this->logNotice("Deleted $type $ids.");
                }
            }

            return $this->redirect($returnUri);
        }

        return $this->render("accounting/paytrans/delete.html.twig", [
            'entity' => $trans,
            'entries' => $glEntries,
            'bankTransactions' => $bankTrans,
            'cancelUrl' => $this->viewUrl($trans),
        ]);
    }

    private function createIdList(array $entities)
    {
        $ids = array_map(function ($entity) {
            return $entity->getId();
        }, $entities);
        return join(', ', $ids);
    }

    /**
     * Implements the steps that are common to changing the date for both
     * supplier and debtor transactions.
     */
    protected function changeDateHelper(PaymentTransaction $trans, Request $request)
    {
        $data = ['newDate' => $trans->getDate()];
        $form = $this->createFormBuilder($data)
            ->add('newDate', DateType::class, [
                'label' => 'New date',
            ])
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                /** @var $newDate DateTime */
                $newDate = $data['newDate'];
                assertion($newDate instanceof DateTime);

                $newPeriod = Period::findForDate($newDate, $this->dbm);

                $bankTrans = $trans->getBankTransactions();
                $glEntries = $trans->getGLEntries();

                $this->dbm->beginTransaction();
                try {
                    foreach ($bankTrans as $bt) {
                        $bt->setDate($newDate);
                    }
                    foreach ($glEntries as $entry) {
                        $entry->changeDate($newDate, $newPeriod);
                    }
                    // TODO: will not work for DebtorTransaction
                    $trans->setDate($newDate);
                    $this->dbm->flushAndCommit();
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }

                $messages = [
                    'transaction' => $trans->getId(),
                    'bank transactions:' => $this->createIdList($bankTrans),
                    'GL entries:' => $this->createIdList($glEntries),
                ];
                foreach ($messages as $type => $ids) {
                    if ($ids) {
                        $this->logNotice(sprintf("Changed date of %s %s to %s.",
                            $type,
                            $ids,
                            $newDate->format('F j, Y')));
                    }
                }

                return JsonResponse::javascriptRedirect($this->viewUrl($trans));
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        return $this->render(
            "core/form/dialogForm.html.twig", [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ]);
    }

    /**
     * @return string The record view URL
     */
    protected abstract function viewUrl(PaymentTransaction $transaction);

}
