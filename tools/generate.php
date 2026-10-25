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
// FLOAT-Felder mit diesen Namen sind Geldbeträge und werden zu Money statt float.
// Mengen, Steuersätze, Skonto-/Rabattsätze und Wechselkurse (`quote`) bleiben float.
const MONEY_FIELDS = [
    'amount', 'charge_amount', 'costs_gross', 'costs_net', 'discount_amount', 'finapi_transaction_amount',
    'open_amount', 'paid_amount', 'purchase_price', 'revenue_gross', 'revenue_net',
    'sales_price', 'sales_price2', 'sales_price3', 'sales_price4', 'sales_price5',
    'total', 'total_gross', 'total_gross_unreduced', 'total_net', 'total_net_unreduced', 'total_reduction', 'unit_price',
];

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
        /** @var array<string, mixed>|list<array<string, mixed>> $loaded */
        $loaded = require $definitionFile;
        $source = 'tools/codegen/definitions/' . basename($definitionFile);

        // Eine Datei darf mehrere Definitionen liefern (z. B. Tag und Tag-Cloud einer Ressource).
        $files = [];
        foreach (array_is_list($loaded) ? $loaded : [$loaded] as $definition) {
            $files = [...$files, ...renderResource($definition, $spec, $source)];
        }

        foreach ($files as $relativePath => $code) {
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
    // Ohne `specResource` (Billomat dokumentiert die Ressource nicht) entfällt der Drift-Abgleich;
    // alle Felder brauchen dann einen expliziten 'type'.
    $specFields = [];
    if (null !== ($slug = $definition['specResource'] ?? null)) {
        $resource = $spec['resources'][$slug] ?? throw new RuntimeException("Spec-Ressource '{$slug}' nicht gefunden ({$source}).");

        // Ohne Create-Options (reines Read-Model) nennt `specEndpoint` den Endpunkt für den Drift-Abgleich.
        $endpoint = $definition['createOptions']['endpoint'] ?? $definition['specEndpoint']
            ?? throw new RuntimeException("Weder 'createOptions' noch 'specEndpoint' angegeben ({$source}).");
        $specFields = specFields($resource['endpoints'], $endpoint, $source);
    }
    $fields = resolveFields($definition['fields'], $specFields, $source);

    $files = ['src/Model/' . $definition['model']['class'] . '.php' => renderModel($definition, $fields, $source)];
    if (isset($definition['createOptions'])) {
        $files['src/Api/' . $definition['createOptions']['class'] . '.php'] = renderOptions($definition, $definition['createOptions'], $fields, $source, true);
    }
    if (isset($definition['updateOptions'])) {
        $files['src/Api/' . $definition['updateOptions']['class'] . '.php'] = renderOptions($definition, $definition['updateOptions'], $fields, $source, false);
    }
    if (isset($definition['filter'])) {
        // `filter.specResource`: Spec-Ressource des Listen-Endpunkts, falls die Definition selbst keine hat.
        $filterSlug = $definition['filter']['specResource'] ?? $slug ?? throw new RuntimeException("Filter ohne Spec-Ressource ({$source}).");
        $filterResource = $spec['resources'][$filterSlug] ?? throw new RuntimeException("Spec-Ressource '{$filterSlug}' nicht gefunden ({$source}).");
        $files['src/Api/' . $definition['filter']['class'] . '.php'] = renderFilter($definition, $definition['filter'], $filterResource['endpoints'], $source);
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
        if ('float' === $type && \in_array($name, MONEY_FIELDS, true)) {
            $type = 'money';
        }
        if (\in_array($type, ['taxes', 'list'], true) && empty($field['readOnly'])) {
            throw new RuntimeException("Listen-Feld '{$name}' muss 'readOnly' sein ({$source}).");
        }
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
            'list' => $field['list'] ?? null,
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

    $listFields = array_values(array_filter($fields, isListField(...)));
    $scalarFields = array_values(array_filter($fields, static fn (array $f): bool => !isListField($f)));

    $imports = ['Justpilot\\Billomat\\Internal\\ScalarCaster', ...enumImports($fields)];
    if ($usesDate) {
        $imports[] = 'DateTimeImmutable';
    }
    if ([] !== $listFields) {
        $imports[] = 'Justpilot\\Billomat\\Internal\\ListCaster';
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
    $out .= 'final readonly class ' . $definition['model']['class'] . "\n{\n";
    // Listen-Felder brauchen den Element-Typ als @param am Konstruktor.
    if ([] !== $listFields) {
        $listTypes = array_map(listTypeHint(...), $listFields);
        $width = max(array_map(\strlen(...), $listTypes));
        $out .= docBlock(array_map(
            static fn (array $f, string $t): string => '@param ' . str_pad($t, $width) . ' $' . $f['property'],
            $listFields,
            $listTypes,
        ), '    ');
    }
    $out .= "    public function __construct(\n";

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
        if (isListField($field)) {
            $out .= ' = []';
        } elseif (empty($field['model']['required'])) {
            if (null !== $field['enumClass'] && !empty($field['model']['nonNull'])) {
                throw new RuntimeException("Nicht-nullbares Enum-Feld '{$field['wire']}' braucht 'required' ({$source}).");
            }
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
    if ($filterNulls && [] !== $listFields) {
        throw new RuntimeException("'filterNulls' und Listen-Felder lassen sich nicht kombinieren ({$source}).");
    }
    $out .= "    public function toArray(): array\n    {\n        " . ([] !== $listFields ? '$data = ' : 'return ') . ($filterNulls ? 'array_filter(' : '') . "[\n";
    $boolAsInt = !empty($definition['model']['boolAsInt']);
    foreach (exportFields($scalarFields, $definition['model']['toArray'] ?? null, $source) as $field) {
        $value = '$this->' . $field['property'];
        $value = match (true) {
            null !== $field['enumClass'] => $value . (empty($field['model']['nonNull']) ? '?->value' : '->value'),
            'date' === modelType($field) => $value . "?->format('Y-m-d')",
            'datetime' === modelType($field) => $value . '?->format(DATE_ATOM)',
            'money' === modelType($field) => $value . (empty($field['model']['nonNull']) ? '?->toString()' : '->toString()'),
            $boolAsInt && 'bool' === modelType($field) => boolAsIntExpression($value, !empty($field['model']['nonNull'])),
            default => $value,
        };
        // `model.key`: abweichender Schlüssel in toArray() (BC, z. B. nach einer Umbenennung bei Billomat).
        $out .= "            '" . ($field['model']['key'] ?? $field['wire']) . "' => " . $value . ",\n";
    }
    $out .= '        ]' . ($filterNulls ? ', static fn (mixed $v): bool => null !== $v)' : '') . ";\n";

    // Listen werden wie bei Billomat verschachtelt und nur ausgegeben, wenn sie Einträge haben.
    foreach ($listFields as $field) {
        $value = '$this->' . $field['property'];
        if ('taxes' === $field['type']) {
            $entry = "['tax' => {$value}]";
        } else {
            $class = $field['list']['class'];
            $entry = "[\n                '{$field['list']['item']}' => array_map(static fn ({$class} \$item): array => \$item->toArray(), {$value}),\n            ]";
        }
        $out .= "\n        if ([] !== {$value}) {\n            \$data['{$field['wire']}'] = {$entry};\n        }\n";
    }
    if ([] !== $listFields) {
        $out .= "\n        return \$data;\n";
    }

    return $out . "    }\n}\n";
}

/**
 * Verschachtelte Liste im Read-Model: `taxes` (Steuerzeilen) oder `list` (z. B. Positionen).
 *
 * @param array<string, mixed> $field
 */
function isListField(array $field): bool
{
    return \in_array($field['type'], ['taxes', 'list'], true);
}

/**
 * @param array<string, mixed> $field
 */
function listTypeHint(array $field): string
{
    return 'taxes' === $field['type']
        ? 'list<array{name: string, rate: float, amount: Money}>'
        : 'list<' . $field['list']['class'] . '>';
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
        'money' => 'Money::zero()',
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
        return (empty($field['model']['nonNull']) ? '?' : '') . $field['enumClass'];
    }
    if (isListField($field)) {
        return 'array';
    }
    // `mixed` schließt null bereits ein.
    if ('mixed' === modelType($field)) {
        return 'mixed';
    }

    $type = match (modelType($field)) {
        'date', 'datetime' => 'DateTimeImmutable',
        'money' => 'Money',
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
        'money' => 'toMoneyOrNull',
        'bool' => 'toBoolOrNull',
        'date', 'datetime' => 'toDateTimeOrNull',
        default => 'toStringOrNull',
    };

    // `model.sources`: Billomat-Felder, die der Reihe nach probiert werden (Fallback).
    $sources = $field['model']['sources'] ?? [$field['wire']];
    $lookup = implode(' ?? ', array_map(static fn (string $s): string => "\$data['{$s}']", $sources));
    $expression = 'ScalarCaster::' . $caster . '(' . $lookup . ' ?? null)';

    if ('taxes' === $field['type']) {
        return "ListCaster::toTaxList(\$data['{$field['wire']}']['tax'] ?? null)";
    }
    // Ein einzelnes Element liefert Billomat als Objekt statt als Liste – erkennbar an der `id`.
    if ('list' === $field['type']) {
        return "ListCaster::toList(\$data['{$field['wire']}']['{$field['list']['item']}'] ?? null, 'id', {$field['list']['class']}::fromArray(...))";
    }

    // `mixed`: Wert unverändert übernehmen (z. B. Eigenschaftswerte, deren Typ von der Eigenschaft abhängt).
    if ('mixed' === modelType($field)) {
        return $lookup . ' ?? null';
    }

    // Nicht-nullbare Enums brauchen ein `fromApi(string): self`, das unbekannte Werte auf einen Default abbildet.
    if (null !== $field['enumClass'] && !empty($field['model']['nonNull'])) {
        return $field['enumClass'] . "::fromApi(ScalarCaster::toStringOrNull({$lookup} ?? null) ?? '')";
    }
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
    // `create.type` / `update.type`: abweichender Typ in Create- bzw. Update-Options (BC).
    $writable = array_map(
        static fn (array $f): array => ['type' => $f[$isCreate ? 'create' : 'update']['type'] ?? $f['type']] + $f,
        $writable,
    );

    // `dateObjects`: Datumsfelder sind DateTimeImmutable statt String (BC).
    $dateObjects = !empty($options['dateObjects']);

    $out = "<?php\n\ndeclare(strict_types=1);\n\nnamespace Justpilot\\Billomat\\Api;\n\n";
    $imports = enumImports($writable);
    if ($dateObjects && \in_array('date', array_column($writable, 'type'), true)) {
        $imports[] = 'DateTimeImmutable';
    }
    if (\in_array('money', array_column($writable, 'type'), true)) {
        $imports[] = 'Justpilot\\Billomat\\Model\\Money';
    }
    sort($imports);
    foreach ($imports as $import) {
        $out .= "use {$import};\n";
    }
    if ([] !== $imports) {
        $out .= "\n";
    }
    $out .= classDoc($options['doc'], $definition['docUrl'], $source, 'Doku:');
    $out .= 'final readonly class ' . $options['class'] . " implements WriteOptions\n{\n";

    // Pflichtfelder beim Anlegen stehen vorne im Konstruktor, danach alle optionalen Felder.
    $required = $isCreate
        ? array_values(array_filter($writable, static fn (array $f): bool => !empty($f['create']['constructor'])))
        : [];
    $ordered = [...$required, ...array_values(array_filter($writable, static fn (array $f): bool => !\in_array($f, $required, true)))];

    // `items`: Positionen, die beim Anlegen verschachtelt mitgesendet werden.
    $items = $isCreate ? ($options['items'] ?? null) : null;

    $out .= "    public function __construct(\n";
    foreach ($ordered as $field) {
        $out .= docBlock(optionsDocLines($field, $isCreate), '        ');
        $out .= '        public ' . optionsParamType($field, $isCreate, $dateObjects) . ' $' . $field['property'] . optionsDefault($field, $isCreate) . ",\n";
    }
    if (null !== $items) {
        $out .= "        /**\n         * Positionen, die beim Anlegen mitgesendet werden.\n         *\n         * @var list<{$items['class']}>\n         */\n";
        $out .= "        public array \$items = [],\n";
    }
    $out .= "    ) {\n    }\n\n";

    // Wither: Kopie mit einem geänderten Feld (clone with, PHP 8.5).
    foreach ($ordered as $field) {
        $property = $field['property'];
        $out .= "    /** Kopie mit geändertem `{$property}`. */\n";
        $out .= '    public function with' . ucfirst($property) . '(' . optionsParamType($field, $isCreate, $dateObjects) . " \${$property}): self\n";
        $out .= "    {\n        return clone(\$this, ['{$property}' => \${$property}]);\n    }\n\n";
    }
    if (null !== $items) {
        $out .= "    /** Kopie mit einer zusätzlichen Position. */\n";
        $out .= "    public function withItem({$items['class']} \$item): self\n    {\n        return clone(\$this, ['items' => [...\$this->items, \$item]]);\n    }\n\n";
    }

    $out .= "    /**\n     * Serialisiert die Options in ein Billomat-Payload-Array.\n";
    if (!$isCreate) {
        $out .= "     *\n     * Nicht gesetzte Felder (null) fehlen im Payload, {@see Clear::Field} wird als null gesendet und leert das Feld.\n";
    }
    $out .= "     *\n     * @return array<string,mixed>\n     */\n";
    $entries = '';
    $filterNulls = false;
    foreach (exportFields($writable, $options['toArray'] ?? null, $source) as $field) {
        $property = '$this->' . $field['property'];
        $nonNull = $isCreate && (!empty($field['create']['nonNull']) || !empty($field['create']['constructor']));
        $filterNulls = $filterNulls || !$nonNull;
        $entries .= "            '" . $field['wire'] . "' => " . optionsExportExpression($field, $property, $isCreate, $nonNull, $dateObjects, $boolAsInt) . ",\n";
    }

    $out .= "    public function toArray(): array\n    {\n";
    // Sind alle Felder nicht-nullbar, gibt es nichts zu filtern.
    $payload = match (true) {
        !$filterNulls => "[\n" . $entries . "        ]",
        $isCreate => "array_filter([\n" . $entries . "        ], static fn (mixed \$v): bool => null !== \$v)",
        // Update: nicht gesetzte Felder entfernen, Clear::Field als null senden.
        default => "array_map(\n            static fn (mixed \$v): mixed => \$v instanceof Clear ? null : \$v,\n            array_filter([\n"
            . preg_replace('/^/m', '    ', rtrim($entries, "\n")) . "\n            ], static fn (mixed \$v): bool => null !== \$v),\n        )",
    };
    if (null === $items) {
        return $out . '        return ' . $payload . ";\n    }\n}\n";
    }

    $out .= '        $data = ' . $payload . ";\n\n";
    $out .= "        if ([] !== \$this->items) {\n            \$data['{$items['wire']}'] = [\n";
    $out .= "                '{$items['item']}' => array_map(static fn ({$items['class']} \$item): array => \$item->toArray(), \$this->items),\n";
    $out .= "            ];\n        }\n\n        return \$data;\n    }\n}\n";

    return $out;
}

/**
 * Filter-Felder eines Listen-Endpunkts aus der Spec, mit Typ aus Name/Beschreibung bzw. Definition.
 *
 * Typen: int, string, bool, date, enum, list<int>, list<string>, list<enum>.
 *
 * @param list<array<string, mixed>> $endpoints
 * @param array<string, mixed>       $filter
 *
 * @return list<array<string, mixed>>
 */
function resolveFilterFields(array $endpoints, array $filter, string $source): array
{
    $specFilters = null;
    foreach ($endpoints as $endpoint) {
        if (($endpoint['method'] ?? null) . ' ' . ($endpoint['path'] ?? '') === $filter['endpoint']) {
            $specFilters = $endpoint['filters'] ?? [];
        }
    }
    if (null === $specFilters) {
        throw new RuntimeException("Filter-Endpunkt '{$filter['endpoint']}' nicht in der Spec gefunden ({$source}).");
    }

    $overrides = $filter['fields'] ?? [];
    $unknown = array_diff(array_keys($overrides), array_column($specFilters, 'name'));
    if ([] !== $unknown) {
        throw new RuntimeException('Filter-Felder stehen nicht in der Spec (' . $source . '): ' . implode(', ', $unknown));
    }

    $resolved = [];
    foreach ($specFilters as $specFilter) {
        $name = (string) $specFilter['name'];
        $override = $overrides[$name] ?? [];
        if (!empty($override['omit'])) {
            continue;
        }
        $doc = (string) ($override['doc'] ?? $specFilter['description_de'] ?? throw new RuntimeException("Filter '{$name}' hat keine Doku ({$source})."));
        // „mehrere Werte kommasepariert“ / „Mehrere Stati können per Komma getrennt werden“
        $multiple = 1 === preg_match('/kommasepariert|per Komma getrennt/i', $doc);
        $enum = $override['enum'] ?? null;

        $type = $override['type'] ?? match (true) {
            null !== $enum => $multiple ? 'list<enum>' : 'enum',
            \in_array($name, ['from', 'to'], true) => 'date',
            'tags' === $name => 'list<string>',
            str_ends_with($name, '_id') => $multiple ? 'list<int>' : 'int',
            str_starts_with($name, 'is_') => 'bool',
            default => 'string',
        };

        $resolved[] = [
            'wire' => $name,
            'property' => lcfirst(str_replace('_', '', ucwords($name, '_'))),
            'type' => $type,
            'enumClass' => $enum,
            'doc' => $doc,
        ];
    }

    return $resolved;
}

/**
 * Erzeugt eine `*Filter`-Klasse (implements ListFilter) für einen Listen-Endpunkt.
 *
 * @param array<string, mixed>       $definition
 * @param array<string, mixed>       $filter
 * @param list<array<string, mixed>> $endpoints
 */
function renderFilter(array $definition, array $filter, array $endpoints, string $source): string
{
    $fields = [
        ...resolveFilterFields($endpoints, $filter, $source),
        ['wire' => 'order_by', 'property' => 'orderBy', 'type' => 'orderBy', 'enumClass' => null, 'doc' => 'Sortierung, z. B. `OrderBy::desc(\'date\')->thenAsc(\'name\')`.'],
        ['wire' => 'page', 'property' => 'page', 'type' => 'int', 'enumClass' => null, 'doc' => 'Seite (ab 1); `iterateAll()` setzt sie selbst.'],
        ['wire' => 'per_page', 'property' => 'perPage', 'type' => 'int', 'enumClass' => null, 'doc' => 'Einträge pro Seite (Billomat: Standard 100, maximal 1000).'],
    ];

    $out = "<?php\n\ndeclare(strict_types=1);\n\nnamespace Justpilot\\Billomat\\Api;\n\n";
    $imports = enumImports($fields);
    if (\in_array('date', array_column($fields, 'type'), true)) {
        $imports[] = 'DateTimeImmutable';
    }
    sort($imports);
    foreach ($imports as $import) {
        $out .= "use {$import};\n";
    }
    if ([] !== $imports) {
        $out .= "\n";
    }
    $out .= classDoc([
        "Typisierter Filter für {$filter['endpoint']}.",
        '',
        'Nicht gesetzte Felder (null bzw. leere Liste) fehlen im Query; Mehrfachwerte',
        'werden kommasepariert gesendet und von Billomat ODER-verknüpft. Weitere',
        'Query-Parameter (z. B. benutzerdefinierte Attribute) gehen über `extra`.',
    ], $definition['docUrl'], $source, 'Doku:');
    $out .= 'final readonly class ' . $filter['class'] . " implements ListFilter\n{\n";

    $out .= "    public function __construct(\n";
    foreach ($fields as $field) {
        // Die Zusammenfassung endet mit Punkt (PHP-CS-Fixer: phpdoc_summary).
        $summary = 1 === preg_match('/[.!?]$/u', $field['doc']) ? $field['doc'] : $field['doc'] . '.';
        $lines = [$summary, ...('orderBy' === $field['type'] ? [] : ['', 'Billomat-Filter: ' . $field['wire']])];
        if (str_starts_with($field['type'], 'list<')) {
            $lines = [...$lines, '', '@var list<' . filterElementType($field) . '>'];
        }
        $out .= docBlock($lines, '        ');
        $out .= '        public ' . filterParamType($field) . ' $' . $field['property'] . (str_starts_with($field['type'], 'list<') ? ' = []' : ' = null') . ",\n";
    }
    $out .= "        /**\n         * Weitere Query-Parameter; überschreiben gleichnamige Felder.\n         *\n         * @var array<string, scalar|array<scalar>>\n         */\n";
    $out .= "        public array \$extra = [],\n";
    $out .= "    ) {\n    }\n\n";

    // Wither: Kopie mit einem geänderten Feld; Listen-Felder nehmen die Werte variadisch.
    foreach ($fields as $field) {
        $property = $field['property'];
        $out .= "    /** Kopie mit geändertem `{$property}`. */\n";
        if (str_starts_with($field['type'], 'list<')) {
            $out .= '    public function with' . ucfirst($property) . '(' . filterElementType($field) . " ...\${$property}): self\n";
            $out .= "    {\n        return clone(\$this, ['{$property}' => array_values(\${$property})]);\n    }\n\n";
        } else {
            $out .= '    public function with' . ucfirst($property) . '(' . filterParamType($field) . " \${$property}): self\n";
            $out .= "    {\n        return clone(\$this, ['{$property}' => \${$property}]);\n    }\n\n";
        }
    }
    $out .= "    /**\n     * Kopie mit weiteren Query-Parametern.\n     *\n     * @param array<string, scalar|array<scalar>> \$extra\n     */\n";
    $out .= "    public function withExtra(array \$extra): self\n    {\n        return clone(\$this, ['extra' => \$extra]);\n    }\n\n";

    $out .= "    public function toQuery(): array\n    {\n        return [\n            ...array_filter([\n";
    foreach ($fields as $field) {
        $out .= "                '" . $field['wire'] . "' => " . filterExportExpression($field, '$this->' . $field['property']) . ",\n";
    }
    $out .= "            ], static fn (mixed \$v): bool => null !== \$v),\n            ...\$this->extra,\n        ];\n    }\n}\n";

    return $out;
}

/**
 * @param array<string, mixed> $field
 */
function filterElementType(array $field): string
{
    return match ($field['type']) {
        'list<int>' => 'int',
        'list<string>' => 'string',
        'list<enum>' => $field['enumClass'],
        default => throw new LogicException("Kein Listen-Typ: {$field['type']}"),
    };
}

/**
 * @param array<string, mixed> $field
 */
function filterParamType(array $field): string
{
    return match ($field['type']) {
        'int', 'string', 'bool' => '?' . $field['type'],
        'date' => '?DateTimeImmutable',
        'enum' => '?' . $field['enumClass'],
        'orderBy' => '?OrderBy',
        'list<int>', 'list<string>', 'list<enum>' => 'array',
        default => throw new LogicException("Unbekannter Filter-Typ: {$field['type']}"),
    };
}

/**
 * @param array<string, mixed> $field
 */
function filterExportExpression(array $field, string $value): string
{
    return match ($field['type']) {
        'int', 'string' => $value,
        'bool' => "null === {$value} ? null : ({$value} ? 1 : 0)",
        'date' => "{$value}?->format('Y-m-d')",
        'enum' => "{$value}?->value",
        'orderBy' => "{$value}?->toQuery()",
        'list<int>', 'list<string>' => "[] === {$value} ? null : implode(',', {$value})",
        'list<enum>' => "[] === {$value} ? null : implode(',', array_map(static fn ({$field['enumClass']} \$v): string => \$v->value, {$value}))",
        default => throw new LogicException("Unbekannter Filter-Typ: {$field['type']}"),
    };
}

/**
 * PHP-Typ eines Options-Parameters; Update-Felder akzeptieren zusätzlich {@see Clear}.
 *
 * @param array<string, mixed> $field
 */
function optionsParamType(array $field, bool $isCreate, bool $dateObjects): string
{
    $type = optionsPhpType($field, $dateObjects);
    if ('mixed' === $type) {
        return 'mixed';
    }
    if (!$isCreate) {
        return $type . '|Clear|null';
    }

    return !empty($field['create']['nonNull']) || !empty($field['create']['constructor']) ? $type : '?' . $type;
}

/**
 * @param array<string, mixed> $field
 */
function optionsDefault(array $field, bool $isCreate): string
{
    if ($isCreate && !empty($field['create']['constructor'])) {
        return \array_key_exists('default', $field['create']) ? ' = ' . var_export($field['create']['default'], true) : '';
    }
    if ($isCreate && !empty($field['create']['nonNull'])) {
        return ' = ' . var_export($field['create']['default'], true);
    }

    return ' = null';
}

/**
 * Ausdruck, mit dem ein Feld in toArray() serialisiert wird.
 *
 * In Update-Options kann ein Feld auch {@see Clear} sein – das wird unverändert durchgereicht.
 *
 * @param array<string, mixed> $field
 */
function optionsExportExpression(array $field, string $value, bool $isCreate, bool $nonNull, bool $dateObjects, bool $boolAsInt): string
{
    $isDate = $dateObjects && 'date' === $field['type'];
    $isBool = $boolAsInt && 'bool' === $field['type'];

    if (!$isCreate) {
        return match (true) {
            null !== $field['enumClass'] => "{$value} instanceof {$field['enumClass']} ? {$value}->value : {$value}",
            $isDate => "{$value} instanceof DateTimeImmutable ? {$value}->format('Y-m-d') : {$value}",
            'money' === $field['type'] => "{$value} instanceof Money ? {$value}->toString() : {$value}",
            $isBool => "\\is_bool({$value}) ? ({$value} ? 1 : 0) : {$value}",
            default => $value,
        };
    }

    if (null !== $field['enumClass']) {
        $value .= $nonNull ? '->value' : '?->value';
    }
    if ($isDate) {
        $value .= ($nonNull ? '->' : '?->') . "format('Y-m-d')";
    }
    if ('money' === $field['type']) {
        $value .= ($nonNull ? '->' : '?->') . 'toString()';
    }
    if ($isBool) {
        $value = boolAsIntExpression($value, $nonNull);
    }

    return $value;
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
function optionsPhpType(array $field, bool $dateObjects = false): string
{
    return $field['enumClass'] ?? match ($field['type']) {
        'date' => $dateObjects ? 'DateTimeImmutable' : 'string',
        'money' => 'Money',
        default => $field['type'],
    };
}

/**
 * Felder für toArray(): ohne Liste in Feldreihenfolge, mit Liste (Billomat-Feldnamen)
 * genau diese Felder in dieser Reihenfolge (BC, wenn toArray() anders sortiert ist als der Konstruktor).
 *
 * @param list<array<string, mixed>> $fields
 * @param list<string>|null $order
 *
 * @return list<array<string, mixed>>
 */
function exportFields(array $fields, ?array $order, string $source): array
{
    if (null === $order) {
        return $fields;
    }

    $byWire = array_column($fields, null, 'wire');

    return array_map(
        static fn (string $wire): array => $byWire[$wire] ?? throw new RuntimeException("toArray-Feld '{$wire}' ist nicht definiert ({$source})."),
        $order,
    );
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
