<?php

namespace Rialto\Purchasing\Catalog\Remote;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Rialto\Purchasing\Catalog\CatalogItem;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\PurchasedStockItem;

class OctopartCatalog implements SupplierCatalog
{
    const BASE_URI = 'https://octopart.com/api/v3/';
    const PARTS_PATH = 'parts/match';

    /** @var Client */
    private $client;

    /** @var OctopartCatalogParser */
    private $parser;

    /** @var string */
    private $baseUri;

    /** @var string */
    private $apikey;

    public function __construct(Client $client,
                                OctopartCatalogParser $parser,
                                string $apiKey)
    {
        $this->client = $client;
        $this->parser = $parser;
        $this->baseUri = rtrim(self::BASE_URI, '/');
        $this->apikey = $apiKey;
    }

    /**
     * @param PurchasingData $purchData
     * @return CatalogItem
     */
    public function getEntry(PurchasingData $purchData)
    {
        $domain = $purchData->getSupplierDomainName();
        assertion('' != $domain);
        $query = OctopartQuery::fromPurchasingData($purchData);
        $response = $this->search($query);
        return $this->parser->findMatchingEntry($response->getBody(), $purchData);
    }

    /**
     * @return CatalogResult[]
     */
    public function findMatchingItems(OctopartQuery $query, ?PurchasedStockItem $item = null)
    {
        $response = $this->search($query);
        return $this->parser->findMatchingItems($response->getBody(), $query, $item);
    }

    private function search(OctopartQuery $query)
    {
        $response = $this->client->request('GET', $this->url(self::PARTS_PATH), [
            'query' => $this->getQueryString($query),
            'http_errors' => false, // don't throw exception for >= 400
        ]);

        if ($response->getStatusCode() >= 400) {
            $this->handleError($response);
        }
        return $response;
    }

    private function url($path)
    {
        return $this->baseUri . '/' . $path;
    }

    private function getQueryString(OctopartQuery $query)
    {
        return [
            'apikey' => $this->apikey,
            'queries' => json_encode([$query->getSearchTerms()]),
            'include' => [
                'short_description',
                'specs',
            ]
        ];
    }

    private function handleError(ResponseInterface $response)
    {
        throw OctopartCatalogException::fromHttpResponse($response);
    }
}
