<?php


namespace Rialto\Ups\TrackingRecord\Web;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Rialto\Ups\TrackingRecord\TrackingRecordRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TrackingExtension extends AbstractExtension
{

    /** @var TrackingRecordRepository */
    private $trackingRecordRepo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->trackingRecordRepo = $em->getRepository(TrackingRecord::class);
    }

    public function getFilters()
    {
        return [
            new TwigFilter('track_status', [$this, 'trackStatus'], ['is_safe' => ['html']]),
            new TwigFilter('track_icon', [$this, 'trackIcon'], ['is_safe' => ['html']]),
            new TwigFilter('ups_link', [$this, 'upsHotlink'], ['is_safe' => ['html']]),
        ];
    }

    private function upsLink(string $trackingNumber): string
    {
        return "https://www.ups.com/track?tracknum=$trackingNumber";
    }

    private function anchor(string $link, string $body): string
    {
        return '<a href="' . $link . '" target="_blank">' . $body . "</a>";
    }

    public function trackStatus(string $trackingNumber): string
    {
        if (!$trackingNumber) {
            return '';
        }

        $trackingRecord = $this->trackingRecordRepo->getByTrackingNumber($trackingNumber);

        if ($trackingRecord) {
            $dateDelivered = $trackingRecord->getDateDelivered();
            if ($dateDelivered) {
                return "$trackingNumber | Delivered on {$dateDelivered->format('Y-m-d')}";
            } else {
                return "$trackingNumber | Delivery in progress";
            }
        } else {
            return "$trackingNumber | No record";
        }
    }

    public function trackIcon(string $trackingNumber): string
    {
        if (!$trackingNumber) {
            return '';
        }
        $trackingRecord = $this->trackingRecordRepo->getByTrackingNumber($trackingNumber);
        $link = $this->upsLink($trackingNumber);

        if ($trackingRecord) {
            $dateDelivered = $trackingRecord->getDateDelivered();
            if ($dateDelivered) {
                $stamp = $dateDelivered->format('Y-m-d');
                return $this->anchor($link,
                    '<img src="/icons/status-complete.png" title="Delivered ' . $stamp . '" alt="Delivered"/>');
            } else {
                return $this->anchor($link,
                    '<img src="/icons/ship.png" title="Delivery in progress" alt="Delivery in progress"/>');
            }
        } else {
            return $this->anchor($link,
                '<img src="/icons/error.png" title="No record" alt="No record"/>');
        }
    }

    public function upsHotlink(string $trackingNumber): string
    {
        if (!$trackingNumber) {
            return '';
        }

        $link = $this->upsLink($trackingNumber);
        return $this->anchor($link, $trackingNumber);
    }
}
