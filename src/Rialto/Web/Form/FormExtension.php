<?php

namespace Rialto\Web\Form;

use Rialto\Web\TwigExtensionTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Twig\Extension\AbstractExtension;

/**
 * Twig extensions for manually rendering forms.
 *
 * Only use these if Symfony's Form components won't do the trick!
 */
class FormExtension extends AbstractExtension
{
    use TwigExtensionTrait;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function getFunctions()
    {
        return [
            $this->simpleFunction('rialto_form_select_options', 'selectOptions', ['html']),
        ];
    }

    public function selectOptions(array $options, $selected = null)
    {
        $output = '';
        foreach ( $options as $value => $label ) {
            $value = htmlspecialchars($value);
            $label = htmlspecialchars($label);
            $s = ( $value == $selected ) ? 'selected' : '';
            $output .= "<option value=\"$value\" $s>$label</option>";
        }
        return $output;
    }

    public function getFilters()
    {
        return [
            $this->simpleFilter('form_error_handler', 'formErrorHandler', ['html']),
        ];
    }

    /**
     * This filter dispatches an event for each form error. It allows
     * subscribers to append additional HTML at the end of the error message.
     *
     * This is useful for, eg, guiding users toward a solution.
     *
     * @return string
     */
    public function formErrorHandler(FormError $error)
    {
        $event = new FormErrorEvent($error);
        $this->dispatcher->dispatch($event::NAME, $event);
        return $event->getHtml();
    }
}
