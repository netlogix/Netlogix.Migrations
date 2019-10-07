<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Handler;

use Neos\Flow\Cli\ConsoleOutput;
use Netlogix\Migrations\Domain\Model\DefaultMigration;
use Netlogix\Migrations\Domain\Model\Migration;

class DefaultMigrationHandler implements MigrationHandler
{

    /**
     * @var ConsoleOutput
     */
    protected $output;

    public function canExecute(Migration $migration): bool
    {
        return $migration instanceof DefaultMigration;
    }

    public function up(Migration $migration): void
    {
        $migration->up();
    }

    public function down(Migration $migration): void
    {
        $migration->down();
    }

    public function setConsoleOutput(?ConsoleOutput $consoleOutput = null): void
    {
        $this->output = $consoleOutput;
    }

    protected function outputLine(string $text, array $arguments = []): void
    {
        if (!$this->output) {
            return;
        }

        $this->output->outputLine($text, $arguments);
    }

}
