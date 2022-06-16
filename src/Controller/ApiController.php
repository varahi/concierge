<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    public const ROLE_OWNER = 'ROLE_OWNER';

    /**
     * @Route("/api/owners", name="owners")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOwners(
        UserRepository $userRepository
    ) {
        $owners = $userRepository->findByRole(self::ROLE_OWNER);
        $arrData = $this->getJsonArrData($owners);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent(\json_encode($arrData));

        return $response;
    }

    private function getJsonArrData($items)
    {
        if ($items) {
            foreach ($items as $item) {
                if ($item->getId()) {
                    $itemId = $item->getId();
                }
                if ($item->getCompany()) {
                    $itemCompany = $item->getCompany();
                }
                if ($item->getFirstName()) {
                    $itemFirstName = $item->getFirstName();
                } else {
                    $itemFirstName = null;
                }
                if ($item->getLastName()) {
                    $itemLastName = $item->getLastName();
                } else {
                    $itemLastName = null;
                }

                $arrData[] = [
                    'id' => $itemId,
                    'company' => $itemCompany,
                    'firstName' => $itemFirstName,
                    'lastName' => $itemLastName,
                ];
            }
            return $arrData;
        } else {
            return null;
        }
    }
}
