<?php declare(strict_types = 1);

namespace SFTools\Data\Schema;

use SFTools\Data\Schema\Parts\Event;
use SFTools\Data\Schema\Parts\ItemAmount;

class Material
{

	use ClassName;

	/** @var ItemAmount[] */
	public array $ingredients = [];

	/** @var Event[] */
	public array $events = [];

}
