<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Ups\TrackingRecord\Cli\PollTrackingNumbersCommand' shared autowired service.

return $this->services['Rialto\\Ups\\TrackingRecord\\Cli\\PollTrackingNumbersCommand'] = new \Rialto\Ups\TrackingRecord\Cli\PollTrackingNumbersCommand(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Port\\CommandBus\\CommandBus']) ? $this->services['Rialto\\Port\\CommandBus\\CommandBus'] : $this->load('getCommandBusService.php')) && false ?: '_'});
