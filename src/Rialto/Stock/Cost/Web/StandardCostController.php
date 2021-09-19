<?php

namespace Rialto\Stock\Cost\Web;

use Rialto\Purchasing\Catalog\Template\PurchasingDataTemplate;
use Rialto\Security\Role\Role;
use Rialto\Stock\Cost\Orm\StandardCostRepository;
use Rialto\Stock\Cost\StandardCost;
use Rialto\Stock\Cost\StandardCostHints;
use Rialto\Stock\Cost\StandardCostUpdater;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing the standard cost of a stock item.
 */
class StandardCostController extends RialtoController
{
    /**
     * Updates the standard cost and shows the history of standard cost changes.
     *
     * @Route("/stock/item/{item}/standard-cost/", name="item_standard_cost")
     * @Route("/Stock/StockItem/{stockCode}/standardCost",
     *   name="Stock_StandardCost_update")
     * @Template("stock/standard-cost/update.html.twig")
     */
    public function updateAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        /** @var $repo StandardCostRepository */
        $repo = $this->dbm->getRepository(StandardCost::class);
        $costs = $repo->findAllByItem($item);
        $stdCost = new StandardCost($item);
        $stdCost->setMaterialCost($item->getMaterialCost());
        $stdCost->setLabourCost($item->getLabourCost());
        $stdCost->setOverheadCost($item->getOverheadCost());

        $form = $this->createForm(StandardCostType::class, $stdCost);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $updater StandardCostUpdater */
            $updater = $this->get(StandardCostUpdater::class);
            $this->dbm->beginTransaction();
            try {
                $updater->update($stdCost);
                $this->dbm->flushAndCommit();
                return $this->redirect($this->getCurrentUri());
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        $hints = new StandardCostHints($this->dbm, $stdCost);
        return [
            'item' => $item,
            'form' => $form->createView(),
            'costs' => $costs,
            'hints' => $hints,
        ];
    }

    /**
     * In which we audit the standard cost of items against their actual
     * purchase cost.
     *
     * @Route("/Stock/StandardCost/audit", name="Stock_StandardCost_audit")
     * @Method("GET")
     * @Template("stock/standard-cost/audit.html.twig")
     */
    public function auditAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $filters = [
            'current' => 'yes',
            '_limit' => 100,
        ];
        $options = [
            'method' => 'get',
            'csrf_protection' => false,
        ];
        $form = $this->createNamedBuilder(null, $filters, $options)
            ->add('_limit', IntegerType::class, [
                'label' => 'Max records per page'
            ])
            ->add('_page', IntegerType::class, [ // deprecated; use _start instead
                'required' => false,
                'label' => 'Page No'
            ])
            ->add('matching', TextType::class, [
                'required' => false,
            ])
            ->getForm();
        $form->submit($request->query->all(), false);
        $filters = $form->getData();

        /** @var $repo StandardCostRepository */
        $repo = $this->getRepository(StandardCost::class);
        $matching = $repo->findByFilters($filters);
        $dbm = $this->dbm;

        // TODO: This was a hotfix for auditing modules, this should be removed ASAP.
        $tempRepo = $this->getRepository(PurchasingDataTemplate::class);
        /** @var PurchasingDataTemplate $temp */
        $temp = $tempRepo->find(10);
        $results = array_map(function (StandardCost $stdCost) use ($dbm, $temp) {
            return new StandardCostHints($dbm, $stdCost, $temp);
        }, $matching);

        return [
            'form' => $form->createView(),
            'results' => $results,
        ];
    }
}
