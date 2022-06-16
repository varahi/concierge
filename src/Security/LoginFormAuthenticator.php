<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function authenticate(Request $request): Passport
    {
        //$username = $request->request->get('username', '');
        $email = $request->request->get('email', '');
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        $credentials = new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );

        return $credentials;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // If user admin, then redirect to admin panel
        $user = $token->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_administrator'));
        } elseif (in_array('ROLE_EMPLOYER', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_employer_profile'));
        } elseif (in_array('ROLE_OWNER', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_owner_profile'));
        } elseif (in_array('ROLE_AGENCY', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_owner_profile'));
        } elseif (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_backend'));
        } else {
            return new RedirectResponse($this->urlGenerator->generate('app_backend'));
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        //return $this->authenticator->onAuthenticationFailure($request, $exception);
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
