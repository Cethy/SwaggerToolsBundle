<?php
namespace Cethyworks\SwaggerToolsBundle\Command;

use Cethyworks\SwaggerToolsBundle\Generator\BaseGenerator;
use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    const NAME = 'swagger:generate:*';

    /**
     * @var BaseGenerator
     */
    protected $generator;

    /**
     * @var DocumentRepository
     */
    protected $documentRepository;

    /**
     * @param DocumentRepository $documentRepository
     * @param BaseGenerator      $generator
     */
    public function __construct(DocumentRepository $documentRepository, BaseGenerator $generator)
    {
        parent::__construct(static::NAME);

        $this->documentRepository = $documentRepository;
        $this->generator          = $generator;
    }
}