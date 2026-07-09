<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

class BuildingMaterial
{

	public function __construct(public string $material, public string $recipe)
	{
	}

}
