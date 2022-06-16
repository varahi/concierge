<?php

namespace App\Controller;

use App\Entity\Materials;
use App\Entity\Packs;
use App\Entity\Params;
use App\Entity\Prestation;
use App\Entity\Services;
use App\Form\Settings\PackFormType;
use App\Form\Settings\EditPackFormType;
use App\Form\Settings\MaterialFormType;
use App\Form\Settings\ServiceFormType;
use App\Form\Settings\PrestationFormType;
use App\Repository\PacksRepository;
use App\Repository\ParamsRepository;
use App\Repository\PrestationRepository;
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
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;

class SettingsController extends AbstractController
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';

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
     * @Route("/add-pack", name="app_add_pack")
     */
    public function addPack(
        Request $request,
        TranslatorInterface $translator,
        PrestationRepository $prestationRepository,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $pack = new Packs();
            $form = $this->createForm(PackFormType::class, $pack, [
                'action' => $this->generateUrl('app_add_pack'),
                'method' => 'POST'
            ]);
            $form->handleRequest($request);
            $prestations = $prestationRepository->findAll();

            if ($form->isSubmitted()) {
                $postArr = $_POST['pack_form'];
                $entityManager = $this->doctrine->getManager();

                // Array of quantities
                if (!empty($postArr['new_quantity'][0] && is_array($postArr['new_quantity']))) {
                    foreach ($postArr['new_quantity'] as $key => $quantity) {
                        $qty[] = $quantity;
                    }
                }
                // New contain array params
                if (!empty($postArr['new_contain'][0] && is_array($postArr['new_contain']))) {
                    foreach ($postArr['new_contain'] as $key => $containId) {
                        $contain = $prestationRepository->findOneBy(['id' => $containId]);
                        $newParam = new Params();
                        $newParam->setPrestation($contain);
                        $newParam->setQuantity($qty[$key]);
                        $newParam->setPacks($pack);
                        $discount = str_replace(',', '.', $postArr['new_discount']);
                        if ($discount[$key] !=='') {
                            $sum[] = $contain->getPrice() * $qty[$key] - $discount[$key];
                            $newParam->setDiscount($discount[$key]);
                            $newParam->setPrice($contain->getPrice() - $discount[$key]);
                        } else {
                            $sum[] = $contain->getPrice() * $qty[$key];
                            $newParam->setDiscount(null);
                            $newParam->setPrice($contain->getPrice());
                        }
                        $entityManager->persist($newParam);
                    }
                    $sum = number_format(array_sum($sum), '2');
                    $pack->setTotal($sum);
                }

                $entityManager->persist($pack);
                $entityManager->flush();
                // Maessage and redirect
                $message = $translator->trans('Pack added', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                //return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }

            return new Response($this->twig->render('administrator/forms/settings/new_pack_form.html.twig', [
                'pack_form' => $form->createView(),
                'prestations' => $prestations,
                'add_pack' => 1
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-pack/pack-{id}", name="app_edit_pack")
     */
    public function editPack(
        Request $request,
        Packs $pack,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        PrestationRepository $prestationRepository,
        ParamsRepository $paramsRepository
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $entityManager = $this->doctrine->getManager();
            $form = $this->createForm(EditPackFormType::class, $pack, [
                'action' => $this->generateUrl('app_edit_pack', ['id' => $pack->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            // Get prestations attached to pack
            if ($pack->getParams()) {
                foreach ($pack->getParams() as $param) {
                    $paramIds[] = $param->getId();
                }
            }

            if (isset($paramIds) && is_array($paramIds)) {
                $paramIdsArr = array_unique($paramIds, SORT_STRING);
                //$prestations = $prestationRepository->findByIds($paramIdsArr);
                $params = $paramsRepository->findByIds($paramIdsArr);
            } else {
                $params = null;
            }

            $prestations = $prestationRepository->findAll();

            if ($form->isSubmitted()) {
                $postArr = $_POST['pack_form'];
                // Edit existing fields
                if (isset($postArr['param'])) {
                    foreach ($postArr['param'] as $key => $param) {
                        $paramObject = $paramsRepository->findOneBy(['id' => $param]);
                        if (isset($postArr['quantity']) && $paramObject instanceof Params) {
                            $paramObject->setQuantity($postArr['quantity'][$key]);
                        }
                        if (isset($postArr['price']) && $paramObject instanceof Params) {
                            $price = str_replace(',', '.', $postArr['price']);
                            $paramObject->setPrice($price[$key]);
                        }
                        if (isset($postArr['name'])) {
                            $pack->setName($postArr['name']);
                        }
                        if (isset($postArr['contain'])) {
                            $prestation = $prestationRepository->findOneBy(['id' => $postArr['contain'][$key]]);
                            if ($prestation instanceof Prestation) {
                                $paramObject->setPrestation($prestation);
                            }
                        }
                        //$sum[] = $paramObject->getPrice() * $paramObject->getQuantity();
                        $discount = str_replace(',', '.', $postArr['discount']);
                        if ($discount[$key] !=='') {
                            $sum[] = $paramObject->getPrestation()->getPrice() * $paramObject->getQuantity() - $discount[$key];
                            $paramObject->setDiscount($discount[$key]);
                            $paramObject->setPrice($paramObject->getPrestation()->getPrice() - $discount[$key]);
                        } else {
                            $sum[] = $paramObject->getPrestation()->getPrice() * $paramObject->getQuantity();
                            //$paramObject->setDiscount(null);
                            $paramObject->setPrice($paramObject->getPrestation()->getPrice());
                        }
                    }
                    $sum = number_format(array_sum($sum), '2');
                    $pack->setTotal($sum);
                    $entityManager->persist($paramObject);
                }

                // Add new fields // New contain array params
                if (!empty($postArr['new_quantity'][0] && is_array($postArr['new_quantity']))) {
                    foreach ($postArr['new_quantity'] as $newQuantity) {
                        $newQty[] = $newQuantity;
                    }
                }
                // Add New contain array params
                if (!empty($postArr['new_contain'][0] && is_array($postArr['new_contain']))) {
                    foreach ($postArr['new_contain'] as $key => $containId) {
                        $newContain = $prestationRepository->findOneBy(['id' => $containId]);
                        $newParam = new Params();
                        $newParam->setPrestation($newContain);
                        $newParam->setQuantity($newQty[$key]);
                        $newParam->setPacks($pack);

                        $discount = str_replace(',', '.', $postArr['new_discount']);
                        if ($discount[$key] !=='') {
                            //$sum .= $newContain->getPrice() * $newQty[$key] - $discount[$key];
                            $newSum[] = $newContain->getPrice() * $newQty[$key] - $discount[$key];
                        } else {
                            //$sum .= $newContain->getPrice() * $newQty[$key];
                            $newSum[] = $newContain->getPrice() * $newQty[$key];
                        }
                        $entityManager->persist($newParam);
                    }

                    $newSum = number_format(array_sum($newSum), '2');
                    $totalNewSum = $sum + $newSum;
                    $pack->setTotal(number_format(array_sum((array)$totalNewSum), '2'));
                }

                $entityManager->persist($pack);
                $entityManager->flush();
                $message = $translator->trans('Pack edited', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }
            return new Response($this->twig->render('administrator/forms/settings/edit_pack_form.html.twig', [
                'pack_form' => $form->createView(),
                'prestations' => $prestations,
                'pack' => $pack,
                'params' => $params,
                'edit_pack' => 1
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-pack/pack-{id}", name="app_delete_pack")
     */
    public function deletePack(
        Packs $pack,
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $entityManager = $this->doctrine->getManager();
            // First remove related params
            if ($pack->getParams()) {
                foreach ($pack->getParams() as $param) {
                    $entityManager->remove($param);
                }
                $entityManager->flush();
            }
            // Remove related in housing
            if ($pack->getHousings()) {
                foreach ($pack->getHousings() as $housing) {
                    $housing->setPacks(null);
                }
                $entityManager->flush();
            }
            // Remove pack
            $entityManager->remove($pack);
            $entityManager->flush();
            $message = $translator->trans('Pack removed', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/add-material", name="app_add_material")
     */
    public function addMaterial(
        Request $request,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $material = new Materials();
            $form = $this->createForm(MaterialFormType::class, $material, [
                'action' => $this->generateUrl('app_add_material'),
                'method' => 'POST'
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // We use checkboxes instead radiobuttons
                //$this->setRadioChoice($request, $material, 'material_form');
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($material);
                $entityManager->flush();
                $message = $translator->trans('Material added', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }

            return new Response($this->twig->render('administrator/forms/settings/material_form.html.twig', [
                'material_form' => $form->createView(),
                'add_material' => 1
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-material/material-{id}", name="app_edit_material")
     */
    public function editMaterial(
        Request $request,
        Materials $material,
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $form = $this->createForm(MaterialFormType::class, $material, [
                'action' => $this->generateUrl('app_edit_material', ['id' => $material->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                // We use checkboxes instead radiobuttons
                //$this->setRadioChoice($request, $material, 'material_form');
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($material);
                $entityManager->flush();
                $message = $translator->trans('Material edited', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }
            return new Response($this->twig->render('administrator/forms/settings/material_form.html.twig', [
                'material_form' => $form->createView(),
                'material' => $material,
                'edit_material' => 1
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-material/material-{id}", name="app_delete_material")
     */
    public function deleteMaterial(
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        Materials $material
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            if ($material instanceof Materials) {
                $entityManager = $this->doctrine->getManager();
                $entityManager->remove($material);
                $entityManager->flush();
                $message = $translator->trans('Material removed', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/add-service", name="app_add_service")
     */
    public function addService(
        Request $request,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $service = new Services();
            $form = $this->createForm(ServiceFormType::class, $service, [
                'action' => $this->generateUrl('app_add_service'),
                'method' => 'POST'
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                // We use checkboxes instead radiobuttons
                //$this->setRadioChoice($request, $service, 'service_form');
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($service);
                $entityManager->flush();
                $message = $translator->trans('Service added', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }

            return new Response($this->twig->render('administrator/forms/settings/service_form.html.twig', [
                'service_form' => $form->createView(),
                'service' => $service,
                'add_service' => 1
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-service/service-{id}", name="app_edit_service")
     */
    public function editService(
        Request $request,
        Services $service,
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $form = $this->createForm(ServiceFormType::class, $service, [
                'action' => $this->generateUrl('app_edit_service', ['id' => $service->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                // We use checkboxes instead radiobuttons
                //$this->setRadioChoice($request, $service, 'service_form');
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($service);
                $entityManager->flush();
                $message = $translator->trans('Service edited', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }
            return new Response($this->twig->render('administrator/forms/settings/service_form.html.twig', [
                'service_form' => $form->createView(),
                'service' => $service,
                'edit_service' => 1
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-service/service-{id}", name="app_delete_service")
     */
    public function deleteService(
        Services $service,
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            if ($service instanceof Services) {
                $entityManager = $this->doctrine->getManager();
                $entityManager->remove($service);
                $entityManager->flush();
                $message = $translator->trans('Service removed', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/add-prestation", name="app_add_prestation")
     */
    public function addPrestation(
        Request $request,
        TranslatorInterface $translator,
        NotifierInterface $notifier
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $prestation = new Prestation();

            $form = $this->createForm(PrestationFormType::class, $prestation, [
                'action' => $this->generateUrl('app_add_prestation'),
                'method' => 'POST'
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                // Custom check renteror owner radiobutton
                //$this->setRadioChoice($request, $prestation, 'prestation_form');
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($prestation);
                $entityManager->flush();
                $message = $translator->trans('Prestation added', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }

            return new Response($this->twig->render('administrator/forms/settings/prestation_form.html.twig', [
                'prestation_form' => $form->createView(),
                'add_prestattion' => 1
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/edit-prestation/prestation-{id}", name="app_edit_prestation")
     */
    public function editPrestation(
        Request $request,
        Prestation $prestation,
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ): Response {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            $form = $this->createForm(PrestationFormType::class, $prestation, [
                'action' => $this->generateUrl('app_edit_prestation', ['id' => $prestation->getId()]),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                // Custom check renteror owner radiobutton
                //$this->setRadioChoice($request, $prestation, 'prestation_form');

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($prestation);
                $entityManager->flush();
                $message = $translator->trans('Prestation edited', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }
            return new Response($this->twig->render('administrator/forms/settings/prestation_form.html.twig', [
                'prestation_form' => $form->createView(),
                'edit_prestattion' => 1,
                'prestation' => $prestation
            ]));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-prestation/prestation-{id}", name="app_delete_prestation")
     */
    public function deletePrestation(
        Prestation $prestation,
        NotifierInterface $notifier,
        TranslatorInterface $translator
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {
            if ($prestation instanceof Prestation) {
                $entityManager = $this->doctrine->getManager();
                $entityManager->remove($prestation);
                $entityManager->flush();
                $message = $translator->trans('Prestation removed', array(), 'flash');
                $notifier->send(new Notification($message, ['browser']));
                return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
            }
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/delete-contain/param-{id}", name="app_delete_contain")
     */
    public function deleteContain(
        Params $param,
        NotifierInterface $notifier,
        ParamsRepository $paramsRepository,
        PacksRepository $packsRepository,
        TranslatorInterface $translator
    ) {
        if ($this->security->isGranted(self::ROLE_ADMIN)) {

            // Remove and flush param
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($param);
            $entityManager->flush();

            // Re-calculate new total sum for pack
            $packId = $param->getPacks()->getId();
            $pack = $packsRepository->findOneBy(['id' => $packId]);
            foreach ($pack->getParams() as $param) {
                $paramObject = $paramsRepository->findOneBy(['id' => $param]);
                $sum[] = $paramObject->getPrestation()->getPrice() * $paramObject->getQuantity() - $paramObject->getDiscount();
            }
            $pack->setTotal(array_sum($sum));
            $entityManager->persist($pack);
            $entityManager->flush();

            // Message and redirect
            $message = $translator->trans('Contain removed', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirect($this->generateUrl('app_administrator', ['lightbox' => '1']));
        } else {
            $message = $translator->trans('Please login', array(), 'flash');
            $notifier->send(new Notification($message, ['browser']));
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @param Request $request
     * @param $property
     * @param string $formKey
     * @return void
     */
    private function setRadioChoice(
        Request $request,
        $property,
        string $formKey
    ) {
        // Custom check renteror owner radiobutton
        $post = $request->request->get($formKey);
        if (isset($post['isRenter'])) {
            if ($post['isRenter'] == 'renter') {
                $property->setIsRenter(true);
                $property->setIsOwner(false);
            }
            if ($post['isRenter'] == 'owner') {
                $property->setIsOwner(true);
                $property->setIsRenter(false);
            }
        }
    }
}
