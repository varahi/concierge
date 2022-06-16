<?php

namespace App\Service;

use App\Entity\User;

class AccessService
{
    /**
     * @param User $user
     * @return bool
     */
    public function isAccessExpired(
        User $user
    ) {
        $currentDate = new \DateTime("now");
        if ($user->getEndDate() < $currentDate) {
            //return false;
            // Temporarilly we turned off this check. So method always return true
            return true;
        } else {
            return true;
        }
    }
}
