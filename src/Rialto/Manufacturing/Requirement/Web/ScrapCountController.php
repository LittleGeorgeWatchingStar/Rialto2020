<?php

namespace Rialto\Manufacturing\Requirement\Web;

use Rialto\Manufacturing\Requirement\ScrapCount;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ScrapCountController extends RialtoController
{
    /**
     * @Route("/manufacturing/scrapcount/", name="scrapcount_edit")
     * @Template("manufacturing/scrapCount/scrapCount-edit.html.twig")
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::ENGINEER]);
        $list = new CountList();
        $list->counts = $this->getRepository(ScrapCount::class)
            ->findBy([], ['package' => 'asc']);

        $form = $this->createFormBuilder($list)
            ->add('counts', CollectionType::class, [
                'entry_type' => ScrapCountType::class,
            ])
            ->add('newCount', ScrapCountType::class, [
                'required' => false,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->get('delete')) {
                $id = $request->get('delete');
                $count = $list->get($id);
                $this->dbm->remove($count);
            }
            if ($list->newCount) {
                $this->dbm->persist($list->newCount);
            }
            $this->dbm->flush();
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'counts' => $list->counts,
            'form' => $form->createView(),
            'packages' => $this->getRepository(StockItem::class)->findAllPackages(),
        ];
    }

}

class CountList
{
    /**
     * @var ScrapCount[]
     *
     * @Assert\Valid(traverse=true)
     */
    public $counts;

    /**
     * @var ScrapCount
     *
     * @Assert\Valid
     */
    public $newCount;

    /** @return ScrapCount */
    public function get($id)
    {
        foreach ($this->counts as $count) {
            if ($count->getId() == $id) {
                return $count;
            }
        }
        throw new \InvalidArgumentException("No such count $id");
    }
}
