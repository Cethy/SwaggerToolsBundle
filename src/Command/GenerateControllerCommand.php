<?php
namespace Cethyworks\SwaggerToolsBundle\Command;

use Doctrine\Common\Util\Inflector;
use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Cethyworks\SwaggerToolsBundle\Generator\ControllerGenerator;
use Cethyworks\SwaggerToolsBundle\Manipulator\ServiceConfigurationManipulator;
use Symfony\Component\Routing\Router;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class GenerateControllerCommand extends BaseCommand
{
    const NAME = 'swagger:generate:controllers';

    /**
     * @var string
     */
    protected $documentBasePath;

    /**
     * @param DocumentRepository  $documentRepository
     * @param ControllerGenerator $generator
     * @param string              $documentBasePath
     */
    public function __construct(DocumentRepository $documentRepository, ControllerGenerator $generator, $documentBasePath)
    {
        parent::__construct($documentRepository, $generator);

        $this
            ->setDescription('Auto-generate Controller & ControllerTest classes based on the Swagger file paths definitions and register them as services.')
            ->addArgument('file', InputArgument::REQUIRED, 'File path to the Swagger document')
            ->addArgument('bundle', InputArgument::REQUIRED, 'Name of the bundle you want the classes in')
            ->addArgument('prefix', InputArgument::OPTIONAL, 'Name of the bundle you want the classes in', 'swagger')
            /*->addOption(
                'exclude',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Does not generate theses definitions',
                array()
            )*/
            ->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'if true, overwrite already existing entities', false)
        ;

        $this->documentBasePath = $documentBasePath;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var KernelInterface $kernel */
        $kernel       = $this->getContainer()->get('kernel');
        $bundle       = $kernel->getBundle($input->getArgument('bundle'));

        /** @var Router $router */
        $router       = $this->getContainer()->get('router');

        $documentName = substr($input->getArgument('file'), 0, -4);
        $prefix       = $input->getArgument('prefix');


        $controllers = array();

        // Extraction

        $allRoutes = $router->getRouteCollection()->all();
        /** @var $route \Symfony\Component\Routing\Route */
        foreach($allRoutes as $routeName => $route)
        {
            // ^{prefix}.{documentName}.{resource}.*.{action}$
            $routeNameParts = explode('.', $routeName);

            if(sizeof($routeNameParts) < 4
                || $routeNameParts[0] != $prefix
                || $routeNameParts[1] != $documentName
            ) {
                continue;
            }

            if(! $route->getDefault('_controller')) {
                throw new \Exception(sprintf('"%s" route does not have a controller set.'));
            }

            list($controllerServiceName, $actionName) = explode(':', $route->getDefault('_controller'));

            $controllers[$controllerServiceName][$actionName]['route'] = $route->getPath();
            $controllers[$controllerServiceName][$actionName]['parameters'][] = $route->compile()->getVariables();
        }

        foreach($controllers as $controllerServiceName => $actions) {
            foreach($actions as $action => $actionData) {
                $controllers[$controllerServiceName][$action] = [
                    'name'         => $action,
                    'route'        => $actionData['route'],
                    'placeholders' => $this->untangleActionParameters($actionData['parameters'])
                ];
            }
        }

        // Generation
        $this->generator->setSkeletonDirs(__DIR__ . '/../Resources/skeleton');
        $serviceFilePath = sprintf('%s/Resources/config/%s', $bundle->getPath(), 'services.yml');

        foreach($controllers as $controllerServiceName => $actions) {
            list( , , $controllerName) = explode('.', $controllerServiceName);
            $controllerName = Inflector::classify($controllerName);

            $this->generator->generate(
                $bundle,
                $this->documentRepository->get($input->getArgument('file')),
                $controllerName,
                $actions,
                sprintf('%s/%s', $this->documentBasePath, $input->getArgument('file')),
                $input->getOption('force')
            );

            // amend {bundle}/Resources/config/services.yml
            $this->declareServiceController(
                $serviceFilePath,
                $controllerServiceName,
                sprintf('%s\Controller\%sController', $bundle->getNamespace(), $controllerName)
            );
        }
    }

    protected function declareServiceController($serviceFilePath, $serviceName, $controllerClassName)
    {
        $manipulator = new ServiceConfigurationManipulator($serviceFilePath);
        try {
            $manipulator->addDefinition($serviceName, $controllerClassName);

        } catch (\RuntimeException $e) {
            return array(
                '- Import the service\'s %s definition in the bundle service file:',
                '',
                $manipulator->getServiceCode($serviceName, $controllerClassName),
                '',
            );
        }
    }

    /**
     * Since several routes can link to the same action, with a variable number of parameters,
     * Cleaning is needed to have a better template generation
     *
     * actual : only take the config with the biggest number of parameters
     * future : @todo should read the swagger doc to use serializer & default values
     *
     * @param array $parameterBags
     * @return array
     */
    public function untangleActionParameters(array $parameterBags)
    {
        $finalParameters = array();
        foreach($parameterBags as $possibleParameters) {
            if(sizeof($possibleParameters) > sizeof($finalParameters)) {
                $finalParameters = $possibleParameters;
            }
        }
        return $finalParameters;
    }
}
