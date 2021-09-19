<?php

namespace Rialto\Printing\Job\Web;


use Rialto\Printing\Job\PrintJob;
use Rialto\Printing\Printer\PrinterException;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For managing print jobs.
 */
class PrintJobController extends RialtoController
{
    /**
     * @Route("/print/job/", name="print_job_list")
     * @Template("prints/printJob/printjob-list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $repo = $this->getRepository(PrintJob::class);
        $jobs = $repo->createQueryBuilder('job')
            ->orderBy('job.dateCreated', 'desc')
            ->setMaxResults(100)
            ->getQuery()->getResult();
        return [
            'jobs' => $jobs,
        ];
    }

    /**
     * Download the file sent to the printer.
     *
     * @Route("/print/job/{id}/download/", name="print_job_download")
     */
    public function downloadAction(PrintJob $job)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return new Response($job->getData(), 200, [
            'content-type' => $job->getContentType(),
            'content-disposition' => "attachment; filename=\"$job\""
        ]);
    }

    /**
     * Manually print a print job.
     *
     * Normally jobs are printed via a cron job.
     *
     * @Route("/print/job/{id}", name="print_job_print")
     * @Method("POST")
     */
    public function reprintAction(PrintJob $job)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $printer = $job->getPrinter();
        try {
            $printer->printJob($job);
        } catch (PrinterException $ex) {
            $this->logException($ex);
            return $this->redirectToRoute('print_job_list');
        }
        $job->setPrinted();
        $this->dbm->flush();
        $this->logNotice("Reprinted $job.");
        return $this->redirectToRoute('print_job_list');
    }
}
