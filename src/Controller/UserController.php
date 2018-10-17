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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends AbstractController
{
     /**
     * Adds a flash message to the current session for type.
     *
     * @throws \LogicException
     */

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
        // if($this->getUser()->getRole() == 1){
            if($user == null){
                $user = new User();
            }
            $form = $this->createForm(UserRegistrationType::class, $user);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                if (!$this->container->has('session')) {
                    throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
                }
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                $manager->persist($user);
                $manager->flush();
                $this->container->get('session')->getFlashBag()->add('success', $this->customizeMsg($request, $user));
                return $this->redirectToRoute('allUsers');
            }
            return $this->render('user/userForm.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
                'connectedUser' => $this->getUser(),
            ]);
        // } else{
        //     throw $this->createAccessDeniedException("You don't have access to this page!");
        // }
    }

    public function customizeMsg(Request $request, User $user){
        if($request->attributes->get('_route') == "addUser"){
            return 'The user '.$user->getFirstName().' is added successfully!';
        }else if($user == $this->getUser()){
            return 'Your profil is updated successfully!';
        }else{
            return 'The user '.$user->getFirstName().' is updated successfully!';
        }
    }

    /**
     * @Route("/user", name="allUsers")
     */
    public function findAll(UserRepository $repo){
        // if($this->getUser()->getRole()==1){
            $users = $repo->findAll();
            return $this->render('user/userBase.html.twig', array(
            'users' => $users,
            'connectedUser' => $this->getUser(),
            )
            );
        // }else{
        //     throw $this->createAccessDeniedException("You don't have access to this page!");
        // }
    }

    /**
     * @Route("/user/delete/{id}", name="deleteUser")
     */
    public function delete(User $user, ObjectManager $manager){
        if($this->getUser()->getRole()==1){
        $manager->remove($user);
        $manager->flush();
        return $this->redirectToRoute('allUsers');
        } else{
            throw $this->createAccessDeniedException("You don't have access to this page");
        }
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){
        throw new \Exception();
    }

    /**
     * @Route("/user/profil", name="profil")
     */
    public function profil(){
        return $this->render('user/profil.html.twig', [
            'connectedUser' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/user/show/{id}", name="showUser")
     */
    public function showDetails(User $user=null){
        if($user){
            return $this->render('user/show.html.twig', [
                'connectedUser' => $this->getUser(),
                'user' => $user,
            ]);
        }
        return $this->redirectToRoute('allUsers');
    }

    /**
     * @Route("user/deleteAll", name="deleteAllUsers")
     */
    public function deleteAll(ObjectManager $manager, UserRepository $repo){
        foreach ($repo->findAll() as $user) {
            if($user != $this->getUser()){
                $manager->remove($user);
                $manager->flush();
            }
        }
        return $this->redirectToRoute('allUsers');
    }

}
