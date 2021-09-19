<?php


namespace Rialto\Geppetto;


use Gumstix\SSOBundle\Service\HttpClientFactory;
use GuzzleHttp\Client;
use Rialto\Geppetto\Cad\GetLibraryPackagesRequest;

class GeppettoClient
{
    /** @var HttpClientFactory */
    private $factory;

    /** @var string */
    private $baseUrl;

    public function __construct(HttpClientFactory $factory, string $baseUrl)
    {
        $this->factory = $factory;
        $this->baseUrl = $baseUrl;
    }

    private function http(): Client
    {
        return $this->factory->builder()
            ->baseUrl($this->baseUrl)
            ->acceptJson()
            ->ssoAuth()
            ->userAgent('Gumstix Rialto')
            ->getClient();
    }

    public function getLibraryPackages(GetLibraryPackagesRequest $request): array
    {
        $url = "api/v3/cad/part-library/{$request->getLibraryName()}/package/";
        $query = [];

        if ($request->hasThroughHolePackages() !== null) {
            $value = $request->hasThroughHolePackages() ? 'yes' : 'no';
            $query['hasThroughHoles'] = $value;
        }

        $response = $this->http()->get($url, [
            'query' => $query,
        ]);
        return json_decode($response->getBody(), true);
    }
}
