<?php

namespace Rialto\Magento2\Api\Web;

use Gumstix\Magento\Oauth\OauthClient;
use Gumstix\Magento\Oauth\OAuthParams;
use OAuth\Common\Consumer\Credentials;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Used for the OAuth dance between Magento 2 and Rialto to get access tokens.
 *
 * @see http://devdocs.magento.com/guides/v2.1/get-started/authentication/gs-authentication-oauth.html
 */
class OAuthController extends RialtoController
{
    /**
     * Used to handle the initial Post request from magento 2. Steps 1-3 in
     * description link
     *
     * @Route("/magento2/oauth/callback/", name="magento2_oauth_callback")
     * @Method("POST")
     */
    public function callbackAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $repo = $this->getRepository(Storefront::class);
        /** @var Storefront $store */
        $store = $repo->findOneBy(['user' => $user->getId()]);
        assertion(null !== $store);
        $oAuthParams = new OAuthParams();
        $form = $this->createForm(OAuthParamsType::class, $oAuthParams);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $store->setOAuthCredentials($oAuthParams);
            $this->dbm->flush();
            return new Response(Response::HTTP_OK);
        }
        throw $this->badRequest();
    }

    /**
     * Used to request and store the access token needed for Rest Api calls to
     * Magento. Steps 4 - 10 in description link
     *
     * @Route("/magento2/oauth/identity/", name="magento2_oauth_identity")
     * @Method("GET")
     */
    public function identityAction(Request $request)
    {
        $repo = $this->getRepository(Storefront::class);
        $consumerKey = $request->query->get('oauth_consumer_key');
        /** @var Storefront $store */
        $store = $repo->findOneBy(['consumerKey' => $consumerKey]);
        if (!$store) {
            throw $this->badRequest("Invalid Magento 2 consumer key");
        }
        $magentoBaseUrl = $store->getStoreUrl();
        $consumerSecret = $store->getConsumerSecret();
        $oauthVerifier = $store->getOAuthVerifier();
        $credentials = new Credentials($consumerKey, $consumerSecret, $magentoBaseUrl);
        $oAuthClient = OauthClient::createWithDefaults($credentials);
        $requestToken = $oAuthClient->requestRequestToken();
        $accessToken = $oAuthClient->requestAccessToken(
            $requestToken->getRequestToken(),
            $oauthVerifier,
            $requestToken->getRequestTokenSecret()
        );
        $store->setAccessTokens(
            $accessToken->getAccessToken(),
            $accessToken->getAccessTokenSecret());
        $this->dbm->flush();
        return $this->render("magento2/oauth-success.html");
    }
}
