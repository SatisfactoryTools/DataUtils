<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers;

use SFTools\Data\Schema\DocsSchema;
use Symfony\Component\Console\Output\OutputInterface;

class CompatibilityRemovalTransformer extends BaseTransformer
{

	public const SchematicClass = 'Schematic_SaveCompatibility_C';

	public function transform(DocsSchema $schema, OutputInterface $output): void
	{
		if (isset($schema->schematics[self::SchematicClass])) {
			$output->writeln('Removing compatibility schematic', OutputInterface::VERBOSITY_VERY_VERBOSE);
			$this->removeSchematic($schema, $schema->schematics[self::SchematicClass], $output);
		}

		foreach ($schema->schematics as $schematic) {
			$displayName = $schematic->name;

			if (str_contains($displayName, 'Discontinued') || trim($displayName) === '') {
				$this->removeSchematic($schema, $schematic, $output);
			}
		}
	}

}
