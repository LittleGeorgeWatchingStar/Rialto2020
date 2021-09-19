<?php

namespace Rialto\Filing\Document;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;
use Sabre\VObject\Component\VCalendar;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Any document or form that must be filed on a regular basis.
 *
 * Government tax forms are a typical example of this.
 *
 * @UniqueEntity("name")
 */
class Document implements RialtoEntity, Persistable
{
    /**
     * @var string UUID
     */
    private $uuid;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Regex(pattern="/^[a-zA-Z0-9_ \.]+$/",
     *   message="Name can only contains letters, numbers, spaces, underscores, and periods.")
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \DateTime
     */
    private $dateUpdated;

    /**
     * @var UploadedFile
     * @Assert\File(maxSize="1M")
     */
    private $templateFile;

    /**
     * @var string
     */
    private $templateFilename = '';

    /**
     * @var DocumentField[]
     * @Assert\Valid(traverse="true")
     */
    private $fields;

    /**
     * The day of the month when this should be filed.
     *
     * Negative values indicate from the last day of the month, so -1
     * means the last day; -2 means the second to last day. A value of
     * zero means no scheduling.
     *
     * @var integer
     * @Assert\Range(min=-31, max=31)
     */
    private $scheduleDay = 0;

    /**
     * The months of the year when this should be filed, comma-separated.
     * @var string
     */
    private $scheduleMonths = '';

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->dateCreated = new \DateTime();
        $this->setUpdated();
    }

    public function setUpdated()
    {
        $this->dateUpdated = new \DateTime();
    }

    /**
     * Get UUID
     *
     * @return string
     */
    public function getId()
    {
        return $this->uuid;
    }

    /**
     * @param string $name
     */
    public function setName($name): self
    {
        $this->name = trim($name);
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }

    /** @return UploadedFile */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    public function setTemplateFile(UploadedFile $templateFile = null)
    {
        $this->templateFile = $templateFile;
    }

    public function hasTemplateFile()
    {
        return (bool) $this->templateFilename;
    }

    /**
     * @param string $filename
     */
    public function setTemplateFilename($filename): self
    {
        $this->templateFilename = trim($filename);
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateFilename()
    {
        return $this->templateFilename;
    }

    /**
     * @return DocumentField[]
     */
    public function getFields()
    {
        return $this->fields->toArray();
    }

    public function addField(DocumentField $field)
    {
        $field->setDocument($this);
        $this->fields[] = $field;
    }

    public function removeField(DocumentField $field)
    {
        $this->fields->removeElement($field);
    }

    public function getScheduleDay()
    {
        return $this->scheduleDay;
    }

    public function setScheduleDay($day)
    {
        $this->scheduleDay = (int) $day;
    }

    /**
     * @return integer[]
     * @Assert\Count(min=0, max=12)
     */
    public function getScheduleMonths()
    {
        return explode(',', $this->scheduleMonths);
    }

    public function setScheduleMonths(array $months)
    {
        $months = array_map('trim', $months);
        sort($months);
        $this->scheduleMonths = join(',', $months);
    }

    /**
     * Adds the filing of this document to $calendar.
     *
     * @see https://github.com/fruux/sabre-vobject/blob/master/doc/usage_3.md
     * @param VCalendar $calendar
     */
    public function addToCalendar(VCalendar $calendar)
    {
        assertion(null != $this->uuid);
        $calendar->add('VEVENT', [
            'SUMMARY' => $this->name,
            'UID' => $this->uuid,
            'CREATED' => $this->dateCreated,
            'LAST-MODIFIED' => $this->dateUpdated,
            'DTSTART' => $this->getNextDueDate(),
            'RRULE' => $this->getRecurrenceRule(),
        ]);
    }

    /** @return \DateTime */
    private function getNextDueDate()
    {
        if (! $this->isRecurring() ) {
            throw new IllegalStateException("$this is not recurring");
        }
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $thisYear = (int) date('Y');
        $schDay = $this->getScheduleDay();
        foreach ( [$thisYear, $thisYear + 1] as $year ) {
            foreach ( $this->getScheduleMonths() as $mon ) {
                $day = $this->getDayOfMonth($year, $mon, $schDay);
                $scheduled = new DateTime("$year-$mon-$day");
                if ( $scheduled >= $today ) {
                    return $scheduled;
                }
            }
        }
        throw new \LogicException("Cannot find next due date!");
    }

    private function isRecurring()
    {
        return ( $this->scheduleDay && $this->scheduleMonths );
    }

    private function getDayOfMonth($year, $mon, $schDay)
    {
        if ( $schDay > 0 ) {
            return $schDay;
        }
        return cal_days_in_month(CAL_GREGORIAN, $mon, $year) + $schDay + 1;
    }

    private function getRecurrenceRule()
    {
        return sprintf('FREQ=YEARLY;BYMONTH=%s;BYMONTHDAY=%s',
            $this->scheduleMonths,
            $this->scheduleDay);
    }

    public function getEntities()
    {
        return [$this];
    }

}
