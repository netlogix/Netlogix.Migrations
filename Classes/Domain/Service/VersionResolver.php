<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Service;

use Netlogix\Migrations\Domain\Model\Migration;

class VersionResolver
{
    public function extractVersion(Migration $migration): string
    {
        $className = get_class($migration);
        preg_match('#\\Version(\d+)$#', $className, $matches);
        return $matches[1];
    }
}
