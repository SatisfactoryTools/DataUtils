<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

class ScannableObject
{

	/**
	 * @param string[] $actors
	 */
	public function __construct(public string $object, public array $actors = [])
	{
	}

}
