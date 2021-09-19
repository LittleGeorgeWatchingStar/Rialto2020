<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

require_once __DIR__.'/globals.php';
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

require_once __DIR__.'/polyfills.php';

return $loader;

