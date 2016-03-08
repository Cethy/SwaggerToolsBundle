<?php
namespace Cethyworks\SwaggerToolsBundle\Generator;

use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class ControllerGenerator extends BaseGenerator
{
    /**
     * @var string
     */
    protected $controllerTemplate;

    /**
     * @var string
     */
    protected $controllerTestTemplate;

    /**
     * @param string $template
     * @param string $innerPath
     */
    public function __construct(
        $controllerTemplate = 'controller/Controller.php.twig',
        $controllerTestTemplate = 'controller/ControllerTest.php.twig')
    {
        $this->controllerTemplate     = $controllerTemplate;
        $this->controllerTestTemplate = $controllerTestTemplate;
    }

    /**
     * @param BundleInterface $bundle
     * @param SwaggerDocument $document
     * @param string          $controllerName
     * @param array           $actions
     * @param string          $specFilePath
     * @param bool            $force
     *
     * @todo handle exclusions
     */
    public function generate(BundleInterface $bundle, SwaggerDocument $document, $controllerName = '', array $actions = array(), $specFilePath = '', $force = false)
    {
        $dir = $bundle->getPath();

        $controllerFile     = sprintf('%s/Controller/%sController.php', $dir, $controllerName);
        $controllerTestFile = sprintf('%s/Tests/Controller/%sControllerTest.php', $dir, $controllerName);
        if (!$force && file_exists($controllerFile)) {
            throw new \RuntimeException(sprintf('Controller "%s" already exists', $controllerName));
        }


        $parameters = array(
            'namespace'    => $bundle->getNamespace(),
            'bundle'       => $bundle->getName(),
            'specFilePath' => $specFilePath,
            'controller'   => $controllerName,
            'actions'      => $actions
        );

        $this->renderFile($this->controllerTemplate, $controllerFile, $parameters);
        $this->renderFile($this->controllerTestTemplate, $controllerTestFile, $parameters);
    }
}
