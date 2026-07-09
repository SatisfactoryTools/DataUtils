<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Buildables;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;

class ManufacturerHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$building = $schema->getOrCreateBuilding($data->getString('ClassName'));

		$building->manufacturingSpeed = $data->getInt('mManufacturingSpeed') ?: 1;
	}

	protected function getClassNames(): array
	{
		return [
			'/Script/FactoryGame.FGBuildableManufacturer',
			'/Script/FactoryGame.FGBuildableManufacturerVariablePower',
			'/Script/FactoryGame.FGBuildableFactorySimpleProducer',
		];
	}

}
