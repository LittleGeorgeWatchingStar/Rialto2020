<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'jms_serializer.templating.helper.serializer' shared service.

return $this->services['jms_serializer.templating.helper.serializer'] = new \JMS\SerializerBundle\Templating\SerializerHelper(${($_ = isset($this->services['JMS\\Serializer\\SerializerInterface']) ? $this->services['JMS\\Serializer\\SerializerInterface'] : $this->load('getSerializerInterfaceService.php')) && false ?: '_'});
