<?php

namespace Rialto\Accounting\Bank\Transfer;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\SystemTypeRepository;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Security\Role\Role;
use Rialto\Time\Web\DateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

final class BankTransferController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SystemTypeRepository */
    private $sysTypeRepo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->sysTypeRepo = $em->getRepository(SystemType::class);
    }

    /**
     * @Route("/accounting/banktransfer/new/", name="banktransfer_create")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $form = $this->createFormBuilder()
            ->add('fromAccount', EntityType::class, [
                'class' => BankAccount::class,
                'label' => 'From Account',
            ])
            ->add('toAccount', EntityType::class, [
                'class' => BankAccount::class,
                'label' => 'To Account',
            ])
            ->add('date', DateType::class)
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BankAccount $from */
            $from = $form->getData()['fromAccount'];

            /** @var BankAccount $to */
            $to = $form->getData()['toAccount'];

            $amount = $form->getData()['amount'];

            $date = $form->getData()['date'];

            $glTrans = new Transaction($this->sysTypeRepo->find(SystemType::BANK_TRANSFER));
            $glTrans->setDate($date);

            $transfer = BankTransfer::create($glTrans, $from, $to, $amount);
            $this->em->persist($glTrans);
            $this->em->persist($transfer);
            $this->em->flush();

            return $this->redirectToRoute('banktransfer_view', [
                'id' => $transfer->getId(),
            ]);
        }

        return $this->render('accounting/banktrans/banktransfer-create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/accounting/banktransfer/{id}/", name="banktransfer_view")
     */
    public function viewAction(BankTransfer $transfer)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        return $this->render('accounting/banktrans/banktransfer-view.html.twig', [
            'transfer' => $transfer,
        ]);
    }
}
