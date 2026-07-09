<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

use Nette\InvalidArgumentException;

enum ItemForm: string
{

	case Solid = 'solid';
	case Liquid = 'liquid';
	case Gas = 'gas';

	public static function fromString(string $form): ItemForm
	{
		return match ($form) {
			'RF_SOLID' => self::Solid,
			'RF_LIQUID' => self::Liquid,
			'RF_GAS' => self::Gas,
			default => throw new InvalidArgumentException('Invalid form ' . $form),
		};
	}

	public function isFluid(): bool
	{
		return $this !== self::Solid;
	}

}
