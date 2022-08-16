<?php

namespace App\Controller;

use App\Controller\Traits\InvoiceTrait;
use App\Entity\Elements;
use App\Entity\Housing;
use App\Entity\Renter;
use App\Entity\Room;
use App\Entity\Task;
use App\Entity\User;
use App\Form\Renter\AddRenterFormType;
use App\Form\Renter\RenterFormType;
use App\Form\User\AddOwnerFormType;
use App\Form\User\EditOwnerFormType;
use App\Form\User\EditAgencyFormType;
use App\Repository\CalendarRepository;
use App\Repository\HousingRepository;
use App\Repository\InvoiceContainRepository;
use App\Repository\InvoiceRepository;
use App\Repository\MaterialsRepository;
use App\Repository\PacksRepository;
use App\Repository\PrestationRepository;
use App\Repository\RenterRepository;
use App\Repository\ServicesRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use App\Security\EmailVerifier;
use App\Service\AccessService;
use App\Service\DateUtility;
use App\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Controller\Traits\RenterTrait;
use App\Controller\Traits\CalendarTrait;
use App\Controller\Traits\TaskTrait;
use Doctrine\Persistence\ManagerRegistry;

class OwnerController extends AbstractController
{
    use RenterTrait;

    use CalendarTrait;

    use TaskTrait;

    use InvoiceTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_OWNER = 'ROLE_OWNER';

    public const ROLE_AGENCY = 'ROLE_AGENCY';

    private $security;

    private $twig;

    private $urlGenerator;

    private $mailer;

    private $adminEmail;

    private $dateFormat;

    private $placeholderDateFormat;

    private $scriptDateFormat;

    private $doctrine;

