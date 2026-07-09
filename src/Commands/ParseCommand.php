<?php declare(strict_types = 1);

namespace SFTools\Data\Commands;

use Nette\InvalidArgumentException;
use SFTools\Data\Export\Exporter;
use SFTools\Data\Export\OldBetaToolsExporter;
use SFTools\Data\Export\OldToolsExporter;
use SFTools\Data\Export\RawExporter;
use SFTools\Data\Export\WikiExporter;
use SFTools\Data\Parser\DocsParser;
use SFTools\Data\Transformers\ChristmasRemovalTransformer;
use SFTools\Data\Transformers\Enrichment\MaterialNameTransformer;
use SFTools\Data\Transformers\Enrichment\SchematicNameTransformer;
use SFTools\Data\Transformers\FluidAmountTransformer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'parse', description: 'Parse a Docs.json file (or a language variant)')]
class ParseCommand extends Command
{

	private const string OutputRaw = 'raw';
	private const string OutputWiki = 'wiki';
	private const string OutputOldTools = 'oldtools';
	private const string OutputOldBetaTools = 'oldbetatools';

	private const string ArgumentPath = 'path';

	private const string OptionFormat = 'format';
	private const string OptionOutput = 'output';

	private const string OptionFicsmas = 'ficsmas';
	private const string OptionFluidConversion = 'fluid-conversion';

	private const string OptionExperimental = 'experimental';
	private const string OptionEnrich = 'enrich';

	protected function configure(): void
	{
		$this->addArgument(self::ArgumentPath, InputArgument::REQUIRED, 'Path to Docs.json file.');

		$this->addOption(self::OptionFormat, 'f', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Export format (e.g. "wiki")', [self::OutputRaw]);
		$this->addOption(self::OptionOutput, 'o', InputOption::VALUE_REQUIRED, 'Output directory', getcwd());
		$this->addOption(self::OptionFicsmas, null, InputOption::VALUE_NEGATABLE, 'Include ficsmas related data', true);
		$this->addOption(self::OptionFluidConversion, null, InputOption::VALUE_NEGATABLE, 'Convert fluid amounts from liters to m3', true);
		$this->addOption(self::OptionExperimental, null, InputOption::VALUE_NONE, 'Marks data as coming from experimental version (used in some formats like wiki)');
		$this->addOption(self::OptionEnrich, null, InputOption::VALUE_NONE, 'Improve data by enriching it with suffixes like materials and such');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		/** @var string $argPath */
		$argPath = $input->getArgument(self::ArgumentPath);

		/** @var string[] $optExports */
		$optExports = (array) $input->getOption(self::OptionFormat);

		/** @var string $optOutput */
		$optOutput = $input->getOption(self::OptionOutput);

		/** @var bool $optFicsmas */
		$optFicsmas = $input->getOption(self::OptionFicsmas);

		/** @var bool $optFluidConversion */
		$optFluidConversion = $input->getOption(self::OptionFluidConversion);


		$exporters = [];
		foreach ($optExports as $outputType) {
			try {
				$exporters[$outputType] = $this->createExporter($outputType);
			} catch (InvalidArgumentException $e) {
				$output->writeln($e->getMessage());
				return Command::FAILURE;
			}
		}

		if (!count($exporters)) {
			$output->writeln('<error>No export types set.</error>');
			return Command::FAILURE;
		}

		$docsPath = realpath($argPath);
		if (!$docsPath || !is_file($docsPath)) {
			$output->writeln('<error>File not found: ' . $docsPath . '</error>');
			return Command::FAILURE;
		}

		$parser = new DocsParser();
		$content = file_get_contents($docsPath);
		if (!$content) {
			$output->writeln('<error>Could not read file or file empty: ' . $docsPath . '</error>');
			return Command::FAILURE;
		}

		$parsed = $parser->parse($content, $output);

		if (!$optFicsmas) {
			$parsed->transform(new ChristmasRemovalTransformer, $output);
		}

		if ($optFluidConversion) {
			$parsed->transform(new FluidAmountTransformer, $output);
		}

		if ($input->getOption(self::OptionEnrich)) {
			$parsed->transform(new MaterialNameTransformer, $output);
			$parsed->transform(new SchematicNameTransformer, $output);
		}

		$path = realpath($optOutput);
		if (!$path || !is_dir($path)) {
			$output->writeln('<error>Output directory doesn\'t exist: ' . $optOutput . '</error>');
			return Command::FAILURE;
		}

		$experimental = (bool) $input->getOption(self::OptionExperimental);

		foreach ($exporters as $name => $exporter) {
			$output->writeln('<info>Exporting to ' . $path . '</info>');

			$basePath = $path . DIRECTORY_SEPARATOR . $name;
			$result = $exporter->export($parsed->clone(), $experimental);

			foreach ($result as $key => $item) {
				if ($key !== '') {
					$key .= '-';
				}
				$fileName = $basePath . '-' . $key . 'export' . ($experimental ? '-ex' : '') . '.json';
				$output->writeln('<info>Exported ' . $fileName . '</info>');
				file_put_contents($fileName, $item);
			}
		}

		return Command::SUCCESS;
	}

	private function createExporter(string $outputType): Exporter
	{
		return match ($outputType) {
			self::OutputRaw => new RawExporter,
			self::OutputWiki => new WikiExporter,
			self::OutputOldTools => new OldToolsExporter,
			self::OutputOldBetaTools => new OldBetaToolsExporter,
			default => throw new InvalidArgumentException('<error>Invalid export type: ' . $outputType . '</error>')
		};
	}


}
