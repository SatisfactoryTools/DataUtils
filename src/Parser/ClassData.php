<?php declare(strict_types = 1);

namespace SFTools\Data\Parser;

use Nette\InvalidArgumentException;
use Nette\Utils\Strings;
use SFTools\Data\Schema\Parts\Color;
use SFTools\Data\Schema\Parts\Event;
use SFTools\Data\Schema\Parts\ItemAmount;
use SFTools\Data\Schema\Parts\ItemForm;
use SFTools\Data\Unpacker;
use SFTools\Data\UnpackerException;

class ClassData
{

	/** @param array<string, string> $data */
	public function __construct(private readonly array $data, public readonly string $className)
	{
	}

	/** @return array<mixed> */
	public function getRawArray(string $key): array
	{
		return (array) $this->get($key, []);
	}

	/** @return array<mixed> */
	public function getPackedArray(string $key): array
	{
		return array_filter((array) Unpacker::unpack((string) $this->get($key)));
	}

	/** @return ItemAmount[] */
	public function getItemAmountArray(string $key): array
	{
		return array_map(function ($item) {
			/** @var array{'ItemClass': string, 'Amount': ?string} $item */
			return new ItemAmount(self::parseBlueprintClass($item['ItemClass']), (float) ($item['Amount'] ?? 1.0));
		}, $this->getPackedArray($key));
	}

	public function getString(string $key): string
	{
		return $this->normalizeString((string) $this->get($key));
	}

	public function getNullableString(string $key): ?string
	{
		$string = $this->get($key);
		return $string ? $this->normalizeString((string) $string) : null;
	}

	public function getInt(string $key): int
	{
		return (int) $this->get($key, 0);
	}

	public function getFloat(string $key): float
	{
		return (float) $this->get($key, 0.0);
	}

	public function getBool(string $key): bool
	{
		return $this->get($key, false) === 'True';
	}

	public function getColor(string $key): Color
	{
		/** @var array<string, string> $color */
		$color = Unpacker::unpack($this->get($key));
		return new Color($color);
	}

	public function getItemForm(string $key): ItemForm
	{
		return ItemForm::fromString($this->get($key));
	}

	/**
	 * Extracts the texture path from a packed Slate brush struct, e.g.
	 * (..., ResourceObject="/Script/Engine.Texture2D'/Game/Path/Icon.Icon'", ...)
	 * and returns it in the same format plain icon fields use: "Texture2D /Game/Path/Icon.Icon".
	 */
	public function getBrushIcon(string $key): ?string
	{
		try {
			$brush = Unpacker::unpack((string) $this->get($key));
		} catch (UnpackerException) {
			return null;
		}

		$resource = is_array($brush) ? ($brush['ResourceObject'] ?? null) : null;
		if (!is_string($resource)) {
			return null;
		}

		$result = Strings::match($resource, '/Texture2D\'(.+?)\'/');
		return $result ? 'Texture2D ' . $result[1] : null;
	}

	/**
	 * @return Event[]
	 */
	public function getEvents(string $key): array
	{
		return array_filter(array_map(function ($item) {
			/** @var string $item */
			return Event::tryFromString($item);
		}, $this->getPackedArray($key)));
	}

	public function hasKey(string $key): bool
	{
		return array_key_exists($key, $this->data);
	}

	public static function convertEquipmentClassname(string $original): string
	{
		$original = str_replace('Equip_', 'Desc_' , $original);

		return match ($original) {
			'Desc_Rifle_C' => 'BP_EquipmentDescriptorRifle_C',
			'Desc_RebarGun_Projectile_C' => 'Desc_RebarGunProjectile_C',
			'Desc_Beacon_C' => 'BP_EquipmentDescriptorBeacon_C',
			'Desc_GolfCartDispenser_C' => 'Desc_GolfCart_C',
			'Desc_GoldGolfCartDispenser_C' => 'Desc_GolfCartGold_C',
			'Desc_HazmatSuit_C' => 'BP_EquipmentDescriptorHazmatSuit_C',
			'Desc_JetPack_C' => 'BP_EquipmentDescriptorJetPack_C',
			'Desc_JumpingStilts_C' => 'BP_EquipmentDescriptorJumpingStilts_C',
			'Desc_ShockShank_C' => 'BP_EquipmentDescriptorShockShank_C',
			'Desc_StunSpear_C' => 'BP_EquipmentDescriptorStunSpear_C',
			'Desc_CandyCaneBasher_C' => 'BP_EquipmentDescriptorCandyCane_C',
			'Desc_NobeliskDetonator_C' => 'BP_EquipmentDescriptorNobeliskDetonator_C',
			'Desc_GasMask_C' => 'BP_EquipmentDescriptorGasmask_C',
			'Desc_PortableMinerDispenser_C' => 'BP_ItemDescriptorPortableMiner_C',
			'Desc_ObjectScanner_C' => 'BP_EquipmentDescriptorObjectScanner_C',
			'Desc_HoverPack_C' => 'BP_EquipmentDescriptorHoverPack_C',
			'Desc_Zipline_C' => 'BP_EqDescZipLine_C',
			'Desc_Parachute_C' => 'Desc_Parachute_C',
			'Desc_MedKit_C' => 'Desc_Medkit_C',
			default => $original,
		};
	}

	public static function parseClass(string $value): string
	{
		$parts = explode('.', $value);
		return trim(str_replace(['Build_', 'MaterialDesc_'], ['Desc_', 'Recipe_Material_'], end($parts)), '"');
	}

	public static function parseBlueprintClass(string $value): string
	{
		$result = Strings::match($value, '/BlueprintGeneratedClass.*?\.(.*?)["\']+/');
		return $result ? trim($result[1], '"') : $value;
	}

	/**
	 * @template T
	 * @param T $default
	 * @return string|T
	 */
	private function get(string $key, mixed $default = ''): mixed
	{
		return $this->data[$key] ?? $default;
	}

	private function normalizeString(string $string): string
	{
		return trim(str_replace("\r\n", "\n", $string));
	}

}
