<?php

namespace Rialto\Panelization\Web;

use Rialto\Panelization\UnplacedBoard;
use Rialto\Panelization\UnplacedBoardWithQty;
use Rialto\Stock\Item\Version\Web\ItemVersionSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoardOfPanelType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'BoardOfPanel';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemVersion', ItemVersionSelectorType::class, [
                'attr' => ['class' => 'itemVersions']
            ])
            ->add('boardsPerPanel', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UnplacedBoardWithQty::class,
            'validation_groups' => ['Default', 'dimensions'],
        ]);
    }
}
