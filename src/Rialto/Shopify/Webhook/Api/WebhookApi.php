<?php

namespace Rialto\Shopify\Webhook\Api;

use Rialto\Shopify\Api\BaseApi;
use Rialto\Shopify\Webhook\Webhook;

/**
 * An HTTP client for interacting with the Shopify Webhook API.
 */
class WebhookApi extends BaseApi
{
    /**
     * @return Webhook[]
     */
    public function listWebhooks()
    {
        $response = $this->getJson("/admin/webhooks.json");
        $data = $this->decodeBody($response);
        return array_map(function($fields) {
            return Webhook::fromArray($fields);
        }, $data['webhooks']);
    }

    /**
     * @return int The HTTP status code
     */
    public function createWebhook(Webhook $webhook)
    {
        $data = $webhook->toArray();
        $response = $this->postJson("/admin/webhooks.json", ['webhook' => $data]);
        return $response->getStatusCode();
    }

    /**
     * @return int The HTTP status code
     */
    public function deleteWebhook($id)
    {
        $response = $this->delete("/admin/webhooks/$id.json");
        return $response->getStatusCode();
    }

    /**
     * Deletes all webhooks.
     */
    public function deleteAll()
    {
        $list = $this->listWebhooks();
        foreach ( $list as $webhook ) {
            $this->deleteWebhook($webhook->getId());
        }
    }

}
