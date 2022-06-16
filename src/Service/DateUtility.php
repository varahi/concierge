<?php

namespace App\Service;

class DateUtility
{
    private $dateFormat;

    private $timeFormat;

    /**
     * @param string $dateFormat
     * @param string $timeFormat
     */
    public function __construct(
        string $dateFormat,
        string $timeFormat
    ) {
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
    }

    /**
     * Check if date has format Y-m-d
     *
     * @param string $dateString
     * @return \DateTime|false
     */
    public function checkDate(
        $dateString
    ) {
        // Expression yyyy-mm-dd   /^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/
        // Expression dd-mm-yyyy /^(\d{2})-(\d{2})-(\d{4})$/
        // Expression mm/dd/yyyy  /([0-9]{2})\/([0-9]{2})\/([0-9]{4})/
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dateString)) {
            $date = \DateTime::createFromFormat($this->dateFormat, $dateString);
            return $date;
        } else {
            $oldDate = date($dateString);
            $oldDateTimestamp = strtotime($oldDate);
            $newDateString = date($this->dateFormat, $oldDateTimestamp);
            $date = \DateTime::createFromFormat($this->dateFormat, $newDateString);
            return $date;
        }
    }

    /**
     * Set time
     *
     * @param string $timeString
     * @return \DateTime|false
     */
    public function setTime(
        $timeString
    ) {
        if ($timeString !=='') {
            $hour = \datetime::createfromformat($this->timeFormat, $timeString);
        } else {
            $hour = \datetime::createfromformat($this->timeFormat, '00:00:00');
        }
        return $hour;
    }
}
