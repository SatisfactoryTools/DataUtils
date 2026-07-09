<?php declare(strict_types = 1);

namespace SFTools\Data\Utils\SchematicTree\Renderers;

use Nette\Utils\Strings;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\SchematicType;
use SFTools\Data\Schema\Schematic;
use SFTools\Data\Utils\SchematicTree\AndNode;
use SFTools\Data\Utils\SchematicTree\SchematicNode;
use Symfony\Component\Console\Output\ConsoleOutput;

class WikiRenderer implements TreeRenderer
{

	private const string TranslationMegafauna = 'megafauna';
	private const string TranslationAlienTech = 'alientech';
	private const string TranslationCaterium = 'caterium';
	private const string TranslationFlowerPetals = 'flowerpetals';
	private const string TranslationMycelia = 'mycelia';
	private const string TranslationNutrients = 'nutrients';
	private const string TranslationPowerSlugs = 'powerslugs';
	private const string TranslationQuartz = 'quartz';
	private const string TranslationSulfur = 'sulfur';
	private const string TranslationXmas = 'xmas';
	private const string TranslationUnknown = 'unknown';
	private const string TranslationOnboarding = 'onboarding';
	private const string TranslationTier = 'tier';
	private const string TranslationShop = 'shop';
	private const string TranslationMamPrefix = 'mamprefix';
	private const string TranslationMamSuffix = 'mamsuffix';
	private const string TranslationAnd = 'and';
	private const string TranslationOr = 'or';
	private const string TranslationHardDriveScanAfter = 'harddrivescanafter';
	private const string TranslationHardDriveScan = 'harddrivescan';

	/** @var array<string, array<string, string>> */
	private static array $translations = [
		'en' => [
			self::TranslationMegafauna => 'Alien Megafauna',
			self::TranslationAlienTech => 'Alien Technology',
			self::TranslationCaterium => 'Caterium',
			self::TranslationFlowerPetals => 'Flower Petals',
			self::TranslationMycelia => 'Mycelia',
			self::TranslationNutrients => 'Nutrients',
			self::TranslationPowerSlugs => 'Power Slugs',
			self::TranslationQuartz => 'Quartz',
			self::TranslationSulfur => 'Sulfur',
			self::TranslationXmas => 'FICSMAS Holiday Event',
			self::TranslationUnknown => 'Unknown',
			self::TranslationOnboarding => 'Onboarding',
			self::TranslationTier => 'Tier ',
			self::TranslationShop => 'AWESOME Shop',
			self::TranslationMamPrefix => 'MAM ',
			self::TranslationMamSuffix => ' Research',
			self::TranslationAnd => ' AND<br>',
			self::TranslationOr => ' OR<br>',
			self::TranslationHardDriveScanAfter => '[[Hard Drive|Hard Drive scanning]] after unlocking:<br>',
			self::TranslationHardDriveScan => '[[Hard Drive|Hard Drive scanning]]',
		]
	];

	/** @var array<string, string> */
	private array $mamMapping = [];

	public function __construct(DocsSchema $schema, private readonly string $lang = 'en')
	{
		foreach ($schema->schematics as $schematic) {
			if ($schematic->type === SchematicType::MAM) {
				if ($schematic->className === 'Research_Caterium_4_3_C') {
					$this->mamMapping[$schematic->className] = $this->translate(self::TranslationQuartz);
					continue;
				}

				$match = Strings::match($schematic->className, '/Research\_(.+?)\_/');
				if ($match[1] ?? null) {
					$this->mamMapping[$schematic->className] = match ($match[1]) {
						'AOrganisms', 'AOrgans', 'AO', 'ACarapace' => $this->translate(self::TranslationMegafauna),
						'Alien' => $this->translate(self::TranslationAlienTech),
						'Caterium' => $this->translate(self::TranslationCaterium),
						'FlowerPetals' => $this->translate(self::TranslationFlowerPetals),
						'Mycelia' => $this->translate(self::TranslationMycelia),
						'Nutrients' => $this->translate(self::TranslationNutrients),
						'PowerSlugs' => $this->translate(self::TranslationPowerSlugs),
						'Quartz' => $this->translate(self::TranslationQuartz),
						'Sulfur' => $this->translate(self::TranslationSulfur),
						'XMas' => $this->translate(self::TranslationXmas),
						default => $this->translate(self::TranslationUnknown),
					};
				}
			}
		}
	}

	public function renderNode(SchematicNode $node, bool $includeBraces = false, bool $includePrefix = true, bool $ignoreAlts = false): string
	{
		$children = $node->getChildren();

		if ($node->schematic->type === SchematicType::Alternate && count($children) >= 1) {
			$result = $this->renderChildren($node, ignoreAlts: true);
			return $result === '' ?
				$this->translate(self::TranslationHardDriveScan) :
				(($includePrefix ? $this->translate(self::TranslationHardDriveScanAfter) : '') . $result);
		}

		return $this->renderChildren($node, $includeBraces, $ignoreAlts);
	}

	public function renderChildren(SchematicNode $node, bool $includeBraces = false, bool $ignoreAlts = false): string
	{
		$children = $node->getChildren();
		$children = array_unique($children, SORT_REGULAR);
		usort($children, fn (SchematicNode $a, SchematicNode $b) => $a->schematic->className <=> $b->schematic->className);

		if (count($children) > 1) {
			return ($includeBraces ? '(' : '') . implode($this->translate($node instanceof AndNode ? self::TranslationAnd : self::TranslationOr), array_map(function (SchematicNode $child) {
					return $this->renderNode($child, true, false);
				}, $children)) . ($includeBraces ? ')' : '');
		}

		if (count($children) === 1 && in_array($node->schematic->type, [SchematicType::Alternate, SchematicType::Custom], true)) {
			return $this->renderNode($children[0], $includeBraces, false, $ignoreAlts);
		}

		return $this->renderSchematic($node->schematic, $ignoreAlts);
	}

	public function renderSchematic(Schematic $schematic, bool $ignoreAlts = false): string
	{
		if ($schematic->className === 'Schematic_StartingRecipes_C') {
			return $this->translate(self::TranslationOnboarding);
		}

		if ($ignoreAlts && $schematic->type === SchematicType::Alternate) {
			return '';
		}

		$prefix = null;

		switch ($schematic->type) {
			case SchematicType::Milestone:
			case SchematicType::Tutorial:
				$prefix = $this->translate(self::TranslationTier) . $schematic->tier;
				break;
			case SchematicType::AwesomeSink:
				$prefix = $this->translate(self::TranslationShop);
				break;
			case SchematicType::MAM:
				$prefix = $this->mamMapping[$schematic->className] . $this->translate(self::TranslationMamSuffix) .
					'|' . $this->translate(self::TranslationMamPrefix) . $this->mamMapping[$schematic->className] . $this->translate(self::TranslationMamSuffix);
				break;
			default:
				break;
		}

		return ($prefix ? ('[[' . $prefix . ']] - ') : '') . Strings::fixEncoding($schematic->name);
	}

	private function translate(string $key): string
	{
		return self::$translations[$this->lang][$key] ?? $key;
	}

}
