<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers;

use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\Fuel;
use SFTools\Data\Schema\Parts\ItemForm;
use Symfony\Component\Console\Output\OutputInterface;

class FuelTransformer implements Transformer
{

	public function transform(DocsSchema $schema, OutputInterface $output): void
	{
		$sorted = $schema->items;
		ksort($sorted);

		foreach ($schema->buildings as $building) {
			foreach ($building->fuel as $k => $fuel) {
				if ($fuel->acceptsAnySolidFuel) {
					unset($building->fuel[$k]);

					foreach ($sorted as $item) {
						if ($item->energy && !$item->form->isFluid()) {
							$building->fuel[] = $add = new Fuel;
							$add->item = $item->className;
						}
					}

					$building->fuel = array_values($building->fuel);
				} elseif ($fuel->item === 'FGItemDescriptorBiomass') {
					unset($building->fuel[$k]);

					foreach ($sorted as $item) {
						if ($item->energy && $item->isBiomass && !$item->form->isFluid()) {
							$building->fuel[] = $add = new Fuel;
							$add->item = $item->className;
						}
					}

					$building->fuel = array_values($building->fuel);
				}
			}
		}
	}

}
