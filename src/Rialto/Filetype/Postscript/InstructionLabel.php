<?php

namespace Rialto\Filetype\Postscript;


/**
 * A label containing instructions for what to do with some parts, products,
 * etc.
 */
class InstructionLabel
    extends PostscriptLabel
{
    /**
     * @var string[]
     */
    private $instructions;

    /**
     * @param string[] $instructions
     */
    public function __construct(array $instructions)
    {
        if (empty($instructions)) {
            throw new \InvalidArgumentException('No instructions given');
        }
        $this->instructions = $instructions;
    }

    /**
     * Labels are oriented vertically, which is why width is less than height.
     */
    protected function getPageHeight()
    {
        return $this->inchToPoint(3.75);
    }

    /**
     * Labels are oriented vertically, which is why width is less than height.
     */
    protected function getPageWidth()
    {
        return $this->inchToPoint(2.125);
    }

    protected function renderDocument()
    {
        foreach ($this->instructions as $instruction) {
            $this->printInstruction($instruction);
        }
    }

    private function printInstruction($text, $fontSize = 11)
    {
        if (!$text) {
            throw new \InvalidArgumentException('Argument "text" is required');
        }
        $this->beginPage();

        $lines = $this->splitTextIntoLines($text, $fontSize);
        assertion(count($lines) > 0);

        $this->setPsFont(PostscriptFont::getArial($fontSize));

        static $margin = 20;
        $x = $margin;
        $y = $this->getPageWidth() - $margin;
        $this->setTextPosition($x, $y);
        $this->writeText($lines[0]);
        for ($i = 1; $i < count($lines); $i++) {
            $this->continueText($lines[$i]);
        }

        $this->endPage();
    }
}
