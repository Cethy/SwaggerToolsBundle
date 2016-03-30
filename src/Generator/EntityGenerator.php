<?php
namespace Cethyworks\SwaggerToolsBundle\Generator;

use Doctrine\Common\Util\Inflector;
use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class EntityGenerator extends BaseGenerator
{
    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $innerPath;

    public function __construct($template = 'doctrine/Entity.php.twig', $innerPath = 'Entity')
    {
        $this->template  = $template;
        $this->innerPath = $innerPath;
    }

    /**
     * @param BundleInterface $bundle
     * @param SwaggerDocument $document
     * @param string|null     $parentClass
     * @param array           $definitionsExcluded
     * @param bool            $force
     */
    public function generate(BundleInterface $bundle, SwaggerDocument $document, $force = false)
    {
        $dir = $bundle->getPath();

        $parameters = [
            'namespace'        => $bundle->getNamespace(),
            'bundle'           => $bundle->getName(),
            'entity_namespace' => str_replace('/', '\\', $this->innerPath)
        ];

        foreach ($document->getDefinition()->definitions as $typeName => $spec) {
            if(isset($spec->{'x-exclude'}) && $spec->{'x-exclude'}) {
                continue;
            }

            $resourceFile = sprintf('%s/%s/%s.php', $dir, $this->innerPath, $typeName);
            if (!$force && file_exists($resourceFile)) {
                throw new \RuntimeException(sprintf('Entity "%s" already exists', $typeName));
            }

            // normalize stdClass $spec into array for twig to be able to use
            $properties = array();
            if(isset($spec->properties)) {
                foreach($spec->properties as $property => $data) {
                    $properties[$property] = $data;

                    // add required
                    if((isset($spec->required) && in_array($property, $spec->required))
                        || (isset($spec->{'x-required'}) && in_array($property, $spec->{'x-required'})))
                    {
                        $properties[$property]->required = true;
                    }
                }
            }

            $this->renderFile(
                $this->template,
                $resourceFile,
                array_merge(
                    $parameters, [
                    'properties'      => $properties,
                    'entityName'      => $typeName,
                    'parentClass'     => isset($spec->{'x-parent'}) ? $spec->{'x-parent'} : null,
                    'repositoryClass' => isset($spec->{'x-repository'}) ? $spec->{'x-repository'} : null
                ])
            );
        }
    }

    protected function getTwigEnvironment()
    {
        $twigEnvironment = parent::getTwigEnvironment();
        $twigEnvironment->addFilter('classify', new \Twig_SimpleFilter('classify', function ($string) {
            return Inflector::classify($string);
        }));

        return $twigEnvironment;
    }
}
