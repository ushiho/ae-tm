<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class VehicleController extends AbstractController
{
    /**
     * @Route("/vehicle", name="allVehicles")
     */
    public function show()
    {
        return $this->render('vehicle/vehicleBase.html.twig', [
            'connectedUser' => $this->getUser(),
        ]);
    }
}
