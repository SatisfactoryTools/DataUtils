<?php declare(strict_types = 1);

namespace SFTools\Data\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrefixedOutput implements OutputInterface
{

	public function __construct(private readonly OutputInterface $parent, private readonly string $prefix)
	{
	}

	/** @param iterable<string>|string $messages */
	public function write(iterable|string $messages, bool $newline = false, int $options = 0): void
	{
		$this->parent->write($this->formatMessages($messages), $newline, $options);
	}

	/** @param iterable<string>|string $messages */
	public function writeln(iterable|string $messages, int $options = 0): void
	{
		$this->parent->writeln($this->formatMessages($messages), $options);
	}

	public function setVerbosity(int $level): void
	{
		$this->parent->setVerbosity($level);
	}

	public function getVerbosity(): int
	{
		return $this->parent->getVerbosity();
	}

	public function isQuiet(): bool
	{
		return $this->parent->isQuiet();
	}

	public function isVerbose(): bool
	{
		return $this->parent->isVerbose();
	}

	public function isVeryVerbose(): bool
	{
		return $this->parent->isVeryVerbose();
	}

	public function isDebug(): bool
	{
		return $this->parent->isDebug();
	}

	public function setDecorated(bool $decorated): void
	{
		$this->parent->setDecorated($decorated);
	}

	public function isDecorated(): bool
	{
		return $this->parent->isDecorated();
	}

	public function setFormatter(OutputFormatterInterface $formatter): void
	{
		$this->parent->setFormatter($formatter);
	}

	public function getFormatter(): OutputFormatterInterface
	{
		return $this->parent->getFormatter();
	}

	public function isSilent(): bool
	{
		return $this->parent->isSilent();
	}

	/**
	 * @param iterable<string>|string $messages
	 * @return list<string>|string
	 */
	private function formatMessages(iterable|string $messages): iterable|string
	{
		if (is_iterable($messages)) {
			$result = [];
			foreach ($messages as $message) {
				$result[] = $this->prefix . $message;
			}
			return $result;
		}

		return $this->prefix . $messages;
	}

}
