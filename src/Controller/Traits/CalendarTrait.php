<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Entity\Calendar;
use App\Entity\Housing;
use App\Entity\Reservation;
use App\Entity\Task;
use App\Repository\CalendarRepository;
use App\Service\DateUtility;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 *
 */
trait CalendarTrait
{
    private $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param $startDateString
     * @param $endDateString
     * @param NotifierInterface $notifier
     * @param CalendarRepository $calendarRepository
     * @param DateUtility $dateUtility
     * @param Task $task
     * @return Calendar|RedirectResponse|null
     */
    public function getCalendar(
        $startDateString,
        $endDateString,
        NotifierInterface $notifier,
        CalendarRepository $calendarRepository,
        DateUtility $dateUtility,
        Task $task
    ) {

        // Check if format date Y-m-d
        if ($dateUtility->checkDate($startDateString) === false && $dateUtility->checkDate($endDateString) === false) {
            $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
            return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
        } else {
            $startDate = $dateUtility->checkDate($startDateString);
            $endDate = $dateUtility->checkDate($endDateString);
        }

        if ($endDate == false) {
            $calendar = $calendarRepository->findOneBy(
                ['startDate' => $startDate],
                ['id' => 'DESC']
            );
        } else {
            $calendar = $calendarRepository->findOneBy(
                ['startDate' => $startDate, 'endDate' => $endDate],
                ['id' => 'DESC']
            );
        }

        if (!($calendar instanceof Calendar)) {
            // Create calendar object of start date
            $calendar = new Calendar();
            // Set dates
            if ($startDate) {
                $calendar->setStartDate($startDate);
            }
            if ($endDate) {
                $calendar->setEndDate($endDate);
            }
            // Set hours
            if (isset($_POST['start-hour']) && $_POST['start-hour'] !=='') {
                $startHour = $dateUtility->setTime($_POST['start-hour']);
                $calendar->setStartHour($startHour);
            } else {
                $calendar->setStartHour(null);
            }
            if (isset($_POST['end-hour']) && $_POST['end-hour'] !=='') {
                $endHour = $dateUtility->setTime($_POST['end-hour']);
                $calendar->setEndHour($endHour);
            } else {
                $calendar->setEndHour(null);
            }
            $task->setNotification('0');
            $task->setState(0);
            $calendar->addTask($task);
        } else {
            // Edit existing calendar object
            if ($endDate == false) {
                // Do nothing
            } else {
                $calendar->setEndDate($endDate);
            }
            if (isset($_POST['start-hour']) && $_POST['start-hour'] !=='') {
                $startHour = $dateUtility->setTime($_POST['start-hour']);
                $calendar->setStartHour($startHour);
            } else {
                $calendar->setStartHour(null);
            }
            if (isset($_POST['end-hour']) && $_POST['end-hour'] !=='') {
                $endHour = $dateUtility->setTime($_POST['end-hour']);
                $calendar->setEndHour($endHour);
            } else {
                $calendar->setEndHour(null);
            }
            $task->setNotification('0');
            $task->setState(0);
            $calendar->addTask($task);
        }

        return $calendar;
    }

    /**
     * @param $startDateString
     * @param NotifierInterface $notifier
     * @param CalendarRepository $calendarRepository
     * @param DateUtility $dateUtility
     * @param Task $task
     * @return Calendar|RedirectResponse|null
     */
    public function getCalendarByStartDate(
        $startDateString,
        NotifierInterface $notifier,
        CalendarRepository $calendarRepository,
        DateUtility $dateUtility,
        Task $task
    ) {

        // Check if format date Y-m-d
        if ($dateUtility->checkDate($startDateString) === false) {
            $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
            return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
        } else {
            $startDate = $dateUtility->checkDate($startDateString);
        }

        $calendar = $calendarRepository->findOneBy(
            ['startDate' => $startDate],
            ['id' => 'DESC']
        );

        if (!($calendar instanceof Calendar)) {
            // Create calendar object of start date
            $calendar = new Calendar();
            // Set dates
            if ($startDate) {
                $calendar->setStartDate($startDate);
            }
            // Set hours
            if (isset($_POST['start-hour']) && $_POST['start-hour'] !=='') {
                $startHour = $dateUtility->setTime($_POST['start-hour']);
                $calendar->setStartHour($startHour);
            } else {
                $calendar->setStartHour(null);
            }
            if (isset($_POST['end-hour']) && $_POST['end-hour'] !=='') {
                $endHour = $dateUtility->setTime($_POST['end-hour']);
                $calendar->setEndHour($endHour);
            } else {
                $calendar->setEndHour(null);
            }
            $task->setNotification('0');
            $task->setState(0);
            $calendar->addTask($task);
        } else {
            if (isset($_POST['start-hour']) && $_POST['start-hour'] !=='') {
                $startHour = $dateUtility->setTime($_POST['start-hour']);
                $calendar->setStartHour($startHour);
            } else {
                $calendar->setStartHour(null);
            }
            if (isset($_POST['end-hour']) && $_POST['end-hour'] !=='') {
                $endHour = $dateUtility->setTime($_POST['end-hour']);
                $calendar->setEndHour($endHour);
            } else {
                $calendar->setEndHour(null);
            }
            $task->setNotification('0');
            $task->setState(0);
            $calendar->addTask($task);
        }

        return $calendar;
    }


