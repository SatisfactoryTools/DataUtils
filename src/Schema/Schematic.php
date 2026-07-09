<?php declare(strict_types = 1);

namespace SFTools\Data\Schema;

use SFTools\Data\Schema\Parts\DependencyData;
use SFTools\Data\Schema\Parts\Event;
use SFTools\Data\Schema\Parts\ItemAmount;
use SFTools\Data\Schema\Parts\SchematicType;
use SFTools\Data\Schema\Parts\UnlockData;

class Schematic
{

	use ClassName;

	public ?string $icon = null;
	public string $name = '';
	public string $description = '';
	public int $tier = 0;
	/** @var ItemAmount[] */
	public array $cost = [];
	public SchematicType $type = SchematicType::Custom;
	public float $time = 0;
	public UnlockData $unlock;
	public DependencyData $dependency;
	public bool $dependenciesBlockAccess = false;
	public bool $dependenciesHide = false;

	/** @var Event[] */
	public array $events = [];

	public function __construct()
	{
		$this->unlock = new UnlockData;
		$this->dependency = new DependencyData;
	}

}
