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
     * @param array           $definitionsExcluded
     * @param bool            $force
     */
    public function generate(BundleInterface $bundle, SwaggerDocument $document, $definitionsExcluded = array(), $force = false)
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
            // to array
            $spec = json_decode(json_encode($spec), true);

            $resourceFile = sprintf('%s/%s/%s.php', $dir, $this->innerPath, $typeName);
            if (!$force && file_exists($resourceFile)) {
                throw new \RuntimeException(sprintf('Entity "%s" already exists', $typeName));
            }

            $this->renderFile(
                $this->template,
                $resourceFile,
                array_merge($parameters, $spec, ['entityName' => $typeName])
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
