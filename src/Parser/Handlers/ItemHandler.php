<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\StackSize;

class ItemHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$item = $schema->getOrCreateItem($data->getString('ClassName'));

		$item->icon = $data->getString('mPersistentBigIcon');
		$item->name = $data->getString('mDisplayName');
		$item->description = $data->getString('mDescription');
		$item->abbr = $data->getNullableString('mAbbreviatedDisplayName');
		$item->canBeTrashed = $data->getBool('mCanBeDiscarded');
		$item->energy = $data->getFloat('mEnergyValue');
		$item->radioactiveDecay = $data->getFloat('mRadioactiveDecay');
		$item->form = $data->getItemForm('mForm');
		$item->smallIcon = $data->getString('mSmallIcon');
		$item->bigIcon = $data->getString('mPersistentBigIcon');
		$item->fluidColor = $data->getColor('mFluidColor');
		$item->gasColor = $data->getColor('mGasColor');
		$item->sinkPoints = $data->getInt('mResourceSinkPoints');
		$item->stackSize = StackSize::fromString($data->getString('mStackSize'));

		if ($data->hasKey('mIsAlienItem')) {
			$item->isAlien = $data->getBool('mIsAlienItem');
		}

		if ($data->className === '/Script/FactoryGame.FGResourceDescriptor') {
			$schema->resources[] = $item->className;
		}
	}

	protected function getClassNames(): array
	{
		return [
			'/Script/FactoryGame.FGItemDescriptor',
			'/Script/FactoryGame.FGConsumableDescriptor',
			'/Script/FactoryGame.FGPowerShardDescriptor',
			'/Script/FactoryGame.FGItemDescriptorBiomass',
			'/Script/FactoryGame.FGResourceDescriptor',
			'/Script/FactoryGame.FGEquipmentDescriptor',
			'/Script/FactoryGame.FGItemDescriptorNuclearFuel',
			'/Script/FactoryGame.FGAmmoTypeProjectile',
			'/Script/FactoryGame.FGAmmoTypeSpreadshot',
			'/Script/FactoryGame.FGAmmoTypeInstantHit',
			'/Script/FactoryGame.FGItemDescriptorPowerBoosterFuel',
		];
	}

}
