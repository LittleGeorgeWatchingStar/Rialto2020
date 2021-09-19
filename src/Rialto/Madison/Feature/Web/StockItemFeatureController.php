<?php

namespace Rialto\Madison\Feature\Web;


use FOS\RestBundle\View\View;
use Rialto\Madison\Feature\Repository\StockItemFeatureRepository;
use Rialto\Madison\Feature\StockItemFeature;
use Rialto\Madison\Feature\StockItemFeatureCalculator;
use Rialto\Madison\MadisonClient;
use Rialto\Manufacturing\Bom\BomException;
use Rialto\Purchasing\Manufacturer\LogoFilesystem;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class StockItemFeatureController extends RialtoController
{
    /**
     * Edit the immediate features of a stock item.
     *
     * By "immediate features" we mean those that are not inherited through
     * the BOM.
     *
     * @Route("/stock/item/{stockCode}/features/",
     *   name="stock_item_edit_features")
     * @Template("madison/feature/item-edit.html.twig")
     */
    public function editAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);

        $features = $this->getRepo()->findByItem($item);

        /** @var FormInterface $form */
        $form = $this->createFormBuilder(['features' => $features])
            ->add('features', CollectionType::class, [
                'label' => false,
                'entry_type' => EditType::class,
            ])
            ->add('addFeature', CreateType::class, ['item' => $item])
            ->add('submit', SubmitType::class, [
                'label' => 'Update features',
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleDeletions($form);
            $this->handleInsertions($form);
            $this->dbm->flush();
            return $this->redirect($this->getCurrentUri());
        }

        $calculator = $this->get(StockItemFeatureCalculator::class);
        try {
            $inherited = $calculator->getInheritedFeatures($item, Version::any());
        } catch (BomException $ex) {
            $this->logException($ex);
            return $this->redirectToRoute('item_version_edit', [
                'item' => $ex->getSku(),
                'version' => (string) $ex->getItemVersion(),
            ]);
        }
        $api = $this->getApiClient();
        return [
            'features' => $api->getFeatures(),
            'item' => $item,
            'form' => $form->createView(),
            'inherited' => $inherited,
        ];
    }

    /** @return StockItemFeatureRepository */
    private function getRepo()
    {
        return $this->get(StockItemFeatureRepository::class);
    }

    private function handleDeletions(FormInterface $form)
    {
        foreach ($form->get('features') as $subform) {
            if ($subform->get('delete')->isClicked()) {
                $feature = $subform->getData();
                $this->dbm->remove($feature);
                return;
            }
        }
    }

    private function handleInsertions(FormInterface $form)
    {
        $feature = $form->get('addFeature')->getData();
        if ($feature) {
            $this->dbm->persist($feature);
        }
    }

    /** @return MadisonClient|object */
    private function getApiClient()
    {
        return $this->get(MadisonClient::class);
    }

    /**
     * Edit all items that have a particular feature.
     *
     * @Route("/stock/feature/{featureCode}/items/",
     *   name="stock_feature_edit_items")
     * @Template("madison/feature/item-byFeature.html.twig")
     */
    public function byFeatureAction($featureCode, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $api = $this->getApiClient();
        $all = $api->getFeatures();
        if (empty($all[$featureCode])) {
            throw $this->notFound("No such feature $featureCode");
        }
        $feature = $all[$featureCode];

        $features = $this->getRepo()->findByFeatureCode($featureCode);
        /** @var FormInterface $form */
        $form = $this->createFormBuilder(['features' => $features])
            ->add('features', CollectionType::class, [
                'label' => false,
                'entry_type' => EditType::class,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Update features',
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleDeletions($form);
            $this->dbm->flush();
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'feature' => $feature,
            'form' => $form->createView(),
        ];
    }

    /**
     * Get all features, immediate and inherited, of a stock item.
     *
     * @Route("/api/stock/item/{stockCode}/all-features/")
     * @Route("/api/v2/stock/item/{stockCode}/all-features/")
     *
     * @api for Madison
     */
    public function getAllAction(StockItem $item)
    {
        $features = $this->getAllFeaturesForItem($item);
        return View::create(FeatureSummary::fromList($features));
    }

    /**
     * Get the logo associated with manufacturer features for a stock item.
     *
     * @Route("/api/v2/stock/item/{stockCode}/manufacturer-logo/")
     */
    public function getManufacturerImages(StockItem $item)
    {
        $feature = $this->getManufacturerFeature($item);
        if (!$feature) return View::create('');

        $repo = $this->getManufacturerRepo();
        $fs = $this->getLogoFilesystem();

        /** @var Manufacturer|null $manufacturer */
        $manufacturer = $repo->findOneBy([
            'name' => $feature->getValue(),
        ]);

        if ($manufacturer === null) {
            return View::create('');
        }

        $logo = $manufacturer->hasLogoFile() ?
            base64_encode($fs->getFileContents($manufacturer)) : '';


        return View::create($logo);
    }

    private function getManufacturerRepo()
    {
        return $this->getRepository(Manufacturer::class);
    }

    private function getLogoFilesystem(): LogoFilesystem
    {
        return $this->get(LogoFilesystem::class);
    }

    /**
     * @param StockItem $item
     * @return StockItemFeature[]
     */
    private function getAllFeaturesForItem(StockItem $item): array
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $calculator = $this->get(StockItemFeatureCalculator::class);
        return $calculator->getFeatures($item, Version::any());
    }

    /**
     * @param StockItem $item
     * @return StockItemFeature|null
     */
    private function getManufacturerFeature(StockItem $item)
    {
        return array_values(array_filter($this->getAllFeaturesForItem($item),
            function (StockItemFeature $feature) {
                return $feature->getFeatureCode() === 'manufacturer';
            }))[0] ?? null;
    }
}
