<?php

namespace Gumstix\TestingBundle\Client;


use Psr\Container\ContainerInterface;
use Rialto\Database\Orm\DbManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * A wrapper that simplifies interacting with the Symfony test client in web
 * tests.
 */
class TestClient
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request(string $method,
                            string $url,
                            array $params = [],
                            array $files = []): Crawler
    {
        return $this->client->request($method, $url, $params, $files);
    }

    public function getResponse(): Response
    {
        return $this->client->getResponse();
    }

    /** @return Response */
    public function get($url, array $query = [])
    {
        $files = [];
        $this->client->request('GET', $url, $query, $files);
        return $this->client->getResponse();
    }

    /** @return Response */
    public function post($url, array $data = [], array $query = [])
    {
        return $this->modify('POST', $url, $data, $query);
    }

    /** @return Response */
    public function put($url, array $data = [], array $query = [])
    {
        return $this->modify('PUT', $url, $data, $query);
    }

    /** @return Response */
    public function delete($url)
    {
        return $this->modify('DELETE', $url);
    }

    private function modify($method, $url, array $data = [], array $query = [])
    {
        $files = $server = [];
        $content = json_encode($data);
        $this->client->request($method, $url, $query, $files, $server, $content);
        return $this->client->getResponse();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->client->getContainer();
    }

    public function getService($id)
    {
        return $this->client->getContainer()->get($id);
    }

    public function setService($id, $object)
    {
        $this->client->getContainer()->set($id, $object);
    }

    /**
     * @return object|DbManager
     */
    public function getManager(): DbManager
    {
        return $this->getService(DbManager::class);
    }

    /**
     * The $intention should match the block prefix of your root
     * form type.
     *
     * For example:
     * $client->request('POST', '/url/of/some/form/', [
     *     'form_block_prefix' => [
     *        // other form inputs...
     *        '_token' => $client->getCsrfToken('form_block_prefix'),
     *     ],
     * ]);
     */
    public function getCsrfToken(string $intention = 'form'): string
    {
        /** @var $mgr CsrfTokenManager */
        $mgr = $this->getService("security.csrf.token_manager");
        return $mgr->getToken($intention)->getValue();
    }
}
