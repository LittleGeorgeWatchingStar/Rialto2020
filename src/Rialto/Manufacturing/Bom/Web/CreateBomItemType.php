<?php

namespace Rialto\Manufacturing\Bom\Web;


use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Component\Web\ReferenceDesignatorType;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating a new BomItem.
 *
 * @see BomItem
 */
class CreateBomItemType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'CreateBomItem';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('component', JsEntityType::class, [
            'class' => StockItem::class,
            'property' => 'stockCode',
            'mapped' => false
        ]);

        $builder->add('quantity', IntegerType::class);
        $builder->add('designators', ReferenceDesignatorType::class, [
            'attr' => ['rows' => 5, 'cols' => 50],
            'required' => false,
        ]);
        $builder->add('workType', EntityType::class, [
            'class' => WorkType::class,
            'placeholder' => '-- choose --',
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
}
