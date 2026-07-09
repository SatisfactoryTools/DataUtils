<?php declare(strict_types = 1);

namespace SFTools\Data\Export;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\Fuel;
use SFTools\Data\Schema\Parts\SchematicType;
use SFTools\Data\Schema\Schematic;
use SFTools\Data\Utils\SchematicTree\Renderers\TreeRenderer;
use SFTools\Data\Utils\SchematicTree\Renderers\WikiRenderer;
use SFTools\Data\Utils\SchematicTree\SchematicTreeFactory;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;

class WikiExporter implements Exporter
{

	private SchematicTreeFactory $factory;
	private TreeRenderer $renderer;

	/** @return array<string, string> */
	public function export(DocsSchema $schema, bool $experimental = false): array
	{
		$this->factory = new SchematicTreeFactory($schema);
		$this->renderer = new WikiRenderer($schema);

		return [
			'items' => $this->exportItems($schema, $experimental),
			'recipes' => $this->exportRecipes($schema, $experimental),
			'buildings' => $this->exportBuildings($schema, $experimental),
		];
	}

	private function exportItems(DocsSchema $schema, bool $experimental): string
	{
		$items = [];

		foreach ($schema->items as $item) {
			$items[$item->className] = [[
				'className' => $item->className,
				'name' => $item->name,
				'description' => $this->normalizeText($item->description),
				'stackSize' => $item->stackSize,
				'energy' => $item->energy,
				'radioactive' => $item->radioactiveDecay,
				'canBeDiscarded' => $item->canBeTrashed,
				'sinkPoints' => $item->sinkPoints,
				'abbreviation' => $item->abbr,
				'form' => $item->form->value,
				'fluidColor' => $item->fluidColor->toHex(),
				'alienItem' => $item->isAlien,
				'stable' => !$experimental,
				'experimental' => $experimental,
			]];
		}

		return Json::encode($items, pretty: true);
	}

	private function exportRecipes(DocsSchema $schema, bool $experimental): string
	{
		$recipes = [];

		foreach ($schema->recipes as $recipe) {
			$recipes[$recipe->className] = [[
				'className' => $recipe->className,
				'name' => trim(str_replace('Alternate:', '', $recipe->name)),
				'unlockedBy' => $this->getUnlockedByString($schema, $recipe->className),
				'duration' => $recipe->time,
				'ingredients' => array_map(static function ($ingredient) {
					return [
						'item' => $ingredient->item,
						'amount' => $ingredient->amount,
					];
				}, $recipe->ingredients),
				'products' => array_map(static function ($product) {
					return [
						'item' => $product->item,
						'amount' => $product->amount,
					];
				}, $recipe->products),
				'producedIn' => $recipe->producedIn,
				'inCraftBench' => $recipe->inCraftBench,
				'inWorkshop' => $recipe->inEquipmentWorkshop,
				'inBuildGun' => $recipe->inBuildGun,
				'inCustomizer' => false,
				'manualCraftingMultiplier' => $recipe->manualCraftingMultiplier,
				'alternate' => $recipe->alternate,
				'minPower' => $recipe->variablePowerDraw ? $recipe->variablePowerDrawConstant : null,
				'maxPower' => $recipe->variablePowerDraw ? $recipe->variablePowerDrawConstant + $recipe->variablePowerDrawFactor : null,
				'seasons' => $recipe->events,
				'stable' => !$experimental,
				'experimental' => $experimental,
			]];
		}

		foreach ($schema->materials as $material) {
			$recipes[$material->className] = [[
				'className' => $material->className,
				'name' => 'N/A',
				'unlockedBy' => $this->getUnlockedByString($schema, $material->className),
				'duration' => 0,
				'ingredients' => array_map(static function ($ingredient) {
					return [
						'item' => $ingredient->item,
						'amount' => $ingredient->amount,
					];
				}, $material->ingredients),
				'products' => [],
				'producedIn' => [],
				'inCraftBench' => false,
				'inWorkshop' => false,
				'inBuildGun' => false,
				'inCustomizer' => true,
				'manualCraftingMultiplier' => 0,
				'alternate' => false,
				'minPower' => null,
				'maxPower' => null,
				'seasons' => [],
				'stable' => !$experimental,
				'experimental' => $experimental,
			]];
		}

		return Json::encode($recipes, pretty: true);
	}

