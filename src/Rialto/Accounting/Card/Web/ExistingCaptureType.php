<?php

namespace Rialto\Accounting\Card\Web;


use Rialto\Accounting\Card\CardTransaction;
use Rialto\Time\Web\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * For recording in Rialto an existing card capture that was already
 * sent to the payment gateway.
 */
class ExistingCaptureType extends AbstractType
{
    public function getParent()
    {
        return CaptureType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CardTransaction $cardTrans */
        $cardTrans = $options['trans'];
        $builder
            ->add('date', DateTimeType::class, [
                'with_seconds' => true,
                'data' => $cardTrans->getDateCreated(),
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\DateTime(),
                ],
            ]);
    }
}
