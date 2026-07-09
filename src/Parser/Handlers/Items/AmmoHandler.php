<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Items;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;

class AmmoHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$item = $schema->getOrCreateItem($data->getString('ClassName'));

		$item->magazineSize = $data->getInt('mMagazineSize');
		$item->fireRate = $data->getFloat('mFireRate');

		/** @var array<array{'CompatibleItemType': string, 'CompatibleItemDescriptors': array<string>}> $descriptors */
		$descriptors = $data->getPackedArray('mCompatibleItemDescriptors');

		foreach ($descriptors as $descriptor) {
			if ($descriptor['CompatibleItemType'] === 'CIT_WEAPON') {
				array_push($item->compatibleWeapons, ...array_map(function ($item) {
					return ClassData::parseBlueprintClass($item);
				}, $descriptor['CompatibleItemDescriptors']));
			}
		}

		if ($data->hasKey('mNumShots')) {
			/** @var array{'Min': string, 'Max': string} $shots */
			$shots = $data->getPackedArray('mNumShots');
			$item->minShots = (int) $shots['Min'];
			$item->maxShots = (int) $shots['Max'];
		}
	}

	protected function getClassNames(): array
	{
		return [
			'/Script/FactoryGame.FGAmmoTypeProjectile',
			'/Script/FactoryGame.FGAmmoTypeSpreadshot',
			'/Script/FactoryGame.FGAmmoTypeInstantHit',
		];
	}

}
