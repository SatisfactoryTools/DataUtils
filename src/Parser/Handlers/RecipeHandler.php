<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\ItemAmount;
use SFTools\Data\Schema\Recipe;

class RecipeHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		// ignore outdated recipe
		if ($data->getString('ClassName') === 'Recipe_Wall_Window_8x4_03_Steel_C') {
			return;
		}

		$recipe = new Recipe;
		$recipe->className = $data->getString('ClassName');
		$recipe->name = $data->getString('mDisplayName');
		$recipe->ingredients = $data->getItemAmountArray('mIngredients');
		$recipe->products = $data->getItemAmountArray('mProduct');
		$recipe->time = $data->getFloat('mManufactoringDuration');
		$recipe->manualCraftingMultiplier = $data->getFloat('mManualManufacturingMultiplier');
		$recipe->variablePowerDrawConstant = $data->getInt('mVariablePowerConsumptionConstant');
		$recipe->variablePowerDrawFactor = $data->getInt('mVariablePowerConsumptionFactor');
		$recipe->variablePowerDraw = $recipe->variablePowerDrawFactor !== 1 || $recipe->variablePowerDrawConstant !== 0;
		$recipe->events = $data->getEvents('mRelevantEvents');

		/** @var string $producedIn */
		foreach ($data->getPackedArray('mProducedIn') as $producedIn) {
			switch (trim($producedIn, '"')) {
				case '/Game/FactoryGame/Equipment/BuildGun/BP_BuildGun.BP_BuildGun_C':
				case '/Script/FactoryGame.FGBuildGun':
					$recipe->inBuildGun = true;
					break;
				case '/Game/FactoryGame/Buildable/-Shared/WorkBench/BP_WorkBenchComponent.BP_WorkBenchComponent_C':
				case '/Script/FactoryGame.FGBuildableAutomatedWorkBench':
				case '/Game/FactoryGame/Buildable/Factory/AutomatedWorkBench/Build_AutomatedWorkBench.Build_AutomatedWorkBench_C':
					$recipe->inCraftBench = true;
					break;
				case '/Game/FactoryGame/Buildable/-Shared/WorkBench/BP_WorkshopComponent.BP_WorkshopComponent_C':
					$recipe->inEquipmentWorkshop = true;
					break;
				default:
					$recipe->producedIn[] = ClassData::parseClass($producedIn);
					break;
			}
		}

		$schema->recipes[$recipe->className] = $recipe;
	}

	protected function getClassNames(): array
	{
		return ['/Script/FactoryGame.FGRecipe'];
	}

}
