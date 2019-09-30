<?php
declare(strict_types=1);


namespace Netlogix\Migrations\Command;

use Neos\Flow\Cli\CommandController;
use Neos\Flow\Log\ThrowableStorageInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Netlogix\Migrations\Domain\Service\MigrationExecutor;
use Netlogix\Migrations\Domain\Service\MigrationService;
use Psr\Log\LoggerInterface;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class MigrationsCommandController extends CommandController
{
    /**
     * @var MigrationService
     */
    private $migrationService;

    /**
     * @var MigrationExecutor
     */
    private $migrationExecutor;

    /**
     * @var ThrowableStorageInterface
     */
    private $throwableStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MigrationService $migrationService,
        MigrationExecutor $migrationExecutor,
        ThrowableStorageInterface $throwableStorage,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->migrationService = $migrationService;
        $this->migrationExecutor = $migrationExecutor;
        $this->throwableStorage = $throwableStorage;
        $this->logger = $logger;
    }

    public function migrateCommand(bool $quiet = false)
    {
        $unexecutedMigrations = $this->migrationService->findUnexecutedMigrations();

        if ($unexecutedMigrations === []) {
            $this->outputLine('No new migrations available');
            $this->sendAndExit(0);
        }

        foreach ($unexecutedMigrations as $version => $migration) {
            try {
                $this->migrationExecutor->execute($migration);
                if (false === $quiet) {
                    $this->outputLine('Executed Migration "' . $version . '".');
                }
           } catch (\Exception $exception) {
                $this->handleException($exception);
            }
        }
    }

    protected function handleException(\Exception $exception)
    {
        $this->outputLine('<error>%s</error>', [$exception->getMessage()]);
        $this->outputLine();
        $this->outputLine('The exception details have been logged to the Flow system log.');
        $message = $this->throwableStorage->logThrowable($exception);
        $this->outputLine($message);
        $this->logger->error($message, LogEnvironment::fromMethodName(__METHOD__));
        $this->quit(1);
    }
}
