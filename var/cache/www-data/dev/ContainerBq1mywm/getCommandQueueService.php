<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Port\CommandBus\CommandQueue' shared autowired service.

return $this->services['Rialto\\Port\\CommandBus\\CommandQueue'] = new \Infrastructure\CommandBus\JmsCommandQueue(${($_ = isset($this->services['serializer']) ? $this->services['serializer'] : $this->load('getSerializerService.php')) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'});
