<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers;

use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\ItemForm;
use Symfony\Component\Console\Output\OutputInterface;

class FluidAmountTransformer implements Transformer
{

	public const FluidMultiplier = 1000;

	public function transform(DocsSchema $schema, OutputInterface $output): void
	{
		$isFluid = static function ($className) use ($schema) {
			return isset($schema->items[$className]) && $schema->items[$className]->form->isFluid();
		};

		$handleItemAmounts = static function (array $itemAmounts) use ($isFluid) {
			foreach ($itemAmounts as $itemAmount) {
				if ($isFluid($itemAmount->item)) {
					$itemAmount->amount /= self::FluidMultiplier;
				}
			}
		};

		foreach ($schema->recipes as $recipe) {
			$handleItemAmounts($recipe->ingredients);
			$handleItemAmounts($recipe->products);
		}

		foreach ($schema->schematics as $schematic) {
			$handleItemAmounts($schematic->cost);
			$handleItemAmounts($schematic->unlock->items);
		}

		foreach ($schema->items as $item) {
			if ($isFluid($item->className)) {
				$item->sinkPoints *= self::FluidMultiplier;
				$item->energy *= self::FluidMultiplier;
				$item->radioactiveDecay *= self::FluidMultiplier;
			}
		}

		foreach ($schema->buildings as $building) {
			foreach ($building->fuel as $fuel) {
				if ($fuel->byproduct && $isFluid($fuel->byproduct)) {
					$fuel->byproductAmount /= self::FluidMultiplier;
				}
			}
		}
	}

}
