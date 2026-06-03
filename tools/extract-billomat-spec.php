<?php

declare(strict_types=1);

/*
 * Liest den lokalen HTML-Spiegel der Billomat-API-Dokumentation und schreibt
 * eine deterministische, maschinenlesbare Spezifikation nach
 * docs/spec/billomat.json.
 *
 * Aufruf:
 *   composer extract:spec -- ~/Developer/billomat_fetcher/www.billomat.com/api
 *
 * Quelle ist read-only, das Skript verändert sie nicht.
 */

namespace Justpilot\Billomat\Tools;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

require __DIR__ . '/../vendor/autoload.php';

const BASE_PUBLIC_URL = 'https://www.billomat.com/api/';
const OUTPUT_PATH = __DIR__ . '/../docs/spec/billomat.json';

main($argv);

function main(array $argv): void
{
    if (count($argv) < 2) {
        fwrite(STDERR, "Usage: php tools/extract-billomat-spec.php <html-mirror-root>\n");
        fwrite(STDERR, "       <html-mirror-root> sollte den Ordner enthalten, in dem die\n");
        fwrite(STDERR, "       index.html der Hauptseite und die Ressourcen-Unterordner liegen.\n");
        exit(1);
    }

    $root = rtrim($argv[1], '/');
    if (!is_dir($root)) {
        fwrite(STDERR, "Pfad existiert nicht: {$root}\n");
        exit(1);
    }

    $resources = collectResources($root);
    ksort($resources, SORT_STRING);

    $payload = [
        'schema_version' => 1,
        'source_base_url' => BASE_PUBLIC_URL,
        'resources' => $resources,
    ];

    $json = json_encode(
        $payload,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
    );

    @mkdir(dirname(OUTPUT_PATH), 0o755, true);
    file_put_contents(OUTPUT_PATH, $json . "\n");

    fwrite(STDOUT, sprintf("%d Ressourcen nach %s geschrieben.\n", count($resources), OUTPUT_PATH));
}

/**
 * @return array<string, array<string, mixed>>
 */
function collectResources(string $root): array
{
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    $resources = [];
    foreach ($rii as $file) {
        if (!$file->isFile() || $file->getFilename() !== 'index.html') {
            continue;
        }

        $absolute = $file->getRealPath();
        $relativeDir = trim(substr($file->getPath(), strlen($root)), '/');
        if ($relativeDir === '') {
            continue; // /api/index.html ist nur die Übersicht
        }

        $resources[$relativeDir] = parseResourceFile($absolute, $relativeDir);
    }

    return $resources;
}

/**
 * @return array<string, mixed>
 */
function parseResourceFile(string $path, string $relativeDir): array
{
    $html = file_get_contents($path);
    if ($html === false) {
        throw new RuntimeException("Datei konnte nicht gelesen werden: {$path}");
    }

    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $xpath = new DOMXPath($dom);

    $title = textContent(($xpath->query('//h1')->item(0)));
    $contentNodes = collectContentNodes($xpath);

    [$endpoints, $resourceFilters] = parseEndpoints($contentNodes);

    $resource = [
        'title' => $title,
        'source_url' => BASE_PUBLIC_URL . $relativeDir . '/',
        'endpoints' => $endpoints,
    ];

    if ($resourceFilters !== []) {
        $resource['filters'] = $resourceFilters;
    }

    return $resource;
}

/**
 * Sammelt die DOM-Knoten zwischen der ersten <h1> und dem <footer>.
 * Nur strukturierende Elemente (h2, h3, pre, table, p) bleiben übrig.
 *
 * @return list<DOMElement>
 */
function collectContentNodes(DOMXPath $xpath): array
{
    $h1 = $xpath->query('//h1')->item(0);
    if (!$h1 instanceof DOMElement) {
        return [];
    }

    $relevantTags = ['h2', 'h3', 'pre', 'table', 'p'];
    $nodes = [];

    // Verfolge das h1, alle nachfolgenden h2/h3/pre/table/p in Dokument-Reihenfolge.
    // Wir nutzen einen einfachen Walk über alle Elemente, die nach h1 kommen und
    // nicht innerhalb des <footer> liegen.
    $body = $xpath->query('//body')->item(0);
    if (!$body instanceof DOMElement) {
        return [];
    }

    $stopAt = $xpath->query('//footer')->item(0);
    $state = ['h1Reached' => false, 'stopped' => false];

    walkDom(
        $body,
        static function (DOMNode $node) use (&$nodes, $h1, $stopAt, $relevantTags, &$state): bool {
            if ($state['stopped']) {
                return false;
            }
            if (!$node instanceof DOMElement) {
                return true;
            }
            if ($stopAt instanceof DOMElement && $node === $stopAt) {
                $state['stopped'] = true;
                return false;
            }
            if (!$state['h1Reached']) {
                if ($node === $h1) {
                    $state['h1Reached'] = true;
                }
                return true;
            }
            if (in_array(strtolower($node->tagName), $relevantTags, true)) {
                $nodes[] = $node;
            }
            return true;
        },
    );

    return $nodes;
}

