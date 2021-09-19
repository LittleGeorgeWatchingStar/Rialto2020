<?php

namespace Rialto\Purchasing\Invoice\Parser;

use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A set of rules for parsing a CSV document.
 */
class RuleSet
{
    /** @Type("string") */
    public $start;

    /** @Type("string") */
    public $end;

    /** @Type("string") */
    public $lineStart;

    /** @Type("string") */
    public $lineEnd;

    /**
     * @var Header[]
     * @Type("array<Rialto\Purchasing\Invoice\Parser\Header>")
     * @Assert\Valid(traverse=true)
     */
    public $headers = [];

    /**
     * @var Field[]
     * @Type("array<Rialto\Purchasing\Invoice\Parser\Field>")
     * @Assert\Valid(traverse=true)
     */
    public $lines = [];

    /** @return Header */
    public function addHeader($name, $type)
    {
        $header = new Header($name, $type);
        $this->headers[] = $header;
        return $header;
    }

    /** @return Field */
    public function addLine($name, $type)
    {
        $line = new Field($name, $type);
        $this->lines[] = $line;
        return $line;
    }

    public function hasLines()
    {
        return count($this->lines) > 0;
    }

    public function isStart($string)
    {
        return $this->contains($string, $this->start);
    }

    public function isEnd($string)
    {
        return $this->contains($string, $this->end);
    }

    /** @Assert\Callback */
    public function validateEnd(ExecutionContextInterface $context)
    {
        if ($this->start && (! $this->end)) {
            $context->buildViolation("If you have a start, you must have an end.")
                ->atPath('end')
                ->addViolation();
        }
    }

    public function isLineStart($string)
    {
        return $this->contains($string, $this->lineStart);
    }

    public function isLineEnd($string)
    {
        return $this->contains($string, $this->lineEnd);
    }

    /** @Assert\Callback */
    public function validateLine(ExecutionContextInterface $context)
    {
        if (! $this->hasLines()) {
            return;
        }
        if (! $this->lineStart) {
            $context->buildViolation('Line start is required.')
                ->atPath('lineStart')
                ->addViolation();
        }
        if (! $this->lineEnd) {
            $context->buildViolation('Line end is required.')
                ->atPath('lineEnd')
                ->addViolation();
        }
    }

    private function contains($haystack, $needle)
    {
        return (false !== strpos($haystack, $needle));
    }

    public function hasEnoughColumns(array $dataRow)
    {
        return count($dataRow) >= $this->getNumColumns();
    }

    /**
     * The number columns we expect each line to have.
     * @return int
     */
    private function getNumColumns()
    {
        $num = 0;
        foreach ($this->lines as $field) {
            foreach ($field->positions as $pos) {
                $num = max($num, abs((int) $pos->x));
            }
        }
        return $num;
    }
}
