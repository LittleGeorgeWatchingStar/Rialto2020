<?php

namespace Rialto\Supplier\Order\Web;

use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Time\Web\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for entering the commitment date of a purchase order detail.
 */
class CommitmentDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('commitmentDate', DateType::class, [
            'error_bubbling' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'CommitmentDate';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockProducer::class
        ]);
    }

}
