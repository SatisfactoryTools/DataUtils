<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers\Enrichment;

use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\SchematicType;
use SFTools\Data\Transformers\Transformer;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicNameTransformer implements Transformer
{

	public function transform(DocsSchema $schema, OutputInterface $output): void
	{
		foreach ($schema->schematics as $schematic) {
			if ($schematic->type === SchematicType::AwesomeSink && count($schematic->unlock->items)) {
				$schematic->name .= ' (purchase)';
			}
		}
	}

}
