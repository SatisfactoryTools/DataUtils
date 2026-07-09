<?php declare(strict_types = 1);

namespace SFTools\Data\Export;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\Color;
use SFTools\Data\Schema\Parts\Fuel;
use SFTools\Data\Schema\Parts\ItemAmount;
use SFTools\Data\Schema\Parts\ItemForm;
use SFTools\Data\Schema\Parts\SchematicType;

class OldToolsExporter implements Exporter
{

	/** @return array<string, string> */
	public function export(DocsSchema $schema, bool $experimental = false): array
	{
		$result = [
			'items' => $this->exportItems($schema),
			'recipes' => $this->exportRecipes($schema),
			'schematics' => $this->exportSchematics($schema),
			'generators' => $this->exportGenerators($schema),
			'resources' => $this->exportResources($schema),
			'miners' => $this->exportMiners($schema),
			'buildings' => $this->exportBuildings($schema),
		];

		return ['' => Json::encode($result, true)];
	}

	/** @return array<mixed> */
	protected function exportItems(DocsSchema $schema): array
	{
		$items = [];

		foreach ($schema->items as $item) {
			$items[$item->className] = [
				'slug' => Strings::webalize($item->name),
				'icon' => Strings::webalize($item->className),
				'name' => $item->name,
				'description' => $item->description,
				'sinkPoints' => $item->sinkPoints,
				'className' => $item->className,
				'stackSize' => $item->stackSize->value,
				'energyValue' => $item->energy,
				'radioactiveDecay' => $item->radioactiveDecay,
				'liquid' => $item->form->isFluid(),
				'fluidColor' => $this->exportColor($item->fluidColor),
			];
		}

		return $items;
	}

	/** @return array<mixed> */
	protected function exportRecipes(DocsSchema $schema): array
	{
		$recipes = [];

		foreach ($schema->recipes as $recipe) {
			$recipes[$recipe->className] = [
				'slug' => Strings::webalize($recipe->className),
				'name' => $recipe->name,
				'className' => $recipe->className,
				'alternate' => $recipe->alternate,
				'time' => $recipe->time,
				'inHand' => $recipe->inCraftBench,
				'forBuilding' => $recipe->inBuildGun,
				'inWorkshop' => $recipe->inEquipmentWorkshop,
				'inMachine' => count($recipe->producedIn) > 0,
				'manualTimeMultiplier' => $recipe->manualCraftingMultiplier,
				'ingredients' => $this->exportItemAmountArray($recipe->ingredients),
				'products' => $this->exportItemAmountArray($recipe->products),
				'producedIn' => $recipe->producedIn,
				'isVariablePower' => $recipe->variablePowerDraw,
				'minPower' => $recipe->variablePowerDrawConstant,
				'maxPower' => $recipe->variablePowerDrawConstant + $recipe->variablePowerDrawFactor,
			];
		}

		if (isset($schema->items['Desc_Gift_C'])) {
			$recipes['Fake_Recipe_Gift_C'] = [
				'slug' => Strings::webalize('Fake_Recipe_Gift_C'),
				'name' => 'FCISMAS Gift',
				'className' => 'Fake_Recipe_Gift_C',
				'alternate' => false,
				'time' => 4,
				'inHand' => false,
				'forBuilding' => false,
				'inWorkshop' => false,
				'inMachine' => true,
				'manualTimeMultiplier' => 0,
				'ingredients' => [],
				'products' => [
					[
						'item' => 'Desc_Gift_C',
						'amount' => 1,
					],
				],
				'producedIn' => ['Desc_TreeGiftProducer_C'],
				'isVariablePower' => false,
				'minPower' => 0,
				'maxPower' => 1,
			];
		}

		return $recipes;
	}

