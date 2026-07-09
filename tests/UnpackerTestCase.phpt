<?php declare(strict_types = 1);

namespace Tests\SFTools\Data;

require_once __DIR__ . '/bootstrap.php';

use SFTools\Data\Unpacker;
use Tester\Assert;
use Tester\TestCase;

class UnpackerTestCase extends TestCase
{

	/** @dataProvider getParseData */
	public function testParse(string $from, mixed $to, string $description): void
	{
		Assert::equal($to, Unpacker::unpack($from), $description);
	}

	/** @return array<mixed> */
	public function getParseData(): array
	{
		return [
			[
				'TestString',
				'TestString',
				'String',
			],
			[
				'(B=0,G=0,R=0,A=0)',
				[
					'B' => '0',
					'G' => '0',
					'R' => '0',
					'A' => '0',
				],
				'Color object',
			],
			[
				'(/Game/FactoryGame/Buildable/Factory/ConstructorMk1/Build_ConstructorMk1.Build_ConstructorMk1_C,/Game/FactoryGame/Buildable/-Shared/WorkBench/BP_WorkBenchComponent.BP_WorkBenchComponent_C,/Script/FactoryGame.FGBuildableAutomatedWorkBench)',
				[
					'/Game/FactoryGame/Buildable/Factory/ConstructorMk1/Build_ConstructorMk1.Build_ConstructorMk1_C',
					'/Game/FactoryGame/Buildable/-Shared/WorkBench/BP_WorkBenchComponent.BP_WorkBenchComponent_C',
					'/Script/FactoryGame.FGBuildableAutomatedWorkBench',
				],
				'List of craft in',
			],
			[
				'((ItemClass=BlueprintGeneratedClass\'"/Game/FactoryGame/Resource/Parts/QuartzCrystal/Desc_QuartzCrystal.Desc_QuartzCrystal_C"\',Amount=36),(ItemClass=BlueprintGeneratedClass\'"/Game/FactoryGame/Resource/Parts/Cable/Desc_Cable.Desc_Cable_C"\',Amount=28),(ItemClass=BlueprintGeneratedClass\'"/Game/FactoryGame/Resource/Parts/IronPlateReinforced/Desc_IronPlateReinforced.Desc_IronPlateReinforced_C"\',Amount=5))',
				[
					[
						'ItemClass' => 'BlueprintGeneratedClass\'"/Game/FactoryGame/Resource/Parts/QuartzCrystal/Desc_QuartzCrystal.Desc_QuartzCrystal_C"\'',
						'Amount' => '36',
					],
					[
						'ItemClass' => 'BlueprintGeneratedClass\'"/Game/FactoryGame/Resource/Parts/Cable/Desc_Cable.Desc_Cable_C"\'',
						'Amount' => '28',
					],
					[
						'ItemClass' => 'BlueprintGeneratedClass\'"/Game/FactoryGame/Resource/Parts/IronPlateReinforced/Desc_IronPlateReinforced.Desc_IronPlateReinforced_C"\'',
						'Amount' => '5',
					],
				],
				'Ingredient list',
			],
		];
	}

}

(new UnpackerTestCase)->run();
