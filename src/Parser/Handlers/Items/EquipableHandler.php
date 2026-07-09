<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Items;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\Handler;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\EquipSlot;

class EquipableHandler implements Handler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$className = $data->getString('ClassName');
		if ($className === 'BP_ConsumeableEquipment_C') { // ignore weird classname
			return;
		}

		$item = $schema->getOrCreateItem(ClassData::convertEquipmentClassname($className));

		$item->equipSlot = EquipSlot::fromString($data->getString('mEquipmentSlot'));
	}

	public function canHandle(ClassData $data): bool
	{
		return $data->hasKey('mEquipmentSlot');
	}

}
