<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Command;

use Doctrine\Common\Persistence\ObjectManager as DoctrineObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Log\ThrowableStorageInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Netlogix\Migrations\Domain\Service\MigrationExecutor;
use Netlogix\Migrations\Domain\Service\MigrationService;
use Psr\Log\LoggerInterface;
use Neos\Flow\Annotations as Flow;
use RuntimeException;

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

    /**
     * @var DoctrineObjectManager
     */
    private $doctrineObjectManager;

    public function __construct(
        MigrationService $migrationService,
        MigrationExecutor $migrationExecutor,
        ThrowableStorageInterface $throwableStorage,
        LoggerInterface $logger,
        DoctrineObjectManager $doctrineObjectManager
    ) {
        parent::__construct();

        $this->migrationService = $migrationService;
        $this->migrationExecutor = $migrationExecutor;
        $this->throwableStorage = $throwableStorage;
        $this->logger = $logger;
        $this->doctrineObjectManager = $doctrineObjectManager;
    }

    /**
     * Execute all unexecuted migrations
     *
     * @param bool $quiet If set no output will be send
     */
    public function migrateCommand(bool $quiet = false)
    {
        $unexecutedMigrations = $this->migrationService->findUnexecutedMigrations();

        if ($unexecutedMigrations === []) {
            $this->outputLine('No new migrations available');
            $this->sendAndExit(0);
        }

        $this->increaseDatabaseTimeout();

        foreach ($unexecutedMigrations as $version => $migration) {
            try {
                $this->migrationExecutor->execute($migration, 'up', $this->output);
                if (false === $quiet) {
                    $this->outputLine('Executed Migration "' . $version . '".');
                }
           } catch (\Exception $exception) {
                $this->handleException($exception);
            }
        }
    }

    /**
     * Execute a single migration
     *
     * @param string $version The version to migrate
     * @param string $direction Whether to execute the migration up (default) or down
     */
    public function executeCommand(string $version, string $direction = 'up')
    {
        try {
            $this->increaseDatabaseTimeout();
            $migration = $this->migrationService->getMigrationByVersion($version);
            $this->migrationExecutor->execute($migration, $direction, $this->output);
        } catch (\Exception $exception) {
            $this->handleException($exception);
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

    protected function increaseDatabaseTimeout($timeout = 3600): void
    {
        ini_set('default_socket_timeout', (string)$timeout);
        if (!$this->doctrineObjectManager instanceof DoctrineEntityManager) {
            throw new RuntimeException('No Doctrine EntityManager found, cannot increase MySQL timeout');
        }
        $connection = $this->doctrineObjectManager->getConnection();
        if (!$connection || !$connection instanceof Connection) {
            throw new RuntimeException('No Doctrine Connection found, cannot increase MySQL timeout');
        }
        $connection->exec(sprintf('SET SESSION wait_timeout = %d;', $timeout));
    }
}
