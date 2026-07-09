<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Buildables;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\Fuel;

class GeneratorHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$building = $schema->getOrCreateBuilding($data->getString('ClassName'));

		$building->alwaysProducesPower = $data->getBool('mIsFullBlast');
		$building->powerProduction = $data->getInt('mPowerProduction');
		$building->supplementalToPowerRatio = $data->getFloat('mSupplementalToPowerRatio');

		$building->fuel = array_map(function ($item) {
			/** @var array{'mFuelClass': string, 'mSupplementalResourceClass': string, 'mByproduct': string, 'mByproductAmount': string} $item */
			$fuel = new Fuel;

			$fuel->item = $item['mFuelClass'];
			$fuel->supplementalItem = $item['mSupplementalResourceClass'] ?: null;
			$fuel->byproduct = $item['mByproduct'] ?: null;
			$fuel->byproductAmount = $item['mByproductAmount'] ? (int) $item['mByproductAmount'] : 0;

			return $fuel;
		}, $data->getRawArray('mFuel'));
	}

	protected function getClassNames(): array
	{
		return [
			'/Script/FactoryGame.FGBuildableGeneratorFuel',
			'/Script/FactoryGame.FGBuildableGeneratorNuclear',
		];
	}

}
