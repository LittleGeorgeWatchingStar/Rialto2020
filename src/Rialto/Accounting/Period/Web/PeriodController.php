<?php

namespace Rialto\Accounting\Period\Web;

use Psr\Container\ContainerInterface;
use Rialto\Accounting\Period\Orm\PeriodRepository;
use Rialto\Accounting\Period\Period;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;


/**
 * Controller for managing accounting periods.
 */
class PeriodController extends RialtoController
{
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @Route("/accounting/period/", name="period_list")
     * @Method("GET")
     * @Template("accounting/period/list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $periods = $this->getRepository(Period::class)->findAll();
        $ctrl = self::class;
        return [
            'period' => $periods,
            'createAction' => "$ctrl::createAction",
        ];
    }

    /**
     * @Route("/accounting/create-periods/", name="period_create")
     * @Template("accounting/period/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $default = new \DateTime('+1 year');
        $thisYear = (int) date('Y');
        $form = $this->createFormBuilder(['lastDate' => $default])
            ->setAction($this->generateUrl('period_create'))
            ->add('lastDate', DateType::class, [
                'years' => range($thisYear, $thisYear + 5),
                'days' => [28],
                'label' => 'Add periods through'
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $lastDate = $data['lastDate'];
            /** @var $repo PeriodRepository */
            $repo = $this->dbm->getRepository(Period::class);
            $this->dbm->beginTransaction();
            try {
                /* Creates the needed periods. */
                $newest = $repo->findForDate($lastDate);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Created periods through $newest.");
            return $this->redirectToRoute('period_list');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
