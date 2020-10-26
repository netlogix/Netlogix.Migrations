<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Model;

use Neos\Flow\Cli\ConsoleOutput;

interface OutputAware
{
    public function setConsoleOutput(ConsoleOutput $consoleOutput): void;
}
