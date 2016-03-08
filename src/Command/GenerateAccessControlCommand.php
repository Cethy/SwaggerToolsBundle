<?php
/*
 * This file is part of the Cethyworks\SwaggerToolsBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cethyworks\SwaggerToolsBundle\Command;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Cethyworks\SwaggerToolsBundle\Generator\AccessControlGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class GenerateAccessControlCommand extends BaseCommand
{
    const NAME = 'swagger:generate:access-control';

    /**
     * @param DocumentRepository $documentRepository
     * @param AccessControlGenerator  $generator
     */
    public function __construct(DocumentRepository $documentRepository, AccessControlGenerator $generator)
    {
        parent::__construct($documentRepository, $generator);

        $this
            ->setDescription('Auto-generate access-control configuration files based on the Swagger file paths/security definitions and register them.')
            ->addArgument('file', InputArgument::REQUIRED, 'File path to the Swagger document')
            ->addArgument('bundle', InputArgument::REQUIRED, 'Name of the bundle you want the classes in')
            /*->addOption(
                'exclude',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Does not generate theses definitions',
                array()
            )*/
            ->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'if true, overwrite already existing entities', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $bundle = $kernel->getBundle($input->getArgument('bundle'));
        $document = $this->documentRepository->get($input->getArgument('file'));
        $this->generator->setSkeletonDirs(__DIR__ . '/../Resources/skeleton');
        $textToAddToSecurityYml = $this->generator->generate($bundle, $document, 'control-access', $input->getOption('force'));

        $output->writeln(sprintf("<info>Don't forget to update your app/config/security.yml file :</info>\n<comment>%s</comment>", $textToAddToSecurityYml));
    }
}
