<?php

namespace Rialto\Purchasing\Manufacturer\Web;

use FOS\RestBundle\View\View;
use Gumstix\Storage\StorageException;
use Rialto\Database\Orm\EntityList;
use Rialto\Purchasing\Manufacturer\ComplianceFilesystem;
use Rialto\Purchasing\Manufacturer\LogoFilesystem;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Purchasing\Manufacturer\Orm\ManufacturerRepository;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for working with Manufacturers.
 *
 * @see Manufacturer
 */
class ManufacturerController extends RialtoController
{
    /**
     * @Route("/purchasing/manufacturer/", name="part_manufacturer_list")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        $form = $this->createForm(ManufacturerListFilterType::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(Manufacturer::class);
        $results = new EntityList($repo, $form->getData());

        return View::create(ManufacturerSummary::fromList($results))
            ->setTemplate("purchasing/manufacturer/list.html.twig")
            ->setTemplateData([
                'form' => $form->createView(),
                'list' => $results,
                'filename' => 'Gumstix_manufacturers'
            ]);
    }

    /**
     * @Route("/purchasing/manufacturer/logos/", name="part_manufacturer_logos")
     * @Method("GET")
     */
    public function listLogosAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        $form = $this->createForm(ManufacturerListFilterType::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(Manufacturer::class);
        $results = new EntityList($repo, $form->getData());

        return View::create(ManufacturerSummary::fromList($results))
            ->setTemplate("purchasing/manufacturer/listLogos.html.twig")
            ->setTemplateData([
                'form' => $form->createView(),
                'list' => $results,
                'logos' => $this->encodedLogoMap($results->getIterator()),
            ]);
    }

    /**
     * @param Manufacturer[]|\Traversable $manufacturers
     * @return string[]
     */
    private function encodedLogoMap($manufacturers): array
    {
        $fs = $this->getLogoFilesystem();
        $logos = [];
        foreach ($manufacturers as $manufacturer) {
            if (!$manufacturer->hasLogoFile()) continue;
            try {
                $logos[$manufacturer->getId()] =
                    base64_encode($fs->getFileContents($manufacturer));
            } catch (StorageException $exception) {
                $this->logError(
                    "Manufacturer $manufacturer has a logo set but is unobtainable.");
            }
        }

        return $logos;
    }

    /**
     * @Route("/purchasing/add-manufacturer/",
     *   name="part_manufacturer_create")
     * @Method({"GET", "POST"})
     * @Template("purchasing/manufacturer/edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING_DATA);
        $manufacturer = new Manufacturer();
        return $this->handleForm($manufacturer, $request, 'Created');
    }

    private function handleForm(Manufacturer $manufacturer,
                                Request $request,
                                $updated = 'Updated')
    {
        $returnUri = $this->generateUrl('part_manufacturer_list', [
            '_limit' => 0,
        ]);

        $form = $this->createForm(ManufacturerType::class, $manufacturer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($manufacturer);
            $manufacturer->setUpdated($this->getCurrentUser());
            $this->dbm->flush();
            $fs = $this->getComplianceFilesystem();
            $fs->saveConflictFile($manufacturer);
            $this->saveLogo($manufacturer);
            $this->dbm->flush();
            $this->logNotice("$updated manufacturer $manufacturer successfully.");
            return $this->redirect($returnUri);
        }

        return [
            'manufacturer' => $manufacturer,
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'cancelUri' => $returnUri,
        ];
    }

    /**
     * @Route("/purchasing/manufacturer/{id}/set-logo/",
     *     name="part_manufacturer_set_logo")
     * @Method({"GET", "POST"})
     * @Template("purchasing/manufacturer/setLogo.html.twig")
     */
    public function setLogoAction(Manufacturer $manufacturer, Request $request)
    {
        $returnUri = $this->generateUrl('part_manufacturer_logos', [
            '_limit' => 0,
        ]);

        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        $form = $this->createForm(ManufacturerLogoType::class, $manufacturer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($manufacturer);
            $this->dbm->flush();
            $this->saveLogo($manufacturer);
            $this->dbm->flush();
            $this->logNotice("Set logo for $manufacturer successfully.");
            return $this->redirect($returnUri);
        }
        return [
            'manufacturer' => $manufacturer,
            'cancelUri' => $returnUri,
            'form' => $form->createView(),
        ];
    }

    /** @return ComplianceFilesystem */
    private function getComplianceFilesystem()
    {
        return $this->get(ComplianceFilesystem::class);
    }

    private function getLogoFilesystem(): LogoFilesystem
    {
        return $this->get(LogoFilesystem::class);
    }

    private function saveLogo(Manufacturer $manufacturer)
    {
        $this->getLogoFilesystem()->saveLogoFile($manufacturer);
    }

    private function getLogo(Manufacturer $manufacturer): string
    {
        return $this->getLogoFilesystem()->getFileContents($manufacturer);
    }

    /**
     * @Route("/purchasing/manufacturer/{id}/",
     *   name="part_manufacturer_edit")
     * @Method({"GET", "POST"})
     * @Template("purchasing/manufacturer/edit.html.twig")
     */
    public function editAction(Manufacturer $manufacturer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING_DATA);
        if ($request->getRequestFormat() == 'json') {
            return View::create([
                'id' => $manufacturer->getId(),
                'name' => $manufacturer->getName(),
            ]);
        }
        return $this->handleForm($manufacturer, $request);
    }

    /**
     * @Route("/purchasing/manufacturer/{id}/conflict-file/",
     *   name="part_manufacturer_download_conflict")
     * @Method("GET")
     */
    public function downloadConflictFileAction(Manufacturer $manufacturer)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        $filename = $manufacturer->getConflictFilename();
        if (!$filename) {
            throw $this->notFound();
        }
        $fs = $this->getComplianceFilesystem();
        $contents = $fs->getFileContents($manufacturer);
        return FileResponse::fromData($contents, $filename);
    }

    /**
     * @Route("/purchasing/manufacturer/{id}/logo/",
     *     name="part_manufacturer_download_logo")
     * @Method("GET")
     */
    public function downloadLogoFileAction(Manufacturer $manufacturer)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        $filename = $manufacturer->getLogoFilename();
        if (!$filename) throw $this->notFound();

        $contents = $this->getLogo($manufacturer);
        return FileResponse::fromData($contents, $filename);
    }

    /**
     * @Route("/purchasing/manufacturer/{id}/",
     *     name="part_manufacturer_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Manufacturer $man)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING_DATA);
        /** @var $repo ManufacturerRepository */
        $repo = $this->getRepository(Manufacturer::class);
        if ($repo->isInUse($man)) {
            $msg = "$man is associated with existing purchasing data and cannot be deleted.";
            $this->logError($msg);
            return $this->redirectToRoute('part_manufacturer_list');
        }

        $msg = "$man deleted successfully.";
        $this->dbm->remove($man);
        $this->dbm->flush();
        $this->logNotice($msg);
        return $this->redirectToRoute('part_manufacturer_list');
    }
}
