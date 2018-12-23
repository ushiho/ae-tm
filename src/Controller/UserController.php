<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

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
            'error' => $error,
        ]);
    }

    /**
     * @Route("/user/profil/edit/", name="ediProfil")
     */
    public function userForm(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        // if($this->getUser()->getRole() == 1){
        $user = $this->getUser();
        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->container->has('session')) {
                throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
            }
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            $manager->persist($user);
            $manager->flush();
            $this->container->get('session')->getFlashBag()->add('success', $this->customizeMsg($request, $user));

            return $this->redirectToRoute('showUser', [
                'id' => $user->getId(),
                ]);
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

    /**
     * @Route("/admin/addUser", name="addUser")
     */
    public function addUser(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {
        if ($this->getUser()->getRole() == 1) {
            $user = new User();
            $form = $this->createForm(UserToAddType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->generatePasswordAndSendEmail($manager, $user, $encoder, $mailer);
                $request->getSession()->getFlashBag()->add('success', $this->customizeMsg($request, $user));

                return $this->redirectToRoute('allUsers');
            }

            return $this->render('addUser.html.twig', [
                'form' => $form->createView(),
                'connectedUser' => $this->getUser(),
                'user' => $user,
            ]);
        } else {
            return $this->redirectToRoute('profil');
        }
    }

    public function customizeMsg(Request $request, User $user)
    {
        if ($request->attributes->get('_route') == 'addUser') {
            return 'The user '.$user->getFirstName().' is added successfully!';
        } elseif ($user == $this->getUser()) {
            return 'Your profil is updated successfully!';
        } else {
            return 'The user '.$user->getFirstName().' is updated successfully!';
        }
    }

    /**
     * @Route("/user", name="allUsers")
     */
    public function findAll(UserRepository $repo)
    {
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
    public function delete(User $user, ObjectManager $manager)
    {
        if ($this->getUser()->getRole() == 1) {
            $manager->remove($user);
            $manager->flush();

            return $this->redirectToRoute('allUsers');
        } else {
            throw $this->createAccessDeniedException("You don't have access to this page");
        }
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \Exception();
    }

    /**
     * @Route("/user/profil", name="profil")
     */
    public function profil()
    {
        return $this->render('user/profil.html.twig', [
            'connectedUser' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/user/show/{id}", name="showUser")
     */
    public function showDetails(User $user = null)
    {
        if ($user) {
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
    public function deleteAll(ObjectManager $manager, UserRepository $repo)
    {
        foreach ($repo->findAll() as $user) {
            if ($user != $this->getUser()) {
                $manager->remove($user);
                $manager->flush();
            }
        }

        return $this->redirectToRoute('allUsers');
    }

    public function sendEmail($user, $passwordNotCrypted, \Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('AE Transportation'))
            ->setFrom('hlotfi.hamza.lotfi@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    // templates/email/registration.html.twig
                    'email/registration.html.twig', array(
                        'name' => $user->getLastName(),
                        'login' => $user->getEmail(),
                        'password' => $passwordNotCrypted,
                    )
                ),
                'text/html'
            );

        $mailer->send($message);
    }

    /**
     * @Route("/resetPassword/", name="resetPassword")
     */
    public function resetPassword(Request $request, ObjectManager $manager, \Swift_Mailer $mailer, UserRepository $repo, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createFormBuilder()
                    ->add('login', EmailType::class)
                ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $repo->findByEmail($form['login']);
            if ($user) {
                $request->getSession()->getFlashBag()->add('resetPassMsg', 'The email is not exist, please enter a valid email.');

                return $this->render('user/resetPassword.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $this->generatePasswordAndSendEmail($manager, $user, $encoder, $mailer);
            $request->getSession()->getFlashBag()->add('resetPassMsg', 'The password is reseted successfully please verify your boite email.');

            return $this->redirectToRoute('login');
        }

        return $this->render('user/resetPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function generatePasswordAndSendEmail(ObjectManager $manager, $user, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {
        $passwordNotCrypted = random_bytes(10);
        $user->setPassword($encoder->encodePassword($user, $passwordNotCrypted));
        $manager->persist($user);
        $manager->flush();
        $this->sendEmail($user, $passwordNotCrypted, $mailer);
    }
}
