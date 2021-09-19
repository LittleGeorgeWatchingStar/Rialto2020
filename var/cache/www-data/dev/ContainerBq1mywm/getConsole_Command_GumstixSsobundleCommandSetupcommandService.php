<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'console.command.gumstix_ssobundle_command_setupcommand' shared service.

return $this->services['console.command.gumstix_ssobundle_command_setupcommand'] = new \Gumstix\SSOBundle\Command\SetupCommand(${($_ = isset($this->services['Gumstix\\SSO\\Service\\CredentialStorage']) ? $this->services['Gumstix\\SSO\\Service\\CredentialStorage'] : ($this->services['Gumstix\\SSO\\Service\\CredentialStorage'] = new \Gumstix\SSO\Service\FileCredentialStorage(($this->targetDirs[4].'/app/../var/data/gumstix_sso/credential.data')))) && false ?: '_'}, ${($_ = isset($this->services['Gumstix\\SSO\\Service\\SingleSignOn']) ? $this->services['Gumstix\\SSO\\Service\\SingleSignOn'] : $this->load('getSingleSignOnService.php')) && false ?: '_'}, 'http://accounts.mystix.com/', 'gumstix', '', '');
