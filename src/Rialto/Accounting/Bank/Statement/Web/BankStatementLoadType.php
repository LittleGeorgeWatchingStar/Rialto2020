<?php

namespace Rialto\Accounting\Bank\Statement\Web;


use Doctrine\Common\Persistence\ObjectManager;
use Gumstix\Filetype\CsvFile;
use Rialto\Accounting\Bank\Statement\Parser\BankStatementParser;
use Rialto\Accounting\Bank\Statement\Parser\HsbcStatementParser;
use Rialto\Accounting\Bank\Statement\Parser\ParseResult;
use Rialto\Accounting\Bank\Statement\Parser\SiliconValleyBankStatementParser;
use Rialto\Filetype\Excel\XlsConverter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class BankStatementLoadType extends AbstractType
{
    const FORMATS = [
        'Silicon Valley Bank' => 'svb',
        'HSBC' => 'hsbc'
    ];

    const FORMAT_PARSERS = [
        'svb' => SiliconValleyBankStatementParser::class,
        'hsbc' => HsbcStatementParser::class,
    ];

    /** @return ParseResult[] */
    public static function parse(FormInterface $form, ObjectManager $dbm): array
    {
        /** @var UploadedFile $file */
        $file = $form->get('file')->getData();
        if (in_array($file->getMimeType(), XlsConverter::SUPPORTED_MIMETYPES)) {
            $converter = new XlsConverter();
            $csv = $converter->toCsvFile($file);
        } else {
            $csv = new CsvFile();
            $csv->parseFile($file);
        }

        $format = $form->get('format')->getData();
        $parser = self::getParserForFormat($format, $dbm);
        return $parser->parse($csv);
    }

    private static function getParserForFormat(string $format,
                                              ObjectManager $dbm): BankStatementParser
    {
        if (array_key_exists($format, BankStatementLoadType::FORMAT_PARSERS)) {
            $class = BankStatementLoadType::FORMAT_PARSERS[$format];
            return new $class($dbm);
        } else {
            throw new \InvalidArgumentException("Unknown format type '$format'");
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, [
            'label' => 'Transaction details in CSV format',
            'constraints' => [
                new Assert\File([
                    'maxSize' => '1M',
                    'mimeTypes' => [
                        'text/csv',
                        'text/plain',
                        'text/x-Algol68',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ],
                ])
            ],
        ])
            ->add('format', ChoiceType::class, [
                'label' => 'Statement Format',
                'choices' => self::FORMATS,
            ])
            ->add('upload', SubmitType::class);
    }

}
