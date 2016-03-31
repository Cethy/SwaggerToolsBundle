<?php
/*
 * This file is part of the Cethyworks\SwaggerToolsBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cethyworks\SwaggerToolsBundle\Command;

use Cethyworks\SwaggerToolsBundle\Generator\MergeDocumentGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class MergeSwaggerDocumentCommand extends ContainerAwareCommand
{
    const NAME = 'swagger:merge:documents';

    /**
     * @param MergeDocumentGenerator  $generator
     */
    public function __construct(MergeDocumentGenerator $generator)
    {
        parent::__construct(static::NAME);

        $this->generator = $generator;

        $this
            ->setDescription('Merge every documents listed into one target document.')
            ->addArgument('files', InputArgument::IS_ARRAY, 'swagger documents to merge')
            ->addOption('target', null, InputOption::VALUE_OPTIONAL, 'Name of the target swagger document', 'all.yml')
            ->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'if true, overwrite already existing target file', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->generator->generate($input->getArgument('files'), $input->getOption('target'), $input->getOption('force'));

        $output->writeln(sprintf("<info>Result</info>\n<comment>%s</comment>", $result));
    }
}
