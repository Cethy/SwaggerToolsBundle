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
     * @param \stdClass|null  $relationDocument
     * @param array           $definitionsExcluded
     * @param bool            $force
     */
    public function generate(BundleInterface $bundle, SwaggerDocument $document, $relationDocument = null, $definitionsExcluded = array(), $force = false)
    {
        $dir = $bundle->getPath();

        $parameters = [
            'namespace'        => $bundle->getNamespace(),
            'bundle'           => $bundle->getName(),
            'entity_namespace' => str_replace('/', '\\', $this->innerPath)
        ];

        foreach ($document->getDefinition()->definitions as $typeName => $spec) {
            if(in_array($typeName, $definitionsExcluded)) {
                continue;
            }

            $resourceFile = sprintf('%s/%s/%s.php', $dir, $this->innerPath, $typeName);
            if (!$force && file_exists($resourceFile)) {
                throw new \RuntimeException(sprintf('Entity "%s" already exists', $typeName));
            }

            // normalize stdClass $spec into array for twig to be able to use
            $properties = array();
            foreach($spec->properties as $property => $data) {
                $properties[$property] = $data;
            }

            // merge $relationDocument
            if($relationDocument) {
                foreach($relationDocument['entities'] as $entityName => $relations) {
                    if($typeName == $entityName && $relations != null) {
                        foreach($relations as $property => $relation) {
                            if(!is_array($relation)) {
                                $relation = array('type' => $relation);
                            }

                            if(!isset($properties[$property])) {
                                throw new \Exception(sprintf('%s was not found in %s definition, cannot update relation data', $property, $typeName));
                            }

                            $properties[$property]->relation = $relation;
                        }
                    }
                }
            }

            $this->renderFile(
                $this->template,
                $resourceFile,
                array_merge(
                    $parameters, [
                    'properties' => $properties,
                    'entityName' => $typeName
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
