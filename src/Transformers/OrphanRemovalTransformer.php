<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers;

use SFTools\Data\Schema\DocsSchema;
use Symfony\Component\Console\Output\OutputInterface;

class OrphanRemovalTransformer implements Transformer
{

	public function transform(DocsSchema $schema, OutputInterface $output): void
	{
		// filter recipes with invalid product
		foreach ($schema->recipes as $recipe) {
			foreach ($recipe->products as $product) {
				if (!isset($schema->items[$product->item]) && !isset($schema->buildings[$product->item])) {
					$output->writeln('Removing recipe ' . $recipe->name . ' [' . $recipe->className . '] because it makes non-existing item/building ' . $product->item);
					unset($schema->recipes[$recipe->className]);
					continue 2;
				}
			}
		}

		// filter buildables with no recipe
		foreach ($schema->buildings as $building) {
			$found = false;
			foreach ($schema->recipes as $recipe) {
				if ($recipe->inBuildGun) {
					foreach ($recipe->products as $product) {
						if ($product->item === $building->className) {
							$found = true;
							break 2;
						}
					}
				}
			}

			if (!$found) {
				$output->writeln('Removing building ' . $building->name . ' [' . $building->className . '] because it does not have a recipe to be built');
				unset($schema->buildings[$building->className]);
			}
		}
	}

}
