<?php

// Change the namespace according to the location of this class in your bundle

namespace App\Entity;

use App\Repository\DriverRepository;
use App\Repository\MissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Repository\AllocateRepository;

class LoginListener extends AbstractController
{
    public function onLogin(InteractiveLoginEvent $event)
    {
        $missionRepo = $this->getDoctrine()->getManager()->getRepository(Mission::class);
        $driverRepo = $this->getDoctrine()->getManager()->getRepository(Driver::class);
        $rentRepo = $this->getDoctrine()->getManager()->getRepository(Allocate::class);
        $this->update($missionRepo, $driverRepo, $rentRepo);
    }

    public function update(MissionRepository $missionRepo, DriverRepository $driverRepo,
    AllocateRepository $rentRepo)
    {
        $missionRepo->updateMissionTable();
        $driverRepo->updateDriverTable();
        $rentRepo->updateAllocateTable();
    }
}
