<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Ups\Shipping\Webservice\UpsApiService' shared autowired service.

return $this->services['Rialto\\Ups\\Shipping\\Webservice\\UpsApiService'] = new \Rialto\Ups\Shipping\Webservice\UpsApiService(new \Rialto\Ups\UpsAccount('7BCE6A23028BC75C', 'craighughes', 'saywhat'), new \GuzzleHttp\Client(['base_uri' => 'https://wwwcie.ups.com']), ${($_ = isset($this->services['templating']) ? $this->services['templating'] : $this->load('getTemplatingService.php')) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.ups']) ? $this->services['monolog.logger.ups'] : $this->load('getMonolog_Logger_UpsService.php')) && false ?: '_'});
