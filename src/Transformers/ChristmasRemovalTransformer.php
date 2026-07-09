<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers;

use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\Event;
use Symfony\Component\Console\Output\OutputInterface;

class ChristmasRemovalTransformer extends BaseTransformer
{

	public function transform(DocsSchema $schema, OutputInterface $output): void
	{
		foreach ($schema->schematics as $schematic) {
			foreach ($schematic->events as $event) {
				if ($event === Event::Ficsmas) {
					$output->writeln('Removing FICSMAS-related schematic: ' . $schematic->name . ' [' . $schematic->className . ']', OutputInterface::VERBOSITY_VERY_VERBOSE);
					$this->removeSchematic($schema, $schematic, $output);
					break;
				}
			}
		}

		$className = 'Desc_Gift_C';
		if (isset($schema->items[$className])) {
			$output->writeln('Removing Gift item', OutputInterface::VERBOSITY_VERY_VERBOSE);
			unset($schema->items[$className]);
		}
	}
}
