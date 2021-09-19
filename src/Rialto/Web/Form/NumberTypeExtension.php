<?php

namespace Rialto\Web\Form;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Render NumberType as type="number", but ONLY if the step attribute is set.
 * Otherwise, HTML5 assumes step=1, which is often NOT what we want!
 */
class NumberTypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return NumberType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $attr = $this->addDefaultStep($view->vars['attr'], $options);
        $view->vars['attr'] = $attr;
        if (isset($attr['step'])) {
            $view->vars['type'] = 'number';
        }
    }

    /**
     * Adds a default value for the HTML5 "step" attribute based on the
     * "scale" option.
     */
    private function addDefaultStep(array $attr, $options)
    {
        if (isset($attr['step'])) {
            return $attr;
        }
        if (isset($options['scale']) && is_int($options['scale'])) {
            $scale = (int) $options['scale'];
            $attr['step'] = 10 ** -$scale;
        }
        return $attr;
    }
}
