<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'jms_serializer.xml_serialization_visitor' shared service.

$this->services['jms_serializer.xml_serialization_visitor'] = $instance = new \JMS\Serializer\XmlSerializationVisitor(${($_ = isset($this->services['jms_serializer.serialized_name_annotation_strategy']) ? $this->services['jms_serializer.serialized_name_annotation_strategy'] : $this->load('getJmsSerializer_SerializedNameAnnotationStrategyService.php')) && false ?: '_'}, ${($_ = isset($this->services['jms_serializer.accessor_strategy']) ? $this->services['jms_serializer.accessor_strategy'] : $this->load('getJmsSerializer_AccessorStrategyService.php')) && false ?: '_'});

$instance->setFormatOutput(true);

return $instance;