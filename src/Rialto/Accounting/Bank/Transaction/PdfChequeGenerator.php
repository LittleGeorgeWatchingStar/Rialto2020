<?php

namespace Rialto\Accounting\Bank\Transaction;

use Cpdf;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Filetype\Postscript\Fonts;

class PdfChequeGenerator
{
    private $paperSize;
    private $pageWidth;
    private $rightMargin;

    /** @var NumberToTextConverter */
    private $numToText;

    public function __construct($paperSize)
    {
        $this->paperSize = $paperSize;
        $this->numToText = new NumberToTextConverter();
    }

    /**
     * @param BankTransaction[] $cheques
     * @return string The PDF data
     */
    public function generateCheques(array $cheques, $upswing, $rightswing)
    {
        $pdf = $this->startPdf();
        $this->chequeHeader($pdf);
        $this->writeCheques($pdf, $cheques, $upswing, $rightswing);
        return $pdf->output();
    }

    /** @return Cpdf */
    private function startPdf()
    {
        switch ($this->paperSize) {
            case 'A4_Landscape':
                $Page_Width = 842;
                $Page_Height = 595;
                $Top_Margin = 30;
                $Bottom_Margin = 30;
                $Left_Margin = 40;
                $Right_Margin = 30;
                break;

            case 'A3':
                $Page_Width = 842;
                $Page_Height = 1190;
                $Top_Margin = 50;
                $Bottom_Margin = 50;
                $Left_Margin = 50;
                $Right_Margin = 40;
                break;

            case 'A3_landscape':
                $Page_Width = 1190;
                $Page_Height = 842;
                $Top_Margin = 50;
                $Bottom_Margin = 50;
                $Left_Margin = 50;
                $Right_Margin = 40;
                break;

            case 'letter':
                $Page_Width = 612;
                $Page_Height = 792;
                $Top_Margin = 30;
                $Bottom_Margin = 30;
                $Left_Margin = 30;
                $Right_Margin = 25;
                break;

            case 'letter_landscape':
                $Page_Width = 792;
                $Page_Height = 612;
                $Top_Margin = 30;
                $Bottom_Margin = 30;
                $Left_Margin = 30;
                $Right_Margin = 25;
                break;

            case 'legal':
                $Page_Width = 612;
                $Page_Height = 1008;
                $Top_Margin = 50;
                $Bottom_Margin = 40;
                $Left_Margin = 30;
                $Right_Margin = 25;
                break;

            case 'legal_landscape':
                $Page_Width = 1008;
                $Page_Height = 612;
                $Top_Margin = 50;
                $Bottom_Margin = 40;
                $Left_Margin = 30;
                $Right_Margin = 25;
                break;

            case 'label_a':
                $Page_Width = 80;
                $Page_Height = 200;
                $Top_Margin = 10;
                $Bottom_Margin = 10;
                $Left_Margin = 10;
                $Right_Margin = 10;
                break;

            default:
                $Page_Width = 595;
                $Page_Height = 842;
                $Top_Margin = 30;
                $Bottom_Margin = 30;
                $Left_Margin = 40;
                $Right_Margin = 30;
                break;
        }

        $this->pageWidth = $Page_Width;
        $this->rightMargin = $Right_Margin;
        $pageSize = [0, 0, $Page_Width, $Page_Height];
        return new Cpdf($pageSize);
    }

    private function chequeHeader(Cpdf $pdf)
    {
        $bgText = [
            'Silicon Valley Bank', '3003 Tasman Drive.', 'Santa Clara, CA 95054', '(408) 654-7400',
            'Gumstix, Inc.', '3130 Alpine Rd., Suite 288-606', 'Portola Valley, CA 94028', 'DATE',
            '09-4039 / 1211',
            'PAY TO THE ORDER OF',
            'DOLLARS',
            'MEMO'];
        $bgSize = [
            9, 9, 9, 9,
            14, 9, 9, 9,
            8,
            8,
            10,
            9];
        $bgX = [350, 350, 350, 350,
            55, 55, 55, 455,
            370,
            20,
            510,
            20];

        $bgY = [10, 20, 30, 40,
            39, 49, 59, 58,
            65,
            100,
            120,
            213];

        $bgJust = [
            'centre', 'centre', 'centre', 'centre',
            'left', 'left', 'left', 'left',
            'left',
            'left',
            'left',
            'left'];

        $pdf->selectFont(Fonts::find('Helvetica'));
        for ($it = 0; $it < 12; $it++) {
            $pdf->addTextWrap($bgX[$it],
                775 - $bgY[$it],
                150,
                $bgSize[$it],
                $bgText[$it],
                $bgJust[$it]
            );
        }
    }

