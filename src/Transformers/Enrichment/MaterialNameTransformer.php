<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers\Enrichment;

use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Transformers\BaseTransformer;
use Symfony\Component\Console\Output\OutputInterface;

class MaterialNameTransformer extends BaseTransformer
{

	public function transform(DocsSchema $schema, OutputInterface $output): void
	{
		foreach ($schema->buildings as $building) {
			if ($building->className === 'Desc_Wall_8x4_02_C' || str_contains($building->className, 'Steel')) {
				$building->name .= ' (Steel)';
			} elseif (str_contains($building->className, 'Polished')) {
				$building->name .= ' (Polished)';
			} elseif (str_contains($building->className, 'Metal')) {
				$building->name .= ' (Metal)';
			} elseif (str_contains($building->className, 'Conc')) {
				$building->name .= ' (Concrete)';
			} elseif (str_contains($building->className, 'Asphalt')) {
				$building->name .= ' (Asphalt)';
			} elseif (str_contains($building->className, 'Ficsit')) {
				$building->name .= ' (FICSIT)';
			} elseif (str_contains($building->className, 'Window')) {
				$building->name .= ' (Window)';
			} elseif (str_contains($building->className, 'Tar')) {
				$building->name .= ' (Tar)';
			} elseif (str_contains($building->className, '_A_')) {
				$building->name .= ' (FICSIT)';
			} elseif (str_contains($building->className, 'Grip')) {
				$building->name .= ' (Grip)';
			}
		}
	}

}
