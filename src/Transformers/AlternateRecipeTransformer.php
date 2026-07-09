<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers;

use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\SchematicType;
use SFTools\Data\Schema\Schematic;
use Symfony\Component\Console\Output\OutputInterface;

class AlternateRecipeTransformer implements Transformer
{

	public function transform(DocsSchema $schema, OutputInterface $output): void
	{
		foreach ($schema->schematics as $schematic) {
			if ($schematic->type === SchematicType::Alternate) {
				$this->processSchematic($schema, $schematic, $output);
			}
		}
	}

	private function processSchematic(DocsSchema $schema, Schematic $schematic, OutputInterface $output, bool $ignoreType = false): void
	{
		foreach ($schematic->unlock->recipes as $recipeClass) {
			if (isset($schema->recipes[$recipeClass])) {
				foreach ($schema->schematics as $s) {
					if ((!$ignoreType && ($s->type !== SchematicType::Alternate)) && in_array($recipeClass, $s->unlock->recipes, true)) {
						break 2;
					}
				}

				$output->writeln('Marking recipe ' . $recipeClass . ' as alternate', OutputInterface::VERBOSITY_VERY_VERBOSE);
				$schema->recipes[$recipeClass]->alternate = true;
			}
		}

		foreach ($schematic->unlock->schematics as $schematicClass) {
			if (isset($schema->schematics[$schematicClass])) {
				$this->processSchematic($schema, $schema->schematics[$schematicClass], $output, true);
			}
		}
	}

}
