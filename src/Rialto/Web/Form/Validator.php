<?php

namespace Rialto\Web\Form;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * The form validator allows to you validate an object in phases and associate
 * any additional errors with the original form.
 */
class Validator
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate(FormInterface $form, $object, $groups = null)
    {
        $groups = $this->prepGroups($groups);
        $violations = $this->validator->validate($object, null, $groups);
        foreach ($violations as $violation) {
            $form->addError($this->convertViolation($violation));
        }
    }

    private function prepGroups($groups = null)
    {
        if (!$groups) $groups = ['Default'];
        if (!is_array($groups)) $groups = [$groups];
        return $groups;
    }

    private function convertViolation(ConstraintViolationInterface $violation)
    {
        return new FormError(
            $violation->getMessageTemplate(),
            $violation->getParameters()
        );
    }
}
