<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\PcbNg\Service\PcbNgClient' shared autowired service.

return $this->services['Rialto\\PcbNg\\Service\\PcbNgClient'] = new \Rialto\PcbNg\Service\PcbNgClient(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'}, 'https://dev-storefront.pcbng.com/', new \GuzzleHttp\Client(['base_uri' => 'https://api-dev.pcbng.com/api/', 'http_errors' => false]), ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\GerbersConverter']) ? $this->services['Rialto\\PcbNg\\Service\\GerbersConverter'] : ($this->services['Rialto\\PcbNg\\Service\\GerbersConverter'] = new \Rialto\PcbNg\Service\GerbersConverter())) && false ?: '_'}, 'stefan.zhang@gumstix.com', '03500208Gs');
