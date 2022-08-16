<?php

namespace App\Controller;

use App\Controller\Traits\CalendarTrait;
use App\Controller\Traits\InvoiceTrait;
use App\Controller\Traits\TaskTrait;
use App\Entity\Calendar;
use App\Entity\Invoice;
use App\Entity\InvoiceContain;
use App\Entity\Prestation;
use App\Entity\Renter;
use App\Entity\Reservation;
use App\Entity\Task;
use App\Form\Renter\CalendarFormType;
use App\Form\Reservation\ReservationFormType;
use App\Repository\CalendarRepository;
use App\Repository\HousingRepository;
use App\Repository\InvoiceContainRepository;
use App\Repository\InvoiceRepository;
use App\Repository\MaterialsRepository;
use App\Repository\PacksRepository;
use App\Repository\PrestationRepository;
use App\Repository\RenterRepository;
use App\Repository\ReservationRepository;
use App\Repository\ServicesRepository;
use App\Repository\TaskRepository;
use App\Service\DateUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;

class CalendarController extends AbstractController
{
    use CalendarTrait;

    use InvoiceTrait;

    use TaskTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_EMPLOYER = 'ROLE_EMPLOYER';

    public const ROLE_OWNER = 'ROLE_OWNER';

    /**
     * @var Security
     */
    private $security;

    private $twig;

    private $urlGenerator;

    private $dateFormat;

    private $placeholderDateFormat;

    private $scriptDateFormat;

    private $doctrine;

