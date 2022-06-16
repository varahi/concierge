<?php

namespace App\Controller;

use App\Entity\Elements;
use App\Entity\Housing;
use App\Entity\Renter;
use App\Form\Housing\AddApartmentFormType;
use App\Form\Housing\ApartmentElementsFormType;
use App\Form\Housing\EditApartmentFormType;
use App\Repository\HousingRepository;
use App\Repository\PacksRepository;
use App\Security\EmailVerifier;
use App\Service\FileUploader;
use App\Service\Mailer;
use App\Controller\RenterController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\String\u;

class HousingController extends AbstractController
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_OWNER = 'ROLE_OWNER';

    /**
     * @var Security
     */
    private $security;

    private $twig;

    private $urlGenerator;

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
     * @param $apartment
     */
    private function setApartmentName($apartment)
    {
        if ($apartment->getResidence()) {
            $residenceName = $apartment->getResidence();
        } else {
            $residenceName = '';
        }
        if ($apartment->getApartment()) {
            $appName = '-' . $apartment->getApartment();
        } else {
            $appName = '';
        }
        if ($apartment->getLogement()) {
            $logementName = '-' . $apartment->getLogement();
        } else {
            $logementName = '';
        }
        $apartmentName = $residenceName . $appName .$logementName . $apartment->getUser()->getLastName();
        $apartment->setName($apartmentName);
    }

    /**
     * @Route("/add-apartment", name="app_apartment")
     */
    public function addApartment(
        Request $request,
        PacksRepository $packsRepository,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        FileUploader $fileUploader,
        Mailer $mailer
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $housing = new Housing();
            $elements = new Elements();
            $form = $this->createForm(AddApartmentFormType::class, $housing, [
                'action' => $this->generateUrl('app_apartment'),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            $packs = $packsRepository->findAll();

            if ($form->isSubmitted()) {
                // Add pack from POST array
                $packId = $_POST['add_apartment_form']['packs'];
                if ($packId !=='') {
                    $pack = $packsRepository->findOneBy(['id' => $packId]);
                    $housing->setPacks($pack);
                }
                $entityManager = $this->doctrine->getManager();
                $elements->setNote('Elements for ' . $housing->getName());
                $housing->setElement($elements);
                // Add empty renter, currently disabled
                //$renterController = new RenterController($this->security, $this->twig, $this->emailVerifier, $this->urlGenerator);
                //$renterController->addNewEmptyRenter($housing);

                // Upload pdf file
                $apartmentFile = $form->get('file')->getData();
                if (null !== $apartmentFile) {
                    $extension = $apartmentFile->guessExtension();
                    if ($extension == 'pdf' && $apartmentFile) {
                        $apartmentFileName = $fileUploader->uploadPdf($apartmentFile);
                        $housing->setFile($apartmentFileName);
                    } else {
                        $message = $translator->trans('Can upload only pdf', array(), 'flash');
                        $notifier->send(new Notification($message, ['browser']));
                        $referer = $request->headers->get('referer');
                        return new RedirectResponse($referer);
                    }
                }

                $entityManager->persist($elements);
                $entityManager->persist($housing);
                // Set complex apartment name
                $this->setApartmentName($housing);
                $entityManager->flush();

                if ($housing->getUser()) {
                    $subject = $translator->trans('Concierge Me - added apartment', array(), 'messages');
                    $mailer->sendNewApartmentEmail($housing, $subject, 'emails/add_apartment_notification.html.twig');
                }

                $notifier->send(new Notification('Your housing created', ['browser']));
                return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
            }

            return new Response($this->twig->render('administrator/forms/housing/add_apartment.html.twig', [
                'add_housing_form' => $form->createView(),
                'packs' => $packs
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/edit-apartment/apartment-{id}", name="app_edit_apartment")
     */
    public function editApartment(
        Request $request,
        PacksRepository $packsRepository,
        NotifierInterface $notifier,
        Housing $apartment,
        FileUploader $fileUploader,
        TranslatorInterface $translator
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $form = $this->createForm(EditApartmentFormType::class, $apartment, [
                'action' => $this->generateUrl('app_edit_apartment', ['id' => $apartment->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            $packs = $packsRepository->findAll();

            // Edit apartment form
            if ($form->isSubmitted()) {
                // Add pack from POST array
                $packId = $_POST['edit_apartment_form']['packs'];
                if ($packId !=='') {
                    $pack = $packsRepository->findOneBy(['id' => $packId]);
                    $apartment->setPacks($pack);
                }

                // Upload pdf file
                $apartmentFile = $form->get('file')->getData();
                if (null !== $apartmentFile) {
                    $extension = $apartmentFile->guessExtension();
                    if ($extension == 'pdf' && $apartmentFile) {
                        $apartmentFileName = $fileUploader->uploadPdf($apartmentFile);
                        $apartment->setFile($apartmentFileName);
                    } else {
                        $message = $translator->trans('Can upload only pdf', array(), 'flash');
                        $notifier->send(new Notification($message, ['browser']));
                        $referer = $request->headers->get('referer');
                        return new RedirectResponse($referer);
                    }
                }

                // Set complex apartment name
                $this->setApartmentName($apartment);
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($apartment);
                $entityManager->flush();

                $message = $translator->trans('Your apartment updated', array(), 'flash');
                //return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                $notifier->send(new Notification($message, ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }

            return new Response($this->twig->render('administrator/forms/housing/edit_apartment.html.twig', [
                'apartment' => $apartment,
                'packs' => $packs,
                'edit_apartment_form' => $form->createView()
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-apartment-elements/element-{id}", name="app_apartment_elements")
     * @property RouterInterface $router
     */
    public function editApartmentElements(
        Request $request,
        NotifierInterface $notifier,
        Elements $element,
        HousingRepository $housingRepository,
        TranslatorInterface $translator
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_OWNER)) {
            $apartmentId = $request->query->get('apartment');
            $apartment = $housingRepository->findOneBy(['id' => $apartmentId], ['id' => 'DESC']);
            $form = $this->createForm(ApartmentElementsFormType::class, $element, [
                'action' => $this->generateUrl('app_apartment_elements', ['id' => $element->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            // Edit apartment form
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($element);
                $entityManager->flush();
                $message = $translator->trans('Your apartment updated', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
                //return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
            }

            return new Response($this->twig->render('administrator/forms/housing/edit_apartment_elements.html.twig', [
                'apartment' => $apartment,
                'edit_apartment_elements_form' => $form->createView()
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/add-apartment-elements/{apartment}", name="app_add_apartment_elements")
     */
    public function addApartmentElements(
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        Housing $apartment
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $elements = new Elements();
            $elements->setNote('Set of elements for apartment ' . $apartment->getName());
            $apartment->setElement($elements);
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($elements);
            $entityManager->persist($apartment);
            $entityManager->flush();
            $notifier->send(new Notification('Created elements for your apartment', ['browser']));
            return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-apartment/apartment-{id}", name="app_delete_apartment_relation")
     */
    public function deleteApartmentRelation(
        Request $request,
        Housing $apartment,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $entityManager = $this->doctrine->getManager();
            // Check if is tasks related with apartment and delete them
            if ($apartment->getTask()) {
                foreach ($apartment->getTask() as $task) {
                    $entityManager->remove($task);
                }
            }
            // Remove related with owner
            $apartment->setUser(null);
            $entityManager->flush();
            // Message and redirect
            $message = $translator->trans('Relation deleted', array(), 'flash');
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
