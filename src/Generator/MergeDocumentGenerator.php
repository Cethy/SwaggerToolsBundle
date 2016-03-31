<?php
namespace Cethyworks\SwaggerToolsBundle\Generator;

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class MergeDocumentGenerator
{
    protected function getDocumentIntro()
    {
        return [
            'swagger' => '2.0',
            'info' => [
                'title' => 'Syment API',
                'version' => '0.1.0',
            ],
            'host' => 'api.syment.com',
            'schemes' => [
                'https'
            ],
            'basePath' => '/v1',
            'produces' => [
                'application/json'
            ]
        ];
    }

    /**
     * @var string
     */
    protected $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    protected function merge(array $docs)
    {
        $merged = [
            'securityDefinitions'   => [],
            'security'              => [],
            'parameters'            => [],
            'paths'                 => [],
            'definitions'           => []
        ];

        foreach($docs as $doc) {
            // securityDefinitions
            if(isset($doc['securityDefinitions'])) {
                foreach($doc['securityDefinitions'] as $keySecDef => $securityDefinition) {
                    if(! isset($merged['securityDefinitions'][$keySecDef])) {
                        $merged['securityDefinitions'][$keySecDef] = $securityDefinition;
                    }
                    else {
                        $merged['securityDefinitions'][$keySecDef]['scopes'] = array_merge(
                            $merged['securityDefinitions'][$keySecDef]['scopes'],
                            $securityDefinition['scopes']
                        );
                    }
                }
            }

            // security
            if(isset($doc['security'])) {
                //var_dump($doc['security']);die;
                foreach($doc['security'] as $keySec => $security) {
                    if(! isset($merged['security'])) {
                        $merged['security'][$keySec] = $security;
                    }
                    else {
                        foreach ($security as $keySecLvl1 => $securityLvl1) {
                            if(! isset($merged['security'][$keySec][$keySecLvl1])) {
                                $merged['security'][$keySec][$keySecLvl1] = $securityLvl1;
                            }
                            else {
                                $merged['security'][$keySec][$keySecLvl1] = array_merge(
                                    $merged['security'][$keySec][$keySecLvl1],
                                    $securityLvl1
                                );
                            }
                        }
                    }
                }
            }

            // parameters
            if(isset($doc['parameters'])) {
                $merged['parameters'] = array_merge(
                    $merged['parameters'],
                    $doc['parameters']
                );
            }

            // paths
            if(isset($doc['paths'])) {
                $merged['paths'] = array_merge(
                    $merged['paths'],
                    $doc['paths']
                );
            }

            // definitions
            if(isset($doc['definitions'])) {
                $merged['definitions'] = array_merge(
                    $merged['definitions'],
                    $doc['definitions']
                );
            }
        }

        return $merged;
    }

    public function generate(array $files, $targetFileName, $force = false)
    {
        $parser = new Parser();
        $dumper = new Dumper();


        $targetPath = "$this->basePath/$targetFileName";

        if(is_file($targetPath) && !$force) {
            return 'file already exists. use --force=true to overrides';
        }

        $all = [];
        foreach($files as $file) {
            $all[] = $parser->parse(file_get_contents("$this->basePath/$file"));
        }

        $newDoc = array_merge(
            $this->getDocumentIntro(),
            $this->merge($all)
        );

        file_put_contents($targetPath, $dumper->dump($newDoc, 999));

        return 'done';
    }


}