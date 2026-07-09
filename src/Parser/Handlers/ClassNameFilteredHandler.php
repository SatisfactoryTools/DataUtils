<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers;

use SFTools\Data\Parser\ClassData;

abstract class ClassNameFilteredHandler implements Handler
{

	public function canHandle(ClassData $data): bool
	{
		return in_array($data->className, $this->getClassNames(), true);
	}

	/** @return string[] */
	abstract protected function getClassNames(): array;

}
