<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

use Nette\InvalidArgumentException;

enum SchematicType: string
{

	case Custom = 'custom';
	case MAM = 'MAM';
	case Tutorial = 'tutorial';
	case HardDrive = 'hardDrive';
	case Milestone = 'milestone';
	case Alternate = 'alternate';
	case AwesomeSink = 'resourceSink';
	case Customisation = 'customisation';

	public static function fromString(string $type): self
	{
		return match ($type) {
			'EST_Custom' => self::Custom,
			'EST_MAM' => self::MAM,
			'EST_Tutorial' => self::Tutorial,
			'EST_HardDrive' => self::HardDrive,
			'EST_Milestone' => self::Milestone,
			'EST_Alternate' => self::Alternate,
			'EST_ResourceSink' => self::AwesomeSink,
			'EST_Customization' => self::Customisation,
			default => throw new InvalidArgumentException('Invalid schematic type: ' . $type),
		};
	}

	public function toOriginal(): string
	{
		return match ($this) {
			self::Custom => 'EST_Custom',
			self::MAM => 'EST_MAM',
			self::Tutorial => 'EST_Tutorial',
			self::HardDrive => 'EST_HardDrive',
			self::Milestone => 'EST_Milestone',
			self::Alternate => 'EST_Alternate',
			self::AwesomeSink => 'EST_ResourceSink',
			self::Customisation => 'EST_Customization',
		};
	}

}
