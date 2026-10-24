<?php

declare(strict_types=1);

/*
 * Erzeugt Read-Models und Create-/Update-Options aus docs/spec/billomat.json
 * plus einer Definition pro Ressource unter tools/codegen/definitions/.
 *
 * Aufruf:
 *   composer generate           # Dateien unter src/ (neu) schreiben
 *   composer generate:check     # nur prüfen, ob src/ dem Generator-Stand entspricht
 *
 * Die Spec liefert Typen, Defaults und Enum-Werte der schreibbaren Felder.
 * Die Definition liefert Reihenfolge, deutsche Doku, read-only Felder aus den
 * API-Antworten und BC-bedingte Abweichungen. Ein Spec-Feld, das in der
 * Definition fehlt, bricht den Lauf ab – so fällt Drift sofort auf.
 */

namespace Justpilot\Billomat\Tools;

use RuntimeException;

const PROJECT_ROOT = __DIR__ . '/..';
const SPEC_PATH = PROJECT_ROOT . '/docs/spec/billomat.json';
const DEFINITIONS_DIR = __DIR__ . '/codegen/definitions';

try {
    main($argv);
} catch (RuntimeException $e) {
    fwrite(STDERR, 'Fehler: ' . $e->getMessage() . "\n");
    exit(1);
}

/**
 * @param list<string> $argv
 */
function main(array $argv): void
{
    $check = \in_array('--check', $argv, true);

    /** @var array{resources: array<string, array{endpoints: list<array<string, mixed>>}>} $spec */
    $spec = json_decode((string) file_get_contents(SPEC_PATH), true, flags: JSON_THROW_ON_ERROR);

    $definitionFiles = glob(DEFINITIONS_DIR . '/*.php') ?: [];
    sort($definitionFiles);

    $outdated = [];

    foreach ($definitionFiles as $definitionFile) {
        /** @var array<string, mixed> $definition */
        $definition = require $definitionFile;
        $source = 'tools/codegen/definitions/' . basename($definitionFile);

        foreach (renderResource($definition, $spec, $source) as $relativePath => $code) {
            $path = PROJECT_ROOT . '/' . $relativePath;
            $current = is_file($path) ? file_get_contents($path) : null;

            if ($current === $code) {
                continue;
            }

            if ($check) {
                $outdated[] = $relativePath;
                continue;
            }

            file_put_contents($path, $code);
            echo "geschrieben: {$relativePath}\n";
        }
    }

    if ($check && [] !== $outdated) {
        fwrite(STDERR, "Generierte Dateien weichen vom Generator-Stand ab:\n");
        foreach ($outdated as $relativePath) {
            fwrite(STDERR, "  - {$relativePath}\n");
        }
        fwrite(STDERR, "Definition unter tools/codegen/definitions/ anpassen und `composer generate` ausführen.\n");
        exit(1);
    }

    if ($check) {
        echo "Generierte Dateien sind aktuell.\n";
    }
}

/**
 * @param array<string, mixed> $definition
 * @param array{resources: array<string, array{endpoints: list<array<string, mixed>>}>} $spec
 *
 * @return array<string, string> relativer Pfad → Dateiinhalt
 */
function renderResource(array $definition, array $spec, string $source): array
{
    $slug = $definition['specResource'];
    $resource = $spec['resources'][$slug] ?? throw new RuntimeException("Spec-Ressource '{$slug}' nicht gefunden ({$source}).");

    // Ohne Create-Options (reines Read-Model) nennt `specEndpoint` den Endpunkt für den Drift-Abgleich.
    $endpoint = $definition['createOptions']['endpoint'] ?? $definition['specEndpoint']
        ?? throw new RuntimeException("Weder 'createOptions' noch 'specEndpoint' angegeben ({$source}).");
    $specFields = specFields($resource['endpoints'], $endpoint, $source);
    $fields = resolveFields($definition['fields'], $specFields, $source);

    $files = ['src/Model/' . $definition['model']['class'] . '.php' => renderModel($definition, $fields, $source)];
    if (isset($definition['createOptions'])) {
        $files['src/Api/' . $definition['createOptions']['class'] . '.php'] = renderOptions($definition, $definition['createOptions'], $fields, $source, true);
    }
    if (isset($definition['updateOptions'])) {
        $files['src/Api/' . $definition['updateOptions']['class'] . '.php'] = renderOptions($definition, $definition['updateOptions'], $fields, $source, false);
    }

    return $files;
}

