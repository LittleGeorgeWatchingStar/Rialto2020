<?php

namespace Rialto\Magento2\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

/**
 * Authenticates Magento 2 API requests.
 */
class MagentoAuthenticator implements
    SimplePreAuthenticatorInterface,
    AuthenticationFailureHandlerInterface
{
    const KEY_QUERY_PARAM = 'APIKey';

    public function createToken(Request $request, $providerKey)
    {
        $apiKey = $request->query->get(self::KEY_QUERY_PARAM);
        if (!$apiKey) {
            throw new BadCredentialsException("Missing API Key");
        }
        return new PreAuthenticatedToken('.anon', $apiKey, $providerKey);
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return ($token instanceof PreAuthenticatedToken)
            && ($token->getProviderKey() === $providerKey);
    }


    public function authenticateToken(TokenInterface $token,
                                      UserProviderInterface $userProvider,
                                      $providerKey)
    {
        assertion($token instanceof PreAuthenticatedToken);
        $apiKey = $token->getCredentials();
        $user = $userProvider->loadUserByUsername($apiKey);

        return new PreAuthenticatedToken($user,
            $apiKey,
            $providerKey,
            $user->getRoles());
    }

    public function onAuthenticationFailure(Request $request,
                                            AuthenticationException $exception)
    {
        return new Response('Access denied', Response::HTTP_FORBIDDEN);
    }
}
