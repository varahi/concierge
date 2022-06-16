<?php

namespace App\Command;

use App\Repository\TaskRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

//use Symfony\Component\DependencyInjection\ContainerInterface;

class TaskSetTitleIfEmptyCommand extends Command
{
    //private $container;

    protected static $defaultName = 'app:task:settitle';

    /**
     * @var EntityManagerInterface
     */
    private $em;


    /**
     * @param TaskRepository $taskRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        TaskRepository $taskRepository,
        //ContainerInterface $container,
        EntityManagerInterface $em
    ) {
        $this->taskRepository = $taskRepository;
        //$this->container = $container;
        $this->em = $em;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Set a title for a task if has no title')
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
        //$entityManager = $this->container->get('doctrine')->getManager();
        $io = new SymfonyStyle($input, $output);
        $tasks = $this->taskRepository->findEmptyTitle();

        if ($input->getOption('dry-run')) {
            $io->note('Dry mode enabled');
            $count = count($tasks);
        } else {
            foreach ($tasks as $task) {
                $this->setTaskTitle($task);
            }
            $this->em->flush();
            $count = count($tasks);
        }

        $io->success(sprintf('Set title for "%d" tasks.', $count));
        return 0;
    }

    /**
     * Set titles for task
     *
     * @param $task
     */
    private function setTaskTitle($task)
    {
        if ($task->getIsSingle() == null) {
            if ($task->getIsEntry() == true) {
                $task->setTitle('Etat des lieux Entrée');
            } else {
                $task->setTitle('État des lieux Sortie');
            }
        }
    }
}
