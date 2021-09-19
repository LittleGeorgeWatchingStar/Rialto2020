<?php

namespace Rialto\Accounting\Bank\Transaction\Web;

use Doctrine\ORM\EntityRepository;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Bank\Transaction\CancelCheque;
use Rialto\Accounting\Bank\Transaction\Orm\BankTransactionRepository;
use Rialto\Accounting\Bank\Transaction\PdfChequeGenerator;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\SystemTypeRepository;
use Rialto\Database\Orm\EntityList;
use Rialto\Filing\Entry\Orm\EntryRepository;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BankTransactionController extends RialtoController
{
    /**
     * @Route("/accounting/banktransaction/", name="banktransaction_list")
     * @Template("accounting/banktrans/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $form = $this->createForm(BankTransactionFilterType::class);
        $form->submit($request->query->all());
        /** @var EntryRepository $repo */
        $repo = $this->getRepository(BankTransaction::class);
        $list = new EntityList($repo, $form->getData());
        return [
            'form' => $form->createView(),
            'list' => $list,
        ];
    }

    /**
     * @Route("/accounting/banktransaction/{id}/", name="bank_transaction_view")
     * @Method("GET")
     * @Template("accounting/banktrans/view.html.twig")
     */
    public function viewAction(BankTransaction $transaction)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        return ['entity' => $transaction];
    }

    /**
     * Print any unprinted cheques.
     *
     * @Route("/Accounting/BankTransaction/printCheques/",
     *   name="Accounting_printCheques")
     * @Template("accounting/banktrans/printCheques.html.twig")
     */
    public function printChequesAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $validTypes = [
            SystemType::CREDITOR_PAYMENT,
            SystemType::CUSTOMER_REFUND,
        ];
        $requestedType = $request->get('type');
        if (!in_array($requestedType, $validTypes)) {
            $requestedType = SystemType::CREDITOR_PAYMENT;
        }
        /** @var FormInterface $form */
        $form = $this->createFormBuilder()
            ->add('bankAccount', EntityType::class, [
                'class' => BankAccount::class,
            ])
            ->add('systemType', EntityType::class, [
                'class' => SystemType::class,
                'query_builder' => function (SystemTypeRepository $repo) use ($validTypes) {
                    return $repo->createQueryBuilder('st')
                        ->andWhere('st.id in (:types)')
                        ->setParameter('types', $validTypes);
                },
                'label' => 'Type',
                'data' => SystemType::fetch($requestedType, $this->dbm),
            ])
            ->add('chequeNumbers', TextType::class, [
                'required' => false,
                'label' => 'Cheque numbers to print',
                'attr' => [
                    'placeholder' => 'all unprinted',
                ],
                'label_attr' => [
                    'title' => 'comma-separated',
                    'class' => 'tooltip',
                ]
            ])
            ->add('upswing', IntegerType::class, [
                'data' => 3,
            ])
            ->add('rightswing', IntegerType::class, [
                'data' => 0,
            ])
            ->add('preview', SubmitType::class)
            ->add('pdf', SubmitType::class, [
                'label' => 'Preview PDF',
            ])
            ->getForm();

        if ($request->isMethod('post')) {
            $form->add('printCheques', SubmitType::class);
        }

        $cheques = null;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $bankAccount = $form->get('bankAccount')->getData();
            $sysType = $form->get('systemType')->getData();
            $chequeNos = array_filter(array_map('trim', explode(
                ',', $form->get('chequeNumbers')->getData())));
            $cheques = $this->getRepo()->findChequesToPrint($bankAccount, $sysType, $chequeNos);

            if (count($cheques) > 0) {
                if ($form->get('pdf')->isClicked()) {
                    return $this->renderPdfResponse($form, $cheques, 'CheckPreview.pdf');
                } elseif ($form->get('printCheques')->isClicked()) {
                    $response = $this->renderPdfResponse($form, $cheques, 'CheckPrintout.pdf');
                    foreach ($cheques as $cheque) {
                        $cheque->setPrinted();
                    }
                    $this->dbm->flush();
                    return $response;
                }
            }
        }

        return [
            'form' => $form->createView(),
            'cheques' => $cheques,
        ];
    }

    /** @return BankTransactionRepository|EntityRepository */
    private function getRepo()
    {
        return $this->getRepository(BankTransaction::class);
    }

    /** @return Response */
    private function renderPdfResponse(FormInterface $form, array $cheques, $filename)
    {
        $upswing = $form->get('upswing')->getData();
        $rightswing = $form->get('rightswing')->getData();
        $generator = new PdfChequeGenerator($this->getCurrentUser()->getDefaultPageSize());
        $pdfData = $generator->generateCheques($cheques, $upswing, $rightswing);
        return PdfResponse::create($pdfData, $filename);
    }

    /**
     * @Route("/Accounting/BankTransaction/{id}/cancelCheque",
     *   name="Accounting_BankTransaction_cancelCheque")
     * @Method("POST")
     */
    public function cancelChequeAction(BankTransaction $bankTrans)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $company = $this->getDefaultCompany();
        $cancelService = new CancelCheque($this->dbm, $company);
        $error = $cancelService->validateCheque($bankTrans);
        if ($error) {
            throw $this->badRequest($error);
        }

        $this->dbm->beginTransaction();
        try {
            $cancelService->cancel($bankTrans);
            $this->dbm->flushAndCommit();
            $this->logNotice(sprintf("Cancelled cheque #%s.",
                $bankTrans->getChequeNumber()
            ));

            return $this->redirectToRoute('bank_transaction_view', [
                'id' => $bankTrans->getId(),
            ]);
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }
}
