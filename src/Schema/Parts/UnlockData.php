<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

class UnlockData
{

	/** @var string[] */
	public array $recipes = [];

	/** @var string[] */
	public array $schematics = [];

	/** @var ItemAmount[] */
	public array $items = [];

	/** @var ScannableObject[] */
	public array $scannableObjects = [];

	/** @var string[] */
	public array $scannableResources = [];

	/** @var string[] */
	public array $tapes = [];

	/** @var string[] */
	public array $emotes = [];

	public int $inventorySlots = 0;
	public int $equipmentSlots = 0;

}
