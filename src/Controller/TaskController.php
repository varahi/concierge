<?php

namespace App\Controller;

use App\Controller\Traits\TaskTrait;
use App\Entity\Task;
use App\Form\Task\EditTaskFormType;
use App\Form\Task\AddTaskFormType;
use App\Form\Task\TaskNoteFormType;
use App\Form\Task\TaskPhotoFormType;
use App\Repository\InvoiceRepository;
use App\Repository\InvoiceContainRepository;
use App\Repository\TaskRepository;
use App\Service\Mailer;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use App\Repository\CalendarRepository;
use App\Repository\UserRepository;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\FileUploader;
use App\Controller\Traits\CalendarTrait;
use Doctrine\Persistence\ManagerRegistry;

class TaskController extends AbstractController
{
    use CalendarTrait;

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
     * @param ManagerRegistry $doctrine
     * @param string $placeholderDateFormat
     * @param string $scriptDateFormat
     */
    public function __construct(
        Security $security,
        Environment $twig,
        EmailVerifier $emailVerifier,
        UrlGeneratorInterface $urlGenerator,
        string $dateFormat,
        ManagerRegistry $doctrine,
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
     * Add a single task
     *
     * @Route("/add-task", name="app_add_task")
     */
    public function addSingleTask(
        Request $request,
        UserRepository $userRepository,
        CalendarRepository $calendarRepository,
        DateUtility $dateUtility,
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $employers = $userRepository->findByRole('ROLE_EMPLOYER');
            $entryTask = new Task();
            $form = $this->createForm(AddTaskFormType::class, $entryTask, [
                'action' => $this->generateUrl('app_add_task'),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                // If filled only start date
                if (!empty($_POST['start-date'])) {
                    $startDateString = $request->request->get('start-date');
                    if ($dateUtility->checkDate($startDateString) === false) {
                        $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
                        return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                    } else {
                        $startDate = $dateUtility->checkDate($startDateString);
                    }
                    // Property start date for task
                    $entryTask->setStartDate($startDate);
                    // Set calendar for task
                    $calendar = $this->getCalendar($startDateString, $endDateString = null, $notifier, $calendarRepository, $dateUtility, $entryTask);
                }

                // If filled star and end dates
                if (!empty($_POST['start-date']) && !empty($_POST['end-date'])) {
                    $startDateString = $request->request->get('start-date');
                    $endDateString = $request->request->get('end-date');
                    if ($dateUtility->checkDate($startDateString) === false && $dateUtility->checkDate($endDateString) === false) {
                        $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
                        return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                    } else {
                        $startDate = $dateUtility->checkDate($startDateString);
                        $endDate = $dateUtility->checkDate($endDateString);
                    }
                    // Property start date and end date for task
                    $entryTask->setStartDate($startDate);
                    $entryTask->setEndDate($endDate);
                    // Set calendar for task
                    $calendar = $this->getCalendar($startDateString, $endDateString, $notifier, $calendarRepository, $dateUtility, $entryTask);
                }

                /*
                if (!empty($_POST['start-date']) && !empty($_POST['end-date'])) {
                    $startDateString = $request->request->get('start-date');
                    $endDateString = $request->request->get('end-date');
                    // Check if format date Y-m-d
                    if ($dateUtility->checkDate($startDateString) === false && $dateUtility->checkDate($endDateString) === false) {
                        $notifier->send(new Notification('You should enter date format dd-mm-yyyy', ['browser']));
                        return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                    }
                    // Set calendar for task
                    $calendar = $this->getCalendar($startDateString, $endDateString, $notifier, $calendarRepository, $dateUtility, $entryTask);
                }
                */

                $entityManager = $this->doctrine->getManager();

                // Set single task, single task should be also entry task
                $entryTask->setIsEntry(true);
                $entryTask->setIsSingle(true);
                $entityManager->persist($entryTask);

                // Set renter for calendar
                $calendar->setIsSingle(true);
                $calendar->setRenter($entryTask->getRenter());
                $entityManager->persist($calendar);

                // Set properties for end task
                //$this->createEndTask($entryTask);

                $entityManager->flush();
                $notifier->send(new Notification('Your task created', ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }

            return new Response($this->twig->render('administrator/forms/task/add_task.html.twig', [
                'add_task_form' => $form->createView(),
                'employers' => $employers,
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
     * @Route("/edit-task/task-{task}", name="app_edit_task")
     */
    public function editTask(
        Request $request,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        DateUtility $dateUtility,
        CalendarRepository $calendarRepository,
        TaskRepository $taskRepository,
        Task $task,
        Mailer $mailer
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) ||
            $this->security->isGranted(self::ROLE_EMPLOYER) ||
            $this->security->isGranted(self::ROLE_OWNER)) {
            $form = $this->createForm(EditTaskFormType::class, $task, [
                'action' => $this->generateUrl('app_edit_task', ['task' => $task->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if (!empty($_POST['start-date']) || !empty($_POST['end-date'])) {
                    // Check if format date Y-m-d
                    $startDateString = $request->request->get('start-date');
                    $endDateString = $request->request->get('end-date');
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

                    // Set color for end task like on entry task, only not for single
                    if ($task->getIsSingle() == 0 || $task->getIsSingle() == false) {
                        $relatedTask = $taskRepository->findRelaytedTask($task);
                        if ($relatedTask->getIsEntry() == true) {
                            $task->setColor($relatedTask->getColor());
                        }
                    }

                    if ($startDate) {
                        $task->setStartDate($startDate);
                    }
                    if ($endDate) {
                        $task->setEndDate($endDate);
                    }
                    // Note! To end task we set start date as end date. It needs to normal sorting of tasks.
                    if ($task->getIsPrestation() == true || $task->getIsEntry() == false || $task->getIsEntry() == 0) {
                        $task->setStartDate($endDate);
                    }

                    //$startHourString = $request->request->get('start-hour');
                    //$endHourString = $request->request->get('end-hour');
                    //$post = $request->request->get('task_form');
                    //if (is_null($calendar && $calendar instanceof Calendar)) {
                    //$calendarId = $post['calendar'];
                    //$calendar = $calendarRepository->findOneBy(['id' => $calendarId]);
                    //}

                    $calendar = $this->getCalendar($startDateString, $endDateString, $notifier, $calendarRepository, $dateUtility, $task);
                    $this->changeCalendar($startDateString, $endDateString, $calendar, $notifier, $dateUtility);
                }

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($task);
                $entityManager->persist($calendar);
                $entityManager->flush();
                $notifier->send(new Notification('Modification effectuée', ['browser']));

                if ($task->getNotification() == 1) {
                    $postArray = $_POST['task_form'];
                    $message = $translator->trans('Task changed', array(), 'flash');
                    $subject = $translator->trans($message, array(), 'flash');
                    $mailer->sendTaskEmail($task, $subject, 'emails/task_notification.html.twig', $postArray);
                }

                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }
            return new Response($this->twig->render('administrator/forms/task/edit_task.html.twig', [
                'edit_task_form' => $form->createView(),
                'task' => $task,
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
     * @Route("/show-taskphoto/task-{task}", name="app_show_taskphoto")
     */
    public function showTaskPhoto(
        Task $task,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_EMPLOYER)) {
            return new Response($this->twig->render('employer/forms/show_task_photo.html.twig', [
                'task' => $task,
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/show-tasknote/task-{task}", name="app_show_tasknote")
     */
    public function showTaskNote(
        Task $task,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_EMPLOYER)) {
            return new Response($this->twig->render('employer/forms/show_task_note.html.twig', [
                'task' => $task,
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/task-note/task-{task}", name="app_task_note")
     */
    public function taskNote(
        Request $request,
        Task $task,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ) {
        if ($this->security->isGranted(self::ROLE_EMPLOYER)) {
            $form = $this->createForm(TaskNoteFormType::class, $task, [
                'action' => $this->generateUrl('app_task_note', ['task' => $task->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($task);
                $entityManager->flush();
                $message = $translator->trans('Alert added', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                //return new RedirectResponse($this->urlGenerator->generate('app_employer_profile'));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }
            return new Response($this->twig->render('employer/forms/task_note.html.twig', [
                'task_note_form' => $form->createView(),
                'task' => $task,
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/task-photo/task-{task}", name="app_task_photo")
     */
    public function taskPhoto(
        Request $request,
        Task $task,
        TranslatorInterface $translator,
        NotifierInterface $notifier,
        FileUploader $fileUploader
    ) {
        if ($this->security->isGranted(self::ROLE_EMPLOYER)) {
            $form = $this->createForm(TaskPhotoFormType::class, $task, [
                'action' => $this->generateUrl('app_task_photo', ['task' => $task->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {

                // @TODO: remove old file if upload new
                // Upload image to task
                $photoFile = $form->get('photo')->getData();
                if ($photoFile) {
                    $photoFileName = $fileUploader->upload($photoFile);
                    $task->setPhoto($photoFileName);
                }

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($task);
                $entityManager->flush();
                $message = $translator->trans('Photo added', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return new RedirectResponse($this->urlGenerator->generate('app_employer_profile'));
            }
            return new Response($this->twig->render('employer/forms/task_photo.html.twig', [
                'task_photo_form' => $form->createView(),
                'task' => $task,
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-task/task-{id}", name="app_delete_task")
     */
    public function deleteTask(
        Request $request,
        Task $task,
        TaskRepository $taskRepository,
        TranslatorInterface $translator,
        InvoiceRepository $invoiceRepository,
        InvoiceContainRepository $invoiceContainRepository,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $entityManager = $this->doctrine->getManager();

            if ($task->getIsPrestation() == true) {
                // Delete prestation contain
                $invoiceId = $task->getInvoice()->getId();
                $invoice = $invoiceRepository->findOneBy(['id' => $invoiceId]);
                if (count($invoice->getContain()) > 0) {
                    foreach ($invoice->getContain() as $contain) {
                        if ($contain->getPrestation()->getIsTask() == true) {
                            //$entityManager->remove($contain);
                            $invoiceContainRepository->deleteContainNative($contain);
                        }
                    }
                }
                $taskRepository->deleteTaskNative($task);

                // Message and redirect
                $message = $translator->trans('Task deleted', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }

            if ($task->getIsSingle() == true) {
                // Remove single task
                $entityManager->remove($task);
            } else {
                // Remove both non single task
                //$relatedTask = $taskRepository->findRelaytedTask($task->getHousing(), $task->getCalendar(), $task->getRenter(), $task->getId());
                $relatedTask = $taskRepository->findRelaytedTask($task);
                $entityManager->remove($task);
                if (is_null($relatedTask)) {
                    // RelatedTask not exist
                } else {
                    if (isset($relatedTask) && count($relatedTask) >= 1) {
                        foreach ($relatedTask as $item) {
                            $entityManager->remove($item);
                        }
                    } else {
                        $entityManager->remove($relatedTask);
                    }
                }
            }
            $entityManager->flush();

            // Message and redirect
            $message = $translator->trans('Task deleted', array(), 'flash');
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
