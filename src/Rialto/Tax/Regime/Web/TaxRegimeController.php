<?php

namespace Rialto\Tax\Regime\Web;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Period\Orm\PeriodRepository;
use Rialto\Accounting\Period\Period;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Sales\Web\Report\InternalSalesByCounty;
use Rialto\Sales\Web\Report\InternalSalesReport;
use Rialto\Sales\Web\Report\LcdDisplayReport;
use Rialto\Sales\Web\Report\SalesTaxReport;
use Rialto\Security\Role\Role;
use Rialto\Tax\Regime\TaxRegime;
use Rialto\Tax\Regime\TaxRegimeReport;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for maintaining California state tax info.
 */
class TaxRegimeController extends RialtoController
{
    /**
     * @Route("/tax/regime/", name="tax_regime_list")
     * @Method("GET")
     * @Template("tax/regime/list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $regimes = $this->getRepository(TaxRegime::class)->findAll();
        return ['regimes' => $regimes];
    }

    /**
     * @Route("/Tax/TaxRegime/", name="Tax_Regime_create")
     * @Template("tax/regime/edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return $this->processForm(new TaxRegime(), "created", $request);
    }

    /**
     * @Route("/Tax/TaxRegime/{id}/", name="Tax_Regime_edit")
     * @Template("tax/regime/edit.html.twig")
     */
    public function editAction(TaxRegime $regime, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return $this->processForm($regime, "updated", $request);
    }

    private function processForm(TaxRegime $regime, $updated, Request $request)
    {
        $form = $this->createForm(TaxRegimeType::class, $regime);
        $returnUri = $this->generateUrl('tax_regime_list');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->dbm->persist($regime);
                $this->dbm->flush();
                $this->logNotice("Regime \"$regime\" $updated.");
                return $this->redirect($returnUri);
            } catch (DBALException $ex) {
                $this->logError($ex->getMessage());
            }
        }

        return [
            'heading' => $regime->getId() ? (string) $regime : 'New tax regime',
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ];
    }

    /**
     * @Route("/Sales/SalesTaxReport", name="Tax_SalesTaxReport")
     * @Template("tax/regime/report.html.twig")
     */
    public function reportAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $filters = $this->getDefaultFilterValues();
        $options = [
            'method' => 'get',
            'csrf_protection' => false,
        ];
        $form = $this->createNamedBuilder(null, $filters, $options)
            ->add('start', EntityType::class, [
                'class' => Period::class,
                'label' => 'Starting from'
            ])
            ->add('end', EntityType::class, [
                'class' => Period::class,
                'label' => 'through (and including)',
            ])
            ->add('internal', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Internal sales',
                'required' => false,
            ])
            ->getForm();

        $form->submit($request->query->all(), false);
        $filters = $form->getData();
        $start = $filters['start'];
        $end = $filters['end'];
        $conn = $this->dbm->getConnection();
        /* @var $conn Connection */
        $salesTaxReport = new SalesTaxReport($conn);
        $salesTaxReport->loadData($start, $end);
        $internalSalesReport = new InternalSalesReport($conn);
        $internalSalesReport->loadData($start, $end);
        $internalSalesReport->setManualAmount($filters['internal']);
        $internalSalesByCounty = new InternalSalesByCounty($conn);
        $internalSalesByCounty->loadData($start, $end);
        $taxRegimeReport = new TaxRegimeReport($this->dbm);
        $taxRegimeReport->loadData($start, $end);
        $lcdDisplayReport = new LcdDisplayReport($conn);
        $lcdDisplayReport->loadData($start, $end);

        $reports = [
            'salesTaxReport' => $salesTaxReport,
            'internalSalesReport' => $internalSalesReport,
            'internalSalesByCounty' => $internalSalesByCounty,
            'taxRegimeReport' => $taxRegimeReport,
            'lcdDisplayReport' => $lcdDisplayReport,
        ];

        if ($request->get('_format') == 'pdf') {
            return $this->renderPdf($reports);
        }

        $reports['form'] = $form->createView();
        return $reports;
    }

    private function renderPdf(array $reports)
    {
        $pdf = $this->get(PdfGenerator::class);
        $pdfData = $pdf->render('tax/regime/report.tex.twig', $reports);
        $filename = "TaxRegimeReport";
        return PdfResponse::create($pdfData, $filename);
    }

    private function getDefaultFilterValues()
    {
        $startDate = new DateTime(date('Y') . '-01-01');
        /** @var $periodRepo PeriodRepository */
        $periodRepo = $this->getRepository(Period::class);
        $start = $periodRepo->findForDate($startDate);
        $endDate = new DateTime(date('Y') . '-12-31');
        $end = $periodRepo->findForDate($endDate);
        return [
            'start' => $start,
            'end' => $end,
            'internal' => 0,
        ];
    }

}
