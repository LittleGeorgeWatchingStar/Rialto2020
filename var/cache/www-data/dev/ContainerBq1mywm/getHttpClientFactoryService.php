<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Gumstix\SSOBundle\Service\HttpClientFactory' shared service.

return $this->services['Gumstix\\SSOBundle\\Service\\HttpClientFactory'] = new \Gumstix\SSOBundle\Service\HttpClientFactory(${($_ = isset($this->services['Gumstix\\SSO\\Service\\CredentialStorage']) ? $this->services['Gumstix\\SSO\\Service\\CredentialStorage'] : ($this->services['Gumstix\\SSO\\Service\\CredentialStorage'] = new \Gumstix\SSO\Service\FileCredentialStorage(($this->targetDirs[4].'/app/../var/data/gumstix_sso/credential.data')))) && false ?: '_'});
