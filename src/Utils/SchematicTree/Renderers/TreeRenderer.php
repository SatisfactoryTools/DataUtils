<?php declare(strict_types = 1);

namespace SFTools\Data\Utils\SchematicTree\Renderers;

use SFTools\Data\Utils\SchematicTree\SchematicNode;

interface TreeRenderer
{

	public function renderNode(SchematicNode $node): string;

}
