<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request)
    {
        dump($request);
        return $this->render('user/login.html.twig');
    }

    /**
     * @Route("/user/new", name="addUser")
     * @Route("user/edit/{id}", name="editUser")
     */
    public function userForm(Request $request, ObjectManager $manager,
    UserPasswordEncoderInterface $encoder, User $user = null){
        if($user == null){
            $user = new User();
        }
        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('allUsers');
        }
        return $this->render('user/show.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/user", name="allUsers")
     */
    public function findAll(UserRepository $repo){
        $users = $repo->findAll();
        return $this->render('user/userBase.html.twig', array(
        'users' => $users,
        )
        );
    }

    /**
     * @Route("/user/delete/{id}", name="deleteUser")
     */
    public function delete(User $user, ObjectManager $manager){
        $manager->remove($user);
        $manager->flush();
        return $this->redirectToRoute('allUsers');
    }

}
