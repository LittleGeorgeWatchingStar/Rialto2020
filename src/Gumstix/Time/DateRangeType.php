<?php

namespace Gumstix\Time;


use Rialto\Time\Web\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for selecting date ranges.
 */
class DateRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', DateType::class, [
                'required' => false,
                'label' => isset($options['start_label']) ? $options['start_label'] : 'Between', // todo: php7
            ])
            ->add('end', DateType::class, [
                'required' => false,
                'label' => isset($options['end_label']) ? $options['end_label'] : 'and', // todo: php7
            ]);
        $builder->addModelTransformer($this->createTransformer());
    }

    private function createTransformer()
    {
        return new CallbackTransformer(
            function ($dateRange) {
                /** @var $dateRange DateRange */
                if (!$dateRange) {
                    return [];
                }
                if (!$dateRange instanceof DateRange) {
                    throw new UnexpectedTypeException($dateRange, DateRange::class);
                }
                return [
                    'start' => $dateRange->getStart(),
                    'end' => $dateRange->getEnd(),
                ];
            },
            function ($formData) {
                if (count($formData) === 0) {
                    return null;
                }
                if (!is_array($formData)) {
                    throw new UnexpectedTypeException($formData, 'array');
                }
                return DateRange::create()
                    ->withStart($formData['start'])
                    ->withEnd($formData['end']);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', null);
        $resolver->setDefault('label', false);
        $resolver->setDefined('start_label');
        $resolver->setDefined('end_label');
    }


}
