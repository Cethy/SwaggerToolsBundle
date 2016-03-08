<?php
/**
 * @todo auto-insert into security.yml
 */
namespace Cethyworks\SwaggerToolsBundle\Generator;

use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class AccessControlGenerator extends BaseGenerator
{
    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $innerPath;

    public function __construct($template = 'control-access.yml.twig', $innerPath = 'Resources/config')
    {
        $this->template  = $template;
        $this->innerPath = $innerPath;
    }

    /**
     * @param BundleInterface $bundle
     * @param SwaggerDocument $document
     * @param string          $fileName
     * @param array           $definitionsExcluded
     * @param bool $force
     *
     * @return string         The part to add to app/config/security.yml
     *
     * @todo handle exclusions
     */
    public function generate(BundleInterface $bundle, SwaggerDocument $document, $fileName = 'control-access'/*, $pathsExcluded = array()*/, $force = false)
    {
        $pathPrefix = $document->getDefinition()->basePath;

        $accessControlFile = sprintf('%s/%s/%s.yml', $bundle->getPath(), $this->innerPath, $fileName);
        if (!$force && file_exists($accessControlFile)) {
            throw new \RuntimeException(sprintf('Access Control Configuration file already exists'));
        }

        $accessControls = array();
        foreach ($document->getDefinition()->paths as $path => $spec) {
            /*if(in_array($path, $pathsExcluded)) {
                continue;
            }*/

            foreach($spec as $method => $subSpec) {
                if(isset($subSpec->security)) {
                    foreach($subSpec->security as $securitySpecs) {
                        foreach($securitySpecs as $securityProtocol => $roles) {
                            $accessControls[] = [
                                'path'   => sprintf('^%s%s$', $pathPrefix, preg_replace('/\{[^}]+\}/', '[^/]+', $path)),
                                'method' => $method,
                                'roles'  => $roles
                            ];
                        }
                    }
                }
            }
        }

        $this->renderFile(
            $this->template,
            $accessControlFile,
            ['accessControls' => $accessControls]
        );

        return '
imports:
    - { resource: "@'. sprintf('%s/%s/%s.yml', $bundle->getName(), $this->innerPath, $fileName). '" }
';
    }
}
