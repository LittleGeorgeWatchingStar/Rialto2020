<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'jms_serializer.twig_extension.serializer_runtime_helper' shared service.

return $this->services['jms_serializer.twig_extension.serializer_runtime_helper'] = new \JMS\Serializer\Twig\SerializerRuntimeHelper(${($_ = isset($this->services['JMS\\Serializer\\SerializerInterface']) ? $this->services['JMS\\Serializer\\SerializerInterface'] : $this->load('getSerializerInterfaceService.php')) && false ?: '_'});
