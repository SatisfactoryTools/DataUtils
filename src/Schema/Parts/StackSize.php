<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

enum StackSize: int
{

	case None = 0;
	case One = 1;
	case Small = 50;
	case Medium = 100;
	case Big = 200;
	case Huge = 500;
	case Fluid = 50000;

	public static function fromString(string $stackSize): StackSize
	{
		return match ($stackSize) {
			'SS_ONE' => self::One,
			'SS_SMALL' => self::Small,
			'SS_MEDIUM' => self::Medium,
			'SS_BIG' => self::Big,
			'SS_HUGE' => self::Huge,
			'SS_FLUID' => self::Fluid,
			default => self::None,
		};
	}

}
