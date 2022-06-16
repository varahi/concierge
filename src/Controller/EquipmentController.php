<?php

namespace App\Controller;

use App\Entity\Housing;
use App\Entity\Objet;
use App\Form\Objet\AddEquipmentFormType;
use App\Form\Objet\EditEquipmentFormType;
use App\ImageOptimizer;
use App\Repository\RoomRepository;
use App\Security\EmailVerifier;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentController extends AbstractController
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_OWNER = 'ROLE_OWNER';

    public const APARTMENT_PRE_DIR = 'apartment_';

    /**
     * @var Security
     */
    private $security;

    private $twig;

    private $urlGenerator;

    private $imageOptimizer;

    private $photoDir;

    private $doctrine;

    /**
     * @param Security $security
     * @param Environment $twig
     * @param EmailVerifier $emailVerifier
     * @param UrlGeneratorInterface $urlGenerator
     * @param ImageOptimizer $imageOptimizer
     * @param ManagerRegistry $doctrine
     * @param string $photoDir
     */
    public function __construct(
        Security $security,
        Environment $twig,
        EmailVerifier $emailVerifier,
        UrlGeneratorInterface $urlGenerator,
        ImageOptimizer $imageOptimizer,
        ManagerRegistry $doctrine,
        string $photoDir
    ) {
        $this->security = $security;
        $this->twig = $twig;
        $this->emailVerifier = $emailVerifier;
        $this->urlGenerator = $urlGenerator;
        $this->imageOptimizer = $imageOptimizer;
        $this->photoDir = $photoDir;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/add-equipment/apartment-{id}", name="app_add_equipment")
     */
    public function addEquipment(
        Request $request,
        Housing $apartment,
        RoomRepository $roomRepository,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_OWNER)) {
            $rooms = $roomRepository->findBy(['apartment' => $apartment], ['id' => 'DESC']);
            $equipment = new Objet();
            $form = $this->createForm(AddEquipmentFormType::class, $equipment, [
                'action' => $this->generateUrl('app_add_equipment', ['id' => $apartment->getId()]),
                'apartmentId' => $apartment->getId(),
                'method' => 'POST'
            ]);
            $form->handleRequest($request);

            // Separate upload directories depends on equipment
            $suffixDirectoryName = self::APARTMENT_PRE_DIR . $apartment->getId() . '/';
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->doctrine->getManager();

                if ($equipment->getDocuments()) {
                    foreach ($equipment->getDocuments() as $document) {
                        $document->setSuffixDirectoryName($suffixDirectoryName);
                        $entityManager->persist($document);
                    }
                }

                $entityManager->persist($equipment);
                $entityManager->flush();
                $message = $translator->trans('New equipment created', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }

            return new Response($this->twig->render('administrator/forms/objet/add_equipment.html.twig', [
                'rooms' => $rooms,
                'apartment' => $apartment,
                'suffixDirectoryName' => $suffixDirectoryName,
                'equipment_form' => $form->createView(),
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-equipment/equipment-{id}", name="app_edit_equipment")
     */
    public function editEquipment(
        Request $request,
        Objet $equipment,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_OWNER)) {
            $form = $this->createForm(EditEquipmentFormType::class, $equipment, [
                'action' => $this->generateUrl('app_edit_equipment', ['id' => $equipment->getId()]),
                //'apartmentId' => $equipment->getId(),
                'method' => 'POST'
            ]);
            $form->handleRequest($request);

            // Separate upload directories depends on equipmnet
            $suffixDirectoryName = self::APARTMENT_PRE_DIR . $equipment->getRoom()->getApartment()->getId() . '/';

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->doctrine->getManager();
                if ($equipment->getDocuments()) {
                    foreach ($equipment->getDocuments() as $document) {
                        $document->setSuffixDirectoryName($suffixDirectoryName);
                        $entityManager->persist($document);
                    }
                }
                // ToDo: implement multiple upload via service, not Doctine events
                $entityManager->persist($equipment);
                $entityManager->flush();
                $message = $translator->trans('Equipment updated', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));

                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
            }

            return new Response($this->twig->render('administrator/forms/objet/edit_equipment.html.twig', [
                'equipment' => $equipment,
                'suffixDirectoryName' => $suffixDirectoryName,
                'equipment_form' => $form->createView(),
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/show-equipment/equipment-{id}", name="app_show_equipment")
     */
    public function showEquipment(
        Request $request,
        Objet $equipment,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_OWNER)) {
            $suffixDirectoryName = self::APARTMENT_PRE_DIR . $equipment->getRoom()->getApartment()->getId() . '/';
            // Get and optimize images
            if ($equipment->getDocuments()) {
                foreach ($equipment->getDocuments() as $document) {
                    $this->imageOptimizer->resize($this->photoDir.'/'.$document->getSuffixDirectoryName().$document->getImageName());
                }
            }

            return new Response($this->twig->render('administrator/forms/objet/show_equipment.html.twig', [
                'equipment' => $equipment,
                'suffixDirectoryName' => $suffixDirectoryName
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-equipment/equipment-{id}", name="app_delete_equipment")
     */
    public function deleteEquipment(
        Request $request,
        Objet $equipment,
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_OWNER)) {
            if ($equipment instanceof Objet) {
                $entityManager = $this->doctrine->getManager();
                $entityManager->remove($equipment);
                $entityManager->flush();
                $message = $translator->trans('Equipment removed', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));

                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
                //return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
            }
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }
}
