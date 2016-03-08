<?php
namespace Cethyworks\SwaggerToolsBundle\Command;

use Cethyworks\SwaggerToolsBundle\Generator\EntityGenerator;
use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class GenerateDoctrineEntityCommand extends BaseCommand
{
    const NAME = 'swagger:generate:entities';

    /**
     * @param DocumentRepository $documentRepository
     * @param EntityGenerator  $generator
     */
    public function __construct(DocumentRepository $documentRepository, EntityGenerator $generator)
    {
        parent::__construct($documentRepository, $generator);

        $this
            ->setDescription('Auto-generate doctrine entities based on Swagger file resource definitions.')
            ->addArgument('file', InputArgument::REQUIRED, 'File path to the Swagger document')
            ->addArgument('bundle', InputArgument::REQUIRED, 'Name of the bundle you want the classes in')
            ->addOption(
                'exclude',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Does not generate theses definitions',
                array()
            )
            ->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'if true, overwrite already existing entities', false)
        ;
    }


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $bundle = $kernel->getBundle($input->getArgument('bundle'));
        $document = $this->documentRepository->get($input->getArgument('file'));
        $this->generator->setSkeletonDirs(__DIR__ . '/../Resources/skeleton');
        $this->generator->generate($bundle, $document, $input->getOption('exclude'), $input->getOption('force'));
    }
}
