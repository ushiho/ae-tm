<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    
    private $userRepo;
    protected $encoder;
    private $router;
    
    public function __construct(UserRepository $userRepo, UserPasswordEncoderInterface $encoder,
    RouterInterface $router){
        $this->userRepo = $userRepo;
        $this->encoder = $encoder;
        $this->router = $router;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'login'
        && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        return [
            'email' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $this->user = $this->userRepo->findOneBy(['email' => $credentials['email']]);
        return $this->userRepo->findOneBy(['email' => $credentials['email']]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
    //     dd($this->encoder->encodePassword($user, $credentials['password']).'
    //   user password '.$user->getPassword());
    //     return $this->encoder->encodePassword($user, $credentials['password']) == $user->getPassword();
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->router->generate('allUsers'));
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // todo
    }

    public function supportsRememberMe()
    {
        // todo
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('login');
    }
}
