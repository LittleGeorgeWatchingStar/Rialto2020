<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'security.access_map' shared service.

$this->services['security.access_map'] = $instance = new \Symfony\Component\Security\Http\AccessMap();

$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/login'), [0 => 'IS_AUTHENTICATED_ANONYMOUSLY'], NULL);
$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/$'), [0 => 'IS_AUTHENTICATED_FULLY'], NULL);
$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/css/compiled/'), [0 => 'IS_AUTHENTICATED_FULLY'], NULL);
$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/js/routing$'), [0 => 'IS_AUTHENTICATED_FULLY'], NULL);
$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/supplier'), [0 => 'ROLE_SUPPLIER_SIMPLE', 1 => 'ROLE_EMPLOYEE'], NULL);
$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/api'), [0 => 'ROLE_API_CLIENT'], NULL);
$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/status'), [0 => 'ROLE_ADMIN'], NULL);
$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/magento2/oauth/callback'), [0 => 'ROLE_API_CLIENT'], NULL);
$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/admin-jobs'), [0 => 'ROLE_ADMIN'], NULL);
$instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/'), [0 => 'ROLE_EMPLOYEE'], NULL);

return $instance;
