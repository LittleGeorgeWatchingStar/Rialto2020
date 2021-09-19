<?php

namespace Rialto\Sales\Returns\Disposition\Web;

use Rialto\Sales\Returns\Disposition\SalesReturnResults;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form used for recording the test results of returned merchandise.
 */
class SalesReturnResultsType
extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('items', CollectionType::class, [
            'entry_type' => SalesReturnItemResultsType::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesReturnResults';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesReturnResults::class
        ]);
    }
}
