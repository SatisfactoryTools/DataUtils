<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Buildables;

use SFTools\Data\Transformers\FuelTransformer;
use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\Fuel;

class VehicleHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$building = $schema->getOrCreateBuilding($data->getString('ClassName'));

		$building->storageSize = $data->getInt('mInventorySize');
		if ($data->hasKey('mFuelConsumption')) {
			$building->powerUsage = $data->getInt('mFuelConsumption');
		} else {
			$building->powerUsage = $data->getInt('mManualFuelConsumption');
		}
		$building->isVehicle = true;

		if ($building->powerUsage && $building->className !== 'Desc_Locomotive_C') {
			$building->fuel[] = $fuel = new Fuel();
			$fuel->acceptsAnySolidFuel = true;
		}
	}

	protected function getClassNames(): array
	{
		return ['/Script/FactoryGame.FGVehicleDescriptor'];
	}

}
