<?php declare(strict_types = 1);

namespace SFTools\Data\Schema\Parts;

class Color
{

	/** @param array<string, string> $data */
	public function __construct(array $data = [])
	{
		$this->r = (int) ($data['R'] ?? 0);
		$this->g = (int) ($data['G'] ?? 0);
		$this->b = (int) ($data['B'] ?? 0);
		$this->a = (int) ($data['A'] ?? 0);
	}

	public int $r;
	public int $g;
	public int $b;
	public int $a;

	public function toHex(): string
	{
		return sprintf("#%02x%02x%02x", $this->r, $this->g, $this->b);
	}

}