    /**
     * @param Cpdf $pdf
     * @param BankTransaction[] $cheques
     */
    private function writeCheques(Cpdf $pdf, array $cheques, $upswing, $rightswing)
    {
        $page_num = 0;
        foreach ($cheques as $cheque) {

            if ($page_num++ != 0) {
                $pdf->newPage();
                $this->chequeHeader($pdf);
            }

            if ($cheque->isType(SystemType::CREDITOR_PAYMENT)) {
                $payment = $cheque->getSupplierTransaction();
                $supplier = $payment->getSupplier();
                $payeeName = $supplier->getName();
                $payeeAddress = $supplier->getPaymentAddress();
                $memo = $supplier->getCustomerAccount();
                $memo = $memo ? "ACCOUNT#$memo" : null;

                $this->writeCheque($pdf, $cheque, $payeeName, $payeeAddress, $memo);
                $this->writeMicrText($pdf, $cheque, $upswing, $rightswing);
                $this->writeSupplierLog($pdf, $cheque, $payment, 300);
                $this->writeSupplierLog($pdf, $cheque, $payment, 600);
            } elseif ($cheque->isType(SystemType::CUSTOMER_REFUND)) {
                $refund = $cheque->getDebtorTransaction();
                $customer = $refund->getCustomer();
                $payeeName = $customer->getCompanyName();
                $payeeAddress = $customer->getAddress();
                $this->writeCheque($pdf, $cheque, $payeeName, $payeeAddress);
                $this->writeMicrText($pdf, $cheque, $upswing, $rightswing);
                $this->writeDebtorLog($pdf, $cheque, $payeeName, 300);
                $this->writeDebtorLog($pdf, $cheque, $payeeName, 600);
            } else {
                throw new \UnexpectedValueException(
                    "Unsupported cheque type " . $cheque->getSystemType());
            }
        }
    }

    private function writeCheque(
        Cpdf $pdf,
        BankTransaction $cheque,
        $payeeName,
        PostalAddress $payeeAddr,
        $memo = null)
    {
        $baseline = 0;
        $line_1 = 792 - 65 - 10;
        $line_2 = 792 - 108 - 10;
        $line_3 = 792 - 129 - 10;
        $line_4 = 792 - 159 - 10;
        $line_5 = 792 - 207 - 10;

        $line_spacing = 10;
        $tab_0 = 20;
        $tab_1 = 72;
        $tab_2 = 100;
        $tab_3 = 490;
        $tab_4 = 512;

        // Main cheque number in the top-right
        $fontSize = 14;
        $pdf->selectFont(Fonts::find('Helvetica'));
        $pdf->addText(500, 800 - 060, $fontSize, $cheque->getChequeNumber());

        $pdf->selectFont(Fonts::find('Times-Bold'));
        $fontSize = 16;
        // Large cheque amount
        $pdf->addText($tab_4 - 10, $line_2, $fontSize, '$ ' . number_format(-$cheque->getAmount(), 2));
        // Pay to the order of:
        $pdf->addText($tab_2 + 20, $line_2, $fontSize, $payeeName);
        // Cheque amount in words
        $pdf->addText($tab_0, $line_3, $fontSize, $this->numToText->convertMoney(-$cheque->getAmount()));

        $pdf->selectFont(Fonts::find('Times-Roman'));
        $fontSize = 11;
        // cheque date
        $pdf->addText($tab_3, $line_1, $fontSize, $cheque->getDate()->format('Y-m-d'));

        // payee name and address
        $addressLine = 0;
        $pdf->addText($tab_1, $line_4 - ($line_spacing * $addressLine++), $fontSize, $payeeName);
        $pdf->addText($tab_1, $line_4 - ($line_spacing * $addressLine++), $fontSize, $payeeAddr->getStreet1());
        $cityStateZip = sprintf('%s, %s %s',
            $payeeAddr->getCity(),
            $payeeAddr->getStateCode(),
            $payeeAddr->getPostalCode());
        if ($payeeAddr->getStreet2()) {
            $pdf->addText($tab_1, $line_4 - ($line_spacing * $addressLine++), $fontSize, $payeeAddr->getStreet2());
        }
        $pdf->addText($tab_1, $line_4 - ($line_spacing * $addressLine++), $fontSize, $cityStateZip);
        $pdf->addText($tab_1, $line_4 - ($line_spacing * $addressLine++), $fontSize, $payeeAddr->getCountryName());

        if ($memo) {
            $pdf->addText($tab_1 - 10, $line_4 - $line_spacing * 6, $fontSize - 1, $memo);
        }
        // lines for date, payee, amount text, and signature
        $pdf->line($tab_2 + 15, $line_2 - 2, $tab_4, $line_2 - 2);
        $pdf->line($tab_3, $line_1 - 2, $tab_4 + 30, $line_1 - 2);
        $pdf->line($tab_0, $line_3 - 2, $tab_4, $line_3 - 2);
        $pdf->line($tab_2 + 230, $line_5 + 12, $tab_4, $line_5 + 12);

        $pdf->addText(380, 792 - 210 - 4, $fontSize, 'W. Gordon Kruberg, M.D.'); // endorser

        $pdf->selectFont(Fonts::find('Helvetica'));
        $pdf->addText(420, 800 - 600, $fontSize, 'CK#' . $cheque->getChequeNumber());
    }

