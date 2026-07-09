<?php declare(strict_types = 1);

namespace SFTools\Data\Utils\SchematicTree;

use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\SchematicType;
use SFTools\Data\Schema\Schematic;

class SchematicTreeFactory
{

	private readonly DocsSchema $schema;

	public function __construct(DocsSchema $schema)
	{
		$this->schema = $schema->clone();

		// sort schematics by classname to guarantee fixed order
		$schematics = $this->schema->schematics;
		ksort($schematics);
		$this->schema->schematics = $schematics;
	}

	public function createForMultiple(Schematic ...$targets): SchematicNode
	{
		$node = new OrNode($targets[0]); // pass first schematic, only gets used if it's the only one
		foreach ($targets as $target) {
			$node->addChild($this->createFor($target));
		}
		return $node;
	}

	public function createFor(Schematic $target): SchematicNode
	{
		foreach ($this->schema->schematics as $schematic) {
			if (in_array($target->className, $schematic->unlock->schematics, true)) {
				return $this->createFor($schematic);
			}
		}

		$node = $target->dependency->requireAll ? new AndNode($target) : new OrNode($target);

		foreach ($target->dependency->schematics as $schematicClass) {
			$schematic = $this->schema->schematics[$schematicClass] ?? null;
			if ($schematic !== null) {
				$node->addChild($this->createFor($schematic));
			}
		}

		return $node;
	}

}
