<?php

namespace App\Controller;

use App\Controller\Traits\InvoiceTrait;
use App\Entity\Invoice;
use App\Entity\InvoiceContain;
use App\Entity\Params;
use App\Entity\Prestation;
use App\Entity\User;
use App\Entity\Task;
use App\Form\Invoice\InvoicePageFormType;
use App\Form\Invoice\InvoiceContainFormType;
use App\Form\Invoice\EditInvoiceContainFormType;
use App\Form\Invoice\AddInvoiceContainFormType;
use App\Form\Invoice\EditInvoiceFormType;
use App\Repository\InvoiceContainRepository;
use App\Repository\InvoiceRepository;
use App\Repository\MaterialsRepository;
use App\Repository\PrestationRepository;
use App\Repository\ServicesRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\DateUtility;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class InvoiceController extends AbstractController
{
    use InvoiceTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var Security
     */
    private $security;

    private $twig;

    private $urlGenerator;

    private $placeholderDateFormat;

    private $scriptDateFormat;

    private $doctrine;

    /**
     * @param Security $security
     * @param Environment $twig
     * @param EmailVerifier $emailVerifier
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $placeholderDateFormat
     * @param string $scriptDateFormat
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        Security $security,
        Environment $twig,
        EmailVerifier $emailVerifier,
        UrlGeneratorInterface $urlGenerator,
        string $placeholderDateFormat,
        string $scriptDateFormat,
        ManagerRegistry $doctrine
    ) {
        $this->security = $security;
        $this->twig = $twig;
        $this->emailVerifier = $emailVerifier;
        $this->urlGenerator = $urlGenerator;
        $this->placeholderDateFormat = $placeholderDateFormat;
        $this->scriptDateFormat = $scriptDateFormat;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/invoice-page/user-{id}", name="app_invoice_page")
     */
    public function invoicePage(
        Request $request,
        User $user,
        UserRepository $userRepository,
        TranslatorInterface $translator,
        DateUtility $dateUtility,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $routeName = $request->attributes->get('_route');
            $searchString = $request->query->get('q');

            if ($user->getInvoices()) {
                foreach ($user->getInvoices() as $invoice) {
                    $invoices[] = $invoice;
                }
            }

            $invoice = new Invoice();
            $url = $this->generateUrl('app_invoice_page', ['id' => $user->getId()]);
            $form = $this->createForm(InvoicePageFormType::class, $invoice, [
                'action' => $url,
                'userId' => $user->getId(),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $post = $request->request->get('invoice_page_form');
                $userId = $post['user'];
                $user = $userRepository->findOneBy(['id' => $userId]);
                $invoice->setOwner($user);
                if ($post['date'] !=='') {
                    $date = $dateUtility->checkDate($post['date']);
                    $invoice->setDate($date);
                }
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($invoice);
                $invoice->setNumber('FV' . $invoice->getId());
                $entityManager->persist($user);
                $entityManager->flush();

                $message = $translator->trans('Invoice created', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($url);
            }

            return new Response($this->twig->render('invoice/invoice_page.html.twig', [
                'invoice_page_form' => $form->createView(),
                'user' => $user,
                'routeName' => $routeName,
                'searchString' => $searchString,
                'placeholderDateFormat' => $this->placeholderDateFormat,
                'scriptDateFormat' => $this->scriptDateFormat,
            ]));


        // Redirect to first invoice
            /*if(isset($invoices)) {
                return new RedirectResponse($this->urlGenerator->generate('app_edit_invoice', [
                    'id' => $invoices[0]->getId(),
                    'user' => $user->getId()
                ]));
            } else {
            }*/
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-invoice/invoice-{id}", name="app_edit_invoice")
     */
    public function editInvoice(
        Request $request,
        UserRepository $userRepository,
        Invoice $invoice,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $routeName = $request->attributes->get('_route');
            $searchString = $request->query->get('q');
            $userId = $request->query->get('user');
            $user = $userRepository->findOneBy(['id' => $userId]);
            $url = $this->generateUrl('app_edit_invoice', ['id' => $invoice->getId(), 'user' => $userId]);
            $form = $this->createForm(EditInvoiceFormType::class, $invoice, [
                'action' => $url,
                'userId' => $user->getId(),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            // Automatically set total to contain
            $entityManager = $this->doctrine->getManager();
            if ($invoice->getContain()) {
                foreach ($invoice->getContain() as $contain) {
                    if ($contain->getPrestation()) {
                        $contain->setPrice($contain->getPrestation()->getPrice());
                    }
                    if ($contain->getService()) {
                        $contain->setPrice($contain->getService()->getPrice());
                    }
                    if ($contain->getMaterial()) {
                        $contain->setPrice($contain->getMaterial()->getPrice());
                    }
                    $price = $contain->getPrice();
                    $qty = $contain->getQuantity();

                    $contain->setTotal($price * $qty);
                    $totalContain[] = $contain->getTotal();
                }
            }

            if (!empty($totalContain)) {
                $total = array_sum($totalContain);
                $invoice->setTotal($total);
            }

            $invoice->setNumber('FV' . $invoice->getId());
            $entityManager->persist($invoice);
            $entityManager->flush();

            if ($form->isSubmitted() && $form->isValid()) {
                $invoice->setNumber('FV' . $invoice->getId());
                //$entityManager = $this->doctrine->getManager();
                $entityManager->persist($invoice);
                $entityManager->persist($user);
                $entityManager->flush();
                $message = $translator->trans('Invoice updated', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($url);
            }
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }

        return new Response($this->twig->render('invoice/edit_invoice_page.html.twig', [
            'invoice_form' => $form->createView(),
            'invoice' => $invoice,
            'user' => $user,
            'routeName' => $routeName,
            'searchString' => $searchString,
            'placeholderDateFormat' => $this->placeholderDateFormat,
            'scriptDateFormat' => $this->scriptDateFormat,
        ]));
    }

    /**
     * @Route("/delete-invoice/invoice-{id}", name="app_delete_invoice")
     */
    public function deleteInvoice(
        Request $request,
        UserRepository $userRepository,
        Invoice $invoice,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $userId = $request->query->get('user');
            $user = $userRepository->findOneBy(['id' => $userId]);
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($invoice);
            $entityManager->flush();
            // Message and redirect
            $message = $translator->trans('Invoice deleted', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirect($this->generateUrl('app_invoice_page', ['id' => $user->getId()]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/add-invoice-contain/invoice-{id}", name="app_add_invoice_contain")
     */
    public function addInvoiceContain(
        Request $request,
        Invoice $invoice,
        UserRepository $userRepository,
        InvoiceRepository $invoiceRepository,
        MaterialsRepository $materialsRepository,
        ServicesRepository $servicesRepository,
        PrestationRepository $prestationRepository,
        InvoiceContainRepository $invoiceContainRepository,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $contain = new InvoiceContain();
            $userId = $request->query->get('user');
            $user = $userRepository->findOneBy(['id' => $userId]);
            $url = $this->generateUrl('app_add_invoice_contain', ['id' => $invoice->getId(), 'user' => $userId]);
            $form = $this->createForm(AddInvoiceContainFormType::class, $contain, [
                'action' => $url,
                //'invoiceId' => $invoice->getId(),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            $materials = $materialsRepository->findAllOrder(['name' => 'ASC']);
            $services = $servicesRepository->findAllOrder(['name' => 'ASC']);
            $prestations = $prestationRepository->findAllOrder(['name' => 'ASC']);

            if ($form->isSubmitted()) {
                $post = $request->request->get('calendar_form');
                $invoice = $invoiceRepository->findOneBy(['id' => $post['invoice']]);

                // Set related prestations //add_invoice_contain_form
                $this->setInvoiceRelatedParams($request, $prestationRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'prestation', 'setPrestation');
                // Set related services
                $this->setInvoiceRelatedParams($request, $servicesRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'service', 'setService');
                // Set related materials
                $this->setInvoiceRelatedParams($request, $materialsRepository, $invoiceContainRepository, $invoice, 'calendar_form', 'material', 'setMaterial');

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($invoice);
                $entityManager->flush();

                $message = $translator->trans('Invoice updated', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_edit_invoice', ['id' => $invoice->getId(), 'user' => $user->getId()]));
            }
            return new Response($this->twig->render('administrator/forms/invoice/add_contain.html.twig', [
                'add_contain_form' => $form->createView(),
                'invoice' => $invoice,
                'prestations' => $prestations,
                'materials' => $materials,
                'services' => $services,
                'user' => $user
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-invoice-contain/contain-{id}", name="app_edit_invoice_contain")
     */
    public function editInvoiceContain(
        Request $request,
        InvoiceContain $contain,
        PrestationRepository $prestationRepository,
        ServicesRepository $servicesRepository,
        MaterialsRepository $materialsRepository,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $url = $this->generateUrl('app_edit_invoice_contain', ['id' => $contain->getId()]);
            $form = $this->createForm(EditInvoiceContainFormType::class, $contain, [
                'action' => $url,
                'method' => 'POST',
            ]);

            $form->handleRequest($request);
            $prestations = $prestationRepository->findAll();
            $services = $servicesRepository->findAll();
            $materials = $materialsRepository->findAll();

            if ($form->isSubmitted()) {
                $postArr = $request->request->get('edit_invoice_contain_form');
                if (isset($postArr['prestation']) && $postArr['prestation'] !=='') {
                    $prestation = $prestationRepository->findOneBy(['id' => $postArr['prestation']]);
                    $contain->setPrestation($prestation);
                }
                if (isset($postArr['service']) && $postArr['service'] !=='') {
                    $service = $servicesRepository->findOneBy(['id' => $postArr['service']]);
                    $contain->setService($service);
                }
                if (isset($postArr['material']) && $postArr['material'] !=='') {
                    $material = $materialsRepository->findOneBy(['id' => $postArr['material']]);
                    $contain->setMaterial($material);
                }

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($contain);
                $entityManager->flush();
                $notifier->send(new Notification('Contain updated', ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }

            return new Response($this->twig->render('administrator/forms/invoice/edit-invoice-contain.html.twig', [
                'invoice_contain_form' => $form->createView(),
                'prestations' => $prestations,
                'services' => $services,
                'materials' => $materials,
                'contain' => $contain
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            throw new AccessDeniedException($message);
        }
    }

    /**
     * @Route("/edit-invoice-contain/contain-{id}", name="app_edit_invoice_contain_back")
     */
    public function _editInvoiceContain_back(
        Request $request,
        InvoiceContain $contain,
        UserRepository $userRepository,
        InvoiceRepository $invoiceRepository,
        PrestationRepository $prestationRepository,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $userId = $request->query->get('user');
            $user = $userRepository->findOneBy(['id' => $userId]);
            $invoiceId = $request->query->get('invoice');
            $invoice = $invoiceRepository->findOneBy(['id' => $invoiceId]);
            $form = $this->createForm(InvoiceContainFormType::class, $contain, [
                //'action' => $url,
                'action' => $this->generateUrl('app_edit_invoice_contain', ['id' => $contain->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            $prestations = $prestationRepository->findAll();

            if ($form->isSubmitted()) {
                $post = $request->request->get('invoice_contain_form');
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($contain);
                $entityManager->flush();
                $notifier->send(new Notification('Contain updated', ['browser']));
                return $this->redirect($this->generateUrl('app_edit_invoice', ['id' => $post['invoice'], 'user' => $post['user'] ]));
            }

            return new Response($this->twig->render('administrator/forms/invoice/edit-invoice-contain.html.twig', [
                'add_contain_form' => $form->createView(),
                'edit_contain' => '1',
                'invoice' => $invoice,
                'prestations' => $prestations,
                'user' => $user
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-invoice-contain/contain-{id}", name="app_delete_invoice_contain")
     */
    public function deleteInvoiceContain(
        Request $request,
        InvoiceContain $contain,
        PrestationRepository $prestationRepository,
        TaskRepository $taskRepository,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $entityManager = $this->doctrine->getManager();

            // We remove task if prestation have option "isTask"
            $taskId = $request->query->get('task');
            $task = $taskRepository->findOneBy(['id' => $taskId]);
            if (null !== $task) {
                if ($contain->getPrestation()) {
                    $prestationObj = $prestationRepository->findOneBy(['id' => $contain->getPrestation()->getId()]);
                    if ($prestationObj->getIsTask() == true) {
                        // We will use native task because when removing task, then removing invoice belong this task
                        //$entityManager->remove($task);
                        $taskRepository->deleteTaskNative($task);
                    }
                }
            }
            $entityManager->remove($contain);
            $entityManager->flush();

            // Message and redirect
            $message = $translator->trans('Contain deleted', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            $referer = $request->headers->get('referer');
            return new RedirectResponse($referer);

        //$message = $translator->trans('Contain deleted', array(), 'flash');
            //$notifier->send(new Notification($message, ['browser']));
            //return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }
}
