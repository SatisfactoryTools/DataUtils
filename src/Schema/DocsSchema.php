<?php declare(strict_types = 1);

namespace SFTools\Data\Schema;

use Nette\Utils\Strings;
use ReflectionClass;
use SFTools\Data\Console\Output\PrefixedOutput;
use SFTools\Data\Transformers\Transformer;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class DocsSchema
{

	/** @var Item[] */
	public array $items = [];

	/** @var Schematic[] */
	public array $schematics = [];

	/** @var Recipe[] */
	public array $recipes = [];

	/** @var Building[] */
	public array $buildings = [];

	/** @var Material[] */
	public array $materials = [];

	/** @var string[] */
	public array $resources = [];

	public function clone(): self
	{
		/** @var self $clone */
		$clone = unserialize(serialize($this));
		return $clone;
	}

	public function transform(Transformer $transformer, ?OutputInterface $output = null): void
	{
		if (!$output) {
			$output = new NullOutput;
		}

		$reflection = new ReflectionClass($transformer);
		$transformer->transform($this, new PrefixedOutput($output, '[' . $reflection->getShortName() . '] '));
	}

	public function getOrCreateItem(string $className): Item
	{
		foreach ($this->items as $item) {
			if ($item->className === $className) {
				return $item;
			}
		}

		$item = new Item;
		$item->className = $className;
		$this->items[$item->className] = $item;
		return $item;
	}

	public function getOrCreateBuilding(string $className): Building
	{
		$className = self::convertBuildingClassName($className);

		foreach ($this->buildings as $building) {
			if ($building->className === $className) {
				return $building;
			}
		}

		$building = new Building;
		$building->className = $className;
		$this->buildings[$building->className] = $building;
		return $building;
	}

	/**
	 * Converts Build_XX_C to Desc_XX_C and handles non-matching classes
	 */
	public static function convertBuildingClassName(string $className): string
	{
		$className = str_replace('Build_', 'Desc_', $className);

		$mapping = [
			'Desc_Wall_Concrete_FlipTris_8x(\d)_C' => 'Desc_Wall_Concrete_8x$1_FlipTris_C',
			'Desc_Wall_Concrete_Tris_8x(\d)_C' => 'Desc_Wall_Concrete_8x$1_Tris_C',
			'Desc_Wall_Orange_FlipTris_8x(\d)_C' => 'Desc_Wall_Orange_8x$1_FlipTris_C',
			'Desc_Wall_Orange_Tris_8x(\d)_C' => 'Desc_Wall_Orange_8x$1_Tris_C',
			'Desc_XmassTree_C' => 'Desc_XMassTree_C',
			'Desc_XmassLightsLine_C' => 'Desc_xmassLights_C',
			'Desc_WalkwayTrun_C' => 'Desc_WalkwayTurn_C',
			'Desc_CatwalkCorner_C' => 'Desc_CatwalkTurn_C',
			'Desc_QuarterPipeMiddle_Ficsit_8x(\d)_C' => 'Desc_QuarterPipeMiddle_Ficsit_4x$1_C',
			'Desc_PowerPoleWallDouble_Mk(\d)_C' => 'Desc_PowerPoleWallDoubleMk$1_C',
			'Desc_PowerPoleWall_Mk(\d)_C' => 'Desc_PowerPoleWallMk$1_C',
			'Desc_Foundation_ConcretePolished_8x2_2_C' => 'Foundation_ConcretePolished_8x2_C',
			'Desc_Foundation_ConcretePolished_8x4_C' => 'Foundation_ConcretePolished_8x4_C',
		];

		foreach ($mapping as $from => $to) {
			$className = Strings::replace($className, '/' . $from . '/', $to);
		}

		return $className;
	}

}
