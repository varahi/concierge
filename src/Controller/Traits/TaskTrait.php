<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\DateUtility;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

trait TaskTrait
{
    private $doctrine;

    public function __construct(
        ManagerRegistry $doctrine
    ) {
        $this->doctrine = $doctrine;
    }

    /**
     * @param Task $entryTask
     */
    public function createEndTask(
        Task $entryTask
    ) {
        $entityManager = $this->doctrine->getManager();
        $endTask = new Task();

        if (is_null($entryTask->getTitle())) {
            $endTask->setTitle('End task');
        } else {
            //$endTask->setTitle($entryTask->getTitle());
            $endTask->setTitle('End task');
        }
        if (is_null($entryTask->getDescription())) {
            $endTask->setDescription('');
        } else {
            $endTask->setDescription($entryTask->getDescription());
        }

        if ($entryTask->getCalendar()) {
            $endTask->setCalendar($entryTask->getCalendar());
        }
        if ($entryTask->getHousing()) {
            $endTask->setHousing($entryTask->getHousing());
        }
        if ($entryTask->getRenter()) {
            $endTask->setRenter($entryTask->getRenter());
        }
        if ($entryTask->getHousing()->getEmployer()) {
            $endTask->addUser($entryTask->getHousing()->getEmployer());
        }
        if ($entryTask->getUsers()) {
            foreach ($entryTask->getUsers() as $user) {
                $endTask->addUser($user);
            }
        }
        // Note! To end task we set start date as end date. It needs to normal sorting of tasks.
        if ($entryTask->getEndDate()) {
            $endTask->setStartDate($entryTask->getEndDate());
            $endTask->setEndDate($entryTask->getEndDate());
        }
        $entityManager->persist($endTask);
        $entityManager->flush();
    }

    /**
     * @param array $apartmentIds
     * @param TaskRepository $taskRepository
     * @return array|int|string
     */
    public function getTasksByApartments(
        array $apartmentIds,
        TaskRepository $taskRepository
    ) {
        if (isset($apartmentIds) && is_array($apartmentIds)) {
            foreach ($apartmentIds as $apartmentId) {
                $notArchivedTasks[] = $taskRepository->findByApartment(intval($apartmentId));
            }
        }
        // Merge task arrays
        $taskArraysMerged = [];
        foreach ($notArchivedTasks as $array) {
            $taskArraysMerged = array_merge($taskArraysMerged, $array);
        }

        if (isset($taskArraysMerged)) {
            return $taskArraysMerged;
        } else {
            return null;
        }
    }

    /**
     * @param array $apartmentIds
     * @param TaskRepository $taskRepository
     * @return array|int|string
     */
    public function getTasksByApartmentsNotArchived(
        array $apartmentIds,
        TaskRepository $taskRepository
    ) {
        if (isset($apartmentIds) && is_array($apartmentIds)) {
            foreach ($apartmentIds as $apartmentId) {
                $notArchivedTasks[] = $taskRepository->findByApartment(intval($apartmentId));
            }
        }
        // Merge task arrays
        $taskArraysMerged = [];
        foreach ($notArchivedTasks as $array) {
            $taskArraysMerged = array_merge($taskArraysMerged, $array);
        }

        if (isset($taskArraysMerged)) {
            return $taskArraysMerged;
        } else {
            return null;
        }
    }


    /**
     * @param Request $request
     * @param TaskRepository $taskRepository
     * @param DateUtility $dateUtility
     * @param array $employeeId
     * @param string $startDateDiff
     * @param string $endDateDiff
     * @return float|int|mixed|string
     */
    public function getTasksByDateRange(
        Request $request,
        TaskRepository $taskRepository,
        DateUtility $dateUtility,
        array $employeeId,
        string $startDateDiff,
        string $endDateDiff
    ) {
        $dateStartTimestamp = $request->query->get('dateStart');
        $dateEndTimestamp = $request->query->get('dateEnd');
        if ($dateStartTimestamp == null && $dateEndTimestamp == null) {
            // Get current date and +1 month after
            $currentDateStr = date($this->dateFormat);
            $startDateStr = date($this->dateFormat, strtotime($startDateDiff, strtotime($currentDateStr)));
            $endDateStr = date($this->dateFormat, strtotime($endDateDiff, strtotime($currentDateStr)));
            $startDate = $dateUtility->checkDate($startDateStr);
            $endDate = $dateUtility->checkDate($endDateStr);
            $tasks = $taskRepository->findByDatesRangeAndForeignApartment('ASC', $startDate, $endDate, $employeeId);
        } else {
            $startDate = new \DateTime();
            $endDate = new \DateTime();
            $startDate->setTimestamp((int)$dateStartTimestamp)->format($this->dateFormat);
            $endDate->setTimestamp((int)$dateEndTimestamp)->format($this->dateFormat);
            $tasks = $taskRepository->findByDatesRangeAndForeignApartment('ASC', $startDate, $endDate, $employeeId);
        }

        return $tasks;
    }


    /**
     * @param Request $request
     * @param TaskRepository $taskRepository
     * @param DateUtility $dateUtility
     * @return int|mixed|string
     */
    public function getTasksByDateRangeAndCustomOrdering(
        Request $request,
        TaskRepository $taskRepository,
        DateUtility $dateUtility,
        $order,
        $fieldName,
        bool $isCalendar
    ) {
        $dateStartTimestamp = $request->query->get('dateStart');
        $dateEndTimestamp = $request->query->get('dateEnd');
        if ($dateStartTimestamp == null && $dateEndTimestamp == null) {
            // Get current date and +1 month after
            $currentDateStr = date($this->dateFormat);
            $endDateStr = date($this->dateFormat, strtotime("+1 month", strtotime($currentDateStr)));
            $startDate = $dateUtility->checkDate($currentDateStr);
            $endDate = $dateUtility->checkDate($endDateStr);
            if ($fieldName == null) {
                $tasks = $taskRepository->findByDatesRangeAndForeignApartment($order, $startDate, $endDate, $employeeId = '');
            } else {
                $tasks = $taskRepository->findByDatesRangeAndForeignApartmentSorting($order, $fieldName, $startDate, $endDate);
            }
        } else {
            $startDate = new \DateTime();
            $endDate = new \DateTime();
            $startDate->setTimestamp((int)$dateStartTimestamp)->format($this->dateFormat);
            $endDate->setTimestamp((int)$dateEndTimestamp)->format($this->dateFormat);
            if ($fieldName == null) {
                $tasks = $taskRepository->findByDatesRangeAndForeignApartment($order, $startDate, $endDate, $employeeId = '');
            } else {
                $tasks = $taskRepository->findByDatesRangeAndForeignApartmentSorting($order, $fieldName, $startDate, $endDate);
            }
        }

        return $tasks;
    }
}