    /**
     * @param Security $security
     * @param Environment $twig
     * @param EmailVerifier $emailVerifier
     * @param UrlGeneratorInterface $urlGenerator
     * @param MailerInterface $mailer
     * @param ManagerRegistry $doctrine
     * @param string $adminEmail
     * @param string $dateFormat
     * @param string $placeholderDateFormat
     * @param string $scriptDateFormat
     */
    public function __construct(
        Security $security,
        Environment $twig,
        EmailVerifier $emailVerifier,
        UrlGeneratorInterface $urlGenerator,
        MailerInterface $mailer,
        ManagerRegistry $doctrine,
        string $adminEmail,
        string $dateFormat,
        string $placeholderDateFormat,
        string $scriptDateFormat
    ) {
        $this->security = $security;
        $this->twig = $twig;
        $this->emailVerifier = $emailVerifier;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->dateFormat = $dateFormat;
        $this->placeholderDateFormat = $placeholderDateFormat;
        $this->scriptDateFormat = $scriptDateFormat;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/owner/profile", name="app_owner_profile")
     */
    public function ownerProfile(
        Request $request,
        NotifierInterface $notifier,
        HousingRepository $housingRepository,
        TaskRepository $taskRepository,
        Mailer $mailer,
        TranslatorInterface $translator,
        AccessService $accessService
    ): Response {
        if ($this->isGranted(self::ROLE_OWNER) || $this->security->isGranted(self::ROLE_AGENCY)) {
            $user = $this->security->getUser();
            if ($accessService->isAccessExpired($user) == false) {
                $message = $translator->trans('Your date expired', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirectToRoute("app_login");
            }

            $apartments = $housingRepository->findByUser($user);

            // @ToDO: strange result, not working selectbox
            if (is_array($apartments)) {
                foreach ($apartments as $appt) {
                    $apartmentIds[] = $appt->getId();
                }
            }
            // Find tasks by apartments and not archived
            if (isset($apartmentIds) && is_array($apartmentIds)) {
                $tasks = $this->getTasksByApartments($apartmentIds, $taskRepository);
            } else {
                $tasks = null;
            }

            // Redirect to first apartment
            if ($apartments) {
                return new RedirectResponse($this->urlGenerator->generate('app_detail_apartment', ['id' => $apartments[0]->getId()]));
            }

            $renter = new Renter();
            $form = $this->createForm(AddRenterFormType::class, $renter, [
                'action' => $this->generateUrl('app_owner_profile', ['id' => $renter->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            //$services = $servicesRepository->findAll();

            return new Response($this->twig->render('owner/profile.html.twig', [
                'apartments' => $apartments,
                'user' => $user,
                'tasks' => $tasks,
                'housing' => '',
                'placeholderDateFormat' => $this->placeholderDateFormat,
                'scriptDateFormat' => $this->scriptDateFormat,
                'renter_form' => $form->createView(),
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/owner/detail-apartment/apartment-{id}", name="app_detail_apartment")
     */
    public function detailApartment(
        Request $request,
        HousingRepository $housingRepository,
        CalendarRepository $calendarRepository,
        TaskRepository $taskRepository,
        RenterRepository $renterRepository,
        ReservationRepository $reservationRepository,
        ServicesRepository $servicesRepository,
        PrestationRepository $prestationRepository,
        PacksRepository $packsRepository,
        MaterialsRepository $materialsRepository,
        InvoiceRepository $invoiceRepository,
        InvoiceContainRepository $invoiceContainRepository,
        NotifierInterface $notifier,
        Housing $apartment,
        TranslatorInterface $translator,
        DateUtility $dateUtility,
        AccessService $accessService
    ): Response {
        if ($this->security->isGranted(self::ROLE_OWNER) || $this->security->isGranted(self::ROLE_AGENCY)) {
            $user = $this->security->getUser();
            if ($accessService->isAccessExpired($user) == false) {
                $message = $translator->trans('Your date expired', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirectToRoute("app_login");
            }

            // Redirect if edit not owner user
            if ($apartment->getUser() !== $this->getUser()) {
                //throw $this->createAccessDeniedException();
                $message = $translator->trans('Please login', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirectToRoute("app_login");
            }
            $apartments = $housingRepository->findByUser($user);
            if (is_array($apartments)) {
                foreach ($apartments as $appt) {
                    $apartmentIds[] = $appt->getId();
                }
            }
            // Find tasks by apartments and not archived
            if (is_array($apartmentIds)) {
                $tasks = $this->getTasksByApartments($apartmentIds, $taskRepository);
            }

            // Task fror js calendar
            $calendarTasks = $taskRepository->findByApartment(intval($apartment->getId()), '1');

            $reservations = $reservationRepository->findByUser($user);
            $services = $servicesRepository->findAllOrder(['name' => 'ASC']);
            $prestations = $prestationRepository->findAllOrder(['name' => 'ASC']);
            $packs = $packsRepository->findAllOrder(['name' => 'ASC']);
            $materials = $materialsRepository->findAllOrder(['name' => 'ASC']);

            $newRenter = new Renter();
            $form = $this->createForm(AddRenterFormType::class, $newRenter, [
                'action' => $this->generateUrl('app_detail_apartment', ['id' => $apartment->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $post = $request->request->get('add_renter_form');
                // Check and redirect if renter is busy
                $startDate = $post['startDate'];
                $dateCompare1 = strtotime($startDate);
                if ($apartment) {
                    $calendars = $this->getClendarsByApartment($apartment, $calendarRepository);
                }
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

                // Find existing renter
                $existingRenter = $renterRepository->findOneBy(['firstName' => $post['firstName'], 'lastName' => $post['lastName'], 'email' => $post['email']]);
                if (!($existingRenter instanceof Renter)) {
                    // nothing to do, use empty Renter object
                } else {
                    // Use existing renter
                    $newRenter = $existingRenter;
                }

                // Start to move to trait
                if (!empty($post['apartments'])) {
                    foreach ($post['apartments'] as $apartmentId) {
                        $appt = $housingRepository->findOneBy(['id' => $apartmentId]);
                        if (isset($post['owner_occupation']) && $post['owner_occupation'] == 'on') {
                            // Add only renter if choosed owner_occupation
                            $this->createReservationAndRenter($request, $post, $appt, $user, $calendarRepository, $notifier, $translator, $dateUtility);
                        } else {
                            $task = $this->createTasksAndRenter($request, $post, $newRenter, $appt, $renterRepository, $calendarRepository, $notifier, $translator, $dateUtility);
                            $relatedTask = $taskRepository->findRelaytedTask($task);
                            $invoice = $this->setInvoice($post, $task, $relatedTask, $appt, $newRenter, $dateUtility, $renterRepository);
                            $this->setAppointed($request, $invoice, 'add_renter_form');
                        }
                    }
                } else {
                    if (isset($post['owner_occupation']) && $post['owner_occupation'] == 'on') {
                        // Add only renter if choosed owner_occupation
                        $this->createReservationAndRenter($request, $post, $apartment, $user, $calendarRepository, $notifier, $translator, $dateUtility);
                    } else {
                        $task = $this->createTasksAndRenter($request, $post, $newRenter, $apartment, $renterRepository, $calendarRepository, $notifier, $translator, $dateUtility);
                        $relatedTask = $taskRepository->findRelaytedTask($task);
                        $invoice = $this->setInvoice($post, $task, $relatedTask, $appt, $newRenter, $dateUtility, $renterRepository);
                        $this->setAppointed($request, $invoice, 'add_renter_form');
                    }
                }

                // Set note for task
                if ($post['note'] !=='') {
                    $task->setNote($post['note']);
                    $relatedTask->setNote($post['note']);
                }

                // Currently the owner can't add prestations or services
                // Set services, prestations relations
                //$invoice = $invoiceRepository->findOneBy(['id' => $task->getInvoice()]);
                // Set related prestations
                //$this->setInvoiceRelatedParams($request, $prestationRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'prestation', 'setPrestation');
                // Set related services
                //$this->setInvoiceRelatedParams($request, $servicesRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'service', 'setService');
                // Set related packs
                //$this->setInvoiceRelatedParams($request, $packsRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'pack', 'setPack');
                // Set related materials
                //$this->setInvoiceRelatedParams($request, $materialsRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'material', 'setMaterial');
                // Save relation data

                $entityManager = $this->doctrine->getManager();
                $entityManager->flush();

                $message = $translator->trans('Renter created', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }

            return new Response($this->twig->render('owner/profile.html.twig', [
                'apartments' => $apartments,
                'apartment' => $apartment,
                'user' => $user,
                'tasks' => $tasks,
                'calendarTasks' => $calendarTasks,
                'reservations' => $reservations,
                'services' => $services,
                'prestations' => $prestations,
                'packs' => $packs,
                'materials' => $materials,
                'placeholderDateFormat' => $this->placeholderDateFormat,
                'scriptDateFormat' => $this->scriptDateFormat,
                'renter_form' => $form->createView()
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/add-owner", name="app_addowner")
     */
    public function addOwner(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        PacksRepository $packsRepository,
        NotifierInterface $notifier,
        Mailer $mailer,
        TranslatorInterface $translator
    ): Response {
        if ($this->isGranted(self::ROLE_ADMIN)) {
            $user = new User();
            $form = $this->createForm(AddOwnerFormType::class, $user, [
                'action' => $this->generateUrl('app_addowner'),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            $packs = $packsRepository->findAll();

            if ($form->isSubmitted()) {

                // Check if empty password
                if ($form->get('plainPassword')->getData() === null) {
                    $message = $translator->trans('Mismatch password', array(), 'flash');
                    $notifier->send(new Notification($message, ['browser']));
                    return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                }

                // Check if user already exists
                $post = $request->request->get('add_owner_form');
                $existingUser = $userRepository->findOneBy(['email' => $post['email']]);
                if ($existingUser instanceof User) {
                    $message = $translator->trans('User exist', array(), 'flash');
                    $notifier->send(new Notification($message, ['browser']));
                    return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                }

                $entityManager = $this->doctrine->getManager();
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                // Set role Agency
                if (isset($post['isAgency']) && $post['isAgency'] == 'on') {
                    $user->setRoles(array('ROLE_AGENCY'));
                } else {
                    $user->setRoles(array('ROLE_OWNER'));
                }
                $user->setIsVerified('1');

                // Currently new housing block don't used. Please don't remove it, maybe will be use in the future.
                if (!empty($post['housing']['title'])) {
                    $housing = new Housing();
                    //Create elements for this house
                    $elements = new Elements();
                    $entityManager->persist($elements);
                    $housing->setElement($elements);
                    $housing->setName($post['housing']['title']);
                    if (!empty($post['housing']['address'])) {
                        $housing->setAddress($post['housing']['address']);
                    }
                    if (!empty($post['housing']['zip'])) {
                        $housing->setZip($post['housing']['zip']);
                    }
                    if (!empty($post['housing']['city'])) {
                        $housing->setCity($post['housing']['city']);
                    }
                    if (!empty($post['pack'])) {
                        $pack = $packsRepository->findOneBy(['id' => $post['pack']['0']], ['id' => 'DESC']);
                        $housing->setPacks($pack);
                    }
                    if ($housing instanceof Housing) {
                        $entityManager->persist($housing);
                        $housing->setUser($user);
                    }

                    // Add empty renter. Currently new renter don't used. Please don't remove it, maybe will be use in future.
                    //$renter = new Renter();
                    //$renter->setFirstName('Renter id' . $renter->getId());
                    //$entityManager->persist($renter);
                    //$housing->setRenter($renter);
                    //$entityManager->persist($renter);
                }

                $entityManager->persist($user);
                $entityManager->flush();
                // Temporarily turn off email
                // Mail for new owner
                //$postArray = $_POST['add_owner_form'];
                //$subject = $translator->trans('New owner created', array(), 'messages');
                //$mailer->sendUserEmail($user, $subject, 'emails/owner_created.html.twig', $postArray);
                // Browser notify
                $notifier->send(new Notification('Your owner created', ['browser']));
                return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
            }

            return new Response($this->twig->render('administrator/forms/user/add_owner.html.twig', [
                'add_owner_form' => $form->createView(),
                'packs' => $packs
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-agency/agency-{id}", name="app_edit_agency")
     */
    public function editAgency(
        Request $request,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        User $agency,
        HousingRepository $housingRepository,
        Mailer $mailer
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $form = $this->createForm(EditAgencyFormType::class, $agency, [
                'action' => $this->generateUrl('app_edit_agency', ['id' => $agency->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            $apartments = $housingRepository->findAll();

            if ($form->isSubmitted() && $form->isValid()) {
                $postArray= [];
                $subject = $translator->trans('Concierge Me - Agency profile updated', array(), 'messages');
                $mailer->sendUserEmail($agency, $subject, 'emails/user_notification.html.twig', $postArray);

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($agency);
                $entityManager->flush();
                $notifier->send(new Notification('Agency updated', ['browser']));
                return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
            }

            return new Response($this->twig->render('administrator/forms/user/edit_agency.html.twig', [
                'agency' => $agency,
                'apartments' => $apartments,
                'agency_form' => $form->createView(),
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-owner/owner-{id}", name="app_edit_owner")
     */
    public function editOwner(
        Request $request,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        User $owner,
        HousingRepository $housingRepository,
        Mailer $mailer
    ): Response {
        //$user = $this->security->getUser();
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $form = $this->createForm(EditOwnerFormType::class, $owner, [
                'action' => $this->generateUrl('app_edit_owner', ['id' => $owner->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            $apartments = $housingRepository->findAll();

            // Edit owner form
            if ($form->isSubmitted()) {
                $postArray = $_POST['edit_owner_form'];
                if ($postArray['edit_owner_form_telephone_full_number'] !=='') {
                    $owner->setTelephoneFullNumber($postArray['edit_owner_form_telephone_full_number']);
                }
                if ($postArray['edit_owner_form_telephone2_full_number'] !=='') {
                    $owner->setTelephone2FullNumber($postArray['edit_owner_form_telephone2_full_number']);
                }
                if ($postArray['telephone'] !=='') {
                    $owner->setTelephone($postArray['telephone']);
                }
                if ($postArray['telephone2'] !=='') {
                    $owner->setTelephone2($postArray['telephone2']);
                }
                $subject = $translator->trans('Concierge Me - Your profile updated', array(), 'messages');
                $mailer->sendUserEmail($owner, $subject, 'emails/user_notification.html.twig', $postArray);
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($owner);
                $entityManager->flush();
                $notifier->send(new Notification('Your owner updated', ['browser']));
                return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
            }

            return new Response($this->twig->render('administrator/forms/user/edit_owner.html.twig', [
                'owner' => $owner,
                'apartments' => $apartments,
                'edit_owner_form' => $form->createView(),
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-owner-card/{id}", name="app_show_owner_card")
     */
    public function ownerCard(
        User $owner,
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            return new Response($this->twig->render('administrator/forms/owner_card.html.twig', [
                'owner' => $owner
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-owner/owner-{id}", name="app_delete_owner")
     */
    public function deleteOwner(
        Request $request,
        User $owner,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $entityManager = $this->doctrine->getManager();
            if (count($owner->getApartments()) > 0) {
                foreach ($owner->getApartments() as $apartment) {
                    $apartment->setIsHidden(1);
                    $apartment->setUser(null);
                    $entityManager->persist($apartment);
                    $entityManager->flush();
                    // Remove all tasks associated with apartment
                    if ($apartment->getTask()) {
                        foreach ($apartment->getTask() as $task) {
                            $entityManager->remove($task);
                        }
                    }
                }
                $entityManager->persist($apartment);
            }
            $entityManager->remove($owner);
            $entityManager->flush();

            // Message and redirect
            $message = $translator->trans('Owner deleted', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            $referer = $request->headers->get('referer');
            return new RedirectResponse($referer);
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }
}