    /**
     * @param $startDateString
     * @param $endDateString
     * @param NotifierInterface $notifier
     * @param CalendarRepository $calendarRepository
     * @param DateUtility $dateUtility
     * @param Task $task
     * @return Calendar|RedirectResponse|null
     */
    public function getReservationCalendar(
        $startDateString,
        $endDateString,
        NotifierInterface $notifier,
        CalendarRepository $calendarRepository,
        DateUtility $dateUtility,
        Reservation $reservation
    ) {

        // Check if format date Y-m-d
        if ($dateUtility->checkDate($startDateString) === false && $dateUtility->checkDate($endDateString) === false) {
            $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
            return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
        } else {
            $startDate = $dateUtility->checkDate($startDateString);
            $endDate = $dateUtility->checkDate($endDateString);
        }

        $calendar = $calendarRepository->findOneBy(
            ['startDate' => $startDate, 'endDate' => $endDate],
            ['id' => 'DESC']
        );

        if (!($calendar instanceof Calendar)) {
            // Create calendar object of start date
            $calendar = new Calendar();
            // Set dates
            $calendar->setStartDate($startDate);
            $calendar->setEndDate($endDate);
            // Set hours
            if (isset($_POST['start-hour']) && $_POST['start-hour'] !=='') {
                $startHour = $dateUtility->setTime($_POST['start-hour']);
                $calendar->setStartHour($startHour);
            } else {
                $calendar->setStartHour(null);
            }
            if (isset($_POST['end-hour']) && $_POST['end-hour'] !=='') {
                $endHour = $dateUtility->setTime($_POST['end-hour']);
                $calendar->setEndHour($endHour);
            } else {
                $calendar->setEndHour(null);
            }
            $reservation->setIsOwner(true);
            $calendar->addReservation($reservation);
        } else {
            // Edit existing calendar object
            $calendar->setEndDate($endDate);
            if (isset($_POST['start-hour']) && $_POST['start-hour'] !=='') {
                $startHour = $dateUtility->setTime($_POST['start-hour']);
                $calendar->setStartHour($startHour);
            } else {
                $calendar->setStartHour(null);
            }
            if (isset($_POST['end-hour']) && $_POST['end-hour'] !=='') {
                $endHour = $dateUtility->setTime($_POST['end-hour']);
                $calendar->setEndHour($endHour);
            } else {
                $calendar->setEndHour(null);
            }
            $reservation->setIsOwner(true);
            $calendar->addReservation($reservation);
        }

        return $calendar;
    }

    /**
     * @param $startDateString
     * @param $endDateString
     * @param Calendar $calendar
     * @param NotifierInterface $notifier
     * @param DateUtility $dateUtility
     * @return Calendar|RedirectResponse
     */
    public function changeCalendar(
        $startDateString,
        $endDateString,
        Calendar $calendar,
        NotifierInterface $notifier,
        DateUtility $dateUtility
    ) {

        // Check if format date Y-m-d
        if ($dateUtility->checkDate($startDateString) === false && $dateUtility->checkDate($endDateString) === false) {
            $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
            return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
        } else {
            $startDate = $dateUtility->checkDate($startDateString);
            $endDate = $dateUtility->checkDate($endDateString);
        }

        $calendar->setStartDate($startDate);
        $calendar->setEndDate($endDate);
        if (isset($_POST['start-hour']) && $_POST['start-hour'] !=='') {
            $startHour = $dateUtility->setTime($_POST['start-hour']);
            $calendar->setStartHour($startHour);
        }
        if (isset($_POST['end-hour']) && $_POST['end-hour'] !=='') {
            $endHour = $dateUtility->setTime($_POST['end-hour']);
            $calendar->setEndHour($endHour);
        }

        //$task->setNotification('0');
        //$task->setState(0);
        //$calendar->addTask($task);

        return $calendar;
    }

    /**
     * @param array $apartments
     * @param CalendarRepository $calendarRepository
     * @return mixed
     */
    public function getClendarsByMultipleApartment(
        $apartments,
        CalendarRepository $calendarRepository
    ) {
        foreach ($apartments as $apartment) {
            if (count($apartment->getTask()) > 0) {
                foreach ($apartment->getTask() as $task) {
                    $tasks[] = $task;
                    $calendarIds[] = $task->getCalendar();
                }
            } else {
                $calendarIds = null;
            }
        }

        if (isset($calendarIds) && is_array($calendarIds)) {
            $calendarIdsArr = array_unique($calendarIds, SORT_STRING);
            $calendars = $calendarRepository->findById($calendarIdsArr);
            return $calendars;
        } else {
            return null;
        }
    }

    /**
     * @param Housing $apartment
     * @param CalendarRepository $calendarRepository
     * @return mixed
     */
    public function getClendarsByApartment(
        Housing $apartment,
        CalendarRepository $calendarRepository
    ) {
        foreach ($apartment->getTask() as $task) {
            $calendarIds[] = $task->getCalendar();
        }

        if (isset($calendarIds) && is_array($calendarIds)) {
            $calendarIdsArr = array_unique($calendarIds, SORT_STRING);
            $calendars = $calendarRepository->findById($calendarIdsArr);
        }

        if (isset($calendars)) {
            return $calendars;
        } else {
            return null;
        }
    }

    /**
     * @param $tasks
     * @param CalendarRepository $calendarRepository
     * @return int|mixed|string|null
     */
    public function getCalendarsByTasks(
        $tasks,
        CalendarRepository $calendarRepository
    ) {
        if ($tasks) {
            foreach ($tasks as $task) {
                if ($task->getCalendar()) {
                    $calendarIds[] = $task->getCalendar()->getId();
                }
            }
            if (isset($calendarIds) && is_array($calendarIds)) {
                $calendarIdsArr = array_unique($calendarIds, SORT_STRING);
                $calendars = $calendarRepository->findByTasks($calendarIdsArr);
            }
            return $calendars;
        } else {
            return null;
        }
    }
}
