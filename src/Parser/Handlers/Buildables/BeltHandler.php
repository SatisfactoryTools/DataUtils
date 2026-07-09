<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Buildables;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;

class BeltHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$building = $schema->getOrCreateBuilding($data->getString('ClassName'));

		$building->beltSpeed = $data->getInt('mSpeed') / 2;
		$building->maxLength = 56 * 100;
		$building->lengthPerCost = 2;
	}

	protected function getClassNames(): array
	{
		return ['/Script/FactoryGame.FGBuildableConveyorBelt'];
	}

}
