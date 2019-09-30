<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Model;

interface Migration
{
    public function up(): void;

    public function down(): void;
}
