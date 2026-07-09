<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

enum EquipSlot: string
{

	case Arms = 'arms';
	case Body = 'body';
	case Legs = 'legs';
	case Back = 'back';
	case None = '';

	public static function fromString(string $slot): self
	{
		return match ($slot) {
			'ES_ARMS' => self::Arms,
			'ES_BODY' => self::Body,
			'ES_LEGS' => self::Legs,
			'ES_BACK' => self::Back,
			default => self::None,
		};
	}

}
