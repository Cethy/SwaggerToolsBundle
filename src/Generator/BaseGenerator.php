<?php
namespace Cethyworks\SwaggerToolsBundle\Generator;

use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

abstract class BaseGenerator extends Generator
{
    abstract public function generate(BundleInterface $bundle, SwaggerDocument $document);
}