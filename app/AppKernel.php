<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Craue\FormFlowBundle\CraueFormFlowBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle(),
            new JMS\JobQueueBundle\JMSJobQueueBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Nelmio\SecurityBundle\NelmioSecurityBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new League\Tactician\Bundle\TacticianBundle(),
            new Gumstix\FormBundle\GumstixFormBundle(),
            new Gumstix\GeographyBundle\GumstixGeographyBundle(),
            new Gumstix\RestBundle\GumstixRestBundle(),
            new Gumstix\SSOBundle\GumstixSSOBundle(),
            new Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Gumstix\TestingBundle\GumstixTestingBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    private function getProcessOwner(): string
    {
        $processUser = posix_getpwuid(posix_geteuid());
        return $processUser['name'] ?? 'unknown-user';
    }

    public function getCacheDir()
    {
        $proj = $this->getProjectDir();
        $env = $this->getEnvironment();
        $user = $this->getProcessOwner();
        return "$proj/var/cache/$user/$env";
    }

    public function getLogDir()
    {
        $proj = $this->getProjectDir();
        $user = $this->getProcessOwner();
        return "$proj/var/logs/$user";
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yaml');
    }
}
