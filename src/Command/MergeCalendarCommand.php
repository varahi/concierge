<?php

namespace App\Command;

use App\Repository\CalendarRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class MergeCalendarCommand extends Command
{
    private $calendarRepository;

    private $container;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected static $defaultName = 'app:calendar:merge';


    /**
     * @param CalendarRepository $calendarRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        CalendarRepository $calendarRepository,
        EntityManagerInterface $em
    ) {
        $this->calendarRepository = $calendarRepository;
        $this->em = $em;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Merge calendar with same dates and move tasks to merged')
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

        if ($input->getOption('dry-run')) {
            $io->note('Dry mode enabled');
        } else {
        }

        $count = 0;
        $io->success(sprintf('Merged "%d" calendars.', $count));

        return 0;
    }

    /**
     * @return array
     */
    private function getEqualsCalendars()
    {
    }
}
