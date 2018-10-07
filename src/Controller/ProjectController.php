<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @Route("/project", name="allProjects")
     */
    public function show()
    {
        return $this->render('project/show.html.twig', [
           'connectedUser' => $this->getUser(),
        ]);
    }
}
