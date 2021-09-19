<?php

namespace Rialto\Shopify\Webhook;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Rialto\Security\User\User;
use Rialto\Shopify\Storefront\Storefront;
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
 * Authenticates Shopify webhook requests.
 *
 * @see http://docs.shopify.com/api/tutorials/using-webhooks#verify-webhook
 */
class WebhookAuthenticator implements SimplePreAuthenticatorInterface,
    AuthenticationFailureHandlerInterface
{
    const DOMAIN_HEADER = 'X-Shopify-Shop-Domain';
    const AUTH_HEADER = 'X-Shopify-Hmac-Sha256';

    /** @var ObjectRepository */
    private $storefrontRepo;

    public function __construct(ObjectManager $om)
    {
        $this->storefrontRepo = $om->getRepository(Storefront::class);
    }

    public function createToken(Request $request, $providerKey)
    {
        if (! $request->headers->has(self::DOMAIN_HEADER) ) {
            throw new BadCredentialsException("Missing domain header");
        }
        if (! $request->headers->has(self::AUTH_HEADER) ) {
            throw new BadCredentialsException("Missing auth header");
        }

        return new PreAuthenticatedToken('.anon', $request, $providerKey);
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return ($token instanceof PreAuthenticatedToken) &&
            ($token->getProviderKey() === $providerKey);
    }


    public function authenticateToken(TokenInterface $token,
        UserProviderInterface $userProvider,
        $providerKey)
    {
        assert($token instanceof PreAuthenticatedToken);
        $request = $token->getCredentials();
        assert($request instanceof Request);
        $store = $this->getStorefront($request);
        $this->validate($request, $store);
        $user = $store->getUser();
        assert($user instanceof User);

        return new PreAuthenticatedToken($user,
            $request,
            $providerKey,
            $user->getRoles());
    }

    /** @return Storefront */
    private function getStorefront(Request $request)
    {
        $storeDomain = $request->headers->get(self::DOMAIN_HEADER);
        $store = $this->storefrontRepo->findOneBy(['domain' => $storeDomain]);
        if ( $store ) {
            return $store;
        }
        throw new AuthenticationException("No such storefront '$storeDomain'");
    }

    /**
     * Checks the authentication header against the request and shared secret.
     *
     * @see http://docs.shopify.com/api/tutorials/using-webhooks#verify-webhook
     */
    private function validate(Request $request, Storefront $store)
    {
        $secret = $store->getSharedSecret();
        $data = $request->getContent();
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
        $providedHmac = $request->headers->get(self::AUTH_HEADER);
        if (! hash_equals($calculatedHmac, $providedHmac)) {
            throw new AuthenticationException("Provided HMAC does not match");
        }
    }

    public function onAuthenticationFailure(Request $request,
        AuthenticationException $exception)
    {
        return new Response('Access denied', 403);
    }
}
