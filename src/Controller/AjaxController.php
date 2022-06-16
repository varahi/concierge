<?php

namespace App\Controller;

use App\Entity\Task;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use App\Service\DateUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;

class AjaxController extends AbstractController
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_EMPLOYER = 'ROLE_EMPLOYER';

    /**
     * @var Security
     */
    private $security;

    private $doctrine;

    /**
     * @param Security $security
     * @param Environment $twig
     * @param EmailVerifier $emailVerifier
     * @param UrlGeneratorInterface $urlGenerator
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        Security $security,
        Environment $twig,
        EmailVerifier $emailVerifier,
        UrlGeneratorInterface $urlGenerator,
        ManagerRegistry $doctrine
    ) {
        $this->security = $security;
        $this->twig = $twig;
        $this->emailVerifier = $emailVerifier;
        $this->urlGenerator = $urlGenerator;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/set-task-starthour/task-{task}", name="app_set_task_starthour")
     */
    public function setTaskStartHour(
        Request $request,
        Task $task,
        DateUtility $dateUtility
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_EMPLOYER)) {
            $calendar = $task->getCalendar();

            $startHourStr = $request->request->get('hour_start'); // format 15:31
            if ($startHourStr !=='') {
                $startHour = $dateUtility->setTime($startHourStr);
                $calendar->setStartHour($startHour);
                //$arrData = ['output' => 'Start time changed to' . $startHourStr];
                $arrData = ['output' => 'enregistré'];
            }

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return new JsonResponse($arrData);
        }
    }

    /**
     * @Route("/set-task-endhour/task-{task}", name="app_set_task_endthour")
     */
    public function setTaskEndHour(
        Request $request,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        Task $task,
        DateUtility $dateUtility
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_EMPLOYER)) {
            $calendar = $task->getCalendar();

            $endHourStr = $request->request->get('hour_end');
            if ($endHourStr !=='') {
                $endHour = $dateUtility->setTime($endHourStr);
                $calendar->setEndHour($endHour);
                //$arrData = ['output' => 'End time changed to' . $endHourStr];
                $arrData = ['output' => 'enregistré'];
            }

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return new JsonResponse($arrData);
        }
    }

    /**
     * @Route("/set-notification/task-{task}", name="app_set_notification")
     */
    public function setNotification(
        Request $request,
        Task $task
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_EMPLOYER)) {
            $notification = $request->query->get('notification');
            $task->setNotification($notification);
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
            //$notifier->send(new Notification('Your notification is updated', ['browser']));
            //return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
            $request = $request->request->get('notification');
            if ($request == 1) {
                //$arrData = ['output' => 'For task #' . $task->getId() . ' turned off notifications'];
            } else {
                //$arrData = ['output' => 'For task #' . $task->getId() . ' turned on notifications'];
            }
            $arrData = ['output' => ''];
            return new JsonResponse($arrData);
        }
    }

    /**
     * @Route("/set-to-archive/task-{task}", name="app_set_archive")
     */
    public function setArchive(
        Request $request,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        Task $task
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_EMPLOYER)) {
            $archive = $request->query->get('archive');
            $task->setIsArchived($archive);
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
            //$notifier->send(new Notification('Task moved to archive', ['browser']));
            //return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
            $request = $request->request->get('archive');
            if ($request == 1) {
                //$arrData = ['output' => 'Task #' . $task->getId() . ' moved to archive'];
            } else {
                //$arrData = ['output' => 'Task #' . $task->getId() . ' moved from archive to active'];
            }
            $arrData = ['output' => ''];
            return new JsonResponse($arrData);
        }
    }
}
