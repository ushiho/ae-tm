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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
     * @Route("/user/profil/edit/", name="editProfil")
     */
    public function userForm(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserRegistrationType::class, $user)
            ->add('password', PasswordType::class)
            ->add('confirmPassword', PasswordType::class);
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
    }

    /**
     * @Route("/admin/addUser", name="addUser")
     * @Route("/admin/editUser/{id}", name="editUser")
     */
    public function addUser(User $user = null, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {
        if ($this->getUser()->getRole() == 1) {
            if (!$user) {
                $user = new User();
            }
            $form = $this->createForm(UserRegistrationType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->generatePasswordAndSendEmail($manager, $user, $encoder, $mailer, $request);
                $request->getSession()->getFlashBag()->add('success', $this->customizeMsg($request, $user));

                return $this->redirectToRoute('allUsers');
            }

            return $this->render('user/addUser.html.twig', [
                'form' => $form->createView(),
                'connectedUser' => $this->getUser(),
                'user' => $user,
            ]);
        }else{
            return $this->redirectToRoute('error403');
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
        if($this->getUser()->getRole()==1){
        $users = $repo->findAll();

        return $this->render('user/userBase.html.twig', array(
            'users' => $users,
            'connectedUser' => $this->getUser(),
            )
            );
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/user/delete/{id}", name="deleteUser")
     */
    public function delete(User $user, ObjectManager $manager)
    {
        if ($this->getUser()->getRole() != 1) {
            return $this->redirectToRoute('error403');
        }else{
            $manager->remove($user);
            $manager->flush();

            return $this->redirectToRoute('allUsers');
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
        if($this->getUser()->getRole() == 1){
            if ($user) {
                return $this->render('user/show.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'user' => $user,
                ]);
            }

            return $this->redirectToRoute('allUsers');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("user/deleteAll", name="deleteAllUsers")
     */
    public function deleteAll(ObjectManager $manager, UserRepository $repo)
    {
        if($this->getUser()->getRole() == 1){
            foreach ($repo->findAll() as $user) {
                if ($user != $this->getUser()) {
                    $manager->remove($user);
                    $manager->flush();
                }
            }
            return $this->redirectToRoute('allUsers');
        }else{
            return $this->redirectToRoute('error403');
        }

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
                        'lastName' => $user->getLastName(),
                        'login' => $user->getEmail(),
                        'password' => $passwordNotCrypted,
                    )
                ),
                'text/html'
            );

        if (!$mailer->send($message, $failures)) {
            echo 'Failures:';
            print_r($failures);
        }

        $mailer->send($message);
    }

    /**
     * @Route("user/resetPassword", name="resetPassword")
     */
    public function resetPassword(Request $request, ObjectManager $manager, \Swift_Mailer $mailer, UserRepository $repo, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createFormBuilder()
                    ->add('login', EmailType::class)
                    ->add('submit', SubmitType::class, array(
                        'label' => 'Send Password',
                        'attr' => array(
                            'class' => 'btn btn-primary btn-block',
                        ),
                    ))
                ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $repo->findByEmail($form->getData()['login']);
            if (!$user) {
                $request->getSession()->getFlashBag()->add('resetPassMsg', 'The email is not exist, please enter a valid email.');
                
                return $this->render('user/resetPassword.html.twig', [
                    'form' => $form->createView(),
                    ]);
                }
            $this->generatePasswordAndSendEmail($manager, $user, $encoder, $mailer, $request);
            $request->getSession()->getFlashBag()->add('resetPassMsg', 'The password is reseted successfully please verify your boite email.');

            return $this->redirectToRoute('login');
        }

        return $this->render('user/resetPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function generatePasswordAndSendEmail(ObjectManager $manager, $user, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, Request $request)
    {
        if($request->attributes->get('_route') != 'editUser'){
            $passwordNotCrypted = $this->randomPassword(8);
            $user->setPassword($encoder->encodePassword($user, $passwordNotCrypted));
            $this->sendEmail($user, $passwordNotCrypted, $mailer);
        }
        $manager->persist($user);
        $manager->flush();
    }

    function randomPassword($limit) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $limit; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
