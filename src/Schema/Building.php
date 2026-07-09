<?php declare(strict_types = 1);

namespace SFTools\Data\Schema;

use SFTools\Data\Schema\Parts\BuildingMaterial;
use SFTools\Data\Schema\Parts\Fuel;
use SFTools\Data\Schema\Parts\ItemForm;

class Building
{

	use ClassName;

	public ?string $icon = null;
	public string $name = '';
	public string $description = '';
	public bool $allowColoring = false;
	public bool $allowPatterning = false;

	/** @var BuildingMaterial[] */
	public array $materials = [];

	public bool $canOverclock = false;
	public float $minOverclock = 0;
	public float $maxOverclock = 0;
	public float $clockChangePerShard = 0;

	public bool $canSloop = false;
	public int $sloopSlots = 0;
	public float $sloopBoost = 0;

	public float $width = 0;
	public float $height = 0;

	public int $manufacturingSpeed = 0;
	public int $powerUsage = 0;
	public float $powerUsageExponent = 0;

	public bool $alwaysProducesPower = true;
	public int $powerProduction = 0;
	/** @var Fuel[] */
	public array $fuel = [];
	public float $supplementalToPowerRatio = 0;

	/** @var string[] */
	public array $acceptedFuel = [];
	public int $tripPowerCostBase = 0;
	public int $tripPowerCostPerMeter = 0;
	public int $storageSize = 0;
	public int $fuelStorageSize = 0;

	/** @var string[] */
	public array $allowedResources = [];
	/** @var ItemForm[] */
	public array $allowedResourceForms = [];
	public int $miningRatePerCycle = 0;
	public float $miningCycleLength = 0;

	public int $beltSpeed = 0;

	public int $maxLength = 0;
	public int $lengthPerCost = 0;

	public bool $isVehicle = false;

}
