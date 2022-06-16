<?php

namespace App\Command;

use App\Repository\TaskRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class SetTaskDatesFromCalendar extends Command
{
    /**
     * @var TaskRepository
     */
    private $taskRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    protected static $defaultName = 'app:task:settaskdates';


    /**
     * @param TaskRepository $taskRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        TaskRepository $taskRepository,
        EntityManagerInterface $em
    ) {
        $this->taskRepository = $taskRepository;
        $this->em = $em;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Set start and end dates to tasks properties from calendar entity')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tasks = $this->taskRepository->findAll();

        if ($input->getOption('dry-run')) {
            $count = count($tasks);
        } else {
            $count = count($tasks);
            foreach ($tasks as $task) {
                if ($task->getCalendar()) {
                    $startDate = $task->getCalendar()->getStartDate();
                    $endDate = $task->getCalendar()->getEndDate();
                    if ($task->getIsEntry() == 1) {
                        $task->setStartDate($startDate);
                        $task->setEndDate($endDate);
                    }
                    if ($task->getIsEntry() == 0) {
                        $task->setStartDate($endDate);
                        $task->setEndDate($endDate);
                    }
                }
                $this->em->persist($task);
                $this->em->flush();
            }
        }

        $io->success(sprintf('Updated "%d" tasks.', $count));
        return 0;
    }
}
