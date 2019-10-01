<?php
declare(strict_types=1);


namespace Netlogix\Migrations\Domain\Service;

use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Reflection\ReflectionService;
use Netlogix\Migrations\Domain\Handler\MigrationHandler;
use Netlogix\Migrations\Domain\Model\Migration;
use Netlogix\Migrations\Error\MissingMigrationHandler;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class MigrationExecutor
{
    /**
     * @var ReflectionService
     */
    private $reflectionService;

    /**
     * var ObjectManager
     */
    private $objectManager;

    /**
     * @var VersionLogger
     */
    private $versionLogger;

    public function __construct(
        ReflectionService $reflectionService,
        ObjectManager $objectManager,
        VersionLogger $versionLogger
    ) {
        $this->reflectionService = $reflectionService;
        $this->objectManager = $objectManager;
        $this->versionLogger = $versionLogger;
    }

    public function execute(Migration $migration, $direction = 'up')
    {
        foreach ($this->reflectionService->getAllImplementationClassNamesForInterface(MigrationHandler::class) as $handlerClassName) {

            /** @var MigrationHandler $handler */
            $handler = $this->objectManager->get($handlerClassName);

            if ($handler->canExecute($migration)) {
                $result = $handler->{$direction}($migration);
                $this->versionLogger->logMigration($migration, $direction);
                return $result;
            }
        }
        throw new MissingMigrationHandler('No Migration Handler found for "'.get_class($migration)."'");
    }
}
