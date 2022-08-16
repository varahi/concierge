<?php

namespace App\Controller;

use App\Controller\Traits\InvoiceTrait;
use App\Controller\Traits\TaskTrait;
use App\Controller\Traits\CalendarTrait;
use App\Controller\Traits\RenterTrait;
use App\Entity\Renter;
use App\Entity\User;
use App\Form\Renter\AddRenterFormType;
use App\Form\User\EmployerFormType;
use App\Form\User\EditEmployerFormType;
use App\Repository\InvoiceContainRepository;
use App\Repository\InvoiceRepository;
use App\Repository\RenterRepository;
use App\Repository\ReservationRepository;
use App\Service\Mailer;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use App\Repository\CalendarRepository;
use App\Repository\HousingRepository;
use App\Repository\ServicesRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Repository\PacksRepository;
use App\Repository\PrestationRepository;
use App\Repository\MaterialsRepository;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\DateUtility;
use App\Service\AccessService;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;

class AdministratorController extends AbstractController
{
    use TaskTrait;

    use CalendarTrait;

    use RenterTrait;

    use InvoiceTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_OWNER = 'ROLE_OWNER';

    public const ROLE_AGENCY = 'ROLE_AGENCY';

    public const ROLE_EMPLOYER = 'ROLE_EMPLOYER';

    public const LIMIT_PER_PAGE = '10';

