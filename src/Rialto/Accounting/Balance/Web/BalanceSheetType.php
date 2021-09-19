<?php

namespace Rialto\Accounting\Balance\Web;


use DateTime;
use Rialto\Accounting\Period\Web\PeriodRangeType;
use Rialto\Time\Web\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BalanceSheetType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function getParent()
    {
        return PeriodRangeType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $numUnposted = $options['num_unposted'];
        $builder->add('closedCutoffDate', DateType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Cutoff date for closed entries',
                'data' => $this->defaultCutoffDate(),
                'disabled' => true,
            ])
            ->add('submit', SubmitType::class)
            ->add('csv', SubmitType::class, [
                'label' => 'Download CSV'
            ])
            ->add('postEntries', SubmitType::class, [
                'label' => sprintf('Post %s unposted entries and submit',
                    number_format($numUnposted)),
                'attr' => [
                    'disabled' => $numUnposted > 0 ? false : true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('num_unposted');
    }

    private function defaultCutoffDate(): DateTime
    {
        return new DateTime('first day of this month midnight');
    }
}
