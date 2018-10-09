<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RentController extends AbstractController
{
    /**
     * @Route("/rent", name="allRents")
     */
    public function show()
    {
        return $this->render('rent/rentBase.html.twig', [
            'connectedUser' => $this->getUser(),
        ]);
    }
}
