<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Handler;

use Neos\Flow\Cli\ConsoleOutput;
use Netlogix\Migrations\Domain\Model\Migration;

interface MigrationHandler
{
    public function canExecute(Migration $migration): bool;

    public function up(Migration $migration): void;

    public function down(Migration $migration): void;

    public function setConsoleOutput(ConsoleOutput $consoleOutput): void;
}