/**
 * @param list<array<string, mixed>> $endpoints
 *
 * @return array<string, array<string, mixed>> Feldname → Spec-Feld
 */
function specFields(array $endpoints, string $endpoint, string $source): array
{
    [$method, $path] = explode(' ', $endpoint, 2);

    foreach ($endpoints as $candidate) {
        if ($candidate['method'] === $method && $candidate['path'] === $path) {
            $fields = [];
            foreach ($candidate['fields'] ?? [] as $field) {
                $fields[$field['name']] = $field;
            }

            return $fields;
        }
    }

    throw new RuntimeException("Endpunkt '{$endpoint}' nicht in der Spec gefunden ({$source}).");
}

/**
 * Führt Definition und Spec zu einer Feldliste zusammen und prüft beide gegeneinander.
 *
 * @param array<string, array<string, mixed>> $definitionFields
 * @param array<string, array<string, mixed>> $specFields
 *
 * @return list<array<string, mixed>>
 */
function resolveFields(array $definitionFields, array $specFields, string $source): array
{
    $missing = array_diff(array_keys($specFields), array_keys($definitionFields));
    if ([] !== $missing) {
        throw new RuntimeException('Spec-Felder fehlen in der Definition (' . $source . '): ' . implode(', ', $missing));
    }

    $resolved = [];

    foreach ($definitionFields as $name => $field) {
        $specField = $specFields[$name] ?? null;

        if (null === $specField && !isset($field['type'])) {
            throw new RuntimeException("Feld '{$name}' steht nicht in der Spec und braucht einen expliziten 'type' ({$source}).");
        }

        if (null === $specField && empty($field['readOnly']) && empty($field['writable'])) {
            throw new RuntimeException("Feld '{$name}' steht nicht in der Spec: 'readOnly' oder 'writable' angeben ({$source}).");
        }

        $type = $field['type'] ?? phpTypeFromSpec((string) $specField['type']);
        $enum = $specField['enum'] ?? null;

        $resolved[] = [
            'wire' => $name,
            'property' => $field['model']['property'] ?? lcfirst(str_replace('_', '', ucwords($name, '_'))),
            'type' => $type,
            'doc' => $field['doc'] ?? ($specField['description_de'] ?? null) ?? throw new RuntimeException("Feld '{$name}' hat keine Doku ({$source})."),
            'details' => $field['details'] ?? [],
            'enum' => $enum,
            'enumClass' => $field['enum'] ?? null,
            'specType' => null !== $specField ? (null !== $enum ? implode(' | ', $enum) : (string) $specField['type']) : null,
            'specDefault' => $specField['default'] ?? null,
            'writable' => empty($field['readOnly']),
            'inModel' => empty($field['optionsOnly']),
            'deprecated' => $field['deprecated'] ?? null,
            'model' => $field['model'] ?? [],
            'create' => $field['create'] ?? [],
            'update' => $field['update'] ?? [],
        ];
    }

    return $resolved;
}

function phpTypeFromSpec(string $specType): string
{
    return match ($specType) {
        'INT' => 'int',
        'FLOAT' => 'float',
        'BOOL' => 'bool',
        'DATE' => 'date',
        default => 'string',
    };
}

// -------------------------------------------------------------------------
// Read-Model
// -------------------------------------------------------------------------

/**
 * @param array<string, mixed> $definition
 * @param list<array<string, mixed>> $fields
 */
