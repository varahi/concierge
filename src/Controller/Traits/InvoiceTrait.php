<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Entity\Housing;
use App\Entity\Invoice;
use App\Entity\InvoiceContain;
use App\Entity\Task;
use App\Entity\Renter;
use App\Repository\InvoiceContainRepository;
use App\Repository\MaterialsRepository;
use App\Repository\PacksRepository;
use App\Repository\PrestationRepository;
use App\Repository\RenterRepository;
use App\Repository\ServicesRepository;
use App\Service\DateUtility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

/**
 *
 */
trait InvoiceTrait
{
    private $logger;

    private $doctrine;

    /**
     * @param LoggerInterface|null $logger
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        LoggerInterface $logger = null,
        ManagerRegistry $doctrine
    ) {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
    }


    /**
     * @param Request $request
     * @param string $userServices
     * @param string $userPrestations
     * @param string $userPacks
     * @param string $userMaterials
     * @param Invoice $invoice
     * @param ServicesRepository $servicesRepository
     * @param PrestationRepository $prestationRepository
     * @param PacksRepository $packsRepository
     * @param MaterialsRepository $materialsRepository
     * @param string $formKey
     * @return void
     */
    public function renterOrOwnerRelates(
        Request $request,
        string $userServices,
        string $userPrestations,
        string $userPacks,
        string $userMaterials,
        Invoice $invoice,
        ServicesRepository $servicesRepository,
        PrestationRepository $prestationRepository,
        PacksRepository $packsRepository,
        MaterialsRepository $materialsRepository,
        string $formKey
    ) {
        // ToDo: remove this method
        // Set services and prestations for renter
        // This method don't use anymore because changed model with relations of services, prestations

        $entityManager = $this->doctrine->getManager();
        $post = $request->request->get($formKey);

        // Set services for renter and owner
        if (!empty($post[$userServices])) {
            if ($invoice->getServices()) {
                foreach ($invoice->getServices() as $service) {
                    $invoice->removeService($service);
                    $entityManager->persist($invoice);
                    $entityManager->flush();
                }
            }
            foreach ($post[$userServices] as $renterServiceId) {
                $renterService = $servicesRepository->findOneBy(['id' => $renterServiceId]);
                $invoice->addService($renterService);
            }
        }

        // Set prestations
        if (!empty($post[$userPrestations])) {
            if ($invoice->getPrestations()) {
                foreach ($invoice->getPrestations() as $prestation) {
                    $invoice->removePrestation($prestation);
                    $entityManager->persist($invoice);
                    $entityManager->flush();
                }
            }
            foreach ($post[$userPrestations] as $renterPrestationId) {
                $renterPrestation = $prestationRepository->findOneBy(['id' => $renterPrestationId]);
                $invoice->addPrestation($renterPrestation);
            }
        }

        // Set packs
        if (!empty($post[$userPacks])) {
            if ($invoice->getPacks()) {
                foreach ($invoice->getPacks() as $pack) {
                    $invoice->removePack($pack);
                    $entityManager->persist($invoice);
                    $entityManager->flush();
                }
            }
            foreach ($post[$userPacks] as $renterPackId) {
                $renterPack = $packsRepository->findOneBy(['id' => $renterPackId]);
                $invoice->addPack($renterPack);
            }
        }

        // Set materials
        if (!empty($post[$userMaterials])) {
            if ($invoice->getMaterials()) {
                foreach ($invoice->getMaterials() as $material) {
                    $invoice->removePack($material);
                    $entityManager->persist($invoice);
                    $entityManager->flush();
                }
            }
            foreach ($post[$userMaterials] as $renterMaterialId) {
                $renterMaterial = $materialsRepository->findOneBy(['id' => $renterMaterialId]);
                $invoice->addMaterial($renterMaterial);
            }
        }

        // Set qty for services
        if (!empty($post['renter_services_qty'])) {
            $servicesQty = $post['renter_services_qty'];
            $invoice->setServicesQty((int)$servicesQty);
        }

        // Set qty for prestations
        if (!empty($post['renter_prestations_qty'])) {
            $prestationsQty = $post['renter_prestations_qty'];
            $invoice->setPrestationsQty((int)$prestationsQty);
        }

        // Set qty for packs
        if (!empty($post['renter_packs_qty'])) {
            $packsQty = $post['renter_packs_qty'];
            $invoice->setPacksQty((int)$packsQty);
        }

        // Set qty for materials
        if (!empty($post['renter_materials_qty'])) {
            $materialsQty = $post['renter_materials_qty'];
            $invoice->setMaterialsQty((int)$materialsQty);
        }

        $entityManager->persist($invoice);
        $entityManager->flush();
    }

