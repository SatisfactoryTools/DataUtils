<?php declare(strict_types = 1);

namespace SFTools\Data\Parser;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use ReflectionClass;
use SFTools\Data\Parser\Handlers\Buildables\DronePortHandler;
use SFTools\Data\Parser\Handlers\Buildables\GeneratorHandler;
use SFTools\Data\Parser\Handlers\Buildables\ManufacturerHandler;
use SFTools\Data\Parser\Handlers\Buildables\MinerHandler;
use SFTools\Data\Parser\Handlers\Buildables\PowerLineHandler;
use SFTools\Data\Parser\Handlers\Buildables\VehicleHandler;
use SFTools\Data\Parser\Handlers\BuildingDescriptorHandler;
use SFTools\Data\Parser\Handlers\BuildingHandler;
use SFTools\Data\Parser\Handlers\Handler;
use SFTools\Data\Parser\Handlers\ItemHandler;
use SFTools\Data\Parser\Handlers\Items\AmmoHandler;
use SFTools\Data\Parser\Handlers\Items\BiomassHandler;
use SFTools\Data\Parser\Handlers\Items\ConsumableHandler;
use SFTools\Data\Parser\Handlers\Items\EquipableHandler;
use SFTools\Data\Parser\Handlers\Items\WeaponHandler;
use SFTools\Data\Parser\Handlers\MaterialHandler;
use SFTools\Data\Parser\Handlers\RecipeHandler;
use SFTools\Data\Parser\Handlers\SchematicHandler;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Transformers\AlternateRecipeTransformer;
use SFTools\Data\Transformers\CompatibilityRemovalTransformer;
use SFTools\Data\Transformers\FuelTransformer;
use SFTools\Data\Transformers\MissingEntryCompletionTransformer;
use SFTools\Data\Transformers\OrphanRemovalTransformer;
use SFTools\Data\Transformers\Transformer;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class DocsParser
{

	/** @var Handler[] */
	private array $handlers;
	/** @var Transformer[] */
	private array $transformers;

	/**
	 * @param Handler[] $handlers
	 * @param Transformer[] $transformers
	 */
	public function __construct(?array $handlers = null, ?array $transformers = null)
	{
		if ($handlers === null) {
			foreach ([
				AmmoHandler::class,
				BiomassHandler::class,
				BuildingDescriptorHandler::class,
				BuildingHandler::class,
				ConsumableHandler::class,
				DronePortHandler::class,
				EquipableHandler::class,
				GeneratorHandler::class,
				ItemHandler::class,
				ManufacturerHandler::class,
				MaterialHandler::class,
				MinerHandler::class,
				PowerLineHandler::class,
				RecipeHandler::class,
				SchematicHandler::class,
				VehicleHandler::class,
				WeaponHandler::class,
			] as $className) {
				$this->handlers[] = new $className;
			}
		} else {
			$this->handlers = $handlers;
		}

		if ($transformers === null) {
			foreach ([
				MissingEntryCompletionTransformer::class,
				CompatibilityRemovalTransformer::class,
				OrphanRemovalTransformer::class,
				FuelTransformer::class,
				AlternateRecipeTransformer::class,
			] as $className) {
				$this->transformers[] = new $className;
			}
		} else {
			$this->transformers = $transformers;
		}
	}

	public function parse(string $docsString, ?OutputInterface $output = null): DocsSchema
	{
		$schema = new DocsSchema;
		if (!$output) {
			$output = new NullOutput;
		}

		try {
			$data = Json::decode($docsString, forceArrays: true);
		} catch (JsonException) {
			try {
				$docsString = iconv('UTF-16LE', 'UTF-8', $docsString);
				if (!$docsString) {
					throw new InvalidSchemaException('Cannot convert encoding.');
				}
				$docsString = preg_replace('/^' . pack('H*','EFBBBF') . '/', '', $docsString);
				if (!$docsString) {
					throw new InvalidSchemaException('Error while removing BOM.');
				}

				$data = Json::decode($docsString, forceArrays: true);
			} catch (Throwable $e) {
				throw new InvalidSchemaException('Cannot parse Docs string: ' . $e->getMessage(), $e->getCode());
			}
		}

		if (!$data) {
			throw new InvalidSchemaException('Cannot parse Docs string, file does not contain any data.');
		}

		$output->writeln('Parsing docs string', OutputInterface::VERBOSITY_VERBOSE);

		/** @var array<array{'NativeClass': string, 'Classes': array<array<string, string>>}> $data */
		foreach ($data as $classGroup) {
			$nativeClass = Strings::replace($classGroup['NativeClass'], '/^.*?Class\'(.*?)\'$/', '$1');
			$output->writeln('Processing ' . $nativeClass, OutputInterface::VERBOSITY_VERY_VERBOSE);
			$classes = $classGroup['Classes'];
			$first = true;

			foreach ($classes as $class) {
				$classData = new ClassData($class, $nativeClass);

				$handled = false;
				foreach ($this->handlers as $handler) {
					if ($handler->canHandle($classData)) {
						$handled = true;
						if ($first) {
							$reflection = new ReflectionClass($handler);
							$output->writeln('- handled by ' . $reflection->getShortName(), OutputInterface::VERBOSITY_DEBUG);
						}
						$handler->handle($schema, $classData);
					}
				}

				if ($first && !$handled) {
					$output->writeln('<comment>Class ' . $nativeClass . ' was not handled!</comment>', OutputInterface::VERBOSITY_DEBUG);
				}
				$first = false;
			}

			$output->writeln('', OutputInterface::VERBOSITY_DEBUG);
		}

		foreach ($this->transformers as $transformer) {
			$schema->transform($transformer, $output);
		}

		return $schema;
	}

}
