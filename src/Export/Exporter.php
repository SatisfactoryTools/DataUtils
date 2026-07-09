<?php declare(strict_types = 1);

namespace SFTools\Data\Export;

use SFTools\Data\Schema\DocsSchema;

interface Exporter
{

	/** @return array<string, string> returns array of file_prefix => content. Can leave prefix empty */
	public function export(DocsSchema $schema, bool $experimental = false): array;

}
