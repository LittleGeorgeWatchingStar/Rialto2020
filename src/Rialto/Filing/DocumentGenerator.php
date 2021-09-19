<?php

namespace Rialto\Filing;

use Cpdf;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use Rialto\Database\Orm\DbManager;
use Rialto\Filetype\Postscript\FontFilesystem;
use Rialto\Filing\Document\Document;
use Rialto\Filing\Document\DocumentField;

/**
 * Generates documents by taking a template image or PDF and populating
 * the fields thereon.
 */
class DocumentGenerator
{
    /** @var EntityRepository */
    private $documentRepo;

    /** @var DocumentFilesystem */
    private $filesystem;

    /** @var FontFilesystem */
    private $fonts;

    private $leftMargin = null;
    private $pageHeight = null;
    private $pageWidth = null;

    public function __construct(
        DbManager $dbm,
        DocumentFilesystem $fs,
        FontFilesystem $fonts)
    {
        $this->documentRepo = $dbm->getRepository(Document::class);
        $this->filesystem = $fs;
        $this->fonts = $fonts;
    }

    public function generate($documentName, array $header, array $lineItems)
    {
        $document = $this->findDocument($documentName);
        $documentImage = $this->filesystem->getTemplateContents($document);

        $pdf = $this->createPdf();
        $pdf->addinfo('Title', $documentName );
        $pdf->addinfo('Subject', "Filing");

        $topImage = imagecreatefromstring($documentImage);
        $pdf->addImage($topImage, 0, 0, $this->pageWidth);

        $fields = $document->getFields();
        foreach ($fields as $field ) {
            if ( $field->isLineItem() ) {
                foreach ( $lineItems as $j => $item ) {
                    $value = $field->getValueFromData($item);
                    $this->write($pdf, $field, $value, $j);
                }
            }
            elseif ( $field->isVariable() ) {
                $value = $field->getValueFromData($header);
                $this->write($pdf, $field, $value);
            }
            else {
                $value = $field->getValue();
                $this->write($pdf, $field, $value);
            }
        }
        return $pdf->output();
    }

    /** @return Document */
    private function findDocument($documentName)
    {
        $document = $this->documentRepo->findOneBy(['name' => $documentName]);
        if ( $document ) {
            return $document;
        }
        throw new InvalidArgumentException("No such document $documentName");
    }

    private function createPdf()
    {
        switch ( $this->getPaperSize() )
        {
          case 'A4':
              $Page_Width=595;
              $Page_Height=842;
              $Top_Margin=30;
              $Bottom_Margin=30;
              $Left_Margin=40;
              $Right_Margin=30;
              break;

          case 'A4_Landscape':
              $Page_Width=842;
              $Page_Height=595;
              $Top_Margin=30;
              $Bottom_Margin=30;
              $Left_Margin=40;
              $Right_Margin=30;
              break;

           case 'A3':
              $Page_Width=842;
              $Page_Height=1190;
              $Top_Margin=50;
              $Bottom_Margin=50;
              $Left_Margin=50;
              $Right_Margin=40;
              break;

           case 'A3_landscape':
              $Page_Width=1190;
              $Page_Height=842;
              $Top_Margin=50;
              $Bottom_Margin=50;
              $Left_Margin=50;
              $Right_Margin=40;
              break;

           case 'letter':
              $Page_Width=612;
              $Page_Height=792;
              $Top_Margin=30;
              $Bottom_Margin=30;
              $Left_Margin=30;
              $Right_Margin=25;
              break;

           case 'letter_landscape':
              $Page_Width=792;
              $Page_Height=612;
              $Top_Margin=30;
              $Bottom_Margin=30;
              $Left_Margin=30;
              $Right_Margin=25;
              break;

           case 'legal':
              $Page_Width=612;
              $Page_Height=1008;
              $Top_Margin=50;
              $Bottom_Margin=40;
              $Left_Margin=30;
              $Right_Margin=25;
              break;

           case 'legal_landscape':
              $Page_Width=1008;
              $Page_Height=612;
              $Top_Margin=50;
              $Bottom_Margin=40;
              $Left_Margin=30;
              $Right_Margin=25;
              break;

           case 'label_a':
              $Page_Width= 80;
              $Page_Height=200;
              $Top_Margin=10;
              $Bottom_Margin=10;
              $Left_Margin=10;
              $Right_Margin=10;
              break;
        }
        $this->leftMargin = $Left_Margin;
        $this->pageHeight = $Page_Height;
        $this->pageWidth = $Page_Width;

        $PageSize = [0, 0, $Page_Width, $Page_Height];

        $pdf = new Cpdf($PageSize);
        $fontFile = $this->fonts->getFontFile('Helvetica');
        $pdf->selectFont($fontFile->getRealPath());
        return $pdf;
    }

    private function getPaperSize()
    {
        return 'letter';
    }

    private function write(Cpdf $pdf, DocumentField $field, $value, $lineNo = 0)
    {
        $value = utf8ToAscii($value);
        $pdf->addTextWrap(
            $this->leftMargin + $field->getXPosition(),
            $this->pageHeight - $field->getYPosition() - (25 * $lineNo),
            $field->getLeft(),
            10,
            $value,
            $field->getAlignment());
    }
}