function renderModel(array $definition, array $fields, string $source): string
{
    $fields = array_values(array_filter($fields, static fn (array $f): bool => $f['inModel']));
    $types = array_map(modelType(...), $fields);
    $usesDate = [] !== array_intersect($types, ['date', 'datetime']);
    $usesAtom = \in_array('datetime', $types, true);

    $imports = ['Justpilot\\Billomat\\Internal\\ScalarCaster', ...enumImports($fields)];
    if ($usesDate) {
        $imports[] = 'DateTimeImmutable';
    }
    sort($imports);

    $out = "<?php\n\ndeclare(strict_types=1);\n\nnamespace Justpilot\\Billomat\\Model;\n\n";
    foreach ($imports as $import) {
        $out .= "use {$import};\n";
    }
    if ($usesAtom) {
        $out .= "\nuse const DATE_ATOM;\n";
    }

    $out .= "\n" . classDoc($definition['model']['doc'], $definition['docUrl'], $source, 'Dokumentation:');
    $out .= 'final readonly class ' . $definition['model']['class'] . "\n{\n    public function __construct(\n";

    foreach ($fields as $field) {
        $lines = [$field['doc']];
        $details = $field['details'];
        if (null !== $field['enum']) {
            $details[] = 'Mögliche Werte: ' . implode(', ', $field['enum']);
        }
        if ([] !== $details) {
            $lines = [...$lines, '', ...$details];
        }
        if (null !== $field['deprecated']) {
            $lines = [...$lines, '', '@deprecated ' . $field['deprecated']];
        }

        $out .= docBlock($lines, '        ');
        $out .= '        public ' . modelTypeHint($field) . ' $' . $field['property'];
        if (empty($field['model']['required'])) {
            $out .= ' = ' . (empty($field['model']['nonNull']) ? 'null' : nonNullFallback(modelType($field)));
        }
        $out .= ",\n";
    }

    $out .= "    ) {\n    }\n\n";

    $out .= "    /**\n     * Hydriert das Model aus einem Billomat-Response-Array.\n     *\n     * @param array<string,mixed> \$data\n     */\n";
    $out .= "    public static function fromArray(array \$data): self\n    {\n        return new self(\n";
    foreach ($fields as $field) {
        $out .= '            ' . $field['property'] . ': ' . hydrateExpression($field) . ",\n";
    }
    $out .= "        );\n    }\n\n";

    $out .= "    /**\n     * Exportiert das Model als Array mit Billomat-Feldnamen.\n     *\n     * Praktisch für Debugging oder Log-Ausgaben.\n     *\n     * @return array<string,mixed>\n     */\n";
    // `model.filterNulls`: toArray() lässt Null-Felder weg (BC).
    $filterNulls = !empty($definition['model']['filterNulls']);
    $out .= "    public function toArray(): array\n    {\n        return " . ($filterNulls ? 'array_filter(' : '') . "[\n";
    $boolAsInt = !empty($definition['model']['boolAsInt']);
    foreach ($fields as $field) {
        $value = '$this->' . $field['property'];
        $value = match (true) {
            null !== $field['enumClass'] => $value . '?->value',
            'date' === modelType($field) => $value . "?->format('Y-m-d')",
            'datetime' === modelType($field) => $value . '?->format(DATE_ATOM)',
            $boolAsInt && 'bool' === modelType($field) => boolAsIntExpression($value, !empty($field['model']['nonNull'])),
            default => $value,
        };
        // `model.key`: abweichender Schlüssel in toArray() (BC, z. B. nach einer Umbenennung bei Billomat).
        $out .= "            '" . ($field['model']['key'] ?? $field['wire']) . "' => " . $value . ",\n";
    }
    $out .= '        ]' . ($filterNulls ? ', static fn (mixed $v): bool => null !== $v)' : '') . ";\n    }\n}\n";

    return $out;
}

/**
 * Typ im Read-Model; `model.type` überschreibt den Typ aus Spec/Definition (BC).
 *
 * @param array<string, mixed> $field
 */
function modelType(array $field): string
{
    return $field['model']['type'] ?? $field['type'];
}

/**
 * Ersatzwert für nicht-nullbare Felder, wenn Billomat nichts liefert.
 */
