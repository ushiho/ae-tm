<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    /**
     * @Route("/login", name="login")
     * @Method("POST")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('user/login.html.twig', [
            'username' => $lastUsername,
            'error'    => $error,
        ]);
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
            'connectedUser' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/user", name="allUsers")
     */
    public function findAll(UserRepository $repo){
        $users = $repo->findAll();
        return $this->render('user/userBase.html.twig', array(
        'users' => $users,
        'connectedUser' => $this->getUser(),
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

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){
        throw new \Exception();
    }
}