    /**
     * @param Request $request
     * @param Invoice $invoice
     * @param string $formKey
     * @return void
     */
    public function setAppointed(
        Request $request,
        Invoice $invoice,
        string $formKey
    ) {
        $entityManager = $this->doctrine->getManager();
        $post = $request->request->get($formKey);

        $invoice->setIsRenter(false);
        $invoice->setIsOwner(false);

        if (!empty($post['invoice_appointed'])) {
            if ($post['invoice_appointed'] == 'renter') {
                $invoice->setIsRenter(true);
            }
        }

        if (!empty($post['invoice_appointed'])) {
            if ($post['invoice_appointed'] == 'owner') {
                $invoice->setIsOwner(true);
            }
        }

        $entityManager->persist($invoice);
        $entityManager->flush();
    }


    /**
     * @param $post
     * @param Task $task
     * @param Task $relatedTask
     * @param Housing $apartment
     * @param Renter $renter
     * @param DateUtility $dateUtility
     * @param RenterRepository $renterRepository
     * @return Invoice
     */
    public function setInvoice(
        $post,
        Task $task,
        Task $relatedTask,
        Housing $apartment,
        Renter $renter,
        DateUtility $dateUtility,
        RenterRepository $renterRepository
    ) {
        $entityManager = $this->doctrine->getManager();
        $existingRenter = $renterRepository->findOneBy(
            [
                'firstName' => $post['firstName'],
                'lastName' => $post['lastName'],
                'email' => $post['email'],
            ]
        );
        if (!($existingRenter instanceof Renter)) {
            // nothing to do, use empty Renter object
        } else {
            // Use existing renter
            $renter = $existingRenter;
        }

        // Create invoice for task
        $invoice = new Invoice();
        $invoice->setNumber('PR');

        // Save invoice to get id
        try {
            $entityManager->persist($invoice);
            $entityManager->flush();
        } catch (\Exception $e) {
            //$this->logger->debug('Cant save to database.');
        }

        if ($invoice->getId()) {
            $invoice->setNumber('PR' . $invoice->getId());
        } else {
            $invoice->setNumber('PR' . $task->getId());
        }

        $invoice->addTask($task);
        if (!empty($relatedTask)) {
            $invoice->addTask($relatedTask);
        }
        $currentDateStr = date($this->dateFormat);
        $currentDate = $dateUtility->checkDate($currentDateStr);
        $invoice->setCreated($currentDate);
        $invoice->setDate($currentDate);
        $invoice->setApartment($apartment);
        $invoice->setRenter($renter);
        $invoice->setOwner($apartment->getUser());

        return $invoice;
    }

    /**
     * @param Renter $renter
     * @param Invoice $invoice
     * @return void
     */
    public function setInvoiceForRenter(
        Renter $renter,
        Invoice $invoice
    ) {
        $entityManager = $this->doctrine->getManager();
        $invoice->setRenter($renter);
        $entityManager->persist($invoice);
        $entityManager->flush();
    }

    /**
     * @param Request $request
     * @param PrestationRepository $prestationRepository
     * @param Invoice $invoice
     * @param Task $task
     * @param string $formKey
     * @param string $fieldName
     * @return void
     */
    public function checkPrestationHasTask(
        Request $request,
        PrestationRepository $prestationRepository,
        Invoice $invoice,
        Task $task,
        string $formKey,
        string $fieldName
    ) {
        $entityManager = $this->doctrine->getManager();
        $post = $request->request->get($formKey);

        if (isset($post[$fieldName])) {
            if (!empty($post[$fieldName][0] && is_array($post[$fieldName]))) {
                foreach ($post[$fieldName] as $prestationId) {
                    $prestationObj = $prestationRepository->findOneBy(['id' => $prestationId]);
                    if ($prestationObj->getIsTask() == 1) {
                        $newTask = new Task();
                        // Note! To end task we set start date as end date. It needs to normal sorting of tasks.
                        $newTask->setStartDate($task->getEndDate());
                        $newTask->setEndDate($task->getEndDate());
                        $newTask->setInvoice($invoice);
                        $newTask->setRenter($task->getRenter());
                        $newTask->setHousing($task->getHousing());
                        $newTask->setTitle($prestationObj->getName());
                        $newTask->setCalendar($task->getCalendar());
                        $newTask->setIsPrestation(true);
                        $entityManager->persist($newTask);
                    }
                }
            }
        }
    }