function nonNullFallback(string $type): string
{
    return match ($type) {
        'int' => '0',
        'float' => '0.0',
        'bool' => 'false',
        default => "''",
    };
}

/**
 * @param array<string, mixed> $field
 */
function modelTypeHint(array $field): string
{
    if (null !== $field['enumClass']) {
        return '?' . $field['enumClass'];
    }

    $type = match (modelType($field)) {
        'date', 'datetime' => 'DateTimeImmutable',
        default => modelType($field),
    };

    return empty($field['model']['nonNull']) ? '?' . $type : $type;
}

/**
 * @param array<string, mixed> $field
 */
function hydrateExpression(array $field): string
{
    $caster = match (modelType($field)) {
        'int' => 'toIntOrNull',
        'float' => 'toFloatOrNull',
        'bool' => 'toBoolOrNull',
        'date', 'datetime' => 'toDateTimeOrNull',
        default => 'toStringOrNull',
    };

    // `model.sources`: Billomat-Felder, die der Reihe nach probiert werden (Fallback).
    $sources = $field['model']['sources'] ?? [$field['wire']];
    $lookup = implode(' ?? ', array_map(static fn (string $s): string => "\$data['{$s}']", $sources));
    $expression = 'ScalarCaster::' . $caster . '(' . $lookup . ' ?? null)';

    // Unbekannte oder leere Werte werden zu null statt eine Exception zu werfen.
    if (null !== $field['enumClass']) {
        return $field['enumClass'] . "::tryFrom(ScalarCaster::toStringOrNull({$lookup} ?? null) ?? '')";
    }

    return empty($field['model']['nonNull']) ? $expression : $expression . ' ?? ' . nonNullFallback(modelType($field));
}

// -------------------------------------------------------------------------
// Create-/Update-Options
// -------------------------------------------------------------------------

/**
 * @param array<string, mixed> $definition
 * @param array<string, mixed> $options
 * @param list<array<string, mixed>> $fields
 */
function renderOptions(array $definition, array $options, array $fields, string $source, bool $isCreate): string
{
    $boolAsInt = !empty($options['boolAsInt']);
    $writable = array_values(array_filter(
        $fields,
        static fn (array $f): bool => $f['writable'] && ($isCreate || empty($f['update']['skip'])),
    ));

    $out = "<?php\n\ndeclare(strict_types=1);\n\nnamespace Justpilot\\Billomat\\Api;\n\n";
    $imports = enumImports($writable);
    sort($imports);
    foreach ($imports as $import) {
        $out .= "use {$import};\n";
    }
    if ([] !== $imports) {
        $out .= "\n";
    }
    $out .= classDoc($options['doc'], $definition['docUrl'], $source, 'Doku:');
    $out .= 'final class ' . $options['class'] . "\n{\n";

    // Pflichtfelder beim Anlegen werden als Konstruktor-Parameter erzwungen.
    $promoted = $isCreate
        ? array_values(array_filter($writable, static fn (array $f): bool => !empty($f['create']['constructor'])))
        : [];
    if ([] !== $promoted) {
        $out .= "    public function __construct(\n";
        foreach ($promoted as $field) {
            $out .= docBlock(optionsDocLines($field, $isCreate), '        ');
            $out .= '        public ' . optionsPhpType($field) . ' $' . $field['property'];
            if (\array_key_exists('default', $field['create'])) {
                $out .= ' = ' . var_export($field['create']['default'], true);
            }
            $out .= ",\n";
        }
        $out .= "    ) {\n    }\n\n";
    }

    foreach ($writable as $field) {
        if (\in_array($field, $promoted, true)) {
            continue;
        }

        $nonNull = $isCreate && !empty($field['create']['nonNull']);

        $out .= docBlock(optionsDocLines($field, $isCreate), '    ');
        $out .= '    public ' . ($nonNull ? '' : '?') . optionsPhpType($field) . ' $' . $field['property'] . ' = '
            . ($nonNull ? var_export($field['create']['default'], true) : 'null') . ";\n\n";
    }

    $out .= "    /**\n     * Serialisiert die Options in ein Billomat-Payload-Array.\n     *\n     * @return array<string,mixed>\n     */\n";
    $entries = '';
    $filterNulls = false;
    foreach ($writable as $field) {
        $value = '$this->' . $field['property'];
        $nonNull = $isCreate && (!empty($field['create']['nonNull']) || !empty($field['create']['constructor']));
        $filterNulls = $filterNulls || !$nonNull;
        if (null !== $field['enumClass']) {
            $value .= $nonNull ? '->value' : '?->value';
        }
        if ($boolAsInt && 'bool' === $field['type']) {
            $value = boolAsIntExpression($value, $nonNull);
        }
        $entries .= "            '" . $field['wire'] . "' => " . $value . ",\n";
    }

    $out .= "    public function toArray(): array\n    {\n";
    // Sind alle Felder nicht-nullbar, gibt es nichts zu filtern.
    if (!$filterNulls) {
        return $out . "        return [\n" . $entries . "        ];\n    }\n}\n";
    }
    $out .= "        \$data = [\n" . $entries . "        ];\n\n        // Null-Felder entfernen\n";
    $out .= "        return array_filter(\$data, static fn (mixed \$v): bool => null !== \$v);\n    }\n}\n";

    return $out;
}

