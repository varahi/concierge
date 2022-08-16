<?php

namespace App\Command;

use App\Repository\TaskRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class TaskExpiredDateToArchiveCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $dateFormat;

    protected static $defaultName = 'app:task:expiredtoarchive';

    /**
     * @param TaskRepository $taskRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        TaskRepository $taskRepository,
        EntityManagerInterface $em,
        string $dateFormat
    ) {
        $this->taskRepository = $taskRepository;
        $this->em = $em;
        $this->dateFormat = $dateFormat;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Move tasks with expired dates to archive')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        //$tasks = $this->taskRepository->findAll();
        $activeTasks = $this->taskRepository->findActive();
        $archivedTasks = $this->taskRepository->findArchived();
        $count = $this->countExpiredDateTaskToArchive($activeTasks);

        if ($input->getOption('dry-run')) {
            $io->note('Dry mode enabled');
        } else {
            $this->setTaskToArchive($activeTasks);
            // Set tasks to active if task has flag "archived", but has actual visible dates
            $this->setTaskToActive($archivedTasks);
        }

        $io->success(sprintf('Moved to archive "%d" tasks.', $count));
        return 0;
    }

    /**
     * @param $tasks
     */
    private function setTaskToArchive(
        $tasks
    ) {
        if ($tasks) {
            $dateNow = date($this->dateFormat);

            foreach ($tasks as $task) {
                if (null !== $task->getCalendar()->getEndDate()) {
                    $taskDate = $task->getCalendar()->getEndDate()->format($this->dateFormat);
                    if ($dateNow > $taskDate) {
                        $task->setIsArchived(true);
                    }
                    $this->em->persist($task);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * @param $tasks
     * @return void
     */
    private function setTaskToActive(
        $tasks
    ) {
        if ($tasks) {
            $dateNow = date($this->dateFormat);

            foreach ($tasks as $task) {
                if (null !== $task->getCalendar()->getEndDate()) {
                    $taskDate = $task->getCalendar()->getEndDate()->format($this->dateFormat);
                    if ($dateNow <= $taskDate) {
                        $task->setIsArchived(false);
                    }
                    $this->em->persist($task);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * @param $tasks
     * @return int|null
     */
    private function countExpiredDateTaskToArchive(
        $tasks
    ) {
        if ($tasks) {
            $dateNow = date($this->dateFormat);

            // Count tasks to archive to display it in command controller
            $i = 0;
            foreach ($tasks as $task) {
                if (null !== $task->getCalendar()->getEndDate()) {
                    $taskDate = $task->getCalendar()->getEndDate()->format($this->dateFormat);
                    if ($dateNow > $taskDate) {
                        $i++;
                    }
                }
            }
            return $i;
        }

        return null;
    }
}
