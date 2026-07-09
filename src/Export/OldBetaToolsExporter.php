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

class OldBetaToolsExporter extends OldToolsExporter
{

	public const string FakeSinkPoint = 'special__sinkPoint';
	public const string FakePower = 'special__power';

	protected function exportItems(DocsSchema $schema): array
	{
		$items = [];

		foreach ($schema->items as $item) {
			$items[$item->className] = [
				'slug' => Strings::webalize($item->className),
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

		$items[self::FakeSinkPoint] = [
			'className' => self::FakeSinkPoint,
			'description' => 'A special FICSIT bonus program Coupon, obtained through the AWESOME Sink. Can be redeemed in the AWESOME Shop for bonus milestones and rewards',
			'energyValue' => 0,
			'fluidColor' => [
				'r' => 0,
				'g' => 0,
				'b' => 0,
				'a' => 0,
			],
			'liquid'=> false,
			'name'=> 'Sink point',
			'radioactiveDecay' => 0,
			'sinkPoints' => 0,
			'slug' => 'sink-point',
			'stackSize' => 500,
		];

		$items[self::FakePower] = [
			'className' => self::FakePower,
			'description' => 'Power',
			'energyValue' => 0,
			'fluidColor' => [
				'r' => 0,
				'g' => 0,
				'b' => 0,
				'a' => 0,
			],
			'liquid' => false,
			'name' => 'Power',
			'radioactiveDecay' => 0,
			'sinkPoints' => 0,
			'slug' => 'power',
			'stackSize' => 1,
		];

		return $items;
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
				'slug' => Strings::webalize($schematic->className),
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
					'fuel' => array_map(static function (Fuel $fuel) {
						return $fuel->item;
					}, $building->fuel),
					'fuels' => array_map(static function (Fuel $fuel) {
						return [
							'item' => $fuel->item,
							'supplementalItem' => $fuel->supplementalItem,
							'byproduct' => $fuel->byproduct,
							'byproductAmount' => $fuel->byproductAmount,
						];
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
	protected function exportBuildings(DocsSchema $schema): array
	{
		$buildings = [];

		foreach ($schema->buildings as $building) {
			$buildings[$building->className] = [
				'slug' => Strings::webalize($building->className),
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

}
