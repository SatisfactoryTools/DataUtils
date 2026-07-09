<?php declare(strict_types = 1);

namespace SFTools\Data;

use Nette\Tokenizer\Stream;
use Nette\Tokenizer\Tokenizer;
use Nette\Utils\Strings;

class Unpacker
{

	private const OpeningBrace = 'ob';
	private const ClosingBrace = 'cb';
	private const String = 'str';
	private const Equal = 'eq';
	private const Separator = 'sep';

	private static Tokenizer $tokenizer;

	/** @return string|mixed[] */
	public static function unpack(string $value): string|array
	{
		if (!$value) {
			return '';
		}

		$stream = self::getTokenizer()->tokenize($value);

		$result = self::parseValue($stream);

		if ($stream->nextToken()) {
			throw new UnpackerException('Unexpected "' . $stream->currentValue() . '", expected end of file.');
		}

		return $result;
	}

	/** @return string|mixed[] */
	private static function parseValue(Stream $stream): string|array
	{
		$token = $stream->nextToken();

		if (!$token) {
			throw new UnpackerException('Unexpected end of file at ' . $stream->currentToken()?->offset);
		}

		return match ($token->type) {
			self::String => $stream->currentValue() ?? '',
			self::OpeningBrace => self::parseArrayOrObject($stream),
			default => throw new UnpackerException('Expected string or opening brace at ' . $stream->currentToken()?->offset),
		};
	}

	/** @return mixed[] */
	private static function parseArrayOrObject(Stream $stream): array
	{
		if ($stream->isNext(self::ClosingBrace)) {
			$stream->nextToken();
			return [];
		}

		if ($stream->isNext(self::String)) {
			$value = self::trimString($stream->nextValue() ?? '');
			return $stream->isNext(self::Equal) ? self::parseObject($stream, $value) : self::parseArray($stream, $value);
		}

		return self::parseArray($stream, self::parseValue($stream));
	}

	/** @return array<mixed> */
	private static function parseObject(Stream $stream, string $firstKey): array
	{
		self::consumeToken($stream, self::Equal, 'Equal sign');

		$value = self::parseValue($stream);
		$result = [
			$firstKey => $value,
		];

		while ($stream->isNext(self::Separator)) {
			$stream->nextToken();

			$key = self::consumeToken($stream, self::String, 'Array key identifier');
			self::consumeToken($stream, self::Equal, 'Equal sign');
			$result[$key] = self::parseValue($stream);
		}

		self::consumeToken($stream, self::ClosingBrace, 'Closing brace');

		return $result;
	}

	/** @return array<mixed> */
	private static function parseArray(Stream $stream, mixed $firstItem): array
	{
		$result = [
			$firstItem
		];

		while ($stream->isNext(self::Separator)) {
			$stream->nextToken();

			$result[] = self::parseValue($stream);
		}

		self::consumeToken($stream, self::ClosingBrace, 'Closing brace');

		return $result;
	}

	private static function consumeToken(Stream $stream, string $type, string $name): string
	{
		if (!$stream->isNext($type)) {
			throw new UnpackerException($name . ' expected at ' . $stream->currentToken()?->offset);
		}
		return $stream->nextValue() ?? '';
	}

	private static function trimString(string $string): string
	{
		return trim($string, '"');
	}

	private static function getTokenizer(): Tokenizer
	{
		if (!isset(self::$tokenizer)) {
			self::$tokenizer = new Tokenizer([
				self::OpeningBrace => '\(',
				self::ClosingBrace => '\)',
				self::String => '[a-zA-Z0-9:\\/.\'"_\-]+',
				self::Equal => '=',
				self::Separator => ',',
			]);
		}

		return self::$tokenizer;
	}

}
