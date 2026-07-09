<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Buildables;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\ItemForm;

class MinerHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$building = $schema->getOrCreateBuilding($data->getString('ClassName'));

		if ($data->getBool('mOnlyAllowCertainResources')) {
			$building->allowedResources = array_map(function ($item) {
				/** @var string $item */
				return ClassData::parseBlueprintClass($item);
			}, $data->getPackedArray('mAllowedResources'));
		} else {
			$building->allowedResourceForms = array_map(function ($item) {
				/** @var string $item */
				return ItemForm::fromString($item);
			}, $data->getPackedArray('mAllowedResourceForms'));
		}

		$building->miningRatePerCycle = $data->getInt('mItemsPerCycle');
		$building->miningCycleLength = $data->getFloat('mExtractCycleTime');
	}

	protected function getClassNames(): array
	{
		return [
			'/Script/FactoryGame.FGBuildableFrackingExtractor',
			'/Script/FactoryGame.FGBuildableResourceExtractor',
		];
	}

}
