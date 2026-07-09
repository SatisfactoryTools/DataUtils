<?php declare(strict_types = 1);

namespace SFTools\Data\Export;

use Nette\Utils\Json;
use SFTools\Data\Schema\DocsSchema;

class RawExporter implements Exporter
{

	/** @return array<string, string> */
	public function export(DocsSchema $schema, bool $experimental = false): array
	{
		return ['' => Json::encode($schema, true)];
	}

}