    /**
     * "MICR" is Magnetic Ink Character Recognition -- those numbers at the
     * bottom of the cheque in a funny font.
     */
    private function writeMicrText(Cpdf $pdf, BankTransaction $cheque, $upswing, $rightswing)
    {
        $pdf->selectFont(Fonts::find('GnuMICR-0.30/GnuMICR'));
        $micr_line_1 = 792 - 237 + $upswing;
        $micr_line_2 = 792 - 245 + $upswing;
        $micr_line_3 = 792 - 245 + $upswing;

        $micr_tab_1 = 192 + $rightswing;
        $micr_tab_2 = 250 + $rightswing;
        $micr_tab_3 = 400 + $rightswing;

        $micr_text_1 = "1A121140399A" . $cheque->getChequeNumber() . "D3300417704C";
        $micr_text_2 = "";
        $micr_text_3 = "";

        $micrList = [
            new Micr($micr_line_1, $micr_tab_1, $micr_text_1),
            new Micr($micr_line_2, $micr_tab_2, $micr_text_2),
            new Micr($micr_line_3, $micr_tab_3, $micr_text_3),
        ];

        $micr_spacing = 9.0; //8.95;
        $micr_font_size = 12;
        foreach ($micrList as $micr) {
            /* @var $micr Micr */
            for ($dig = 0; $dig < strlen($micr->text); $dig++) {
                $pdf->addText(
                    $micr->tab + $micr_spacing * $dig,
                    $micr->line, $micr_font_size,
                    substr($micr->text, $dig, 1));
            }
        }
    }

    private function writeSupplierLog(Cpdf $pdf, BankTransaction $cheque, SupplierTransaction $payment, $yOffset)
    {
        $supplier = $payment->getSupplier();
        $pdf->selectFont(Fonts::find('Times-Roman'));
        $fontSize = 11;
        $myYPos = 5;
        $Page_Width = $this->pageWidth;
        $Right_Margin = $this->rightMargin;
        $pdf->line(170, 792 - $yOffset + $myYPos, $Page_Width - $Right_Margin - 10, 792 - $yOffset + $myYPos);

        $myYPos -= 8;
        $invoiceNumberX = 175;
        $invoiceDateX = 350;
        $invoiceAmountX = 468;
        $pdf->addText($invoiceNumberX, 790 - $yOffset + $myYPos, $fontSize, "Invoice Number");
        $pdf->addText($invoiceDateX, 790 - $yOffset + $myYPos, $fontSize, "Invoice Date");
        $pdf->addText($invoiceAmountX, 790 - $yOffset + $myYPos, $fontSize, "Invoice Amount");

        $myYPos -= 4;
        $pdf->line(170, 792 - $yOffset + $myYPos, $Page_Width - $Right_Margin - 10, 792 - $yOffset + $myYPos);

        $myYPos -= 14;
        foreach ($payment->getAllocations() as $alloc) {
            $invoice = $alloc->getInvoice();
            /* @var $invoice SupplierTransaction */
            $pdf->addText($invoiceNumberX, 792 - $yOffset + $myYPos, $fontSize, $invoice->getReference());
            $pdf->addText($invoiceDateX, 792 - $yOffset + $myYPos, $fontSize, $invoice->getDate()->format('Y-m-d'));
            $pdf->addTextWrap($invoiceAmountX, 792 - $yOffset + $myYPos, 50, $fontSize, number_format($alloc->getAmount(), 2), 'right');
            $myYPos -= 15;
        }

        $pdf->addText($invoiceNumberX, 800 - $yOffset, $fontSize, 'Account #' . $supplier->getCustomerAccount());
        $pdf->addText(30, 800 - $yOffset, $fontSize, $payment->getSupplierName());
        $pdf->addText($invoiceDateX, 800 - $yOffset, $fontSize, $cheque->getDate()->format('Y-m-d'));

        $pdf->selectFont(Fonts::find('Helvetica'));
        $pdf->addText(480, 800 - $yOffset, $fontSize, '$' . number_format(-$cheque->getAmount(), 2), 'right');
    }


    private function writeDebtorLog(Cpdf $pdf, BankTransaction $cheque, $payeeName, $yOffset)
    {
        $pdf->selectFont(Fonts::find('Times-Roman'));
        $line_1 = 792 - 65 - 10;
        $tab_1 = 72;
        $fontSize = 11;

        $pdf->addText($tab_1, $line_1 - $yOffset, $fontSize, "Refund cheque for $payeeName");
        $pdf->addText($tab_1, $line_1 - $yOffset + 10, $fontSize, 'Cheque #' . $cheque->getChequeNumber());
        $pdf->addText($tab_1, $line_1 - $yOffset + 20, $fontSize, 'Amount $' . number_format(-$cheque->getAmount(), 2));

        $pdf->selectFont(Fonts::find('Helvetica'));
        $pdf->addText(480, 800 - $yOffset, $fontSize, '$' . number_format(-$cheque->getAmount(), 2), 'right');
    }
}

class Micr
{
    public $line;
    public $tab;
    public $text;

    public function __construct($line, $tab, $text)
    {
        $this->line = $line;
        $this->tab = $tab;
        $this->text = $text;
    }
}
