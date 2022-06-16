<?php

namespace App\Controller;

use App\Form\ServicesFormType;
use App\Repository\RenterRepository;
use App\Repository\ReservationRepository;
use App\Repository\ServicesRepository;
use App\Security\EmailVerifier;
use App\Repository\CalendarRepository;
use App\Service\AccessService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;

class EmployerController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    private $twig;

    private $emailVerifier;

    private $urlGenerator;

    public const ROLE_EMPLOYER = 'ROLE_EMPLOYER';

    public function __construct(
        Security $security,
        Environment $twig,
        EmailVerifier $emailVerifier,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->security = $security;
        $this->twig = $twig;
        $this->emailVerifier = $emailVerifier;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/employer/profile", name="app_employer_profile")
     */
    public function employerProfile(
        Request $request,
        NotifierInterface $notifier,
        CalendarRepository $calendarRepository,
        RenterRepository $renterRepository,
        ServicesRepository $servicesRepository,
        ReservationRepository $reservationRepository,
        TranslatorInterface $translator,
        AccessService $accessService,
        ManagerRegistry $doctrine
    ): Response {
        if ($this->security->isGranted(self::ROLE_EMPLOYER)) {
            $user = $this->security->getUser();
            if ($accessService->isAccessExpired($user) == false) {
                $message = $translator->trans('Your date expired', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirectToRoute("app_login");
            }

            $reservations = $reservationRepository->findAll();

            if (count($user->getTask()) > 0) {
                foreach ($user->getTask() as $task) {
                    $tasks[] = $task;
                    if ($task->getCalendar()) {
                        $calendarIds[] = $task->getCalendar()->getId();
                    } else {
                        $calendarIds = null;
                    }
                }
            } else {
                $calendarIds = null;
            }

            if (is_array($calendarIds)) {
                $calendarIdsArr = array_unique($calendarIds, SORT_STRING);
                $calendars = $calendarRepository->findByTasks($calendarIdsArr);
            } else {
                $calendars = null;
            }

            // Find renters
            if (isset($tasks)) {
                foreach ($tasks as $task) {
                    if ($task->getRenter()) {
                        $renterIds[] = $task->getRenter()->getId();
                        $rentersIdsArr = array_unique($renterIds, SORT_STRING);
                        $renters = $renterRepository->findById($rentersIdsArr);
                    } else {
                        $renters = '';
                    }
                }
            }

            // Show custom services forms
            if (isset($tasks)) {
                foreach ($tasks as $key => $task) {
                    $form[$key] = $this->createForm(ServicesFormType::class);
                    $form[$key]->handleRequest($request);
                    //$formArr[] = $form[$key]->createView();
                    //$submittedToken = $request->request->get('token');
                }
                // Processing forms
                if ($form[$key]->isSubmitted()) {
                    $postArr = $_POST['services_form'];

                    // If isset renter
                    if (isset($postArr['renter'])) {
                        if ($postArr['renter']) {
                            $renterId = $postArr['renter'];
                            $renter = $renterRepository->findOneBy(['id' => $renterId], ['id' => 'DESC']);
                        }
                    }

                    // Move service to completed service
                    if (isset($_POST['services_form']['services'])) {
                        foreach ($postArr['services'] as $serviceId) {
                            $service = $servicesRepository->findOneBy(['id' => $serviceId], ['id' => 'DESC']);
                            $renter->addCompletedService($service);
                            $renter->removeService($service);
                        }
                    }

                    // Move service to non-completed service
                    if (isset($_POST['services_form']['services_completed'])) {
                        foreach ($postArr['services_completed'] as $serviceId) {
                            $service = $servicesRepository->findOneBy(['id' => $serviceId], ['id' => 'DESC']);
                            $renter->addService($service);
                            $renter->removeCompletedService($service);
                        }
                    }

                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($renter);
                    $entityManager->flush();

                    $message = $translator->trans('Task updated', array(), 'flash');
                    $notifier->send(new Notification($message, ['browser']));
                    return new RedirectResponse($this->urlGenerator->generate('app_employer_profile'));
                }

                return new Response($this->twig->render('employer/profile.html.twig', [
                    'currentUser' => $user,
                    'tasks' => $tasks,
                    'calendars' => $calendars,
                    'renters' => $renters,
                    'reservations' => $reservations
                    //'formArr' => $formArr
                    //'services_form' => $form->createView(),
                ]));
            } else {
                return new Response($this->twig->render('employer/profile_notasks.html.twig', [
                    'currentUser' => $user,
                    //'tasks' => $tasks,
                    'calendars' => $calendars,
                    'reservations' => $reservations
                    //'renters' => $renters,
                ]));
            }
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }
}
