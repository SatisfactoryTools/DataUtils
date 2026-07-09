<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Items;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;

class ConsumableHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$item = $schema->getOrCreateItem($data->getString('ClassName'));

		$item->consumable = true;
		$item->healthGain = $data->getInt('mHealthGain');
	}

	protected function getClassNames(): array
	{
		return ['/Script/FactoryGame.FGConsumableDescriptor'];
	}

}
