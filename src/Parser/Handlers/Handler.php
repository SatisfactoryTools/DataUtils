<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Schema\DocsSchema;

interface Handler
{

	public function canHandle(ClassData $data): bool;

	public function handle(DocsSchema $schema, ClassData $data): void;

}
