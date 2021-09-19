<?php

namespace Rialto\Shipping\Web;

use Rialto\Security\Role\Role;
use Rialto\Shipping\Web\Facades\TrackingFacade;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Rialto\Ups\TrackingRecord\TrackingRecordRepository;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DashboardController extends RialtoController
{
    /** @var TrackingRecordRepository */
    private $trackingRepo;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->trackingRepo = $this->dbm->getRepository(TrackingRecord::class);
    }

    /**
     * A single source for us to keep track of what orders are being shipped
     *
     * @Route("/shipping/dashboard/", name="shipping_dashboard")
     * @Method("GET")
     * @Template("shipping/dashboard/shippingDashboard.html.twig")
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::MANUFACTURING, Role::STOCK]);

        $trackingRecords = $this->trackingRepo->findAll();

        usort($trackingRecords, function (TrackingRecord $a, TrackingRecord $b) {
            return $a->getDateCreated() > $b->getDateCreated() ? -1 : 1;
        });

        $facades = array_map(function (TrackingRecord $record) {
            return new TrackingFacade($record);
        }, $trackingRecords);


        $serializer = $this->get('serializer');
        $json = $serializer->serialize($facades, 'json');

        return [
            'json' => $json
        ];
    }
}