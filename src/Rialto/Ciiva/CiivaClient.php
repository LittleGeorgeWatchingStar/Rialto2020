<?php


namespace Rialto\Ciiva;


use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\ClientException;
use Rialto\Ciiva\ApiDto\AuthenticationRequest;
use Rialto\Ciiva\ApiDto\AuthenticationResponse;
use Rialto\Ciiva\ApiDto\DtoSerializerFactory;
use Rialto\Ciiva\ApiDto\RequestDto;
use Symfony\Component\Serializer\Serializer;

/**
 * Web client for interacting with the Ciiva API.
 */
class CiivaClient
{
    const BASE_URI = 'https://api.ciiva.com/api';

    /** @var Client */
    private $httpClient;

    /** @var CookieJarInterface */
    private $cookieJar;

    /** @var Serializer */
    private $serializer;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $apiPassword;

    public function __construct(Client $client,
                                string $apiKey,
                                string $apiPassword,
                                string $cookieFile)
    {
        $this->httpClient = $client;
        $this->cookieJar = new FileCookieJar($cookieFile, true);
        $this->serializer = DtoSerializerFactory::create();
        $this->apiKey = $apiKey;
        $this->apiPassword = $apiPassword;

    }

    public function post(RequestDto $requestDto, $retry = true)
    {
        if (!$this->cookieJar->getCookieByName('ss-id')) {
            $this->requestSessionId();
        }

        try {
            $response = $this->httpClient->post($this->url($requestDto->getEndpoint()), [
                'json' => $this->serializer->normalize($requestDto, 'json', [
                    'groups' => ['payload'],
                ]),
                'cookies' => $this->cookieJar,
            ]);
        } catch (ClientException $exception) {
            // TODO: Convert this to middleware.
            if ($exception->getCode() == 401 && $retry) {
                $this->requestSessionId();
                return $this->post($requestDto, false);
            } else {
                throw $exception;
            }
        }

        if ($requestDto->responseClass()) {
            return $this->serializer->deserialize($response->getBody(),
                $requestDto->responseClass(), 'json');
        } else {
            return json_decode($response->getBody(), true);
        }
    }

    private function url(string $path): string
    {
        return self::BASE_URI . $path;
    }

    private function requestSessionId(): string
    {
        $authRequest = new AuthenticationRequest($this->apiKey, $this->apiPassword);

        /** @var AuthenticationResponse $authResponse */
        $authResponse = $this->postWithoutAuth($authRequest);
        $sessionId = $authResponse->getSessionId();

        $this->cookieJar->setCookie(new SetCookie([
            'Domain' => 'ciiva.com',
            'Name' => 'ss-id',
            'Value' => $sessionId,
        ]));

        return $sessionId;
    }

    private function postWithoutAuth(RequestDto $requestDto)
    {
        $response = $this->httpClient->post($this->url($requestDto->getEndpoint()), [
            'json' => $this->serializer->normalize($requestDto, 'json',
                ['groups' => ['payload']]),
        ]);

        if ($requestDto->responseClass()) {
            return $this->serializer->deserialize($response->getBody(),
                $requestDto->responseClass(), 'json');
        } else {
            return json_decode($response->getBody(), true);
        }
    }
}
