<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Material;

class MaterialHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$material = new Material;
		$material->className = $data->getString('ClassName');
		$material->ingredients = $data->getItemAmountArray('mIngredients');
		$material->events = $data->getEvents('mRelevantEvents');

		$schema->materials[] = $material;
	}

	protected function getClassNames(): array
	{
		return ['/Script/FactoryGame.FGCustomizationRecipe'];
	}

}
