<?php

namespace App\Controller;

use App\Controller\Traits\TaskTrait;
use App\Entity\Housing;
use App\Entity\Invoice;
use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\DateUtility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\FileService;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class DownloadController extends AbstractController
{
    use TaskTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_EMPLOYER = 'ROLE_EMPLOYER';

    public const ROLE_OWNER = 'ROLE_OWNER';

    private $dateFormat;

    /**
     * @var Security
     */
    private $security;

    /**
     *
     * @param string $dateFormat
     */
    public function __construct(
        Security $security,
        string $dateFormat
    ) {
        $this->security = $security;
        $this->dateFormat = $dateFormat;
    }


    public function saveOwnerCSVFile(
        $owners,
        $filePathName,
        $headline,
        FileService $fileService
    ) {
        $fileService->writeCSVFileEntry($filePathName, $headline."\r\n", 'w+');
        $tableColumn = explode(';', $headline);

        if ($owners) {
            foreach ($owners as $owner) {
                $record = '';
                foreach ($tableColumn as $column) {
                    switch ($column) {
                        case 'Id':
                            $record .= $owner->getId().';';
                            break;
                        case 'First Name':
                            $record .= $owner->getFirstName().';';
                            break;
                        case 'Last Name':
                            $record .= $owner->getLastName().';';
                            break;
                        case 'Email':
                            $record .= $owner->getEmail().';';
                            break;
                        case 'Telephone1':
                            $record .= $owner->getTelephone().';';
                            break;
                        case 'Telephone2':
                            $record .= $owner->getTelephone2().';';
                            break;
                        case 'Address':
                            $record .= $owner->getAddress().';';
                            break;
                        case 'Postal Code':
                            $record .= $owner->getZip().';';
                            break;
                        case 'City':
                            $record .= $owner->getCity().';';
                            break;
                        default:
                            $record .= ';';
                    }
                }
                $fileService->writeCSVFileEntry($filePathName, $record."\r\n", 'a+');
            }
        }
    }

    /**
     * @Route("/administrator/download-owners-list", name="app_owners_csv")
     */
    public function ownersCsv(
        Request $request,
        UserRepository $userRepository,
        FileService $fileService
    ): Response {
        if ($request->query->get('users')) {
            $usersId = $request->query->get('users');
            $usersIArr = explode(',', $usersId);
            $owners = $userRepository->findByIds($usersIArr);
        } else {
            $owners = $userRepository->findByRole(self::ROLE_OWNER);
        }

        $fileName = 'owners-list.csv';
        $headline = 'Id;First Name;Last Name;Email;Telephone1;Telephone2;Address;Postal Code;City';
        $this->saveOwnerCSVFile($owners, $fileName, $headline, $fileService);
        $fileService->downloadFile($fileName);

        $response = new Response();
        return $response;
    }

    /**
     * @Route("/administrator/download-owners-pdf", name="app_owners_pdf")
     */
    public function ownersPdf(
        Request $request,
        UserRepository $userRepository,
        TaskRepository $taskRepository,
        DateUtility $dateUtility,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        FileService $fileService
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $owners = $userRepository->findByRole(self::ROLE_OWNER);

            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('administrator/pdf/owners_pdf.html.twig', [
                'owners' => $owners,
            ]);

            $dompdf = $fileService->prepareDompdf($html, 'apartment.pdf');

            $response = new Response();
            $response->setContent($dompdf->output());
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'application/pdf');

            return $response;
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/entry-leaving/download-pdf", name="app_entry_leaving_pdf")
     */
    public function entryLeavingPdf(
        Request $request,
        UserRepository $userRepository,
        TaskRepository $taskRepository,
        DateUtility $dateUtility,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        FileService $fileService
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            if ($request->query->get('reverse') == 0) {
                $order = 'ASC';
            } else {
                $order = 'DESC';
            }

            $fieldName = $request->query->get('fieldName');
            $employers = $userRepository->findByRole(self::ROLE_EMPLOYER);

            // Changed at 04.04.2022
            //$tasks = $this->getTasksByDateRangeAndCustomOrdering($request, $taskRepository, $dateUtility, $order, $fieldName, $isCalendar = false);
            if ($request->query->get('employee')) {
                $employeeId = explode(',', $request->query->get('employee'));
            } else {
                $employeeId = [];
            }

            //$tasks = $this->getTasksByDateRange($request, $taskRepository, $dateUtility, $employeeId, '-15 days', '+6 month');
            $tasks = $this->getTasksByDateRange($request, $taskRepository, $dateUtility, $employeeId, '0 days', '+6 month');

            $userId = $request->query->get('user');

            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('administrator/pdf/entry_leaving_pdf.html.twig', [
                'title' => "Entry leaving PDF",
                'employers' => $employers,
                'tasks' => $tasks,
                'userId' => $userId
            ]);

            $dompdf = $fileService->prepareDompdf($html, 'entry-leaving.pdf');

            $response = new Response();
            $response->setContent($dompdf->output());
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'application/pdf');

            return $response;
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/entry-leaving/task-list-pdf", name="app_task_list_pdf")
     */
    public function taskListPdf(
        Request $request,
        UserRepository $userRepository,
        TaskRepository $taskRepository,
        DateUtility $dateUtility,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        FileService $fileService
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $employers = $userRepository->findByRole(self::ROLE_EMPLOYER);
            if ($request->query->get('employee')) {
                $employeeId = explode(',', $request->query->get('employee'));
            } else {
                $employeeId = [];
            }
            $tasks = $this->getTasksByDateRange($request, $taskRepository, $dateUtility, $employeeId, '-15 days', '+6 month');

            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('administrator/pdf/task_list_pdf.html.twig', [
                'title' => "Task list PDF",
                'employers' => $employers,
                'tasks' => $tasks,
                //'userId' => $userId
            ]);

            $dompdf = $fileService->prepareDompdf($html, 'entry-leaving.pdf');

            $response = new Response();
            $response->setContent($dompdf->output());
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'application/pdf');

            return $response;
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/apartment-{id}/download-pdf", name="app_apartment_pdf")
     */
    public function apartmentPdf(
        Housing $apartment,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        FileService $fileService
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {

            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('administrator/pdf/apartment_pdf.html.twig', [
                'apartment' => $apartment,
            ]);

            $dompdf = $fileService->prepareDompdf($html, 'apartment.pdf');

            $response = new Response();
            $response->setContent($dompdf->output());
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'application/pdf');

            return $response;
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/task-invoice-{id}/download-pdf", name="app_task_invoice_pdf")
     */
    public function taskInvoicePdf(
        Task $task,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        FileService $fileService
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {

            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('administrator/pdf/task_invoice_pdf.html.twig', [
                'title' => "Invoice PDF",
                'task' => $task,
            ]);

            $dompdf = $fileService->prepareDompdf($html, 'task_invoice.pdf');

            $response = new Response();
            $response->setContent($dompdf->output());
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'application/pdf');

            return $response;
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/invoice-{id}/download-pdf", name="app_invoice_pdf")
     */
    public function invoicePdf(
        Invoice $invoice,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        FileService $fileService
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {

            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('administrator/pdf/invoice_pdf.html.twig', [
                'title' => "Invoice PDF",
                'invoice' => $invoice,
            ]);

            $dompdf = $fileService->prepareDompdf($html, 'task_invoice.pdf');

            $response = new Response();
            $response->setContent($dompdf->output());
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'application/pdf');

            return $response;
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }
}
