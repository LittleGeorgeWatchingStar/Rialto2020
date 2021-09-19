<?php

namespace Rialto\Stock\Bin\Web;

use FOS\RestBundle\View\View;
use Rialto\Database\Orm\EntityList;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\Web\ListFilterType;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BinStyleController extends RialtoController
{
    /**
     * List all available bin styles (eg, reel, tube, fabpack).
     *
     * @Route("/api/v2/stock/binstyle/")
     *
     * @api for Geppetto Client
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::PURCHASING]);
        $styles = $this->getRepository(BinStyle::class)->findAll();
        return View::create(BinStyleDetail::fromList($styles));
    }

    /**
     * Batch update the bin styles of purchasing data records.
     *
     * @Route("/stock/binstyle/update-purchdata/", name="binstyle_update_purchdata")
     * @Template("stock/binstyle/update-purchdata.html.twig")
     */
    public function updatePurchasingDataAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::PURCHASING]);
        $filterForm = $this->createForm(ListFilterType::class);
        $repo = $this->dbm->getRepository(PurchasingData::class);
        $filterForm->submit($request->query->all());
        $list = new EntityList($repo, $filterForm->getData());


        $updateForm = $this->createFormBuilder()
            ->add('style', BinStyleType::class, [
                'label' => 'Update bin style of the above records to:',
                'placeholder' => '-- choose --',
            ])
            ->getForm();

        $updateForm->handleRequest($request);
        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $newStyle = $updateForm->get('style')->getData();
                foreach ($list as $pd) {
                    /** @var $pd PurchasingData */
                    $pd->setBinStyle($newStyle);
                }
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice("Bin styles updated to '$newStyle'.");
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'filterForm' => $filterForm->createView(),
            'list' => $list,
            'updateForm' => $updateForm->createView(),
        ];
    }
}
