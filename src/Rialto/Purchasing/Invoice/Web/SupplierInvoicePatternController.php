<?php

namespace Rialto\Purchasing\Invoice\Web;

use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface as JmsSerializer;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for managing supplier invoice patterns.
 *
 * @see SupplierInvoicePattern
 */
class SupplierInvoicePatternController extends RialtoController
{
    /** @var JmsSerializer */
    private $serializer;

    /** @var ValidatorInterface */
    private $validator;

    protected function init(ContainerInterface $container)
    {
        $this->serializer = $this->get(JmsSerializer::class);
        $this->validator = $this->get(ValidatorInterface::class);
    }

    /**
     * @Route("/Purchasing/SupplierInvoicePattern/{id}",
     *   name="Purchasing_SupplierInvoicePattern_edit")
     * @Template("purchasing/invoice/pattern/pattern-edit.html.twig")
     */
    public function editAction(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $pattern = $this->dbm->find(SupplierInvoicePattern::class, $supplier->getId());
        $new = false;
        if (!$pattern) {
            $pattern = new SupplierInvoicePattern($supplier);
            $new = true;
        }

        $form = $this->createForm(SupplierInvoicePatternType::class, $pattern);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($pattern);
            $this->dbm->flush();
            $this->logNotice("Invoice pattern for $supplier updated successfully.");
            $uri = $request->get('finished') ?
                $this->supplierUrl($supplier) :
                $this->getCurrentUri();
            return $this->redirect($uri);
        }

        return [
            'form' => $form->createView(),
            'supplier' => $supplier,
            'cancelUri' => $this->supplierUrl($supplier),
            'new' => $new,
        ];
    }

    private function supplierUrl(Supplier $supplier)
    {
        return $this->generateUrl('supplier_view', [
            'supplier' => $supplier->getId(),
        ]);
    }

    /**
     * @Route("/Purchasing/SupplierInvoicePattern/{id}/clone",
     *   name="Purchasing_SupplierInvoicePattern_clone")
     * @Method("POST")
     */
    public function cloneAction(Supplier $toSupplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $pattern = $this->dbm->find(SupplierInvoicePattern::class, $request->get('supplier'));
        $newPattern = new SupplierInvoicePattern($toSupplier);
        $newPattern->setFormat($pattern->getFormat());
        $newPattern->setKeyword($pattern->getKeyword());
        $newPattern->setLocation($pattern->getLocation());
        $newPattern->setSender($pattern->getSender());
        $newPattern->setSplitPattern($pattern->getSplitPattern());
        $newPattern->setParseRules($pattern->getParseRules($this->serializer), $this->serializer);

        $this->dbm->persist($newPattern);
        $this->dbm->flush();

        $uri = $this->generateUrl('Purchasing_SupplierInvoicePattern_edit', [
            'id' => $toSupplier->getId(),
        ]);
        return JsonResponse::javascriptRedirect($uri);
    }

    /**
     * @Route("/Purchasing/SupplierInvoicePattern/{id}/definition/",
     *   name="Purchasing_SupplierInvoicePattern_definition",
     *   options={"expose"=true})
     * @Method({"GET", "PUT"})
     */
    public function definitionAction(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        /** @var SupplierInvoicePattern $pattern */
        $pattern = $this->dbm->find(SupplierInvoicePattern::class, $supplier->getId());
        if (!$pattern) {
            return View::create([])->setFormat('json');
        }
        if ($request->isMethod("PUT")) {
            $json = $request->getContent();
            $rules = $pattern->getParseRules($this->serializer, $json);
            $errors = $this->validator->validate($rules);
            if (count($errors) > 0) {
                return JsonResponse::fromValidationErrors($errors);
            }
            $pattern->setParseRules($rules, $this->serializer);
            $this->dbm->flush();
            return View::create(null)->setFormat('json');
        }
        return new Response($pattern->getRawParseRules());
    }

}
