<?php

namespace Rialto\Accounting\Balance\Web;


use DateTime;
use DateTimeZone;
use Rialto\Accounting\Period\Web\PeriodRangeType;
use Rialto\Accounting\Report\ProfitAndLossReport;
use Rialto\Time\Web\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfitAndLossType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $numUnposted = $options['num_unposted'];
        $builder
            ->add('periods', PeriodRangeType::class, [
                'label' => false,
            ])
            ->add('periodLength', IntegerType::class)
            ->add('closedCutoffDate', DateType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Cutoff date for closed entries',
                'data' => $this->defaultCutoffDate(),
                'disabled' => true,
            ])
            ->add('submit', SubmitType::class)
            ->add('csv', SubmitType::class, [
                'label' => 'Download CSV',
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
        $resolver->setDefaults([
            'data_class' => ProfitAndLossReport::class,
            'csrf_protection' => false,
        ]);
    }

    private function defaultCutoffDate(): DateTime
    {
        return new DateTime('first day of this month midnight');
    }
}
