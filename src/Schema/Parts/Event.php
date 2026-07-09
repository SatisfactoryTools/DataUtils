<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

enum Event: string
{

	case Ficsmas = 'ficsmas';
	case Unknown = '?';

	public static function tryFromString(string $event): ?self
	{
		return match ($event) {
			'EV_Christmas' => self::Ficsmas,
			default => null,
		};
	}

}
