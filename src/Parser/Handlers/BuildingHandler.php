<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\BuildingMaterial;

class BuildingHandler implements Handler
{

	public function canHandle(ClassData $data): bool
	{
		return $data->hasKey('mBuildEffectSpeed');
	}

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$className = $data->getString('ClassName');

		if ($className === 'Build_BlueprintDesigner_Mk3_C') {
			$className = 'Build_BlueprintDesigner_MK3_C';
		}

		$building = $schema->getOrCreateBuilding($className);

		$name = $data->getString('mDisplayName');
		if (!$building->name && $name) {
			$building->name = $name;
		}

		$description = $data->getString('mDescription');
		if (!$building->description && $description) {
			$building->description = $description;
		}

		$building->allowColoring = $data->getBool('mAllowColoring');
		$building->allowPatterning = $data->getBool('mAllowPatterning');

		$building->canOverclock = !(in_array($building->className, [
			'Desc_AlienPowerBuilding_C',
			'Desc_DroneStation_C',
			'Desc_FrackingExtractor_C',
		], true)) && $data->getBool('mCanChangePotential');
		$building->minOverclock = $data->getFloat('mMinPotential');
		$building->maxOverclock = $data->getFloat('mMaxPotential');
		$building->clockChangePerShard = $data->getFloat('mMaxPotentialIncreasePerCrystal');


		if ($data->hasKey('mProductionShardSlotSize')) {
			$building->canSloop = $data->getBool('mCanChangeProductionBoost');
			$building->sloopSlots = $building->className === 'Desc_SmelterMk1_C' ? 1 : $data->getInt('mProductionShardSlotSize');
			$building->sloopBoost = $data->getFloat('mProductionShardBoostMultiplier');
		}

		if ($data->hasKey('mWidth')) {
			$building->width = round($data->getFloat('mWidth') / 100, 2);
		}
		if ($data->hasKey('mHeight')) {
			$building->height = round($data->getFloat('mHeight') / 100, 2);
		}

		$materials = $data->getPackedArray('mAlternativeMaterialRecipes');

		/** @var array{'mMaterial': string, 'mRecipe': string} $material */
		foreach ($materials as $material) {
			$building->materials[] = new BuildingMaterial(
				ClassData::parseClass($material['mMaterial']),
				ClassData::parseClass($material['mRecipe'])
			);
		}

		$building->powerUsage = $data->getInt('mPowerConsumption');
		$building->powerUsageExponent = $data->getFloat('mPowerConsumptionExponent');
	}

}
