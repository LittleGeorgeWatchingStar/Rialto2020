<?php

namespace Rialto\Manufacturing\Bom\Web;

use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Item\Version\Web\VersionTextType;
use Rialto\Web\Form\TextEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for adding a BOM item via the API.
 * @see BomItem
 */
class BomItemApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('component', TextEntityType::class, [
            'class' => StockItem::class,
            'mapped' => false,
        ]);
        $builder->add('version', VersionTextType::class, [
            'required' => false,
            'empty_data' => Version::any(),
        ]);
        $builder->add('quantity', IntegerType::class);
        $builder->add('designators', CollectionType::class, [
            'entry_type' => TextType::class,
            'required' => false,
            'by_reference' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ]);
        $builder->add('workType', EntityType::class, [
            'class' => WorkType::class,
            'empty_data' => function(FormInterface $form) {
                /** @var StockItem $component */
                $component = $form->getParent()->get('component')->getData();

                if ($component && $component->getDefaultWorkType()) {
                    return $component->getDefaultWorkType()->getId();
                } else {
                    return WorkType::SMT;
                }
            },
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BomItem::class,
            'empty_data' => function(FormInterface $form) {
                $component = $form->get('component')->getData();
                if (! $component ) {
                    throw new TransformationFailedException("Component is required");
                }
                return new BomItem($component);
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'BomItem';
    }
}
