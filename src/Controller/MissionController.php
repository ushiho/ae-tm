<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MissionController extends AbstractController
{
    /**
     * @Route("/mission", name="mission")
     */
    public function index()
    {
        return $this->render('mission/index.html.twig', [
            'controller_name' => 'MissionController',
        ]);
    }
}
