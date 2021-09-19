<?php

namespace Rialto\Summary\Menu\Web;


use Rialto\Summary\Menu\LazySummaryLink;
use Rialto\Summary\Menu\Summary;
use Rialto\Summary\Menu\SummaryNode;
use Twig\Extension\AbstractExtension;
use Twig_Test;

class SummaryExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return [
            new Twig_Test('parent_node', function (Summary $summary) {
                return $summary instanceof SummaryNode;
            }),
            new Twig_Test('lazy_node', function (Summary $summary) {
                return $summary instanceof LazySummaryLink;
            }),
        ];
    }
}
