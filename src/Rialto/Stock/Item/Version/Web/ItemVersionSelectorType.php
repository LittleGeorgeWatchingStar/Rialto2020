<?php

namespace Rialto\Stock\Item\Version\Web;

use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Orm\ItemVersionRepository;
use Rialto\Web\Form\JsEntityChoiceLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Use Javascript and AJAX to select an ItemVersion based on user input.
 */
class ItemVersionSelectorType extends AbstractType
{
    /** @var ItemVersionRepository */
    private $repo;

    public function __construct(ItemVersionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_loader' => function (Options $options) {
                $transformer = new ItemVersionToSkuTransformer($this->repo);
                return new JsEntityChoiceLoader($transformer);
            },
            'choice_label' => function (Options $options) {
                return function (ItemVersion $choice = null) {
                    return $choice ? $choice->getFullSku() : '';
                };
            },
            'attr' => ['class' => 'js-entity'],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'item_version_selector';
    }
}
