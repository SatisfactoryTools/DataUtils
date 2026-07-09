# SatisfactoryTools/DataUtils

This package can parse exported files from Satisfactory to generate info about recipes, items, schematics, etc.

## Running the parser

You need to either have Docker installed, or PHP 8.5+.

For docker, you can use the prebuilt image from GitHub Container Registry:

```shell
docker run --rm -v "$PWD:/data" ghcr.io/satisfactorytools/docs-parser [command]
```

Or build the container yourself once, and then just run it:

```shell
docker build -t docs-parser .
docker run --rm -v "$PWD:/data" docs-parser [command]
```

Note that the container only sees files under the mounted `/data` directory, so pass input/output paths through it, e.g.:

```shell
docker run --rm -v "$PWD:/data" docs-parser parse /data/Docs.json -o /data -f wiki
```

If you want to use it locally, install dependencies first (requires [Composer](https://getcomposer.org/) installed), then run it through PHP:

```shell
composer install
php bin/docsParser [command]
```

### Commands

If you run the app without any arguments, it'll show you a list of commands.

You can use `help [command]` to get info about supported parameters.

### Examples

Parse data, export to wiki format, and omit ficsmas data:

```shell
php bin/docsParser parse /path/to/docs.json -o /path/to/output/dir -f wiki --no-ficsmas
```

## Using in PHP

If you want to use the parser in your own PHP project, you can add it via composer

```shell
composer require satisfactory-tools/docs-parser
```

Example usage:

```php
use SFTools\Data\Parser\DocsParser;

$parser = new DocsParser;
$schema = $parser->parse(file_get_contents(__DIR__ . '/docs.json')); // $schema is SFTools\Data\Schema\DocsSchema

// You can also export the schema in any format you like
use SFTools\Data\Export\WikiExporter;

$exporter = new WikiExporter;
$output = $exporter->export($schema);

// $output is an array of [file prefix => file content]; the prefix may be an empty string for single-file formats
```

### Custom parsing

You can implement `SFTools\Data\Export\Exporter` interface to define your own format to export. You can also implement `SFTools\Data\Transformers\Transformer` to add more transformers (they can "fix" wrong datapoints or transform data to different values).

To run a transformer, you can do something like this:
```php
$transformer = new MyTransformer;
$schema->transform($transformer);
```

You can also specify a list of handlers/transformers to use in the DocsParser constructor:
```php
$parser = new DocsParser([new MyHandler], [new MyTransformer]);
```

This will skip the default handlers and use only the ones you specified. Passing `null` instead of an array will use the default handlers/transformers.
