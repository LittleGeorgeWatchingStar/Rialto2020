<?php

namespace Rialto\Filetype\Pdf;

use Gumstix\GeographyBundle\Service\AddressFormatter;
use Rialto\Util\Strings\TextFormatter;
use Rialto\Web\TwigExtensionTrait;
use Twig\Extension\AbstractExtension;

/**
 * Twig extensions for processing LaTeX templates (.tex.twig files).
 *
 * @see PdfGenerator
 */
class LatexExtension extends AbstractExtension
{
    use TwigExtensionTrait;

    public function getFilters()
    {
        return [
            $this->simpleFilter('tex', 'escapeLatex', ['html']),
            $this->simpleFilter('tex_nl', 'replaceNewlines', ['html']),
            $this->simpleFilter('tex_address', 'renderAddress', ['html']),
        ];
    }

    public function escapeLatex($unsafeText, $stripControlCharacters = true)
    {
        $escapedText = $unsafeText;

        static $mappedChars = [
            '\\' => '\\textbackslash ',
            '*' => '\**',
        ];
        foreach ($mappedChars as $orig => $esc) {
            $escapedText = str_replace($orig, $esc, $escapedText);
        }

        static $escapableChars = ['~', '^', '&', '%', '$', '#', '_', '{', '}'];
        foreach ($escapableChars as $char) {
            $escapedText = str_replace($char, "\\$char", $escapedText);
        }

        if ($stripControlCharacters) {
            $formatter = new TextFormatter();
            $escapedText = $formatter->stripControlCharacters($escapedText);
        }

        return $escapedText;
    }

    public function replaceNewlines($text)
    {
        $escaped = $this->escapeLatex($text, false);
        return str_replace(PHP_EOL, ' \\\\ ', $escaped);
    }

    public function renderAddress($addr)
    {
        $formatter = new AddressFormatter();

        $lines = $formatter->getLines($addr);
        $escaped = array_map([$this, 'escapeLatex'], $lines);
        return join(" \\\ ", $escaped);
    }

}
