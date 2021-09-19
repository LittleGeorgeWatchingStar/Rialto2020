<?php

namespace Rialto\Shopify\Storefront\Web;

use GuzzleHttp\Client;
use Rialto\Security\Role\Role;
use Rialto\Shopify\Storefront\Storefront;
use Rialto\Shopify\Webhook\Api\WebhookApi;
use Rialto\Shopify\Webhook\Webhook;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * For managing which Shopify storefronts have access to Rialto.
 */
class StorefrontController extends RialtoController
{
    /**
     * List all Shopify storefronts.
     *
     * @Route("/shopify/storefront/", name="shopify_storefront_list")
     * @Method("GET")
     * @Template("shopify/storefront/list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $list = $this->getDoctrine()
            ->getRepository(Storefront::class)
            ->findAll();
        return [
            'storefronts' => $list,
        ];
    }

    /**
     * Create a new Shopify storefront.
     *
     * @Route("/shopify/storefront/new/", name="shopify_storefront_create")
     * @Template("shopify/storefront/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $store = new Storefront();
        return $this->processForm($store, $request, 'Created');
    }

    private function processForm(Storefront $store, Request $request, $created)
    {
        $this->setReturnUri($this->getCurrentUri());
        $form = $this->createForm(StorefrontType::class, $store);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($store);
            $this->dbm->flush();
            $this->logNotice("$created storefront $store successfully.");
            $url = $this->generateUrl('shopify_storefront_list');
            return $this->redirect($url);
        }

        return [
            'form' => $form->createView(),
            'store' => $store,
        ];
    }

    /**
     * Update an existing Shopify storefront.
     *
     * @Route("/shopify/storefront/{id}/", name="shopify_storefront_edit")
     * @Template("shopify/storefront/edit.html.twig")
     */
    public function editAction(Storefront $store, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return $this->processForm($store, $request, 'Updated');
    }

    /**
     * List webhooks for $store.
     *
     * @Route("/Shopify/Storefront/{id}/webhooks/",
     *   name="Shopify_Storefront_webhooks")
     * @Method("GET")
     * @Template("shopify/storefront/listWebhooks.html.twig")
     */
    public function listWebhooksAction(Storefront $store)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $apiClient = $this->createWebhookClient($store);
        return [
            'store' => $store,
            'webhooks' => $apiClient->listWebhooks(),
        ];
    }

    /** @return WebhookApi */
    private function createWebhookClient(Storefront $store)
    {
        $httpClient = new Client();
        return new WebhookApi($httpClient, $store);
    }

    /**
     * Delete a webhook from $store.
     *
     * @Route("/Shopify/Storefront/{id}/webhooks/{hookId}/",
     *   name="Shopify_Storefront_webhooks_delete")
     * @Method("DELETE")
     */
    public function deleteWebhookAction(Storefront $store, $hookId)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $apiClient = $this->createWebhookClient($store);
        $apiClient->deleteWebhook($hookId);
        return $this->redirectToList($store);
    }

    private function redirectToList(Storefront $store)
    {
        $url = $this->generateUrl('Shopify_Storefront_webhooks', [
            'id' => $store->getId(),
        ]);
        return $this->redirect($url);
    }

    /**
     * Install webhooks for $store.
     *
     * @Route("/Shopify/Storefront/{id}/webhooks/",
     *   name="Shopify_Storefront_webhooks_install")
     * @Method("POST")
     */
    public function installWebhooksAction(Storefront $store)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $apiClient = $this->createWebhookClient($store);
        $apiClient->deleteAll();

        // This list could be data-driven instead of hardcoded.
        $list = [
            new Webhook('orders/create',
                $this->generateWebhookAddress('shopify_webhook_order_create')),
        ];
        foreach ($list as $webhook) {
            $apiClient->createWebhook($webhook);
        }

        return $this->redirectToList($store);
    }

    private function generateWebhookAddress($route, array $params = [])
    {
        return $this->generateUrl($route, $params, RouterInterface::ABSOLUTE_URL);
    }
}
