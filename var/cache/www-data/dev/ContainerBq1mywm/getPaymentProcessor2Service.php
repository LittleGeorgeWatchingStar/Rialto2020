<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Payment\PaymentProcessor' shared autowired service.

return $this->services['Rialto\\Payment\\PaymentProcessor'] = new \Rialto\Payment\PaymentProcessor(${($_ = isset($this->services['Rialto\\Payment\\AuthorizeNet']) ? $this->services['Rialto\\Payment\\AuthorizeNet'] : $this->load('getAuthorizeNetService.php')) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.flash']) ? $this->services['monolog.logger.flash'] : $this->load('getMonolog_Logger_FlashService.php')) && false ?: '_'});
