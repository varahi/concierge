<?php

namespace App\Command;

use App\Repository\CalendarRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class CalendarCleanupCommand extends Command
{
    /**
     * @var CalendarRepository
     */
    private $calendarRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    protected static $defaultName = 'app:calendar:cleanup';

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
            ->setDescription('Deletes unused calendars from the database')
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

        //symfony cron calendar_cleanup
        //symfony console app:calendar:cleanup

        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('dry-run')) {
            $io->note('Dry mode enabled');
            $calendarArr = $this->getCountCalendar();
            if (null !== $calendarArr) {
                $count = count($calendarArr);
            } else {
                $count = 0;
            }
        } else {
            $calendarArr = $this->getCountCalendar();
            if (null !== $calendarArr) {
                $count = count($calendarArr);
                foreach ($calendarArr as $calendar) {
                    $this->em->remove($calendar);
                }
                $this->em->flush();
            } else {
                $count = 0;
            }
        }

        $io->success(sprintf('Deleted "%d" old unused calendars.', $count));

        return 0;
    }

    /**
     * @return array
     */
    private function getCountCalendar()
    {
        $calendars = $this->calendarRepository->findByEmptyTasks();
        if ($calendars) {
            foreach ($calendars as $calendar) {
                if (count($calendar->getTask()) > 0) {
                    $calendarArr[] = $calendar;
                }
            }
        } else {
            $calendarArr = null;
        }

        return $calendarArr;
    }
}
