<?php declare(strict_types = 1);

namespace SFTools\Data\Utils\SchematicTree;

use SFTools\Data\Schema\Schematic;

abstract class SchematicNode
{

	/** @var SchematicNode[] */
	private array $children = [];

	public function __construct(public readonly Schematic $schematic)
	{
	}

	public function addChild(SchematicNode $node): void
	{
		$this->children[] = $node;
	}

	/** @return SchematicNode[] */
	public function getChildren(): array
	{
		return $this->children;
	}

	public function getEndOfLinearPath(): SchematicNode
	{
		$node = $this;

		$children = $node->getChildren();
		while (count($children) === 1) {
			$node = reset($children);
		}

		return $node;
	}

}
