<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers\Items;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Parser\Handlers\ClassNameFilteredHandler;
use SFTools\Data\Schema\DocsSchema;

class BiomassHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$item = $schema->getOrCreateItem($data->getString('ClassName'));
		$item->isBiomass = true;
	}

	protected function getClassNames(): array
	{
		return ['/Script/FactoryGame.FGItemDescriptorBiomass'];
	}

}
