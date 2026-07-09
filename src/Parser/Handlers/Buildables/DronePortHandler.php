<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Buildables;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;

class DronePortHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$building = $schema->getOrCreateBuilding($data->getString('ClassName'));

		$building->storageSize = $data->getInt('mStorageSizeX') * $data->getInt('mStorageSizeY');
		$building->fuelStorageSize = $data->getInt('mBatteryStorageSizeX') * $data->getInt('mBatteryStorageSizeY');
		$building->acceptedFuel = array_map(function ($item) {
			/** @var string $item */
			return ClassData::parseBlueprintClass($item);
		}, $data->getPackedArray('mBatteryClasses'));
		$building->tripPowerCostBase = $data->getInt('mTripPowerCost');
		$building->tripPowerCostPerMeter = $data->getInt('mTripPowerPerMeterCost');
	}

	protected function getClassNames(): array
	{
		return ['/Script/FactoryGame.FGBuildableDroneStation'];
	}

}
