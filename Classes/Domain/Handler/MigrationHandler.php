<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Handler;

use Netlogix\Migrations\Domain\Model\Migration;
use Netlogix\Migrations\Domain\Model\MigrationInterface;

interface MigrationHandler
{
    public function canExecute(Migration $migration): bool;

    public function up(Migration $migration): void;

    public function down(Migration $migration): void;
}
