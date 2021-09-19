<?php

namespace Rialto\Web;


use Twig_Filter;
use Twig_Function;

trait TwigExtensionTrait
{
    /**
     * Convenience method to create a simple filter that calls a method of this
     * class.
     *
     * @param string[] $safeFormats
     */
    protected function simpleFilter(string $name,
                                    string $methodName,
                                    array $safeFormats): Twig_Filter
    {
        return new Twig_Filter($name, [$this, $methodName], [
            'is_safe' => $safeFormats
        ]);
    }

    /**
     * Convenience method to create a simple function that calls a method of
     * this class.
     *
     * @param string $name
     * @param string $methodName
     * @param string[] $safeFormats
     * @return Twig_Function
     */
    protected function simpleFunction(string $name,
                                      string $methodName,
                                      array $safeFormats): Twig_Function
    {
        return new Twig_Function($name, [$this, $methodName], [
            'is_safe' => $safeFormats,
        ]);
    }

    protected function none(): string
    {
        return '<span class="null">none</span>';
    }

    protected function link(string $url, string $label, string $target = null): string
    {
        $label = htmlentities($label);
        if ($target) {
            return sprintf('<a href="%s"
                                  target="%s">%s</a>', $url, $target, $label);
        } else {
            return sprintf('<a href="%s">%s</a>', $url, $label);
        }
    }
}
