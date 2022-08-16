<?php

namespace App\Controller;

use App\Controller\Traits\CalendarTrait;
use App\Controller\Traits\RenterTrait;
use App\Entity\Calendar;
use App\Entity\Housing;
use App\Entity\Materials;
use App\Entity\Packs;
use App\Entity\Renter;
use App\Entity\User;
use App\Repository\HousingRepository;
use App\Repository\MaterialsRepository;
use App\Repository\PacksRepository;
use App\Repository\RenterRepository;
use App\Service\Mailer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use App\Form\Renter\RenterFormType;
use App\Repository\CalendarRepository;
use App\Repository\ServicesRepository;
use App\Repository\TaskRepository;
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
use App\Service\DateUtility;
use Doctrine\Persistence\ManagerRegistry;

class RenterController extends AbstractController
{
    use RenterTrait;

    use CalendarTrait;
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_OWNER = 'ROLE_OWNER';

    /**
     * @var Security
     */
    private $security;

    private $twig;

    private $urlGenerator;

    private $placeholderDateFormat;

    private $scriptDateFormat;

    private $doctrine;

    /**
     * @param Security $security
     * @param Environment $twig
     * @param EmailVerifier $emailVerifier
     * @param UrlGeneratorInterface $urlGenerator
     * @param ManagerRegistry $doctrine
     * @param string $placeholderDateFormat
     * @param string $scriptDateFormat
     */
    public function __construct(
        Security $security,
        Environment $twig,
        EmailVerifier $emailVerifier,
        UrlGeneratorInterface $urlGenerator,
        ManagerRegistry $doctrine,
        string $placeholderDateFormat,
        string $scriptDateFormat
    ) {
        $this->security = $security;
        $this->twig = $twig;
        $this->emailVerifier = $emailVerifier;
        $this->urlGenerator = $urlGenerator;
        $this->placeholderDateFormat = $placeholderDateFormat;
        $this->scriptDateFormat = $scriptDateFormat;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/edit-renter/{id}", name="app_edit_renter")
     */
    public function editRenter(
        Request $request,
        Renter $renter,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        ServicesRepository $servicesRepository,
        CalendarRepository $calendarRepository,
        TaskRepository $taskRepository,
        DateUtility $dateUtility,
        PacksRepository $packsRepository,
        MaterialsRepository $materialsRepository,
        Mailer $mailer
    ): Response {
        $user = $this->security->getUser();
        if ($user != null && in_array(self::ROLE_ADMIN, $user->getRoles())) {
            $form = $this->createForm(RenterFormType::class, $renter, [
                'action' => $this->generateUrl('app_edit_renter', ['id' => $renter->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            $services = $servicesRepository->findAll();
            $taskId = $request->query->get('task');
            $taskObj = $taskRepository->findOneBy(['id' => $taskId], ['id' => 'DESC']);

            // Get pack attached to this renter
            if ($renter->getHousing()) {
                foreach ($renter->getHousing() as $housing) {
                    if (is_null($housing->getPacks())) {
                        $renterPackId = '0';
                    } else {
                        $renterPackId = $housing->getPacks()->getId();
                    }
                }
            }
            if ($renterPackId !='') {
                $renterPack = $packsRepository->findOneBy(['id' => $renterPackId]);
            }

            // Edit renter form
            if ($form->isSubmitted()) {
                $entityManager = $this->doctrine->getManager();
                // Works with calendar
                /*
                if (!empty($_POST['start-date'])) {
                    $startDateString = $request->request->get('start-date');
                    $endDateString = $request->request->get('end-date');

                    // Check if format date Y-m-d
                    if ($dateUtility->checkDate($startDateString) === false) {
                        $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
                        return new RedirectResponse($this->urlGenerator->generate('app_employer_profile'));
                    } else {
                        $startDate = $dateUtility->checkDate($startDateString);
                    }
                    if ($dateUtility->checkDate($endDateString) === false) {
                        $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
                        return new RedirectResponse($this->urlGenerator->generate('app_employer_profile'));
                    } else {
                        $endDate = $dateUtility->checkDate($endDateString);
                    }
                    $calendar = $calendarRepository->findOneBy(
                        ['startDate' => $startDate, 'endDate' => $endDate],
                        ['id' => 'DESC']
                    );
                    $task = $taskRepository->findOneBy(['id' => $_POST['task']], ['id' => 'DESC']);
                    //$entityManager = $this->getDoctrine()->getManager();
                    if (!($calendar instanceof Calendar)) {
                        // Create date obj of start date
                        $calendar = new Calendar();
                        $calendar->setStartDate($startDate);
                        $calendar->setEndDate($endDate);
                        if (!empty($_POST['start-hour'])) {
                            $startHour = \datetime::createfromformat('H:i', $_POST['start-hour']);
                            $calendar->setStartHour($startHour);
                        }
                        if (!empty($_POST['end-hour'])) {
                            $endHour = \datetime::createfromformat('H:i', $_POST['end-hour']);
                            $calendar->setStartHour($endHour);
                        }
                        $calendar->addTask($task);
                    } else {
                        //task.housing.renter
                        $calendar->setEndDate($endDate);
                        if (!empty($_POST['start-hour'])) {
                            $startHour = \datetime::createfromformat('H:i', $_POST['start-hour']);
                            $calendar->setStartHour($startHour);
                        }
                        if (!empty($_POST['end-hour'])) {
                            $endHour = \datetime::createfromformat('H:i', $_POST['end-hour']);
                            $calendar->setEndHour($endHour);
                        }
                        $calendar->addTask($task);
                    }
                }
                */
                // Send email if notification turned on
                $task = $taskRepository->findOneBy(['id' => $_POST['task']], ['id' => 'DESC']);
                if ($task->getNotification() == 1) {
                    $postArray = $_POST['renter_form'];
                    $message = $translator->trans('Task changed', array(), 'flash');
                    $subject = $translator->trans($message, array(), 'flash');
                    $mailer->sendTaskEmail($task, $subject, 'emails/task_notification.html.twig', $postArray);
                }

                // Deducate -1 material if selected
                $renterFormData = $request->request->get('renter_form');
                if (isset($renterFormData['materials'])) {
                    foreach ($renterFormData['materials'] as $materialId) {
                        $material = $materialsRepository->findOneBy(['id' => $materialId], ['id' => 'DESC']);
                        $materialQty = $material->getQuantity() - 1;
                        $material->setQuantity($materialQty);
                    }
                }

                //$entityManager->persist($calendar);
                //$entityManager->persist($material);
                $entityManager->flush();
                $notifier->send(new Notification('Your renter updated', ['browser']));
                //$routeName = $request->get('_route');
                //return new RedirectResponse($this->urlGenerator->generate($routeName));
                return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
            }

            return new Response($this->twig->render('administrator/forms/renter/edit_renter.html.twig', [
                'renter' => $renter,
                'services' => $services,
                'edit_renter_form' => $form->createView(),
                'taskId' => $taskId,
                'taskObj' => $taskObj,
                'renterPack' => $renterPack,
                'placeholderDateFormat' => $this->placeholderDateFormat,
                'scriptDateFormat' => $this->scriptDateFormat,
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/archive-renter/{id}", name="app_archive_renter")
     */
    public function archiveRenter(
        Request $request,
        Renter $renter,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        HousingRepository $housingRepository,
        Mailer $mailer
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $housingId = $request->query->get('housing');
            $housing = $housingRepository->findOneBy(['id' => $housingId]);
            $entityManager = $this->doctrine->getManager();
            if ($housing->getTask()) {
                foreach ($housing->getTask() as $task) {
                    $task->setIsArchived('1');
                }
            }
            // Move renter to archive
            $renter->setIsArchived('1');
            //$renter->removeHousing($housing);
            //$renter->setArchivedHousing($housing);
            // Create a new empty renter for this house. Currently disabled
            //$this->addNewEmptyRenter($housing);
            $entityManager->persist($renter);
            $entityManager->persist($housing);
            $entityManager->persist($task);
            $entityManager->flush();
            $message = $translator->trans('Renter archived', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    public function addNewEmptyRenter($housing)
    {
        $entityManager = $this->doctrine->getManager();
        $renter = new Renter();
        $renter->setFirstName('Enter first name');
        $renter->setLastName('Enter last name');
        $housing->setRenter($renter);
        $entityManager->persist($renter);
        $entityManager->flush();
    }

    /**
     * @Route("renter/check_availability_appt", name="app_check_availability_appt")
     */
    public function checkAvailabilityApartments(
        Request $request,
        CalendarRepository $calendarRepository,
        HousingRepository $housingRepository,
        DateUtility $dateUtility
    ): Response {
        $dateStart =  $request->request->get('date_start');
        $timeStart = $request->request->get('time_start');
        $dateTimeStart = $dateStart .' '. $timeStart;
        $dateCompare1 = strtotime($dateStart .' '. $timeStart);

        if ($request->request->get('appartments')) {
            $apartmentIds =  explode(',', $request->request->get('appartments'));
        }

        if (is_array($apartmentIds) && !empty($apartmentIds)) {
            $apartments = $housingRepository->findByIds($apartmentIds);
            $calendars = $this->getClendarsByMultipleApartment($apartments, $calendarRepository);
        }

        // check availability for existing calenadrs
        $arrValue = $this->checkAvailabilityCalendars($dateCompare1, $calendars);

        if (!empty($arrValue) && in_array("busy", $arrValue)) {
            $output = '<p style="color: #ff0000"> ' . 'La date de début est occupée pour la date ' . $dateTimeStart . '</p>';
        } else {
            $output = '<p style="color: #248108"> ' . 'La date de début est libre pour la date ' . $dateTimeStart . '</p>';
        }

        $arrData = ['output' => $output];
        return new JsonResponse($arrData);
    }

    /**
     * @Route("renter/check_availability/apartment-{id}", name="app_check_availability")
     */
    public function checkAvailability(
        Request $request,
        Housing $apartment,
        CalendarRepository $calendarRepository,
        DateUtility $dateUtility
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_OWNER)) {
            $dateStart =  $request->request->get('date_start');
            $dateCompare1 = strtotime($request->request->get('date_start'));
            //$dateCompare2 = strtotime($request->request->get('date_end'));

            // Find calendars for apartment
            if ($apartment) {
                $calendars = $this->getClendarsByApartment($apartment, $calendarRepository);
            }

            // check availability for existing calenadrs
            if ($calendars) {
                foreach ($calendars as $key => $calendar) {
                    $startDate[$key] = $calendar->getStartDate()->getTimestamp();
                    $endDate[$key] = $calendar->getEndDate()->getTimestamp();

                    // @ToDo: incorrect data for multipe calendars
                    if ($dateCompare1 >= $startDate[$key] && $dateCompare1 <= $endDate[$key]) {
                        //$output = '<p style="color: #ff0000"> ' . 'Start date is busy for date ' . $dateStart . ' and appartments ' . $appartments . '</p>';
                        $output = '<p style="color: #ff0000"> ' . 'Start date is busy for date ' . $dateStart . '</p>';
                    } else {
                        //$output = '<p style="color: #248108"> ' . 'Start date is free for date ' . $dateStart . ' and appartments ' . $appartments . '</p>';
                        $output = '<p style="color: #248108"> ' . 'Start date is free for date ' . $dateStart . '</p>';
                    }
                }
            }

            $arrData = ['output' => $output];
            return new JsonResponse($arrData);
        }
    }
}
