<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

class DependencyData
{

	/** @var string[] */
	public array $schematics = [];

	public ?GamePhase $gamePhase = null;

	public bool $requireAll = false;

}
