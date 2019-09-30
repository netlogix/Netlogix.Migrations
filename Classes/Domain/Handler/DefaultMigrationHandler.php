<?php
declare(strict_types=1);


namespace Netlogix\Migrations\Domain\Handler;

use Netlogix\Migrations\Domain\Model\DefaultMigration;
use Netlogix\Migrations\Domain\Model\Migration;

class DefaultMigrationHandler implements MigrationHandler
{
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
}
