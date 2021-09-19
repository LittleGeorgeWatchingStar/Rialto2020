<?php

namespace Rialto\Catalina\Web;


use FOS\RestBundle\View\View;
use GuzzleHttp\Exception\GuzzleException;
use Rialto\Catalina\CatalinaClient;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JobController extends RialtoController
{
    /** @var CatalinaClient */
    private $catalina;

    protected function init(ContainerInterface $container)
    {
        $this->catalina = $container->get(CatalinaClient::class);
    }

    /**
     * View the Catalina job status for a work order: whether is has a job,
     * and whether that job has any results.
     *
     * @Route("/catalina/job-status/{order}/", name="catalina_job_status")
     * @Method("GET")
     * @Template("catalina/job/status.html.twig")
     */
    public function statusAction(WorkOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        try {
            $results = $this->catalina->getResults($order);
        } catch (GuzzleException $ex) {
            if ($ex->getCode() === Response::HTTP_NOT_FOUND) {
                $results = null;
            } else {
                return new Response(sprintf('ERROR %d', $ex->getCode()));
            }
        }

        return [
            'results' => $results,
            'order' => $order,
        ];
    }

    /**
     * @Route("/catalina/job-status/{order}/json/", name="catalina_job_status_json")
     * @Method("GET")
     */
    public function statusJsonAction(WorkOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        $results = $this->catalina->getResults($order);

        return View::create($results);
    }

    /**
     * Ask Catalina to create a job for the given work order.
     *
     * @Route("/catalina/job/{order}/", name="catalina_job_create")
     * @Template("catalina/job/create.html.twig")
     */
    public function createAction(WorkOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK]);
        $form = null;
        try {
            $existingJob = $this->catalina->getJob($order);
        } catch (GuzzleException $ex) {
            return $this->render('error.html.twig', [
                'title' => 'Catalina error',
                'message' => sprintf('Catalina returned error status %d.',
                    $ex->getCode()),
                'uri' => $this->generateUrl('work_order_view', [
                    'order' => $order->getId(),
                ]),
                'linkText' => "Click here to return to $order.",
            ]);
        }
        if (!$existingJob) {
            $form = $this->createFormBuilder()
                ->add('submit', SubmitType::class, [
                    'label' => 'Create Catalina job'
                ])
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->catalina->createJob($order);
                    $order->setUpdated();
                    $this->dbm->flush();
                } catch (GuzzleException $ex) {
                    $this->logException($ex);
                }
                return $this->redirectToRoute('catalina_job_create', [
                    'order' => $order->getId(),
                ]);
            }
        }
        return [
            'order' => $order,
            'form' => $form ? $form->createView() : null,
            'job' => $existingJob,
        ];
    }
}
