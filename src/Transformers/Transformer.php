<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers;

use SFTools\Data\Schema\DocsSchema;
use Symfony\Component\Console\Output\OutputInterface;

interface Transformer
{

	public function transform(DocsSchema $schema, OutputInterface $output): void;

}
