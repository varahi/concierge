<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Housing;
use App\Entity\Task;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class Mailer
{
    private $adminEmail;

    private $mailer;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        string $adminEmail
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->adminEmail = $adminEmail;
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendUserEmail(User $user, string $subject, string $template, array $postArray)
    {
        $date = new \DateTime();
        $email = (new TemplatedEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->from($this->adminEmail)
            ->to($user->getEmail())
            ->context([
                'user' => $user,
                'date' => $date,
                'postArray' => $postArray
            ]);

        $this->mailer->send($email);
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendNewApartmentEmail(Housing $housing, string $subject, string $template)
    {
        $date = new \DateTime();
        $email = (new TemplatedEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->from($this->adminEmail)
            ->to($housing->getUser()->getEmail())
            ->context([
                'housing' => $housing,
                'date' => $date
            ]);

        $this->mailer->send($email);
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendTaskEmail(Task $task, string $subject, string $template, array $postArray)
    {
        $date = new \DateTime();

        foreach ($task->getUsers() as $user) {
            $email = (new TemplatedEmail())
                ->subject($subject)
                ->htmlTemplate($template)
                ->from($this->adminEmail)
                ->to($user->getEmail())
                ->context([
                    'user' => $user,
                    'task' => $task,
                    'date' => $date,
                    'postArray' => $postArray
                ]);

            $this->mailer->send($email);
        }
    }
}
