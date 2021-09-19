<?php

namespace Rialto\Web\Form;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormError;

/**
 * This event is dispatched for each error in a form. Listeners can
 * call setHtml() to provide additional info (eg, helpful links) to a
 * form error message.
 */
class FormErrorEvent extends Event
{
    const NAME = 'rialto.form.handle_error';

    /**
     * @var FormError The error that caused this event.
     */
    private $error;

    /**
     * @var string The supplementary HTML or text that a listener wants to
     *   provide for this error.
     */
    private $html = '';

    public function __construct(FormError $error)
    {
        $this->error = $error;
    }

    /** @return string */
    public function getMessageTemplate()
    {
        return $this->error->getMessageTemplate();
    }

    /**
     * @return mixed The object or data that is invalid.
     */
    public function getFormData()
    {
        return $this->error->getOrigin()->getData();
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }
}
