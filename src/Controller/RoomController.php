<?php

namespace App\Controller;

use App\Entity\Housing;
use App\Entity\Room;
use App\Form\Room\AddRoomFormType;
use App\Form\Room\DeleteRoomFormType;
use App\Repository\RoomRepository;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;

class RoomController extends AbstractController
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
     * @param UserProviderInterface $userProvider
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        Security $security,
        Environment $twig,
        EmailVerifier $emailVerifier,
        UrlGeneratorInterface $urlGenerator,
        UserProviderInterface $userProvider,
        ManagerRegistry $doctrine
    ) {
        $this->security = $security;
        $this->twig = $twig;
        $this->emailVerifier = $emailVerifier;
        $this->urlGenerator = $urlGenerator;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/add-room/apartment-{id}", name="app_add_room")
     */
    public function addRoom(
        Request $request,
        Housing $apartment,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_OWNER)) {
            $room = new Room();

            $form = $this->createForm(AddRoomFormType::class, $room, [
                'action' => $this->generateUrl('app_add_room', ['id' => $apartment->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->doctrine->getManager();
                $room->setApartment($apartment);
                $entityManager->persist($room);
                $entityManager->persist($apartment);
                $entityManager->flush();
                $notifier->send(new Notification('New rom created', ['browser']));
                $referer = $request->headers->get('referer');
                return new RedirectResponse($referer);
                //return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
            }

            return new Response($this->twig->render('administrator/forms/room/add_room.html.twig', [
                'add_room_form' => $form->createView(),
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-room/apartment-{id}", name="app_delete_room")
     */
    public function deleteRoom(
        Request $request,
        Housing $apartment,
        RoomRepository $roomRepository,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN) || $this->security->isGranted(self::ROLE_OWNER)) {
            $rooms = $roomRepository->findBy(['apartment' => $apartment], ['id' => 'DESC']);
            $form = $this->createForm(DeleteRoomFormType::class, $apartment, [
                'action' => $this->generateUrl('app_delete_room', ['id' => $apartment->getId()]),
                'apartmentId' => $apartment->getId(),
                'method' => 'POST'
            ]);
            $form->handleRequest($request);

            /*
            // If using multiple form, then after updates for rooms detached all apartments
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $roomIds = $request->request->get('delete_room_form');
                if($roomIds) {
                    foreach ($roomIds['rooms'] as $roomId) {
                        $room = $roomRepository->findOneBy(['id' => $roomId]);
                        $entityManager->remove($room);
                    }
                    $entityManager->flush();
                    $notifier->send(new Notification('Room(s) removed', ['browser']));
                    return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                }
            }
            */

            if ($form->isSubmitted()) {
                $formRequest = $request->request->get('delete_room_form');
                $roomId = $formRequest['rooms']['0'];
                $room = $roomRepository->findOneBy(['id' => $roomId]);
                if ($room instanceof Room) {
                    $entityManager = $this->doctrine->getManager();
                    $entityManager->remove($room);
                    $entityManager->flush();
                    $notifier->send(new Notification('Room(s) removed', ['browser']));
                    $referer = $request->headers->get('referer');
                    return new RedirectResponse($referer);
                    //return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                }
            }

            return new Response($this->twig->render('administrator/forms/room/delete_room.html.twig', [
                'delete_room_form' => $form->createView(),
                'rooms' => $rooms
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }
}
