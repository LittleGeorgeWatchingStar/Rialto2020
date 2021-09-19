<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Security\Role\Role;
use Rialto\Stock\Item\BatchStockUpdater;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows batch updating of multiple items at once.
 */
class BatchUpdateController extends RialtoController
{
    /**
     * @Route("/Stock/BatchUpdate", name="Stock_BatchUpdate")
     * @Template("stock/item/batch-update.html.twig")
     */
    public function updateAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $updater = $this->getUpdater();
        $options = [
            'method' => 'get',
            'csrf_protection' => false,
        ];
        $searchForm = $this->createNamedBuilder(null, null, $options)
            ->add('matching', TextType::class, [
                'label' => 'Update items matching',
            ])
            ->add('field', ChoiceType::class, [
                'choices' => $updater->getFields(),
                'label' => 'Field to update',
            ])
            ->getForm();

        $searchForm->submit($request->query->all());
        $searchData = $searchForm->getData();
        $container = new \stdClass();
        $container->results = $updater->getInitialValues(
            $searchData['matching'],
            $searchData['field']);

        $updateForm = $this->createNamedBuilder('update', $container)
            ->add('results', CollectionType::class, [
                'entry_type' => TextType::class,
            ])
            ->getForm();

        $updateForm->handleRequest($request);
        if ( $updateForm->isSubmitted() && $updateForm->isValid() ) {
            $this->dbm->beginTransaction();
            try {
                $updater->update(
                    $searchData['field'],
                    $container->results);
                $this->dbm->flushAndCommit();
                $this->logNotice(sprintf("%s items updated successfully.",
                    number_format(count($container->results))));
                return $this->redirect($this->getCurrentUri());
            }
            catch ( \Exception $ex ) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }


        return [
            'searchForm' => $searchForm->createView(),
            'updateForm' => $updateForm->createView(),
        ];
    }

    /** @return BatchStockUpdater */
    private function getUpdater()
    {
        return $this->get(BatchStockUpdater::class);
    }
}