	/** @return array<mixed> */
	protected function exportSchematics(DocsSchema $schema): array
	{
		$schematics = [];

		foreach ($schema->schematics as $schematic) {
			if ($schematic->type === SchematicType::Custom) {
				continue;
			}
			$schematics[$schematic->className] = [
				'className' => $schematic->className,
				'type' => $schematic->type->toOriginal(),
				'name' => $schematic->name,
				'slug' => Strings::webalize($schematic->name),
				'icon' => Strings::webalize($schematic->className),
				'cost' => $this->exportItemAmountArray($schematic->cost),
				'unlock' => [
					'recipes' => $schematic->unlock->recipes,
					'scannerResources' => $schematic->unlock->scannableResources,
					'inventorySlots' => $schematic->unlock->inventorySlots,
					'giveItems' => $this->exportItemAmountArray($schematic->unlock->items)
				],
				'requiredSchematics' => $schematic->dependency->schematics,
				'tier' => $schematic->tier,
				'time' => $schematic->time,
				'mam' => $schematic->type === SchematicType::MAM,
				'alternate' => $schematic->type === SchematicType::Alternate,
			];
		}

		return $schematics;
	}

	/** @return array<mixed> */
	protected function exportGenerators(DocsSchema $schema): array
	{
		$generators = [];

		foreach ($schema->buildings as $building) {
			if ($building->powerProduction > 0) {
				$generators[$building->className] = [
					'className' => $building->className,
					'fuel' => array_map(function (Fuel $fuel) {
						return $fuel->item;
					}, $building->fuel),
					'powerProduction' => $building->powerProduction,
					'powerProductionExponent' => $building->powerUsageExponent,
					'waterToPowerRatio' => $building->supplementalToPowerRatio,
				];
			}
		}

		return $generators;
	}

	/** @return array<mixed> */
	protected function exportResources(DocsSchema $schema): array
	{
		$resources = [];

		foreach ($schema->resources as $className) {
			$resource = $schema->items[$className];
			$resources[$className] = [
				'item' => $className,
				'pingColor' => $this->exportColor($resource->fluidColor),
				'speed' => 1,
			];
		}

		return $resources;
	}

	/** @return array<mixed> */
	protected function exportMiners(DocsSchema $schema): array
	{
		$miners = [];

		foreach ($schema->buildings as $building) {
			if ($building->miningRatePerCycle > 0) {
				$miners[$building->className] = [
					'className' => $building->className,
					'allowedResources' => count($building->allowedResources) ? $building->allowedResources : array_values(array_filter($schema->resources, function (string $className) use ($schema) {
						return !$schema->items[$className]->form->isFluid();
					})),
					'allowLiquids' => in_array(ItemForm::Liquid, $building->allowedResourceForms, true) || in_array('Desc_LiquidOil_C', $building->allowedResources, true),
					'allowSolids' => in_array(ItemForm::Solid, $building->allowedResourceForms, true),
					'itemsPerCycle' => $building->miningRatePerCycle,
					'extractCycleTime' => $building->miningCycleLength,
				];
			}

		}

		return $miners;
	}

	/** @return array<mixed> */
	protected function exportBuildings(DocsSchema $schema): array
	{
		$buildings = [];

		foreach ($schema->buildings as $building) {
			$buildings[$building->className] = [
				'slug' => Strings::webalize($building->name),
				'icon' => Strings::webalize($building->className),
				'name' => $building->name,
				'description' => $building->description,
				'className' => $building->className,
				'categories' => [],
				'buildMenuPriority' => 0,
				'metadata' => [
					'powerConsumption' => $building->powerUsage,
					'powerConsumptionExponent' => $building->powerUsageExponent,
					'manufacturingSpeed' => $building->manufacturingSpeed,
				],
				'size' => [
					'width' => 0,
					'height' => 0,
					'length' => 0,
				]
			];
		}

		return $buildings;
	}

	/**
	 * @param array<ItemAmount> $items
	 * @return array<mixed>
	 */
	protected function exportItemAmountArray(array $items): array
	{
		$result = [];
		foreach ($items as $item) {
			$result[] = $this->exportItemAmount($item);
		}
		return $result;
	}

	/** @return array<mixed> */
	protected function exportItemAmount(ItemAmount $item): array
	{
		return [
			'item' => $item->item,
			'amount' => $item->amount,
		];
	}

	/** @return array<mixed> */
	protected function exportColor(Color $color): array
	{
		return [
			'r' => $color->r,
			'g' => $color->g,
			'b' => $color->b,
			'a' => $color->a,
		];
	}

}