/**
 * Billomat erwartet bzw. liefert an manchen Stellen 1/0 statt true/false.
 */
function boolAsIntExpression(string $value, bool $nonNull): string
{
    return $nonNull ? "({$value} ? 1 : 0)" : "null === {$value} ? null : ({$value} ? 1 : 0)";
}

/**
 * @param array<string, mixed> $field
 */
function optionsPhpType(array $field): string
{
    return $field['enumClass'] ?? ('date' === $field['type'] ? 'string' : $field['type']);
}

/**
 * Use-Statements für die Enum-Klassen der Felder (`enum` in der Definition).
 *
 * @param list<array<string, mixed>> $fields
 *
 * @return list<string>
 */
function enumImports(array $fields): array
{
    $enums = array_filter(array_column($fields, 'enumClass'));

    return array_values(array_unique(array_map(
        static fn (string $enum): string => 'Justpilot\\Billomat\\Model\\Enum\\' . $enum,
        $enums,
    )));
}

/**
 * @param array<string, mixed> $field
 *
 * @return list<string>
 */
function optionsDocLines(array $field, bool $isCreate): array
{
    $lines = [$field['doc']];
    $details = $field['details'];
    if ('date' === $field['type']) {
        $details[] = 'Format: YYYY-MM-DD';
    }
    if ([] !== $details) {
        $lines = [...$lines, '', ...$details];
    }

    $lines[] = '';
    $lines[] = 'Billomat-Feld: ' . $field['wire'];
    if (null !== $field['specType']) {
        $lines[] = 'Typ: ' . $field['specType'];
    }
    // Spec-Defaults gelten beim Anlegen; beim Update bleibt ein nicht gesendetes Feld unverändert.
    if ($isCreate && null !== $field['specDefault']) {
        $lines[] = 'Default: ' . $field['specDefault'];
    }

    return $lines;
}

// -------------------------------------------------------------------------
// Doku-Helfer
// -------------------------------------------------------------------------

/**
 * @param list<string> $doc
 */
function classDoc(array $doc, string $docUrl, string $source, string $urlLabel): string
{
    return docBlock([
        ...$doc,
        '',
        $urlLabel,
        $docUrl,
        '',
        'Generiert aus ' . $source . ' – nicht von Hand bearbeiten,',
        'sondern die Definition anpassen und `composer generate` ausführen.',
    ], '');
}

/**
 * @param list<string> $lines
 */
function docBlock(array $lines, string $indent): string
{
    if (1 === \count($lines)) {
        return $indent . '/** ' . $lines[0] . " */\n";
    }

    $out = $indent . "/**\n";
    foreach ($lines as $line) {
        $out .= '' === $line ? $indent . " *\n" : $indent . ' * ' . $line . "\n";
    }

    return $out . $indent . " */\n";
}
