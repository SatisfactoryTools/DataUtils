<?php declare(strict_types = 1);

namespace Tests\SFTools\Data;

require_once __DIR__ . '/bootstrap.php';

use SFTools\Data\Export\RawExporter;
use SFTools\Data\Export\WikiExporter;
use SFTools\Data\Parser\DocsParser;
use SFTools\Data\Schema\DocsSchema;
use Tester\Assert;
use Tester\TestCase;

/**
 * Snapshot test: parses a full Docs.json fixture (game version 1.0, en-US12 stable)
 * and compares exporter outputs against committed snapshots in tests/fixtures/expected/.
 *
 * When a change to parser/transformer/exporter code intentionally changes the output,
 * regenerate the snapshots (gzencode the new exporter outputs) and review the diff.
 */
class DocsParserSnapshotTestCase extends TestCase
{

	private static ?DocsSchema $schema = null;

	private function getSchema(): DocsSchema
	{
		if (self::$schema === null) {
			$parser = new DocsParser;
			self::$schema = $parser->parse($this->readFixture(__DIR__ . '/fixtures/en-US12stable.json.gz'));
		}

		return self::$schema;
	}

	public function testParsedCounts(): void
	{
		$schema = $this->getSchema();

		Assert::count(196, $schema->items);
		Assert::count(863, $schema->recipes);
		Assert::count(546, $schema->buildings);
		Assert::count(557, $schema->schematics);
		Assert::count(106, $schema->materials);
	}

	public function testSpotChecks(): void
	{
		$schema = $this->getSchema();

		$ironPlate = $schema->items['Desc_IronPlate_C'];
		Assert::same('Iron Plate', $ironPlate->name);
		Assert::true($ironPlate->canBeTrashed);

		$constructor = $schema->buildings['Desc_ConstructorMk1_C'];
		Assert::same('Constructor', $constructor->name);

		Assert::same('Iron Plate', $schema->recipes['Recipe_IronPlate_C']->name);
	}

	public function testRawExport(): void
	{
		$expected = $this->readFixture(__DIR__ . '/fixtures/expected/raw.json.gz');
		$actual = (new RawExporter)->export($this->getSchema()->clone());

		Assert::same(['' => $expected], $actual);
	}

	public function testWikiExport(): void
	{
		$result = (new WikiExporter)->export($this->getSchema()->clone());

		Assert::type('array', $result);
		Assert::same(['items', 'recipes', 'buildings'], array_keys($result));

		foreach ($result as $name => $content) {
			$expected = $this->readFixture(__DIR__ . '/fixtures/expected/wiki-' . $name . '.json.gz');
			Assert::same($expected, $content, 'wiki-' . $name);
		}
	}

	private function readFixture(string $path): string
	{
		$content = file_get_contents($path);
		if ($content === false) {
			throw new \RuntimeException('Cannot read fixture: ' . $path);
		}

		$decoded = gzdecode($content);
		if ($decoded === false) {
			throw new \RuntimeException('Cannot decode fixture: ' . $path);
		}

		return $decoded;
	}

}

(new DocsParserSnapshotTestCase)->run();
