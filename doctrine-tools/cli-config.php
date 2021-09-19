<?php
require_once 'Doctrine/Common/ClassLoader.php';

$loader = new \Doctrine\Common\ClassLoader('gumstix');
$loader->register();

$config = new \Doctrine\ORM\Configuration();
$xmldefs = array(
    __DIR__ .'../src/Rialto/SecurityBundle/Resources/config/doctrine'
);
$driver = new \Doctrine\ORM\Mapping\Driver\XmlDriver($xmldefs);
$config->setMetadataDriverImpl($driver);
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());

$config->setProxyDir(__DIR__ . '/Proxies');
$config->setProxyNamespace('Proxies');

$connectionOptions = array(
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => 'erp_test',
    'user' => 'ianfp',
    'password' => '',
);

$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));