	private function exportBuildings(DocsSchema $schema, bool $experimental): string
	{
		$buildings = [];

		foreach ($schema->buildings as $building) {
			if (count($building->materials)) {
				continue;
			}

			$buildings[$building->className] = [[
				'className' => $building->className,
				'name' => $building->name,
				'description' => $this->normalizeText($building->description),
				'unlockedBy' => $this->getUnlockedByString($schema, $building->className),
				'powerUsage' => $building->isVehicle ? 0 : $building->powerUsage,
				'powerGenerated' => $building->isVehicle ? $building->powerUsage : $building->powerProduction,
				'supplementPerMinute' => $building->supplementalToPowerRatio * $building->powerProduction * 60 / 1000, // TODO
				'burnsFuel' => array_values(array_map(static function (Fuel|string $item) {
					return ($item instanceof Fuel) ? [
						'fuel' => $item->item,
						'supplement' => $item->supplementalItem,
						'byproduct' => $item->byproduct,
						'byproductAmount' => $item->byproductAmount,
					] : [
						'fuel' => $item,
						'supplement' => null,
						'byproduct' => null,
						'byproductAmount' => null,
					];
				}, count($building->acceptedFuel) ? $building->acceptedFuel : $building->fuel)),
				'usesMaterials' => (bool)count($building->materials),
				'overclockable' => $building->canOverclock,
				'somersloopSlots' => $building->canSloop ? $building->sloopSlots : 0,
				'isVehicle' => $building->isVehicle,
				'stable' => !$experimental,
				'experimental' => $experimental,
			]];
		}

		return Json::encode($buildings, pretty: true);
	}

	private function getUnlockedByString(DocsSchema $schema, string $className): string
	{
		$schematic = $this->getSchematicThatUnlocks($schema, $className);

		if (!$schematic) {
			return '';
		}

		$node = is_array($schematic) ? $this->factory->createForMultiple(...$schematic) : $this->factory->createFor($schematic);
		return $this->renderer->renderNode($node);
	}

	/** @return Schematic|Schematic[]|null */
	public function getSchematicThatUnlocks(DocsSchema $schema, string $className): Schematic|array|null
	{
		if (in_array($className, $schema->resources, true)) {
			return null;
		}

		if (isset($schema->items[$className])) {
			$schematics = [];
			foreach ($schema->recipes as $recipe) {
				if (!$recipe->alternate && array_any($recipe->products, static fn($product) => $product->item === $className)) {
					foreach ($schema->schematics as $s) {
						if (in_array($recipe->className, $s->unlock->recipes, true)) {
							$schematics[] = $s;
						}
					}
				}
			}

			if (count($schematics)) {
				return $schematics;
			}

			foreach ($schema->recipes as $recipe) {
				if (array_any($recipe->products, static fn($product) => $product->item === $className)) {
					foreach ($schema->schematics as $s) {
						if (in_array($recipe->className, $s->unlock->recipes, true)) {
							$schematics[] = $s;
						}
					}
				}
			}

			if (count($schematics)) {
				return $schematics;
			}
		}

		if (isset($schema->buildings[$className])) {
			foreach ($schema->recipes as $recipe) {
				if (array_any($recipe->products, static fn($product) => $product->item === $className)) {
					foreach ($schema->schematics as $s) {
						if (in_array($recipe->className, $s->unlock->recipes, true)) {
							return $s;
						}
					}
				}
			}
		}

		$schematics = [];
		foreach ($schema->schematics as $schematic) {
			if (in_array($className, $schematic->unlock->schematics, true)) {
				$schematics[] = $schematic;
			}

			if (in_array($className, $schematic->unlock->recipes, true)) {
				$schematics[] = $schematic;
			}

			if (array_any($schematic->unlock->items, static fn($item) => $item->item === $className)) {
				$schematics[] = $schematic;
			}
		}

		return count($schematics) ? $schematics : null;
	}

	private function normalizeText(string $text): string
	{
		return Strings::replace(str_replace(["\r\n", "\n"], '<br>', $text), '/(?:<br>){2,}/', '<br>');
	}

}
