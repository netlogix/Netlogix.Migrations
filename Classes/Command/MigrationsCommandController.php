<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Log\ThrowableStorageInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Netlogix\Migrations\Domain\Service\MigrationExecutor;
use Netlogix\Migrations\Domain\Service\MigrationService;
use Psr\Log\LoggerInterface;
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
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        MigrationService $migrationService,
        MigrationExecutor $migrationExecutor,
        ThrowableStorageInterface $throwableStorage,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();

        $this->migrationService = $migrationService;
        $this->migrationExecutor = $migrationExecutor;
        $this->throwableStorage = $throwableStorage;
        $this->logger = $logger;
        $this->entityManager = $entityManager;

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
        throw $exception;
    }

    protected function increaseDatabaseTimeout($timeout = 3600): void
    {
        ini_set('default_socket_timeout', (string)$timeout);
        if (!$this->entityManager instanceof EntityManagerInterface) {
            throw new RuntimeException('No Doctrine EntityManager found, cannot increase MySQL timeout');
        }
        $connection = $this->entityManager->getConnection();
        if (!$connection || !$connection instanceof Connection) {
            throw new RuntimeException('No Doctrine Connection found, cannot increase MySQL timeout');
        }
        $connection->exec(sprintf('SET SESSION wait_timeout = %d;', $timeout));
    }
}
