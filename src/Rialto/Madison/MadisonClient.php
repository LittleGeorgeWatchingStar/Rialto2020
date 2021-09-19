<?php

namespace Rialto\Madison;

use Gumstix\SSOBundle\Service\HttpClientFactory;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Rialto\Geppetto\Design\DesignRevision2;
use Rialto\Madison\Link\Icon;
use Rialto\Madison\Link\LinkFactory;
use Rialto\Stock\Item\StockItem;
use Rialto\Util\Date\Date;

class MadisonClient
{
    /** @var HttpClientFactory */
    private $factory;

    /** @var string */
    private $baseUrl;

    /** @var LinkFactory */
    private $linkFactory;

    public function __construct(HttpClientFactory $factory,
                                $baseUrl,
                                LinkFactory $linkFactory)
    {
        $this->factory = $factory;
        $this->baseUrl = $baseUrl;
        $this->linkFactory = $linkFactory;
    }

    /** @return Client */
    private function http()
    {
        return $this->factory->builder()
            ->baseUrl($this->baseUrl)
            ->acceptJson()
            ->ssoAuth()
            ->userAgent('Gumstix Rialto')
            ->getClient();
    }

    /**
     * Updates the board product corresponding to the Geppetto build, or creates
     * it if it does not exist.
     *
     * @return string The URL of the product page on the store.
     */
    public function createOrUpdateBoardProduct(StockItem $board,
                                               DesignRevision2 $designRevision): string
    {
        $sku = $board->getSku();
        $links = [
            $this->linkFactory->createLink(
                'permalink',
                Icon::PERMALINK,
                $designRevision->getDesignPermalink())
        ];
        $data = [
            'name' => $designRevision->getDesignName(),
            'summary' => substr($designRevision->getDesignDescription(), 0, 500),
            'description' => $designRevision->getDesignDescription(),
            'price' => $designRevision->getPrice(),
            'launchDate' => Date::toIso($board->getDateCreated()),
            'images' => [
                [
                    'imageType' => 'overview',
                    'description' => 'overview',
                    'imageUri' => $designRevision->getImageUrl(),
                ]
            ],
            'links' => $links,
            'owner' => $designRevision->getDesignOwnerIdentifier(),
            'public' => $designRevision->isDesignPublic(),
        ];
        $response = $this->http()->put("api/geppetto/product/$sku/", [
            'json' => $data,
        ]);

        $data = $this->parseResponse($response);
        return $data['url'];
    }

    /** @return string[][] */
    private function parseResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() > 299) {
            throw MadisonException::fromResponse($response);
        }
        return json_decode($response->getBody(), true);
    }

    /**
     * @return array<string,array<string>> A list of feature data, indexed
     *  by feature code.
     */
    public function getFeatures()
    {
        $response = $this->http()->get('api/feature/');
        $results = json_decode($response->getBody(), true);
        $index = [];
        foreach ($results as $feature) {
            $code = $feature['code'];
            $index[$code] = $feature;
        }
        return $index;
    }

    public function updateCurrentVersion(StockItem $item)
    {
        $sku = $item->getSku();
        $this->http()->patch("api/product/$sku/", [
            'json' => [
                'currentVersion' => (string) $item->getShippingVersion(),
            ],
            'exceptions' => true,
        ]);
    }

    /**
     * @param string[] $payload
     */
    public function pushManufacturerFeatures(array $payload)
    {
        $this->http()->post("api/product/manufacturer-features/", [
            'json' => [
                'pairs' => $payload,
            ]
        ]);
    }
}
