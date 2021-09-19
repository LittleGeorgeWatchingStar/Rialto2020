<?php

namespace Rialto\Accounting\Terms\Web;


use Rialto\Accounting\Terms\PaymentTerms;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddPaymentTermsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class, [
            'mapped' => false, // constructor argument
            'attr' => ['placeholder' => 'new...'],
        ]);
    }

    public function getParent()
    {
        return PaymentTermsType::class;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            $id = $form->get('id')->getData();
            return $id ? new PaymentTerms($id) : null;
        });
    }
}
