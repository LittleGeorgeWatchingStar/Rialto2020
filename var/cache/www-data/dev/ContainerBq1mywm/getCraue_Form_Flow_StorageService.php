<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'craue.form.flow.storage' shared service.

return $this->services['craue.form.flow.storage'] = new \Craue\FormFlowBundle\Storage\SessionStorage(${($_ = isset($this->services['session']) ? $this->services['session'] : $this->load('getSessionService.php')) && false ?: '_'});
