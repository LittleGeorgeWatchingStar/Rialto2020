<?php

namespace Rialto\Purchasing\Invoice\Parser;

use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Describes how to parse a particular field in a CSV document.
 */
class Field
{
    const TYPE_CONSTANT = 'const';
    const TYPE_PATTERN = 'pattern';
    const TYPE_DATE = 'date';

    /**
     * @Type("string")
     * @Assert\NotBlank(message="Every field must have a name.")
     */
    public $name;

    /**
     * @Type("string")
     * @Assert\NotBlank(message="Every field must have a type.")
     */
    public $type;

    /** @Type("string") */
    public $text;

    /** @Type("string") */
    public $prefix;

    /** @Type("string") */
    public $pattern;

    /** @Type("boolean") */
    public $required = true;

    /**
     * @var Position[]
     * @Type("array<Rialto\Purchasing\Invoice\Parser\Position>")
     * @Assert\Valid(traverse=true)
     */
    public $positions = [];

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function __toString()
    {
        return "field '{$this->name}'";
    }

    public function addPosition($x, $y)
    {
        $this->positions[] = new Position($x, $y);
    }

    public function isValid($value)
    {
        if ( $this->required ) {
            return null !== $value;
        }
        return true;
    }

    public function isConstant()
    {
        return $this->isType(self::TYPE_CONSTANT);
    }

    protected function isType($type)
    {
        return $type == $this->type;
    }

    /** @Assert\Callback */
    public function validateText(ExecutionContextInterface $context)
    {
        if ( $this->isConstant() && (! $this->text) ) {
            $context->buildViolation("Text is required for $this.")
                ->atPath('text')
                ->addViolation();
        }
    }

    /** @Assert\Callback */
    public function validatePattern(ExecutionContextInterface $context)
    {
        $required = $this->isType(self::TYPE_DATE) ||
            $this->isType(self::TYPE_PATTERN);
        if ( $required && (! $this->pattern) ) {
            $context->buildViolation("Pattern is required for $this.")
                ->atPath('pattern')
                ->addViolation();
        }
    }

    public function matchesPrefix($string)
    {
        if (! $this->prefix ) {
            return true;
        }
        $regex = $this->prefixToRegex();
        return preg_match($regex, $string);
    }

    private function prefixToRegex()
    {
        $regex = $this->prefix;
        $regex = str_replace('/', '\\/', $regex);
        return "/($regex)/";
    }

    public function stripPrefix($string)
    {
        if (! $this->prefix ) {
            return $string;
        }
        $regex = $this->prefixToRegex();
        return preg_replace($regex, '', $string);
    }
}
