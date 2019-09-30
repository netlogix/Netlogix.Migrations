<?php
declare(strict_types=1);


namespace Netlogix\Migrations\Domain\Service;

use Neos\Flow\Reflection\ReflectionService;
use Netlogix\Migrations\Domain\Handler\MigrationHandler;
use Netlogix\Migrations\Domain\Model\Migration;
use Netlogix\Migrations\Error\MissingMigrationHandler;

final class MigrationExecutor
{
    /**
     * @var ReflectionService
     */
    private $reflectionService;

    /**
     * @var VersionLogger
     */
    private $versionLogger;

    public function __construct(
        ReflectionService $reflectionService,
        VersionLogger $versionLogger
    ) {
        $this->reflectionService = $reflectionService;
        $this->versionLogger = $versionLogger;
    }

    public function execute(Migration $migration, $direction = 'up')
    {
        /** @var MigrationHandler $handler */
        foreach ($this->reflectionService->getAllImplementationClassNamesForInterface(MigrationHandler::class) as $handler) {
            if ($handler->canExecute($migration)) {
                $result = $handler->{$direction}($migration);
                $this->versionLogger->logMigration($migration, $direction);
                return $result;
            }
        }
        throw new MissingMigrationHandler('No Migration Handler found for "'.get_class($migration)."'");
    }
}
