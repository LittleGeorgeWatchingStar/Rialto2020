<?php

namespace Rialto\Time\Web;


use DateTime;
use Twig\Extension\AbstractExtension;

/**
 * Twig extensions for dealing with time and date.
 */
class TimeExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig_Function('days_left', [$this, 'daysLeft']),
        ];
    }

    /**
     * The number of days left until a due date; eg:
     *   "3 days left"
     *   "2 days late"
     *
     * @param string|DateTime $dueDate
     * @param string|DateTime $asOf For unit testing
     * @return string
     */
    public function daysLeft($dueDate, $asOf = 'today')
    {
        $asOfDate = $this->normalizeDate($asOf);
        $dueDate = $this->normalizeDate($dueDate);

        $diff = $dueDate->diff($asOfDate);
        if ($diff->days === 0) {
            return "due $asOf";
        }
        $left = $diff->invert ? 'left' : 'late';
        $days = $diff->days === 1 ? 'day' : 'days';
        $num = number_format($diff->days);
        return "$num $days $left";
    }

    private function normalizeDate($date)
    {
        if (!$date instanceof DateTime) {
            $date = new DateTime($date);
        }
        $date->setTime(0, 0, 0);
        return $date;
    }
}
