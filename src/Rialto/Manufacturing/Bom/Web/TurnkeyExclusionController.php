<?php

namespace Rialto\Manufacturing\Bom\Web;

use Exception;
use Rialto\Manufacturing\Bom\TurnkeyExclusions;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Where the user chooses which items are excluded from a turnkey build.
 *
 * A "turnkey" build in one in which the manufacturer provides most of the
 * components. "Exclusions" are those items that the manufacturer does NOT
 * provide -- we must provide them.
 */
class TurnkeyExclusionController extends RialtoController
{
    /**
     * @Route("/Manufacturing/TurnkeyExclusions/{id}/",
     *   name="Manufacturing_TurnkeyExclusions_edit")
     * @Template("manufacturing/turnkeyExclusion/turnkey-edit.html.twig")
     */
    public function editAction(PurchasingData $purchData, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::MANUFACTURING]);
        $parent = $purchData->getStockItem();
        if (! $parent->isManufactured()) {
            throw $this->badRequest("Item is not manufactured.");
        }
        $supplier = $purchData->getSupplier();
        $location = $this->findSupplierLocation($supplier);
        $exclusions = new TurnkeyExclusions($this->dbm, $parent, $location);

        $form = $this->createFormBuilder($exclusions)
            ->add('exclusions', EntityType::class, [
                'class' => StockItem::class,
                'choices' => $exclusions->getComponents(),
                'expanded' => true,
                'multiple' => true,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $exclusions->save();
                $this->dbm->flushAndCommit();
                $this->logNotice('Exclusions updated successfully.');
                return $this->redirect($this->getCurrentUri());
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'company' => $this->getDefaultCompany(),
            'parent' => $parent,
            'location' => $location,
            'exclusions' => $exclusions,
            'form' => $form->createView(),
            'cancelUri' => $this->generateUrl('purchasing_data_edit', [
                'id' => $purchData,
            ]),
        ];
    }

    /** @return Facility */
    private function findSupplierLocation(Supplier $supplier)
    {
        /** @var $repo FacilityRepository */
        $repo = $this->dbm->getRepository(Facility::class);
        $location = $repo->findBySupplier($supplier);
        if (! $location) {
            throw $this->badRequest('Supplier is not a manufacturer');
        }
        return $location;
    }
}
