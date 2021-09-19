<?php

namespace Rialto\Purchasing\Receiving\Auth;

use Rialto\Stock\Facility\Facility;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CanReceiveIntoValidator extends ConstraintValidator
{
    /** @var AuthorizationCheckerInterface */
    private $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param Facility $value The value that should be validated
     * @param CanReceiveInto $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$this->auth->isGranted(ReceiveIntoVoter::RECEIVE_INTO, $value)) {
            $this->context->addViolation($constraint->message, [
                '{{ facility }}' => $value->getName(),
            ]);
        }
    }
}
