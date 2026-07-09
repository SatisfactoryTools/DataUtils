<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

class ItemAmount
{

	public function __construct(public string $item, public float $amount)
	{
	}

}
