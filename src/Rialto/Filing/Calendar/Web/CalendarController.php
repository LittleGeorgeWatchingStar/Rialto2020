<?php

namespace Rialto\Filing\Calendar\Web;

use Rialto\Filing\Document\Document;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sabre\VObject\Component\VCalendar;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for generating a calendar of filing dates.
 */
class CalendarController extends RialtoController
{
    /**
     * Downloads the entire calendar in iCalendar (.ics) format.
     *
     * @Route("/Filing/Calendar/",
     *   name="Filing_Calendar")
     * @Method("GET")
     */
    public function calendarAction()
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $documents = $this->dbm->getRepository(Document::class)
            ->findRecurring();

        $calendar = new VCalendar();
        foreach ( $documents as $doc ) {
            /* @var $doc Document */
            $doc->addToCalendar($calendar);
        }

        $response = new Response($calendar->serialize());
        $response->headers->set('content-type', 'text/calendar');
        $response->headers->set('content-disposition', "attachment; filename=\"filings.ics\"");
        return $response;
    }

}
