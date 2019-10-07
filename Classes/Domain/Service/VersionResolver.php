<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Service;

class VersionResolver
{
    public function extractVersion(string $migrationClassName): string
    {
        preg_match('#\\Version(\d+)$#', $migrationClassName, $matches);
        return $matches[1];
    }
}
