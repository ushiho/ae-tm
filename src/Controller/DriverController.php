<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DriverController extends AbstractController
{
    /**
     * @Route("/driver", name="allDrivers")
     */
    public function show()
    {
        return $this->render('driver/driverBase.html.twig', [
            'connectedUser' => $this->getUser(),
        ]);
    }
}
