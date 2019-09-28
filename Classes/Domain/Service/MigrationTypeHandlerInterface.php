<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Service;

use Netlogix\Migrations\Domain\Model\MigrationTypeInterface;

interface MigrationTypeHandlerInterface
{
    public function canExecute(MigrationTypeInterface $migrationType): bool;
    
    public function execute(MigrationTypeInterface $migrationType): void;
}