    /**
     * @param Request $request
     * @param $repository
     * @param InvoiceContainRepository $invoiceContainRepository
     * @param Invoice $invoice
     * @param string $formKey
     * @param string $relatedName
     * @param string $relatedMethod
     * @return void
     */
    public function setInvoiceRelatedParams(
        Request $request,
        $repository,
        InvoiceContainRepository $invoiceContainRepository,
        Invoice $invoice,
        string $formKey,
        string $relatedName,
        string $relatedMethod
    ) {
        $entityManager = $this->doctrine->getManager();
        $post = $request->request->get($formKey);

        // Get services qty
        if (isset($post['new_'.$relatedName.'_quantity'])) {
            if (!empty($post['new_'.$relatedName.'_quantity'][0] && is_array($post['new_'.$relatedName.'_quantity']))) {
                foreach ($post['new_material_quantity'] as $newQuantity) {
                    $newQty[] = $newQuantity;
                }
            }
        }

        // Set materials and qty
        if (isset($post['new_'.$relatedName])) {
            if (!empty($post['new_'.$relatedName][0] && is_array($post['new_'.$relatedName]))) {
                foreach ($post['new_'.$relatedName] as $key => $newRelatedId) {
                    $newRelatedObj = $repository->findOneBy(['id' => $newRelatedId]);
                    $newInvoiceContain = new InvoiceContain();
                    $newInvoiceContain->setName('Contain for invoice ' . $invoice->getId());
                    $newInvoiceContain->$relatedMethod($newRelatedObj);
                    if (!isset($newQuantity[$key])) {
                        $newQuantity[$key] = 1;
                    }
                    $newInvoiceContain->setQuantity((int)$newQty[$key]);
                    $newInvoiceContain->setInvoice($invoice);

                    // Store new contain to get price
                    $entityManager->persist($newInvoiceContain);
                    // ToDo: set price and total to new contain
                    $newInvoiceContain->setPrice($newRelatedObj->getPrice());
                    $newInvoiceContain->setTotal($newRelatedObj->getPrice() * (int)$newQty[$key]);
                    $entityManager->persist($newInvoiceContain);
                }
            }
        }

        // Get existing materials qty
        if (isset($post[$relatedName.'_quantity'])) {
            if (!empty($post[$relatedName.'_quantity'][0] && is_array($post[$relatedName.'_quantity']))) {
                foreach ($post[$relatedName.'_quantity'] as $quantity) {
                    $relatedQty[] = $quantity;
                }
                foreach ($post[$relatedName.'_contains'] as $key => $containId) {
                    $invoiceContain = $invoiceContainRepository->findOneBy(['id' => $containId]);
                    $invoiceContainObj[] = $invoiceContain;
                }
            }
        }

        // Set existing material and qty
        if (isset($post[$relatedName])) {
            if (!empty($post[$relatedName][0] && is_array($post[$relatedName]))) {
                foreach ($post[$relatedName] as $key => $relatedId) {
                    $relatedObj = $repository->findOneBy(['id' => $relatedId]);
                    $invoiceContainObj[$key]->setQuantity((int)$relatedQty[$key]);
                    $invoiceContainObj[$key]->$relatedMethod($relatedObj);
                    $invoiceContainObj[$key]->setPrice($relatedObj->getPrice());
                    $invoiceContainObj[$key]->setTotal($relatedObj->getPrice() * (int)(int)$relatedQty[$key]);
                    $entityManager->persist($invoiceContainObj[$key]);
                }
            }
        }

        $entityManager->flush();
    }
}
