<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    /**
     * @Route("/home", name="home")
     */
    public function home(){
        return $this->render('home.html.twig');
    }

    /**
     * @Route("/", name="login")
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
    public function addUser(Request $request, ObjectManager $manager){
        $user = new User();

        $form = $this->createForm(UserRegistrationType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($user);
            $manager->flush();
        }
        dump($user);
        return $this->render('user/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/test", name="Add")
     */
    public function testAdd(ObjectManager $manager){
        $user = new User();
        $user->setFirstName("hamza")
             ->setLastName("lotfi")
             ->setEmail("lol@gmail.com")
             ->setPassword("123")
             ->setCountry("Morocco")
             ->setBirthday(new \DateTime())
             ->setPhoneNumber("0615478487")
             ->setGender("2")
             ->setRole("1");
        $manager->persist($user);
        $manager->flush();
        return $this->redirectToRoute('home');
    }
}
