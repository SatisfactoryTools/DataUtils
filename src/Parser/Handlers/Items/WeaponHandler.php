<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Items;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;

class WeaponHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$item = $schema->getOrCreateItem(ClassData::convertEquipmentClassname($data->getString('ClassName')));

		$item->reloadTime = $data->getFloat('mReloadTime');
		/** @var string[] $ammoClasses */
		$ammoClasses = $data->getPackedArray('mAllowedAmmoClasses');
		array_push($item->compatibleAmmo, ...array_map(function (string $item) {
			return ClassData::parseBlueprintClass($item);
		}, $ammoClasses));
	}

	protected function getClassNames(): array
	{
		return [
			'/Script/FactoryGame.FGWeapon',
			'/Script/FactoryGame.FGChargedWeapon',
		];
	}

}
