<?php

namespace Rialto\Printing\Printer\Web;


use Rialto\Printing\Printer\Printer;
use Rialto\Printing\Printer\PrinterException;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class PrinterController extends RialtoController
{
    /**
     * @Route("/print/printers/", name="printers_edit")
     * @Template("prints/Printer/printer-edit.html.twig")
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $data = [
            'printers' => $this->getRepository(Printer::class)->findAll()
        ];

        $form = $this->createFormBuilder($data)
            ->add('printers', CollectionType::class, [
                'entry_type' => PrinterType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'constraints' => new Assert\Valid(['traverse' => true]),
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $this->logNotice("Printers updated successfully.");
            return $this->redirectToRoute('printers_edit');
        }
        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Test the connection to the printer.
     *
     * @Route("/print/printers/{printer}/test/", name="printer_test")
     * @Method("POST")
     */
    public function testAction(Printer $printer)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        try {
            $printer->open();
            $printer->close();
        } catch (PrinterException $ex) {
            return new Response($ex->getMessage());
        }
        return new Response('OK');
    }

    /**
     * @Route("/print/printers/add", name="printer_add")
     * @Template("prints/Printer/printer-add.html.twig")
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createForm(CreatePrinterType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $printer = $form->getData();
                $this->dbm->persist($printer);
                $this->dbm->flushAndCommit();
                $this->logNotice(ucfirst("$printer created successfully."));
                return $this->redirectToRoute('printers_edit');
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'form' => $form->createView()
        ];
    }


    /**
     * @Route("/print/printers/delete/{printer}",
     *   name="printer_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Printer $printer)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $DesignatedPrinter = $this->dbm->need(Printer::class, $printer->getId());
        $this->dbm->remove($DesignatedPrinter);
        $this->dbm->flush();

        $msg = "Deleted $printer successfully.";
        $this->logNotice($msg);
        return $this->redirect($this->generateUrl('printers_edit'));
    }
}
