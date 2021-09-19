<?php

namespace Rialto\Shopify\Webhook\Cli;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Rialto\Shopify\Storefront\Storefront;
use Rialto\Shopify\Webhook\Api\WebhookApi;
use Rialto\Shopify\Webhook\Webhook;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see http://docs.shopify.com/api/tutorials/using-webhooks
 */
class WebhookCustomCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('rialto:shopify-webhook-custom')
            ->setDescription('Create a custom Shopify webhook (useful for testing).')
            ->addArgument('storefrontID', InputArgument::REQUIRED,
                "The ID of the storefront that this webhook is for")
            ->addArgument('topic', InputArgument::REQUIRED,
                "Webhook topic")
            ->addArgument('url', InputArgument::REQUIRED,
                'Endpoint URL of the webhook');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storeID = $input->getArgument('storefrontID');
        $store = $this->getContainer()
            ->get('doctrine')
            ->getRepository(Storefront::class)
            ->find($storeID);
        $httpClient = new Client();
        $apiClient = new WebhookApi($httpClient, $store);

        $topic = $input->getArgument('topic');
        $address = $input->getArgument('url');
        $webhook = new Webhook($topic, $address);
        try {
            $status = $apiClient->createWebhook($webhook);
        } catch (ClientException $ex) {
            $status = (string) $ex->getResponse();
        }
        $output->writeln("$webhook : $status");
    }
}
