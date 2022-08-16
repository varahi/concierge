<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Entity\Calendar;
use App\Entity\Invoice;
use App\Entity\Renter;
use App\Entity\User;
use App\Entity\Reservation;
use App\Entity\Task;
use App\Repository\CalendarRepository;
use App\Repository\RenterRepository;
use App\Repository\HousingRepository;
use App\Repository\UserRepository;
use App\Service\DateUtility;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\Persistence\ManagerRegistry;

trait RenterTrait
{
    private $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        ManagerRegistry $doctrine
    ) {
        $this->doctrine = $doctrine;
    }

    /**
     * @param $user
     * @param HousingRepository $housingRepository
     * @param RenterRepository $renterRepository
     * @return mixed
     */
    public function rentersByOwner(
        $user,
        HousingRepository $housingRepository,
        RenterRepository $renterRepository
    ) {
        $apartments = $housingRepository->findByUser($user);
        if ($apartments) {
            foreach ($apartments as $apartment) {
                $renterIds[] = $apartment->getRenter()->getId();
            }
        }
        $renterIdsArr = array_unique($renterIds, SORT_STRING);
        return $renterRepository->findById($renterIdsArr);
    }


    /**
     * @param Request $request
     * @param $post
     * @param $apartment
     * @param RenterRepository $renterRepository
     * @param CalendarRepository $calendarRepository
     * @param NotifierInterface $notifier
     * @param TranslatorInterface $translator
     * @param DateUtility $dateUtility
     * @return RedirectResponse|void
     */
    public function createReservationAndRenter(
        Request $request,
        $post,
        $apartment,
        $user,
        CalendarRepository $calendarRepository,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        DateUtility $dateUtility
    ) {

        // Start add renter function
        $startDateString = $post['startDate'];
        $endDateString = $post['endDate'];

        // Check date format
        if ($dateUtility->checkDate($startDateString) === false) {
            $message = $translator->trans('Incorrect date', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            $referer = $request->headers->get('referer');
            return new RedirectResponse($referer);
        }

        // Add task to renter
        $reservation = new Reservation();
        // Set calendar for reservation
        $calendar = $this->getReservationCalendar($startDateString, $endDateString, $notifier, $calendarRepository, $dateUtility, $reservation);

        // Persist data
        $entityManager = $this->doctrine->getManager();
        $reservation->setTitle('Occupation by ' . $user->getFirstName() .' '. $user->getLastName());
        $reservation->setUser($user);
        $reservation->setHousing($apartment);
        $entityManager->persist($reservation);

        // Set renter for calendar
        $calendar->setUser($reservation->getUser());
        $entityManager->persist($calendar);

        // Flush data
        $entityManager->flush();
    }

    /**
     * @param Request $request
     * @param $post
     * @param $newRenter
     * @param $apartment
     * @param RenterRepository $renterRepository
     * @param CalendarRepository $calendarRepository
     * @param NotifierInterface $notifier
     * @param TranslatorInterface $translator
     * @param DateUtility $dateUtility
     * @return Task|RedirectResponse
     */
    public function createTasksAndRenter(
        Request $request,
        $post,
        $newRenter,
        $apartment,
        RenterRepository $renterRepository,
        CalendarRepository $calendarRepository,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        DateUtility $dateUtility
    ) {
        $existingRenter = $renterRepository->findOneBy(
            [
                'firstName' => $post['firstName'],
                'lastName' => $post['lastName'],
                'email' => $post['email'],
            ]
        );
        if (!($existingRenter instanceof Renter)) {
            // nothing to do, use empty Renter object
        } else {
            // Use existing renter
            $newRenter = $existingRenter;
        }

        // Start add renter function
        $startDateString = $post['startDate'];
        $endDateString = $post['endDate'];

        // Check date format
        if ($dateUtility->checkDate($startDateString) === false) {
            $message = $translator->trans('Incorrect date', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            $referer = $request->headers->get('referer');
            return new RedirectResponse($referer);
        } else {
            $startDate = $dateUtility->checkDate($startDateString);
            $endDate = $dateUtility->checkDate($endDateString);
        }

        // Add task to renter
        $entryTask = new Task();
        // Set calendar for task
        $calendar = $this->getCalendar($startDateString, $endDateString, $notifier, $calendarRepository, $dateUtility, $entryTask);

        // Persist data
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($newRenter);

        // Add tasks
        // Set employer for task by relation task.housing.employer
        if ($apartment->getEmployer()) {
            $entryTask->addUser($apartment->getEmployer());
        }
        $entryTask->setIsEntry(1);
        if ($post['address'] !=='') {
            //$entryTask->setTitle('Task for ' . $post['address']);
            $entryTask->setTitle('Entry Task');
        }
        $entryTask->setRenter($newRenter);
        $entryTask->setHousing($apartment);
        if ($startDate) {
            $entryTask->setStartDate($startDate);
        }
        if ($endDate) {
            $entryTask->setEndDate($endDate);
        }
        $entityManager->persist($entryTask);

        // Set renter for calendar
        $calendar->setRenter($entryTask->getRenter());

        // Set times for calendar
        if (!empty($post['startTime']) && $post['startTime'] !=='') {
            $startHour = \datetime::createfromformat('H:i', $post['startTime']);
            $calendar->setStartHour($startHour);
        }
        if (!empty($post['endTime']) && $post['endTime'] !=='') {
            $endHour = \datetime::createfromformat('H:i', $post['endTime']);
            $calendar->setEndHour($endHour);
        }

        $entityManager->persist($calendar);

        // Set properties for end task
        $this->createEndTask($entryTask);

        // check availability for existing calenadrs
        // @ToDo: incorrect data for multipe calendars
        /*
        if ($apartment) {
            $calendars = $this->getClendarsByApartment($apartment, $calendarRepository);
        }
        $dateCompare1 = strtotime($startDateString);
        if ($calendars) {
            foreach ($calendars as $key => $calendar) {
                $startDate[$key] = $calendar->getStartDate()->getTimestamp();
                $endDate[$key] = $calendar->getEndDate()->getTimestamp();

                if ($dateCompare1 >= $startDate[$key] && $dateCompare1 <= $endDate[$key]) {
                    $message = $translator->trans('Renter date busy', array(), 'flash');
                    $notifier->send(new Notification($message, ['browser']));
                    $referer = $request->headers->get('referer');
                    return new RedirectResponse($referer);
                }
            }
        }
        */

        // Flush data
        $entityManager->flush();

        return $entryTask;
    }
}
