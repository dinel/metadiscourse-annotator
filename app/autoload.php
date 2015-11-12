<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

//Loads FOSUserBundle
$loader->add('FOS', __DIR__.'/../vendor/friendsofsymfony/user-bundle/FOS');

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