/**
 * Tiefen-Walk in Dokument-Reihenfolge. Callback liefert false, um den
 * gesamten Walk abzubrechen.
 *
 * @param callable(DOMNode): bool $visit
 */
function walkDom(DOMNode $node, callable $visit): bool
{
    if ($visit($node) === false) {
        return false;
    }
    foreach ($node->childNodes as $child) {
        if (!walkDom($child, $visit)) {
            return false;
        }
    }
    return true;
}

function nodeIsDescendantOf(DOMNode $node, DOMNode $ancestor): bool
{
    for ($cur = $node; $cur !== null; $cur = $cur->parentNode) {
        if ($cur === $ancestor) {
            return true;
        }
    }
    return false;
}

/**
 * Wandelt die strukturierten Knoten in Endpunkt-Einträge um.
 *
 * @param list<DOMElement> $nodes
 *
 * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>}
 */
function parseEndpoints(array $nodes): array
{
    $endpoints = [];
    $resourceFilters = [];
    $current = null;
    $pendingSubsection = null;

    $flush = function () use (&$current, &$endpoints): void {
        if ($current !== null) {
            $endpoints[] = $current;
            $current = null;
        }
    };

    foreach ($nodes as $node) {
        $tag = strtolower($node->tagName);

        if ($tag === 'h2') {
            $flush();
            $current = [
                'name' => trim(textContent($node)),
                'method' => null,
                'path' => null,
                'description_de' => null,
                'fields' => [],
                'filters' => [],
            ];
            $pendingSubsection = null;
            continue;
        }

        if ($tag === 'h3') {
            $pendingSubsection = textContent($node);
            continue;
        }

        if ($tag === 'p' && $current !== null && $current['description_de'] === null) {
            $text = trim(textContent($node));
            if ($text !== '') {
                $current['description_de'] = $text;
            }
            continue;
        }

        if ($tag === 'pre') {
            $class = (string) $node->getAttribute('class');
            if (str_contains($class, 'brush: plain') && $current !== null && $current['method'] === null) {
                $line = trim(textContent($node));
                if (preg_match('/^(GET|POST|PUT|DELETE|PATCH)\s+(\S+)/', $line, $m)) {
                    $current['method'] = $m[1];
                    $current['path'] = $m[2];
                }
            }
            // XML-Beispiele werden absichtlich nicht extrahiert
            continue;
        }

        if ($tag === 'table') {
            $table = parseTable($node);
            if ($table === null) {
                continue;
            }
            if ($current === null) {
                // Tabelle vor erstem h2 -> ressourcen-weite Filter (selten, robuste Fallback-Logik)
                $resourceFilters = array_merge($resourceFilters, $table['rows']);
                continue;
            }
            if ($table['shape'] === 'fields') {
                $current['fields'] = array_merge($current['fields'], $table['rows']);
            } elseif ($table['shape'] === 'filters') {
                $current['filters'] = array_merge($current['filters'], $table['rows']);
            }
            $pendingSubsection = null;
        }
    }

    $flush();

    // Leere optionale Felder entfernen für saubere JSON
    foreach ($endpoints as $i => $endpoint) {
        if ($endpoint['description_de'] === null) {
            unset($endpoints[$i]['description_de']);
        }
        if ($endpoint['fields'] === []) {
            unset($endpoints[$i]['fields']);
        }
        if ($endpoint['filters'] === []) {
            unset($endpoints[$i]['filters']);
        }
    }

    return [array_values($endpoints), $resourceFilters];
}

/**
 * Erkennt zwei Tabellen-Formen aus der Billomat-Doku:
 *  - Feld-Tabelle: Spalten (XML-Element, Beschreibung, Typ, Default-Wert, Pflichtfeld)
 *  - Filter-Tabelle: Spalten (Parameter, Beschreibung)
 *
 * @return array{shape: string, rows: list<array<string, mixed>>}|null
 */
