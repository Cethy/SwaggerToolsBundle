<?php
namespace Cethyworks\SwaggerToolsBundle\Manipulator;

use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;
use Symfony\Component\Yaml\Yaml;

class ServiceConfigurationManipulator extends Manipulator
{
    protected $file;

    /**
     * Constructor.
     *
     * @param string $file The YAML configuration file path
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Adds a service definition at the top of the existing ones.
     *
     * @param array $definition
     *
     * @throws \RuntimeException If this process fails for any reason
     */
    /**
     * @param $serviceName
     * @param $controllerClassName
     */
    public function addDefinition($serviceName, $controllerClassName)
    {
        // if the services.yml file doesn't exist, don't even try.
        if (!file_exists($this->file)) {
            throw new \RuntimeException(sprintf('The target config file %s does not exist', $this->file));
        }

        $code = $this->getServiceCode($serviceName, $controllerClassName);

        $currentContents = file_get_contents($this->file);
        // Don't add same bundle twice
        if (false !== strpos($currentContents, $code)) {
            throw new \RuntimeException(sprintf(
                'The %s configuration is already imported',
                $serviceName
            ));
        }

        // find the "services" line and add this at the end of that list
        $lastImportedPath = $this->findLastImportedPath($currentContents);
        if (!$lastImportedPath) {
            throw new \RuntimeException(sprintf('Could not find the imports key in %s', $this->file));
        }

        // find imports:
        $importsPosition = strpos($currentContents, 'services:');
        // find the last service
        $lastImportPosition = strpos($currentContents, $lastImportedPath, $importsPosition);
        // find the line break after the last import
        $targetLinebreakPosition = strpos($currentContents, "\n", $lastImportPosition);

        $newContents = substr($currentContents, 0, $targetLinebreakPosition)."\n\n".$code.substr($currentContents, $targetLinebreakPosition);

        if (false === file_put_contents($this->file, $newContents)) {
            throw new \RuntimeException(sprintf('Could not write file %s ', $this->file));
        }
    }

    /**
     * @param string $serviceName
     * @param string $controllerClassName
     * @return string
     */
    public function getServiceCode($serviceName, $controllerClassName)
    {
        return sprintf(<<<EOF
  %s:
    class: %s
EOF
            ,
            $serviceName,
            $controllerClassName
        );
    }

    /**
     * Finds the last imported resource path in the YAML file.
     *
     * @param $yamlContents
     *
     * @return bool|string
     */
    private function findLastImportedPath($yamlContents)
    {
        $data = Yaml::parse($yamlContents);
        if (!isset($data['services'])) {
            return false;
        }
        // find the last imports entry
        $lastImport = end($data['services']);
        return end($lastImport);
    }
}