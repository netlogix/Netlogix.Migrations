<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Entity
 * @ORM\Table(name="netlogix_migrationstatus")
 */
class MigrationStatus
{
    /**
     * @ORM\Id
     * @Flow\Identity
     * @var string
     */
    protected $version;

    public function __construct(string $version)
    {
        $this->version = $version;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
