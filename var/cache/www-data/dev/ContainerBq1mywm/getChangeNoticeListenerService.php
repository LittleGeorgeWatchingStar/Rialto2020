<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Wordpress\ChangeNoticeListener' shared autowired service.

return $this->services['Rialto\\Wordpress\\ChangeNoticeListener'] = new \Rialto\Wordpress\ChangeNoticeListener(new \Gumstix\Wordpress\Service\RpcClient(new \GuzzleHttp\Client(['base_uri' => 'http://www.mystix.com', 'http_errors' => false]), 'rialto', ''), ${($_ = isset($this->services['templating']) ? $this->services['templating'] : $this->load('getTemplatingService.php')) && false ?: '_'}, 'pcn');
