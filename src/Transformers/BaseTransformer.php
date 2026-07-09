<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers;

use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Recipe;
use SFTools\Data\Schema\Schematic;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseTransformer implements Transformer
{

	protected function removeSchematic(DocsSchema $schema, Schematic $schematic, OutputInterface $output): void
	{
		foreach ($schematic->unlock->schematics as $s) {
			if (isset($schema->schematics[$s])) {
				$output->writeln('Removing schematic unlocked by ' . $schematic->name . ' [' . $schematic->className . ']: ' . $schema->schematics[$s]->name . '[' . $schema->schematics[$s]->className . ']', OutputInterface::VERBOSITY_VERY_VERBOSE);
				$this->removeSchematic($schema, $schema->schematics[$s], $output);
			}
		}

		foreach ($schematic->unlock->recipes as $recipeClass) {
			if (isset($schema->recipes[$recipeClass])) {
				$recipe = $schema->recipes[$recipeClass];

				foreach ($schema->schematics as $s) {
					if ($s === $schematic) {
						continue;
					}
					if (in_array($recipeClass, $s->unlock->recipes, true)) {
						continue 2;
					}
				}

				$output->writeln('Removing recipe unlocked by ' . $schematic->name . ' [' . $schematic->className . ']: ' . $recipe->name . ' [' . $recipe->className . ']', OutputInterface::VERBOSITY_VERY_VERBOSE);
				$this->removeRecipe($schema, $recipe, $output);
			}
		}

		unset($schema->schematics[$schematic->className]);
	}

	protected function removeRecipe(DocsSchema $schema, Recipe $recipe, OutputInterface $output): void
	{
		unset($schema->recipes[$recipe->className]);

		if (count($recipe->products) === 1) {
			$productClass = $recipe->products[0]->item;

			$found = false;
			foreach ($schema->recipes as $r) {
				if ($r->className === $recipe->className) {
					continue;
				}
				foreach ($r->products as $test) {
					if ($test->item === $productClass) {
						$found = true;
						break 2;
					}
				}
			}

			if (!$found) {
				if (isset($schema->items[$productClass])) {
					$output->writeln('Removing item produced only by recipe ' . $recipe->name . ' [' . $recipe->className . ']: ' . $schema->items[$productClass]->name . ' [' . $productClass . ']', OutputInterface::VERBOSITY_VERY_VERBOSE);
					unset($schema->items[$productClass]);
				} elseif (isset($schema->buildings[$productClass])) {
					$output->writeln('Removing building built only by recipe ' . $recipe->name . ' [' . $recipe->className . ']: ' . $schema->buildings[$productClass]->name . ' [' . $productClass . ']', OutputInterface::VERBOSITY_VERY_VERBOSE);
					unset($schema->buildings[$productClass]);
				}
			}
		}
	}

}