function parseTable(DOMElement $table): ?array
{
    $rows = [];
    $headerCells = [];
    foreach ($table->getElementsByTagName('tr') as $tr) {
        $cells = [];
        $allTh = true;
        foreach ($tr->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }
            $name = strtolower($child->tagName);
            if ($name !== 'th' && $name !== 'td') {
                continue;
            }
            if ($name !== 'th') {
                $allTh = false;
            }
            $cells[] = $child;
        }
        if ($cells === []) {
            continue;
        }
        $hasHeaderClass = $tr instanceof DOMElement && str_contains((string) $tr->getAttribute('class'), 'thead');
        $isHeader = $hasHeaderClass || ($allTh && $headerCells === []);
        if ($isHeader) {
            $headerCells = array_map(static fn (DOMElement $c) => normaliseHeader(textContent($c)), $cells);
            continue;
        }
        $rows[] = $cells;
    }

    if ($headerCells === []) {
        return null;
    }

    $shape = identifyTableShape($headerCells);
    if ($shape === 'unknown') {
        return null;
    }

    $parsedRows = [];
    foreach ($rows as $cells) {
        $row = mapRow($cells, $headerCells, $shape);
        if ($row !== null) {
            $parsedRows[] = $row;
        }
    }

    return ['shape' => $shape, 'rows' => $parsedRows];
}

/**
 * @param list<string> $headerCells
 */
function identifyTableShape(array $headerCells): string
{
    $normalised = array_map('strtolower', $headerCells);

    $hasField = in_array('xml-element', $normalised, true) || in_array('element', $normalised, true);
    $hasType = in_array('typ', $normalised, true);
    if ($hasField && $hasType) {
        return 'fields';
    }

    if (in_array('parameter', $normalised, true)) {
        return 'filters';
    }

    return 'unknown';
}

/**
 * @param list<DOMElement> $cells
 * @param list<string> $headerCells
 *
 * @return array<string, mixed>|null
 */
function mapRow(array $cells, array $headerCells, string $shape): ?array
{
    if (count($cells) === 0) {
        return null;
    }

    $name = trim(textContent($cells[0]));
    if ($name === '') {
        return null;
    }

    $get = static function (array $headers, array $cells, string $needle): ?string {
        $needle = strtolower($needle);
        foreach ($headers as $i => $header) {
            if (strtolower($header) === $needle && isset($cells[$i])) {
                return trim(textContent($cells[$i]));
            }
        }
        return null;
    };

    if ($shape === 'fields') {
        $type = $get($headerCells, $cells, 'Typ');
        $default = $get($headerCells, $cells, 'Default-Wert');
        $required = $get($headerCells, $cells, 'Pflichtfeld');
        $row = [
            'name' => $name,
            'type' => $type,
            'description_de' => $get($headerCells, $cells, 'Beschreibung'),
            'default' => $default === '' ? null : $default,
            'required' => $required !== null && trim($required) !== '',
        ];

        $enum = extractEnumCandidates($type ?? '');
        if ($enum !== null) {
            $row['enum'] = $enum;
        }

        return $row;
    }

    if ($shape === 'filters') {
        return [
            'name' => $name,
            'description_de' => $get($headerCells, $cells, 'Beschreibung'),
        ];
    }

    return null;
}

/**
 * Aus „TAX, NO_TAX, COUNTRY" oder „SETTINGS, ABSOLUTE, RELATIVE" ableiten.
 *
 * @return list<string>|null
 */
function extractEnumCandidates(string $type): ?array
{
    $type = trim($type);
    if ($type === '') {
        return null;
    }
    // Sieht aus wie Liste großgeschriebener Tokens, durch Komma getrennt
    if (!preg_match('/^[A-Z][A-Z0-9_]*(\s*,\s*[A-Z][A-Z0-9_]*)+$/', $type)) {
        return null;
    }
    $parts = preg_split('/\s*,\s*/', $type);
    return $parts === false ? null : array_values(array_filter($parts, static fn ($p) => $p !== ''));
}

function normaliseHeader(string $value): string
{
    return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
}

function textContent(?DOMNode $node): string
{
    if ($node === null) {
        return '';
    }
    $text = $node->textContent;
    // Whitespace zusammenfassen, aber nicht trimmen (Aufrufer trimmt)
    $text = preg_replace('/\s+/u', ' ', $text) ?? '';
    return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