    /**
     * @param Security $security
     * @param Environment $twig
     * @param EmailVerifier $emailVerifier
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $dateFormat
     * @param string $placeholderDateFormat
     * @param string $scriptDateFormat
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        Security              $security,
        Environment           $twig,
        EmailVerifier         $emailVerifier,
        UrlGeneratorInterface $urlGenerator,
        string $dateFormat,
        string $placeholderDateFormat,
        string $scriptDateFormat,
        ManagerRegistry $doctrine
    ) {
        $this->security = $security;
        $this->twig = $twig;
        $this->emailVerifier = $emailVerifier;
        $this->urlGenerator = $urlGenerator;
        $this->dateFormat = $dateFormat;
        $this->placeholderDateFormat = $placeholderDateFormat;
        $this->scriptDateFormat = $scriptDateFormat;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/edit-event/task-{id}", name="app_edit_event")
     */
    public function editEvent(
        Request $request,
        TranslatorInterface $translator,
        ServicesRepository $servicesRepository,
        PrestationRepository $prestationRepository,
        PacksRepository $packsRepository,
        MaterialsRepository $materialsRepository,
        RenterRepository $renterRepository,
        CalendarRepository $calendarRepository,
        HousingRepository $housingRepository,
        TaskRepository $taskRepository,
        InvoiceRepository $invoiceRepository,
        InvoiceContainRepository $invoiceContainRepository,
        Task $task,
        NotifierInterface $notifier,
        DateUtility $dateUtility
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) ||
            $this->security->isGranted(self::ROLE_EMPLOYER) ||
            $this->security->isGranted(self::ROLE_OWNER)) {
            $services = $servicesRepository->findAll();
            $apartments = $housingRepository->findAll();
            $prestations = $prestationRepository->findAll();
            $packs = $packsRepository->findAll();
            $materials = $materialsRepository->findAll();

            // Edit data if have renter
            if ($task->getRenter() !==null) {
                //if ($task->getIsArchived() == 0 && $task->getCalendar()->getIsArchived() == 0) {}
                $renterId = $task->getRenter()->getId();
                $renter = $renterRepository->findOneBy(['id' => $renterId]);

                // Get existing invoice, prestations, services, packs and materials
                if ($task->getInvoice()) {
                    $invoiceContains = $invoiceContainRepository->findBy(['invoice' => $task->getInvoice()->getId()]);
                    foreach ($invoiceContains as $invoiceContain) {
                        if ($invoiceContain->getPrestation()) {
                            $invoicePrestations[] = $invoiceContain->getPrestation();
                        }
                        if ($invoiceContain->getService()) {
                            $invoiceServices[] = $invoiceContain->getService();
                        }
                        if ($invoiceContain->getPack()) {
                            $invoicePacks[] = $invoiceContain->getPack();
                        }
                        if ($invoiceContain->getMaterial()) {
                            $invoiceMaterials[] = $invoiceContain->getMaterial();
                        }
                    }
                } else {
                    $invoicePrestations = null;
                    $invoiceServices = null;
                    $invoiceContains = null;
                    $invoicePacks = null;
                    $invoiceMaterials = null;
                }

                // Transfer invoice to CalendarFormType to get only needed CollectionType
                if ($task->getInvoice()) {
                    // Call form with existing invoice
                    $invoiceToForm = $invoiceRepository->findOneBy(['id' => $task->getInvoice()->getId()]);
                } else {
                    // Call form without existing attached invoice
                    $invoiceToForm = null;
                }

                $form = $this->createForm(CalendarFormType::class, $renter, [
                    'action' => $this->generateUrl('app_edit_event', ['id' => $task->getId()]),
                    'invoice' => $invoiceToForm,
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    $post = $request->request->get('calendar_form');
                    $entityManager = $this->doctrine->getManager();

                    $apartmentId = $post['apartment'];
                    $startDateString = $request->request->get('start-date');
                    $endDateString = $request->request->get('end-date');
                    $calendar = $this->getCalendar($startDateString, $endDateString, $notifier, $calendarRepository, $dateUtility, $task);

                    // Set note for task
                    if ($post['owner_note'] !=='') {
                        $task->setNote($post['owner_note']);
                    }

                    // Set apartment to task
                    if ($apartmentId !=='') {
                        $apartment = $housingRepository->findOneBy(['id' => $apartmentId]);
                        $task->setHousing($apartment);
                    }

                    // Set dates from POST to task property
                    if ($startDateString !=='') {
                        if ($dateUtility->checkDate($startDateString) === false) {
                            $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
                            return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                        } else {
                            $startDate = $dateUtility->checkDate($startDateString);
                            $task->setStartDate($startDate);
                        }
                    }
                    if ($endDateString !=='') {
                        if ($dateUtility->checkDate($endDateString) === false) {
                            $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
                            return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                        } else {
                            $endDate = $dateUtility->checkDate($endDateString);
                            $task->setEndDate($endDate);
                        }
                    }

                    // Find related task, of existing task
                    $relatedTasks = $taskRepository->findRelaytedTask($task);
                    if (null !== $relatedTasks) {
                        // Set dates property to related tasks
                        if (is_array($relatedTasks)) {
                            foreach ($relatedTasks as $relatedTask) {
                                $relatedTask->setStartDate($startDate);
                                $relatedTask->setEndDate($endDate);
                            }
                        } else {
                            $relatedTasks->setStartDate($startDate);
                            $relatedTasks->setEndDate($endDate);
                        }
                    }

                    // If customer change dates, the calendar will be null and we should be set new dates
                    // Start modify calendars and dates
                    //--------------------------------------------------------------------------------------------------------------------
                    if ($task->getCalendar()->getId() == null) {
                        $newCalendar = new Calendar();
                        $modifiedCalendar = $this->changeCalendar($startDateString, $endDateString, $newCalendar, $notifier, $dateUtility);
                        $calendar = $newCalendar;
                    } else {
                        // If customer change dates, and already is calendar with changed date
                        $modifiedCalendar = $calendarRepository->findOneBy(['id' => $task->getCalendar()->getId()]);
                    }

                    $modifiedCalendar->addTask($task);

                    // If realted tasks more than one
                    if (null !== $relatedTasks) {
                        if (is_array($relatedTasks)) {
                            foreach ($relatedTasks as $relatedTask) {
                                $modifiedCalendar->addTask($relatedTask);
                            }
                        } else {
                            $modifiedCalendar->addTask($relatedTasks);
                        }
                    }

                    $modifiedCalendar->setRenter($task->getRenter());
                    $entityManager->persist($modifiedCalendar);
                    // Finish modify calendars and dates
                    //--------------------------------------------------------------------------------------------------------------------

                    // We don't need invoices for single task. So we check if task have not invoice and task is not single
                    if ($task->getInvoice() == null && $task->getIsSingle() == null) {
                        // Set invoice
                        $invoice = $this->setInvoice($post, $task, $relatedTask, $apartment, $renter, $dateUtility, $renterRepository);
                        $this->setAppointed($request, $invoice, 'calendar_form');
                    }

                    // If invoice exist
                    if ($task->getInvoice()) {
                        $invoice = $invoiceRepository->findOneBy(['id' => $task->getInvoice()]);
                        $this->setAppointed($request, $invoice, 'calendar_form');

                        // Currently the owner can't add prestations or services
                        if ($this->security->isGranted(self::ROLE_ADMIN)) {
                            // If prestation has property isTask == 1, then automatically creates another task with name as prestation name
                            $this->checkPrestationHasTask($request, $prestationRepository, $invoice, $task, 'calendar_form', 'new_prestation');
                            // Set related prestations
                            $this->setInvoiceRelatedParams($request, $prestationRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'prestation', 'setPrestation');
                            // Set related services
                            $this->setInvoiceRelatedParams($request, $servicesRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'service', 'setService');
                            // Set related packs
                            $this->setInvoiceRelatedParams($request, $packsRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'pack', 'setPack');
                            // Set related materials
                            $this->setInvoiceRelatedParams($request, $materialsRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'material', 'setMaterial');
                        }

                        // Calculate total sum for invoice
                        if (count($invoice->getContain()) > 0) {
                            foreach ($invoice->getContain() as $contain) {
                                $totalContain[] = $contain->getTotal();
                            }
                            $total = array_sum($totalContain);
                            $invoice->setTotal($total);
                        }
                    }

                    // Set renter for calendar
                    $calendar->setRenter($task->getRenter());
                    $entityManager->persist($invoice);
                    $entityManager->persist($calendar);
                    $entityManager->persist($renter);
                    $entityManager->persist($task);
                    $entityManager->flush();
                    $message = $translator->trans('Event updated', array(), 'flash');
                    $notifier->send(new Notification($message, ['browser']));
                    $referer = $request->headers->get('referer');
                    return new RedirectResponse($referer);
                }

                // If variables not exist
                if (!isset($invoiceMaterials)) {
                    $invoiceMaterials = null;
                }
                if (!isset($invoicePacks)) {
                    $invoicePacks = null;
                }
                if (!isset($invoicePrestations)) {
                    $invoicePrestations = null;
                }
                if (!isset($invoiceServices)) {
                    $invoiceServices = null;
                }

                return new Response($this->twig->render('administrator/forms/renter/edit_event.html.twig', [
                    'services' => $services,
                    'prestations' => $prestations,
                    'task' => $task,
                    'apartments' => $apartments,
                    'invoicePrestations' => $invoicePrestations,
                    'invoiceServices' => $invoiceServices,
                    'invoiceContains' => $invoiceContains,
                    'invoiceMaterials' => $invoiceMaterials,
                    'invoicePacks' => $invoicePacks,
                    'packs' => $packs,
                    'materials' => $materials,
                    'placeholderDateFormat' => $this->placeholderDateFormat,
                    'scriptDateFormat' => $this->scriptDateFormat,
                    'edit_event_form' => $form->createView(),
                ]));
            } else {
                $message = $translator->trans('Task has no renter', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirectToRoute("app_login");
            }

            return new Response($this->twig->render('administrator/forms/renter/event.html.twig', [
                'services' => $services,
                'task' => $task
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-event/event-{id}", name="app_delete_event")
     */
    public function deleteEvent(
        Request $request,
        Calendar $calendar,
        ReservationRepository $reservationRepository,
        TaskRepository $taskRepository,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_OWNER)) {
            $entityManager = $this->doctrine->getManager();

            // Remove reservation if exist in GET
            if ($request->query->get('reservation')) {
                $reservationId = $request->query->get('reservation');
                $reservation = $reservationRepository->findOneBy(['id' => $reservationId]);
                $entityManager->remove($reservation);
                $entityManager->flush();
            }

            // Remove task if exist in GET
            if ($request->query->get('task')) {
                $taskId = $request->query->get('task');
                $task = $taskRepository->findOneBy(['id' => $taskId]);
                $relatedTask = $taskRepository->findRelaytedTask($task);
                $entityManager->remove($task);
                if (is_null($relatedTask)) {
                    // RelatedTask not exist
                } else {
                    if (is_array($relatedTask) && count($relatedTask) >= 1) {
                        foreach ($relatedTask as $item) {
                            $entityManager->remove($item);
                        }
                    } else {
                        $entityManager->remove($relatedTask);
                    }
                }
                $entityManager->flush();
            }

            // Remove calendar if has not tasks or reservations
            if (count($calendar->getReservations()) == 0 && count($calendar->getTask()) == 0) {
                $entityManager->remove($calendar);
            }

            $entityManager->flush();

            $message = $translator->trans('Event deleted', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            $referer = $request->headers->get('referer');
            return new RedirectResponse($referer);
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/fullcalendar.json", defaults={"_format": "json"}, name="app_full_calendar", methods={"GET","HEAD"})
     */
    public function fullCalendarJson(
        Request $request,
        HousingRepository $housingRepository,
        TaskRepository $taskRepository,
        ReservationRepository $reservationRepository,
        TranslatorInterface $translator,
        NotifierInterface $notifier,
        DateUtility $dateUtility
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            //$tasks = $taskRepository->findAll();
            if ($request->query->get('employee')) {
                $employeeId = explode(',', $request->query->get('employee'));
            } else {
                $employeeId = [];
            }
            // We get only actual tasks, not all
            $tasks = $this->getTasksByDateRange($request, $taskRepository, $dateUtility, $employeeId, '-15 days', '+6 month');
            $reservations = $reservationRepository->findAll();
            $arrData = $this->getJsonArrData($housingRepository, $taskRepository, $tasks, $reservations);
            return new JsonResponse($arrData);
        } elseif ($this->security->isGranted(self::ROLE_EMPLOYER)) {
            $userId = $this->security->getUser()->getId();
            $tasks = $taskRepository->findByUser($userId);
            $reservations = $reservationRepository->findByUser($userId);
            $arrData = $this->getJsonArrData($housingRepository, $taskRepository, $tasks, $reservations);
            return new JsonResponse($arrData);
        } elseif ($this->security->isGranted(self::ROLE_OWNER)) {
            $user = $this->security->getUser();
            foreach ($user->getApartments() as $apartment) {
                if (is_array($apartment->getTask())) {
                    foreach ($apartment->getTask() as $task) {
                        $tasks[] = $task;
                    }
                } else {
                    $tasks = [];
                }
                $userId = $this->security->getUser()->getId();
                $reservations = $reservationRepository->findByUser($userId);
            }
            $arrData = $this->getJsonArrData($housingRepository, $taskRepository, $tasks, $reservations);
            return new JsonResponse($arrData);
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    private function getJsonArrData(
        HousingRepository $housingRepository,
        TaskRepository $taskRepository,
        $tasks,
        $reservations
    ) {
        if ($tasks == null) {
            $apartmentIds = [];
        } else {
            // Find task with calendars
            if ($tasks) {
                foreach ($tasks as $task) {
                    if ($task->getHousing()) {
                        $apartmentIds[] = $task->getHousing();
                    }
                }
            }
        }

        if ($reservations == null) {
            $resApartmentIds = [];
        } else {
            // Find reservations with calendars
            if ($reservations) {
                foreach ($reservations as $reservation) {
                    if ($reservation->getUser()) {
                        $resApartmentIds[] = $reservation->getHousing();
                    }
                }
            }
        }

        // Get calendars only has related tasks
        // If we need to get only apartments with related tasks, uncomment 4 strings below
        //$apartmentIdsArr = array_unique($apartmentIds, SORT_STRING);
        //$resApartmentIdsArr = array_unique($resApartmentIds, SORT_STRING);
        //$mergedArray = array_merge($apartmentIdsArr, $resApartmentIdsArr);
        //$apartments = $housingRepository->findById($mergedArray, ['name' => 'ASC']);

        // Get all apartments
        //$apartments = $housingRepository->findAll();
        $apartments = $housingRepository->findAllOrderAndHasUser();
        foreach ($apartments as $apartment) {
            $id = $apartment->getId();
            if (empty($apartment->getName())) {
                $title = 'Appt ID-' . $apartment->getId();
            } else {
                $title = $apartment->getName();
                if (empty($apartment->getLogement()) && $apartment->getUser()->getCompany()) {
                    $title = $apartment->getName() .' '. $apartment->getUser()->getCompany();
                }
            }

            //$title = $apartment->getName();
            //if ($apartment->getUser()->getFirstName() || $apartment->getUser()->getLastName()) {
            //    $title = 'Appt' .' '. $apartment->getId() .' - '. $apartment->getUser()->getLastName();
            //} else {
            //    $title = 'Appt' .' '. $apartment->getId() .' - '. $apartment->getUser()->getCompany();
            //}

            $arrData1[] = [
                'id' => $id,
                'title' => $title,
                'task' => 'Appartements'
            ];
        }

        $singleTasks = $taskRepository->findSingle();
        if (!empty($singleTasks)) {
            $arrDataOneLine[] = [
                'id' => '9999',
                'title' => 'Single tasks',
                'task' => 'Tâches uniques'
            ];
            return array_merge($arrData1, $arrDataOneLine);
        } else {
            return $arrData1;
        }

        /*
        if (!empty($singleTasks)) {
            foreach ($singleTasks as $singleTask) {
                $arrData2[] = [
                    'id' => $singleTask->getId(),
                    'title' => $singleTask->getTitle(),
                    'task' => 'Tâches uniques'
                ];
            }
            return array_merge($arrData1, $arrData2);
        } else {
            return $arrData1;
        }
        */
    }

    /**
     * @Route("/edit-reservation/reservation-{id}", name="app_edit_reservation")
     */
    public function editReservation(
        Request $request,
        TranslatorInterface $translator,
        CalendarRepository $calendarRepository,
        Reservation $reservation,
        NotifierInterface $notifier,
        DateUtility $dateUtility
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) ||
            $this->security->isGranted(self::ROLE_EMPLOYER) ||
            $this->security->isGranted(self::ROLE_OWNER)) {
            $form = $this->createForm(ReservationFormType::class, $reservation, [
                'action' => $this->generateUrl('app_edit_reservation', ['id' => $reservation->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                //$post = $request->request->get('calendar_form');
                $startDateString = $request->request->get('start-date');
                $endDateString = $request->request->get('end-date');
                $calendar = $this->getReservationCalendar($startDateString, $endDateString, $notifier, $calendarRepository, $dateUtility, $reservation);

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($calendar);
                $entityManager->persist($reservation);
                $entityManager->flush();
                $message = $translator->trans('Reservation updated', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }
            return new Response($this->twig->render('administrator/forms/reservation/edit_reservation.html.twig', [
                'reservation' => $reservation,
                'placeholderDateFormat' => $this->placeholderDateFormat,
                'scriptDateFormat' => $this->scriptDateFormat,
                'edit_reservation_form' => $form->createView(),
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }
}
