<?php

namespace Rialto\Payment\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For entering the expiration year and month of a credit card.
 */
class CreditCardExpiryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('day');
        $builder->add('day', HiddenType::class, [
            'data' => 1
        ]);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $data = $event->getData();
            if (null === $data) {
                return;
            }
            $date = new \DateTime();
            $date->setDate($data['year'], $data['month'], 1);
            $lastDay = (int) $date->format('t'); // "t" means "last day of month"
            $data['day'] = $lastDay;
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'years' => $this->getDefaultYears(),
        ]);
    }

    private function getDefaultYears()
    {
        $thisYear = (int) date('Y');
        $years = range($thisYear, $thisYear + 10);
        return array_combine($years, $years);
    }

    public function getBlockPrefix()
    {
        return 'credit_card_expiry';
    }

    public function getParent()
    {
        return DateType::class;
    }
}
