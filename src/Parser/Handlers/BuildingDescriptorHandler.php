<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Schema\DocsSchema;

class BuildingDescriptorHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$className = $data->getString('ClassName');

		// ignore outdated descriptor
		if ($className === 'Desc_Wall_Window_8x4_03_Steel_C') {
			return;
		}

		$building = $schema->getOrCreateBuilding($className);

		$building->icon = $data->getString('mPersistentBigIcon');
		$name = $data->getString('mDisplayName');
		if (!$building->name && $name) {
			$building->name = $name;
		}

		$description = $data->getString('mDescription');
		if (!$building->description && $description) {
			$building->description = $description;
		}
	}

	protected function getClassNames(): array
	{
		return [
			'/Script/FactoryGame.FGBuildingDescriptor',
			'/Script/FactoryGame.FGPoleDescriptor',
			'/Script/FactoryGame.FGVehicleDescriptor',
		];
	}

}