    /**
     * Time in seconds 3600 - one hour
     */
    public const CACHE_MAX_AGE = '3600';

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
     * @param ManagerRegistry $doctrine
     * @param string $dateFormat
     * @param string $placeholderDateFormat
     * @param string $scriptDateFormat
     */
    public function __construct(
        Security $security,
        Environment $twig,
        EmailVerifier $emailVerifier,
        UrlGeneratorInterface $urlGenerator,
        ManagerRegistry $doctrine,
        string $dateFormat,
        string $placeholderDateFormat,
        string $scriptDateFormat
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
     * Require ROLE_ADMIN for *every* controller method in this class.
     *
     * @IsGranted("ROLE_ADMIN")
     * @Route("/administrator/main", name="app_administrator")
     */
    public function administrator(
        Request $request,
        UserRepository $userRepository,
        TaskRepository $taskRepository,
        CalendarRepository $calendarRepository,
        HousingRepository $housingRepository,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        PacksRepository $packsRepository,
        MaterialsRepository $materialsRepository,
        ServicesRepository $servicesRepository,
        PrestationRepository $prestationRepository,
        RenterRepository $renterRepository,
        ReservationRepository $reservationRepository,
        InvoiceRepository $invoiceRepository,
        InvoiceContainRepository $invoiceContainRepository,
        DateUtility $dateUtility,
        AccessService $accessService
    ): Response {
        if ($this->isGranted(self::ROLE_ADMIN)) {
            $user = $this->security->getUser();
            if ($accessService->isAccessExpired($user) == false) {
                $message = $translator->trans('Your date expired', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirectToRoute("app_login");
            }

            // Get owner depeneds on search result
            $routeName = $request->attributes->get('_route');
            $searchString = trim($request->query->get('q'));
            if ($searchString == null) {
                $owners = $userRepository->findByRole(self::ROLE_OWNER);
                $agencies = $userRepository->findByRole(self::ROLE_AGENCY);
            } else {
                $owners = $userRepository->findOwnersOrApartments($searchString, self::ROLE_OWNER);
                $agencies = $userRepository->findOwnersOrApartments($searchString, self::ROLE_AGENCY);
            }

            $employers = $userRepository->findByRole(self::ROLE_EMPLOYER);
            //$tasks = $taskRepository->findAllOrder(['title' => 'ASC']);
            //$tasks = $taskRepository->findWithRetner();
            //$tasks = $taskRepository->findAllOrderByForeignApartment('ASC');

            if ($request->query->get('employee')) {
                $employeeId = explode(',', $request->query->get('employee'));
            } else {
                $employeeId = [];
            }
            $tasks = $this->getTasksByDateRange($request, $taskRepository, $dateUtility, $employeeId, '-15 days', '+6 month');
            //$calendarTasks = $this->getTasksByDateRange($request, $taskRepository, $dateUtility, $isCalendar = true);
            //$apartments = $housingRepository->findAllOrder(['name' => 'ASC']);
            // Find only apartment which has owner
            $apartments = $housingRepository->findAllOrderAndHasUser();
            $packs = $packsRepository->findAllOrder(['name' => 'ASC']);
            $materials = $materialsRepository->findAllOrder(['name' => 'ASC']);
            $services = $servicesRepository->findAllOrder(['name' => 'ASC']);
            $prestations = $prestationRepository->findAllOrder(['name' => 'ASC']);
            $reservations = $reservationRepository->findAll();

            // Get calendars only related with tasks
            $calendars = $this->getCalendarsByTasks($tasks, $calendarRepository);
            //dd($calendars);

            $newRenter = new Renter();
            $form = $this->createForm(AddRenterFormType::class, $newRenter, [
                'action' => $this->generateUrl('app_administrator'),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $post = $request->request->get('add_renter_form');
                // Check and redirect if renter is busy

                if ($post['startTime'] !=='') {
                    $startDate = $post['startDate'] .' '. $post['startTime'];
                } else {
                    $startDate = $post['startDate'];
                }
                $dateCompare1 = strtotime($startDate);

                if (is_array($post['apartments']) && !empty($post['apartments'])) {
                    $apartments = $housingRepository->findByIds($post['apartments']);
                    $calendars = $this->getClendarsByMultipleApartment($apartments, $calendarRepository);
                }

                // check availability for existing calendars
                $arrValue = $this->checkAvailabilityCalendars($dateCompare1, $calendars);

                // Redirect if apartment busy
                if (!empty($arrValue) && in_array("busy", $arrValue)) {
                    $message = $translator->trans('Renter date busy', array(), 'flash');
                    $notifier->send(new Notification($message, ['browser']));
                    $referer = $request->headers->get('referer');
                    return new RedirectResponse($referer);
                }

                if (!empty($post['apartments'])) {
                    foreach ($post['apartments'] as $apartmentId) {
                        $appt = $housingRepository->findOneBy(['id' => $apartmentId]);

                        if (isset($post['owner_occupation']) && $post['owner_occupation'] == 'on') {
                            // Add only renter if choosed owner_occupation
                            $this->createReservationAndRenter($request, $post, $appt, $appt->getUser(), $calendarRepository, $notifier, $translator, $dateUtility);
                        } else {
                            $task = $this->createTasksAndRenter($request, $post, $newRenter, $appt, $renterRepository, $calendarRepository, $notifier, $translator, $dateUtility);
                            $relatedTask = $taskRepository->findRelaytedTask($task);
                            if (!$relatedTask) {
                                throw new \LogicException('Reservation can\'t be created .');
                            }
                            // Set invoice for task
                            $invoice = $this->setInvoice($post, $task, $relatedTask, $appt, $newRenter, $dateUtility, $renterRepository);
                            $this->setAppointed($request, $invoice, 'calendar_form');
                            // Set services, prestations relations
                            $invoice = $invoiceRepository->findOneBy(['id' => $task->getInvoice()]);
                            if (!$invoice) {
                                throw new \LogicException('Reservation can\'t be created .');
                            }

                            // Set note for task
                            if ($post['note'] !=='') {
                                $task->setNote($post['note']);
                                $relatedTask->setNote($post['note']);
                            }

                            // Set related prestations
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

                            $entityManager = $this->doctrine->getManager();
                            $entityManager->persist($invoice);
                            $entityManager->flush();

                            // Calculate total sum for invoice
                            // ToDo: currently when create new prestation property total not calculated and incorrec qty for contrains
                            if (count($invoice->getContain()) > 0) {
                                foreach ($invoice->getContain() as $contain) {
                                    $totalContain[] = $contain->getTotal();
                                }
                                $total = array_sum($totalContain);
                                $invoice->setTotal($total);
                            }

                            //$entityManager = $this->doctrine->getManager();
                            //$entityManager->persist($invoice);
                            $entityManager->flush();
                        }
                    }
                } else {
                    // ToDo: do we choosing single apartment in the reservation form
                    //$apartment = $housingRepository->findOneBy(['id' => $post['apartment']]);
                    //if (isset($post['owner_occupation']) && $post['owner_occupation'] == 'on') {
                        // Add only renter if choosed owner_occupation
                    //    $this->createReservationAndRenter($request, $post, $apartment, $apartment->getUser(), $calendarRepository, $notifier, $translator, $dateUtility);
                    //} else {
                    //    $this->createTasksAndRenter($request, $post, $newRenter, $apartment, $renterRepository, $calendarRepository, $notifier, $translator, $dateUtility);
                    //}
                }

                $message = $translator->trans('Renter created', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }

            $response = new Response($this->twig->render('administrator/administrator.html.twig', [
                'owners' => $owners,
                'agencies' => $agencies,
                'employers' => $employers,
                'tasks' => $tasks,
                'calendars' => $calendars,
                'apartments' => $apartments,
                'packs' => $packs,
                'prestations' => $prestations,
                'materials' => $materials,
                'services' => $services,
                'searchString' => $searchString,
                'routeName' => $routeName,
                'reservations' => $reservations,
                'placeholderDateFormat' => $this->placeholderDateFormat,
                'scriptDateFormat' => $this->scriptDateFormat,
                'renter_form' => $form->createView()
            ]));

            $response->setSharedMaxAge(self::CACHE_MAX_AGE);
            return $response;
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * Require ROLE_ADMIN for *every* controller method in this class.
     *
     * @IsGranted("ROLE_ADMIN")
     * @Route("administrator/employer-page", name="app_employer_page")
     */
    public function employerPage(
        Request $request,
        UserRepository $userRepository,
        TaskRepository $taskRepository,
        CalendarRepository $calendarRepository,
        HousingRepository $housingRepository,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        PacksRepository $packsRepository,
        MaterialsRepository $materialsRepository,
        ServicesRepository $servicesRepository,
        PrestationRepository $prestationRepository,
        RenterRepository $renterRepository,
        ReservationRepository $reservationRepository,
        DateUtility $dateUtility
    ): Response {
        if ($this->isGranted(self::ROLE_ADMIN)) {

            // Get owner depeneds on search result
            $routeName = $request->attributes->get('_route');
            $searchString = $request->query->get('q');
            if ($searchString == null) {
                $employers = $userRepository->findByRole(self::ROLE_EMPLOYER);
            } else {
                $employers = $userRepository->findEmployers($searchString, self::ROLE_EMPLOYER);
            }

            $owners = $userRepository->findByRole(self::ROLE_OWNER);
            //$tasks = $taskRepository->findAllOrderByForeignApartment('ASC');
            //$request->query->get('dateStart');

            if ($request->query->get('employee')) {
                $employeeId = explode(',', $request->query->get('employee'));
            } else {
                $employeeId = [];
            }
            $tasks = $this->getTasksByDateRange($request, $taskRepository, $dateUtility, $employeeId, '-15 days', '+6 month');
            $apartments = $housingRepository->findAllOrder(['name' => 'ASC']);
            $packs = $packsRepository->findAllOrder(['name' => 'ASC']);
            $materials = $materialsRepository->findAllOrder(['name' => 'ASC']);
            $services = $servicesRepository->findAllOrder(['name' => 'ASC']);
            $prestations = $prestationRepository->findAllOrder(['name' => 'ASC']);
            $reservations = $reservationRepository->findAll();

            // Get calendars only related with tasks
            $calendars = $this->getCalendarsByTasks($tasks, $calendarRepository);

            $newRenter = new Renter();
            $form = $this->createForm(AddRenterFormType::class, $newRenter, [
                'action' => $this->generateUrl('app_administrator'),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            // Submit function executed on name="app_administrator"
            $response = new Response($this->twig->render('administrator/employer_page.html.twig', [
                'owners' => $owners,
                'employers' => $employers,
                'tasks' => $tasks,
                'calendars' => $calendars,
                'apartments' => $apartments,
                'packs' => $packs,
                'materials' => $materials,
                'services' => $services,
                'prestations' => $prestations,
                'searchString' => $searchString,
                'routeName' => $routeName,
                'reservations' => $reservations,
                'placeholderDateFormat' => $this->placeholderDateFormat,
                'scriptDateFormat' => $this->scriptDateFormat,
                'renter_form' => $form->createView()
            ]));

            $response->setSharedMaxAge(self::CACHE_MAX_AGE);
            return $response;
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("administrator/create-employer", name="app_create_employer")
     */
    public function createEmployer(
        Request $request,
        TranslatorInterface $translator,
        NotifierInterface $notifier,
        UserPasswordHasherInterface $passwordHasher,
        TaskRepository $taskRepository,
        UserRepository $userRepository,
        Mailer $mailer,
        DateUtility $dateUtility
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $user = new User();
            $form = $this->createForm(EmployerFormType::class, $user, [
                'action' => $this->generateUrl('app_create_employer'),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            $tasks = $taskRepository->findAll();

            if ($form->isSubmitted()) {

                // Check if entered dates and check date format
                if ($_POST['start-date'] !=='' && $_POST['start-date'] !=='') {
                    $startDateString = $request->request->get('start-date');
                    $endDateString = $request->request->get('end-date');
                    // Check if format date Y-m-d
                    if ($dateUtility->checkDate($startDateString) === false) {
                        $message = $translator->trans('Incorrect date', array(), 'flash');
                        $notifier->send(new Notification($message, ['browser']));
                        return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
                    } else {
                        $startDate = $dateUtility->checkDate($startDateString);
                    }
                    if ($dateUtility->checkDate($endDateString) === false) {
                        $message = $translator->trans('Incorrect date', array(), 'flash');
                        $notifier->send(new Notification($message, ['browser']));
                        return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
                    } else {
                        $endDate = $dateUtility->checkDate($endDateString);
                    }
                    if ($startDate) {
                        $user->setStartDate($startDate);
                    }
                    if ($endDate) {
                        $user->setEndDate($endDate);
                    }
                }

                // Check if empty password
                if ($form->get('plainPassword')->getData() === null) {
                    $message = $translator->trans('Mismatch password', array(), 'flash');
                    $notifier->send(new Notification($message, ['browser']));
                    return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
                }

                // Check if user already exists
                $post = $request->request->get('employer_form');
                $existingUser = $userRepository->findOneBy(['email' => $post['email']]);
                if ($existingUser instanceof User) {
                    $message = $translator->trans('User exist', array(), 'flash');
                    $notifier->send(new Notification($message, ['browser']));
                    return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
                }

                // encode the plain password
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $user->setRoles(array('ROLE_EMPLOYER'));
                $user->setIsVerified('1');
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // generate a signed url and email it to the user
                /*
                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('info@t3dev.ru', 'ConciergeAdmin'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
                */

                $postArray = $_POST['employer_form'];
                $subject = $translator->trans('Concierge Me - Employer created', array(), 'messages');
                $mailer->sendUserEmail($user, $subject, 'emails/employer_created.html.twig', $postArray);

                $message = $translator->trans('You are created new employer', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
            }

            return new Response($this->twig->render('administrator/forms/user/new_employer_form.html.twig', [
                'employer_form' => $form->createView(),
                'tasks' => $tasks,
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
     * @Route("administrator/edit-employer/user-{id}", name="app_edit_employer")
     */
    public function editEmployer(
        Request $request,
        TranslatorInterface $translator,
        NotifierInterface $notifier,
        UserPasswordHasherInterface $passwordHasher,
        TaskRepository $taskRepository,
        HousingRepository $housingRepository,
        DateUtility $dateUtility,
        User $user
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $form = $this->createForm(EditEmployerFormType::class, $user, [
                'action' => $this->generateUrl('app_edit_employer', ['id' => $user->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            $tasks = $taskRepository->findAll();
            //$apartments = $housingRepository->findAll();

            if ($form->isSubmitted()) {
                $post = $request->request->get('edit_employer_form');

                // Set new password if changed
                if ($post['plainPassword']['first'] !=='' && $post['plainPassword']['second'] !=='') {
                    if (strcmp($post['plainPassword']['first'], $post['plainPassword']['second']) == 0) {
                        // encode the plain password
                        $user->setPassword(
                            $passwordHasher->hashPassword(
                                $user,
                                $post['plainPassword']['first']
                            )
                        );
                    } else {
                        $message = $translator->trans('Mismatch password', array(), 'flash');
                        $notifier->send(new Notification($message, ['browser']));
                        return $this->redirectToRoute("app_employer_page");
                    }
                }

                // Set start date if not empty
                if ($post['start-date'] !=='') {
                    $startDateString = $post['start-date'];
                    if ($dateUtility->checkDate($startDateString) === false) {
                        $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
                        return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
                    } else {
                        $startDate = $dateUtility->checkDate($startDateString);
                    }
                }
                if ($startDate) {
                    $user->setStartDate($startDate);
                }

                // Set end date if not empty
                if ($post['end-date'] !=='') {
                    $endDateString = $post['end-date'];
                    if ($dateUtility->checkDate($endDateString) === false) {
                        $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
                        return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
                    } else {
                        $endDate = $dateUtility->checkDate($endDateString);
                    }
                }
                if ($endDate) {
                    $user->setEndDate($endDate);
                }

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $notifier->send(new Notification('Employer updated', ['browser']));
                return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
            }

            return new Response($this->twig->render('administrator/forms/user/edit_employer_form.html.twig', [
                'employer_form' => $form->createView(),
                'tasks' => $tasks,
                'user' => $user,
                'placeholderDateFormat' => $this->placeholderDateFormat,
                'scriptDateFormat' => $this->scriptDateFormat,
                //'apartments' => $apartments
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("administrator/disable-employer/user-{id}", name="app_disable_employer")
     */
    public function disableEmployer(
        TranslatorInterface $translator,
        NotifierInterface $notifier,
        User $user
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $entityManager = $this->doctrine->getManager();
            $user->setIsVerified('0');
            $entityManager->flush();
            $message = $translator->trans('Employer disabled', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return new RedirectResponse($this->urlGenerator->generate('app_employer_page'));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * Require ROLE_ADMIN for *every* controller method in this class.
     *
     * @IsGranted("ROLE_ADMIN")
     * @Route("/administrator/entry-leaving", name="app_entry_leaving")
     */
    public function entryLeaving(
        Request $request,
        PaginatorInterface $paginator,
        UserRepository $userRepository,
        TaskRepository $taskRepository,
        CalendarRepository $calendarRepository,
        HousingRepository $housingRepository,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        PacksRepository $packsRepository,
        MaterialsRepository $materialsRepository,
        ServicesRepository $servicesRepository,
        PrestationRepository $prestationRepository,
        RenterRepository $renterRepository,
        ReservationRepository $reservationRepository,
        DateUtility $dateUtility
    ): Response {
        if ($this->isGranted(self::ROLE_ADMIN)) {

            // Get owner depeneds on search result
            $routeName = $request->attributes->get('_route');
            $searchString = trim($request->query->get('q'));
            if ($searchString == null) {
                $owners = $userRepository->findByRole(self::ROLE_OWNER);
            } else {
                $owners = $userRepository->findOwnersOrApartments($searchString, self::ROLE_OWNER);
            }

            $employers = $userRepository->findByRole(self::ROLE_EMPLOYER);
            //$tasks = $taskRepository->findAllOrderByForeignApartment('ASC');
            if ($request->query->get('employee')) {
                $employeeId = explode(',', $request->query->get('employee'));
            } else {
                $employeeId = [];
            }
            $tasks = $this->getTasksByDateRange($request, $taskRepository, $dateUtility, $employeeId, '0 days', '+6 month');
            //$tasks = $this->getTasksByDateRange($request, $taskRepository, $dateUtility, $employeeId, '-15 days', '+6 month');

            // Pagination for tasks
            /*
            $queryTasks = $taskRepository->findAllOrderByForeignApartmentDql('ASC');
            $tasks = $paginator->paginate(
                $queryTasks,
                $request->query->getInt('page', 1),
                self::LIMIT_PER_PAGE
            );
            */

            $apartments = $housingRepository->findAllOrder(['name' => 'ASC']);
            $packs = $packsRepository->findAllOrder(['name' => 'ASC']);
            $materials = $materialsRepository->findAllOrder(['name' => 'ASC']);
            $services = $servicesRepository->findAllOrder(['name' => 'ASC']);
            $prestations = $prestationRepository->findAllOrder(['name' => 'ASC']);
            $reservations = $reservationRepository->findAll();

            // Get calendars only related with tasks
            $calendars = $this->getCalendarsByTasks($tasks, $calendarRepository);

            $newRenter = new Renter();
            $form = $this->createForm(AddRenterFormType::class, $newRenter, [
                'action' => $this->generateUrl('app_administrator'),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            // Pagination for employers
            /*
            $queryEmployers = $userRepository->findByRoleDql(self::ROLE_EMPLOYER);
            $employers = $paginator->paginate(
                $queryEmployers,
                $request->query->getInt('page', 1),
                self::LIMIT_PER_PAGE
            );
            */

            $dateStartTimestamp = $request->query->get('dateStart');
            $dateEndTimestamp = $request->query->get('dateEnd');

            // Submit function executed on name="app_administrator"
            $response = new Response($this->twig->render('administrator/entry_leaving.html.twig', [
                'owners' => $owners,
                'employers' => $employers,
                'tasks' => $tasks,
                'calendars' => $calendars,
                'apartments' => $apartments,
                'packs' => $packs,
                'prestations' => $prestations,
                'materials' => $materials,
                'services' => $services,
                'searchString' => $searchString,
                'routeName' => $routeName,
                'reservations' => $reservations,
                'dateStartTimestamp' => $dateStartTimestamp,
                'dateEndTimestamp' => $dateEndTimestamp,
                'placeholderDateFormat' => $this->placeholderDateFormat,
                'scriptDateFormat' => $this->scriptDateFormat,
                'renter_form' => $form->createView()
            ]));

            $response->setSharedMaxAge(self::CACHE_MAX_AGE);
            return $response;
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }
}
