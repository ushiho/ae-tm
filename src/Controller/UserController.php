<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render('user/login.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/user/new", name="addUser")
     */
    public function addUser(){
        $user = new User();

        $form = $this->createForm(UserRegistrationType::class, $user);

        return $this->render('user/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
