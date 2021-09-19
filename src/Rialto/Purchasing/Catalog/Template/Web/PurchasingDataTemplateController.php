<?php

namespace Rialto\Purchasing\Catalog\Template\Web;

use Exception;
use FOS\RestBundle\View\View;
use Rialto\Purchasing\Catalog\Template\PurchasingDataStrategy;
use Rialto\Purchasing\Catalog\Template\PurchasingDataTemplate;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use UnexpectedValueException;

/**
 * Controller for managing purchasing data templates, which can
 * generate purchasing data automatically for new stock items.
 *
 * @see PurchasingDataStrategy
 * @see PurchasingDataTemplate
 */
class PurchasingDataTemplateController extends RialtoController
{
    /**
     * @Route("/purchasing/data-template/", name="purchasing_datatemplate_list")
     * @Method("GET")
     * @Template("purchasing/datatemplate/pdt-list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $templates = $this->getRepository(PurchasingDataTemplate::class)
            ->findAll();
        return [
            'templates' => $templates,
        ];
    }

    /**
     * @Route("/Purchasing/PurchasingDataTemplate",
     *   name="Purchasing_PurchasingDataTemplate_create")
     * @Template("purchasing/datatemplate/pdt-edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $template = new PurchasingDataTemplate();
        return $this->processForm($template, $request, 'created');
    }

    /**
     * @Route("/Purchasing/PurchasingDataTemplate/{id}",
     *   name="Purchasing_PurchasingDataTemplate_edit")
     * @Template("purchasing/datatemplate/pdt-edit.html.twig")
     */
    public function editAction(PurchasingDataTemplate $template, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        return $this->processForm($template, $request, 'updated');
    }

    private function processForm(PurchasingDataTemplate $template, Request $request, $updated)
    {
        $returnUri = $this->getDefaultReturnUri();

        /** @var FormFactoryInterface $formFactory */
        $formFactory = $this->get(FormFactoryInterface::class);

        /**
         * Indexed by strategy name.
         * @var FormInterface[] $strategyForms
         */
        $strategyForms = [];
        foreach (PurchasingDataStrategy::getStrategyNames() as $strategyName) {
            $strategyForms[$strategyName] = $formFactory->createNamed(
                $strategyName,
                PurchasingDataTemplateType::class,
                $template,
                ['strategy' => PurchasingDataStrategy::create($strategyName)]);
        }

        $isAFormSubmittedAndValid = false;
        foreach ($strategyForms as $form) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $isAFormSubmittedAndValid = true;
                break;
            }
        }

        if ($isAFormSubmittedAndValid) {
            $this->dbm->persist($template);
            $this->dbm->flush();
            $id = $template->getId();
            $this->logNotice("Template $id $updated successfully.");
            return $this->redirect($returnUri);
        }

        // Make sure if form is submitted and invalid, the selected strategy is
        // set correctly.
        foreach ($strategyForms as $strategyName => $form) {
            if ($form->isSubmitted()) {
                $template->setStrategy($strategyName);
                break;
            }
        }
        $choices = [];
        foreach (PurchasingDataStrategy::getStrategyInstances() as $instance) {
            $description = $instance->getDescription();
            $name = $instance->getName();
            $choices[$description] = $name;

        }
        $strategySelectForm = $formFactory->createNamedBuilder(
            'strategy',
            FormType::class,
            $template)
            ->add('strategy', ChoiceType::class, [
                'choices' => $choices,
            ])
            ->getForm();

        return [
            'template' => $template,
            'strategySelectForm' => $strategySelectForm->createView(),
            'forms' => array_map(function (FormInterface $form) {
                return $form->createView();
            }, $strategyForms),
            'cancelUri' => $returnUri,
        ];
    }

    private function getDefaultReturnUri()
    {
        return $this->generateUrl('purchasing_datatemplate_list');
    }

    /**
     * @Route("/record/Purchasing/PurchasingDataTemplate/{id}",
     *   name="Purchasing_PurchasingDataTemplate_delete")
     * @Method("DELETE")
     */
    public function deleteAction(PurchasingDataTemplate $template)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $id = $template->getId();
        $this->dbm->remove($template);
        $this->dbm->flush();
        $this->logNotice("Template $id deleted successfully.");
        return $this->redirect($this->getDefaultReturnUri());
    }

    /**
     * Automatically create a PurchasingData record from the matching template
     * for the stock item.
     *
     * @Route("/api/v2/stock/item/{stockCode}/version/{version}/purchasingdata/")
     * @Method("POST")
     *
     * @api for Geppetto Client
     */
    public function autoCreateAction(StockItem $item, $version)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $version = $item->getVersion($version);
        $repo = $this->getRepository(PurchasingDataTemplate::class);
        /* @var $templates PurchasingDataTemplate[] */
        $templates = $repo->findAll();

        $this->dbm->beginTransaction();
        try {
            foreach ($templates as $template) {
                if ($template->appliesTo($item)) {
                    if ($template->alreadyExists($version, $this->dbm)) {
                        throw $this->badRequest(
                            "Purchasing data already exists for {$version->getSku()}");
                    }

                    $purchData = $template->createFor($version);
                    $validator = $this->get(ValidatorInterface::class);
                    $violations = $validator->validate($purchData);
                    if (count($violations) == 0) {
                        $this->dbm->persist($purchData);
                        $this->dbm->flushAndCommit();
                        return View::create(null, Response::HTTP_CREATED);
                    } else {
                        throw new UnexpectedValueException(
                            "Purchasing data strategy for $item is invalid: $violations");
                    }
                }
            }
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        throw $this->badRequest("No purchasing data template exists for " .
            $version->getFullSku());
    }
}
