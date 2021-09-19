<?php

namespace Rialto\Web\Report;

use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders "audit reports".
 *
 * An audit report is a class that wraps one or more SQL queries, allowing
 * the developer to write custom reports fairly easily.
 *
 * @see AuditReport
 */
class AuditController extends RialtoController
{
    /**
     * @Route("/{module}/Audit/{auditName}", name="Core_Audit_report")
     * @Method("GET")
     */
    public function reportAction(string $module, string $auditName, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $report = $this->instantiateReport($module, $auditName);

        $this->denyAccessUnlessGranted($report->getAllowedRoles());

        $query = $request->query->all();
        $params = $report->getParameters($query);

        $report->init($this->dbm, $params);

        $formBuilder = $this->createFilterFormBuilder();
        $filterForm = $report->getFilterForm($formBuilder);
        if ($filterForm) {
            $filterForm->submit($params);
            $params = array_merge($params, $filterForm->getData());
        }

        $params = $report->prepareParameters($params);
        $tables = $report->getTables($params);
        foreach ($tables as $table) {
            $table->loadResults($this->dbm, $params);
        }

        $viewParams = [
            'title' => $auditName,
            'tables' => $tables,
            'params' => $params,
            'report' => $report,
            'filterForm' => $filterForm ? $filterForm->createView() : null,
        ];

        if ($request->get('_format') == 'pdf') {
            $template = $this->getTemplate($module, $auditName, 'tex');
            $tex = $this->renderView($template, $viewParams);
            $generator = $this->get(PdfGenerator::class);
            $pdf = $generator->fromTex($tex);
            return PdfResponse::create($pdf, 'auditReport.pdf');
        } elseif ($request->get('_format') == 'csv') {
            $tableNo = $request->get('table', 0);
            return $this->csvResponse($tables[$tableNo]);
        }

        $template = $this->getTemplate($module, $auditName, 'html');
        return $this->render($template, $viewParams);
    }

    private function instantiateReport(string $module,
                                       string $auditName): AuditReport
    {
        $className = "Rialto\\{$module}\\Web\\Report\\$auditName";
        if (class_exists($className)) {
            return new $className();
        }
        throw $this->notFound();
    }

    private function createFilterFormBuilder(): FormBuilderInterface
    {
        $name = null;
        $data = null;
        $options = [
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ];
        $formBuilder = $this->createNamedBuilder($name, $data, $options);
        $formBuilder->setMethod('GET');
        return $formBuilder;
    }

    private function getTemplate(string $module,
                                 string $auditName,
                                 string $markup): string
    {
        $templating = $this->getTemplating();
        $lcModule = strtolower($module);
        $template = "{$lcModule}/audit/$auditName.$markup.twig";
        if ($templating->exists($template)) {
            return $template;
        }
        return "core/audit/report.$markup.twig";
    }

    private function csvResponse(AuditTable $table): Response
    {
        $rows = [];
        foreach ($table->getResults() as $result) {
            $row = [];
            foreach ($table->getColumns() as $column) {
                $value = $column->getValue($result);
                $row[$column->getHeading()] = $this->removeNewlines($value);
            }
            $rows[] = $row;
        }

        $csv = new CsvFileWithHeadings();
        $csv->useWindowsNewline();
        $csv->parseArray($rows);
        $csvData = $csv->toString();

        $desc = $table->getDescription();
        if ($desc) {
            $csvData = $csv->lineToString([$desc]) .
                $csv::NEWLINE_WIN . $csv::NEWLINE_WIN .
                $csvData;
        }

        $response = new Response($csvData);
        $response->headers->set('content-type', 'text/csv');
        $filename = $table->getTitle();
        $response->headers->set('Content-disposition', "attachment; filename=\"$filename.csv\"");
        return $response;
    }

    private function removeNewlines($value)
    {
        return preg_replace('/\s+/', ' ', $value);
    }
}
