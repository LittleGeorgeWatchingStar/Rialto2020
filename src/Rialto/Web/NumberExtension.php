<?php

namespace Rialto\Web;


use Twig\Extension\AbstractExtension;

/**
 * Twig extension for formatting numbers.
 */
class NumberExtension extends AbstractExtension
{
    use TwigExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_Filter('number', 'number_format'),
            $this->simpleFilter('money', 'money', []),
            new \Twig_Filter('min', 'min'),
            new \Twig_Filter('max', 'max'),
            new \Twig_Filter('round', 'round'),
            new \Twig_Filter('difference', function ($value, $decimalPlaces = 0) {
                $prefix = $value > 0 ? '+' : '';
                return $prefix . number_format($value, $decimalPlaces);
            }),
        ];
    }

    public function money($amount, $decimalPlaces = 2, $symbol = '$')
    {
        $pattern = '%s%s';
        if (round($amount, $decimalPlaces) == 0) {
            // Prevent the user from seeing "-0".
            $amount = 0;
        }
        if ($amount < 0) {
            // Accounting format for -5.25 is (5.25).
            $pattern = '(%s%s)';
            $amount = abs($amount);
        }
        return sprintf($pattern, $symbol, number_format($amount, $decimalPlaces));
    }
}
