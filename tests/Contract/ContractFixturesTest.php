<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Contract;

use BackedEnum;
use Closure;
use Generator;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Model\Role;
use Justpilot\Billomat\Model\Settings;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use ReflectionProperty;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const ARRAY_FILTER_USE_KEY;
use const JSON_THROW_ON_ERROR;

/**
 * Contract-Tests gegen aufgezeichnete Sandbox-Antworten (tests/Fixtures/Contract).
 *
 * Jede Fixture läuft durch die echte `*Api`-Methode. Geprüft wird, dass
 *  - die Hydration klappt und so viele Datensätze liefert wie die Antwort enthält,
 *  - jedes Feld der Antwort dem Model bekannt ist (`$extra` bleibt leer, auch in
 *    verschachtelten Models wie Positionen oder Steuerzeilen),
 *  - kein nicht-leerer Wert beim Hydrieren verloren geht – etwa ein Enum-Wert,
 *    den das SDK nicht kennt, oder ein Datum, das sich nicht parsen lässt.
 *
 * Neu aufnehmen: `composer fixtures:record` (siehe docs/testing.md).
 */
#[CoversNothing]
final class ContractFixturesTest extends TestCase
{
    private const string FIXTURE_DIR = __DIR__.'/../Fixtures/Contract';

    #[Test]
    #[DataProvider('fixtures')]
    public function fixtureMatchesModel(string $resource, string $kind): void
    {
        $unexpected = array_filter(
            $this->problems($resource, $kind),
            static fn (string $path): bool => !\array_key_exists(self::withoutIndex($path), self::knownDrift()),
            ARRAY_FILTER_USE_KEY,
        );

        self::assertSame([], $unexpected, "{$resource}/{$kind}: Drift zwischen API und Model");
    }

    #[Test]
    public function knownDriftStillOccurs(): void
    {
        $seen = [];
        foreach (self::fixtures() as [$resource, $kind]) {
            foreach (array_keys($this->problems($resource, $kind)) as $path) {
                $seen[self::withoutIndex($path)] = true;
            }
        }

        $stale = array_values(array_diff(array_keys(self::knownDrift()), array_keys($seen)));

        self::assertSame([], $stale, 'knownDrift()-Einträge treten nicht mehr auf und können entfernt werden');
    }

    /**
     * Bekannte Abweichungen, die der Test bewusst hinnimmt: Model-Pfad.Feld => Begründung.
     *
     * Ein Eintrag, der nicht mehr auftritt, lässt den Test fehlschlagen – dann bitte entfernen.
     * Aktuell gibt es keine; als Methode, damit PHPStan die leere Liste nicht als `array{}` festschreibt.
     *
     * @return array<string, string>
     */
    private static function knownDrift(): array
    {
        return [];
    }

    /**
     * Hydriert eine Fixture über die echte API-Methode und sammelt alle Abweichungen.
     *
     * @return array<string, string> Model-Pfad.Feld => Problem
     */
    private function problems(string $resource, string $kind): array
    {
        $calls = $this->calls()[$resource] ?? null;
        $call = null === $calls ? null : ('list' === $kind ? $calls['list'] : $calls['get']);
        self::assertNotNull($calls, "{$resource}: keine API-Methode zugeordnet, bitte in ContractFixturesTest::calls() ergänzen");
        self::assertNotNull($call, "{$resource}/{$kind}: keine API-Methode zugeordnet, bitte in ContractFixturesTest::calls() ergänzen");

        $body = (string) file_get_contents(self::FIXTURE_DIR."/{$resource}/{$kind}.json");
        $raw = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($raw);

        $mock = new MockHttpClient(new MockResponse($body, ['http_code' => 200]));
        $billomat = new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key'), new Psr18Client($mock));
        $result = $call($billomat);

        $records = $this->records($raw, $resource, $calls['key'], $kind);
        $models = 'list' === $kind ? $result : [$result];
        self::assertIsArray($models);
        self::assertCount(\count($records), $models, "{$resource}/{$kind}: Anzahl der Datensätze");

        $problems = [];
        foreach (array_values($models) as $i => $model) {
            self::assertIsObject($model, "{$resource}/{$kind}: Datensatz {$i} nicht hydriert");
            $record = $records[$i];

            foreach (self::unknownFields($model, $record) as $path => $keys) {
                foreach ($keys as $key) {
                    $problems["{$path}.{$key}"] = 'unbekanntes Feld';
                }
            }

            $class = new ReflectionObject($model)->getShortName();
            foreach ($this->lostValues($model, $record) as $field => $value) {
                $problems["{$class}.{$field}"] = "Wert ging beim Hydrieren verloren: \"{$value}\"";
            }
        }

        return $problems;
    }

    /**
     * `Invoice.items[0].foo` → `Invoice.items.foo` für den Abgleich mit knownDrift().
     */
    private static function withoutIndex(string $path): string
    {
        return (string) preg_replace('/\[\d+\]/', '', $path);
    }

    /**
     * @return Generator<string, array{string, string}>
     */
    public static function fixtures(): Generator
    {
        $files = glob(self::FIXTURE_DIR.'/*/*.json');
        self::assertIsArray($files);
        sort($files);

        foreach ($files as $file) {
            $resource = basename(\dirname($file));
            $kind = basename($file, '.json');

            yield "{$resource}/{$kind}" => [$resource, $kind];
        }
    }

    /**
     * Ressource (Pfad ohne Slash) → Envelope-Key eines Datensatzes und die SDK-Aufrufe.
     *
     * @return array<string, array{key: string, list: (Closure(BillomatClient): mixed)|null, get: (Closure(BillomatClient): mixed)|null}>
     */
    private function calls(): array
    {
        return [
            'clients' => ['key' => 'client', 'list' => static fn (BillomatClient $b): array => $b->clients->list(), 'get' => static fn (BillomatClient $b): ?object => $b->clients->get(1)],
            'contacts' => ['key' => 'contact', 'list' => static fn (BillomatClient $b): array => $b->contacts->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->contacts->get(1)],
            'client-tags' => ['key' => 'client-tag', 'list' => static fn (BillomatClient $b): array => $b->clients->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->clients->tags->get(1)],
            'client-properties' => ['key' => 'client-property', 'list' => static fn (BillomatClient $b): array => $b->clients->properties->list(), 'get' => static fn (BillomatClient $b): ?object => $b->clients->properties->get(1)],
            'client-property-values' => ['key' => 'client-property-value', 'list' => static fn (BillomatClient $b): array => $b->clients->propertyValues->list(), 'get' => static fn (BillomatClient $b): ?object => $b->clients->propertyValues->get(1)],
            'suppliers' => ['key' => 'supplier', 'list' => static fn (BillomatClient $b): array => $b->suppliers->list(), 'get' => static fn (BillomatClient $b): ?object => $b->suppliers->get(1)],
            'supplier-tags' => ['key' => 'supplier-tag', 'list' => static fn (BillomatClient $b): array => $b->suppliers->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->suppliers->tags->get(1)],
            'supplier-properties' => ['key' => 'supplier-property', 'list' => static fn (BillomatClient $b): array => $b->suppliers->properties->list(), 'get' => static fn (BillomatClient $b): ?object => $b->suppliers->properties->get(1)],
            'supplier-property-values' => ['key' => 'supplier-property-value', 'list' => static fn (BillomatClient $b): array => $b->suppliers->propertyValues->list(), 'get' => static fn (BillomatClient $b): ?object => $b->suppliers->propertyValues->get(1)],
            'articles' => ['key' => 'article', 'list' => static fn (BillomatClient $b): array => $b->articles->list(), 'get' => static fn (BillomatClient $b): ?object => $b->articles->get(1)],
            'article-tags' => ['key' => 'article-tag', 'list' => static fn (BillomatClient $b): array => $b->articles->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->articles->tags->get(1)],
            'article-properties' => ['key' => 'article-property', 'list' => static fn (BillomatClient $b): array => $b->articles->properties->list(), 'get' => static fn (BillomatClient $b): ?object => $b->articles->properties->get(1)],
            'article-property-values' => ['key' => 'article-property-value', 'list' => static fn (BillomatClient $b): array => $b->articles->propertyValues->list(), 'get' => static fn (BillomatClient $b): ?object => $b->articles->propertyValues->get(1)],
            'units' => ['key' => 'unit', 'list' => static fn (BillomatClient $b): array => $b->units->list(), 'get' => static fn (BillomatClient $b): ?object => $b->units->get(1)],
            'taxes' => ['key' => 'tax', 'list' => static fn (BillomatClient $b): array => $b->taxes->list(), 'get' => static fn (BillomatClient $b): ?object => $b->taxes->get(1)],
            'invoices' => ['key' => 'invoice', 'list' => static fn (BillomatClient $b): array => $b->invoices->list(), 'get' => static fn (BillomatClient $b): ?object => $b->invoices->get(1)],
            'invoice-items' => ['key' => 'invoice-item', 'list' => static fn (BillomatClient $b): array => $b->invoices->items->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->invoices->items->get(1)],
            'invoice-payments' => ['key' => 'invoice-payment', 'list' => static fn (BillomatClient $b): array => $b->invoices->payments->list(), 'get' => static fn (BillomatClient $b): ?object => $b->invoices->payments->get(1)],
            'invoice-comments' => ['key' => 'invoice-comment', 'list' => static fn (BillomatClient $b): array => $b->invoices->comments->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->invoices->comments->get(1)],
            'invoice-tags' => ['key' => 'invoice-tag', 'list' => static fn (BillomatClient $b): array => $b->invoices->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->invoices->tags->get(1)],
            'offers' => ['key' => 'offer', 'list' => static fn (BillomatClient $b): array => $b->offers->list(), 'get' => static fn (BillomatClient $b): ?object => $b->offers->get(1)],
            'offer-items' => ['key' => 'offer-item', 'list' => static fn (BillomatClient $b): array => $b->offers->items->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->offers->items->get(1)],
            'offer-comments' => ['key' => 'offer-comment', 'list' => static fn (BillomatClient $b): array => $b->offers->comments->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->offers->comments->get(1)],
            'offer-tags' => ['key' => 'offer-tag', 'list' => static fn (BillomatClient $b): array => $b->offers->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->offers->tags->get(1)],
            'confirmations' => ['key' => 'confirmation', 'list' => static fn (BillomatClient $b): array => $b->confirmations->list(), 'get' => static fn (BillomatClient $b): ?object => $b->confirmations->get(1)],
            'confirmation-items' => ['key' => 'confirmation-item', 'list' => static fn (BillomatClient $b): array => $b->confirmations->items->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->confirmations->items->get(1)],
            'confirmation-comments' => ['key' => 'confirmation-comment', 'list' => static fn (BillomatClient $b): array => $b->confirmations->comments->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->confirmations->comments->get(1)],
            'confirmation-tags' => ['key' => 'confirmation-tag', 'list' => static fn (BillomatClient $b): array => $b->confirmations->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->confirmations->tags->get(1)],
            'credit-notes' => ['key' => 'credit-note', 'list' => static fn (BillomatClient $b): array => $b->creditNotes->list(), 'get' => static fn (BillomatClient $b): ?object => $b->creditNotes->get(1)],
            'credit-note-items' => ['key' => 'credit-note-item', 'list' => static fn (BillomatClient $b): array => $b->creditNotes->items->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->creditNotes->items->get(1)],
            'credit-note-payments' => ['key' => 'credit-note-payment', 'list' => static fn (BillomatClient $b): array => $b->creditNotes->payments->list(), 'get' => static fn (BillomatClient $b): ?object => $b->creditNotes->payments->get(1)],
            'credit-note-comments' => ['key' => 'credit-note-comment', 'list' => static fn (BillomatClient $b): array => $b->creditNotes->comments->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->creditNotes->comments->get(1)],
            'credit-note-tags' => ['key' => 'credit-note-tag', 'list' => static fn (BillomatClient $b): array => $b->creditNotes->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->creditNotes->tags->get(1)],
            'delivery-notes' => ['key' => 'delivery-note', 'list' => static fn (BillomatClient $b): array => $b->deliveryNotes->list(), 'get' => static fn (BillomatClient $b): ?object => $b->deliveryNotes->get(1)],
            'delivery-note-items' => ['key' => 'delivery-note-item', 'list' => static fn (BillomatClient $b): array => $b->deliveryNotes->items->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->deliveryNotes->items->get(1)],
            'delivery-note-comments' => ['key' => 'delivery-note-comment', 'list' => static fn (BillomatClient $b): array => $b->deliveryNotes->comments->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->deliveryNotes->comments->get(1)],
            'delivery-note-tags' => ['key' => 'delivery-note-tag', 'list' => static fn (BillomatClient $b): array => $b->deliveryNotes->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->deliveryNotes->tags->get(1)],
            'reminders' => ['key' => 'reminder', 'list' => static fn (BillomatClient $b): array => $b->reminders->list(), 'get' => static fn (BillomatClient $b): ?object => $b->reminders->get(1)],
            'reminder-items' => ['key' => 'reminder-item', 'list' => static fn (BillomatClient $b): array => $b->reminders->items->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->reminders->items->get(1)],
            'reminder-tags' => ['key' => 'reminder-tag', 'list' => static fn (BillomatClient $b): array => $b->reminders->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->reminders->tags->get(1)],
            'letters' => ['key' => 'letter', 'list' => static fn (BillomatClient $b): array => $b->letters->list(), 'get' => static fn (BillomatClient $b): ?object => $b->letters->get(1)],
            'letter-comments' => ['key' => 'letter-comment', 'list' => static fn (BillomatClient $b): array => $b->letters->comments->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->letters->comments->get(1)],
            'letter-tags' => ['key' => 'letter-tag', 'list' => static fn (BillomatClient $b): array => $b->letters->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->letters->tags->get(1)],
            'recurrings' => ['key' => 'recurring', 'list' => static fn (BillomatClient $b): array => $b->recurrings->list(), 'get' => static fn (BillomatClient $b): ?object => $b->recurrings->get(1)],
            'recurring-items' => ['key' => 'recurring-item', 'list' => static fn (BillomatClient $b): array => $b->recurrings->items->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->recurrings->items->get(1)],
            'recurring-tags' => ['key' => 'recurring-tag', 'list' => static fn (BillomatClient $b): array => $b->recurrings->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->recurrings->tags->get(1)],
            'recurring-email-receivers' => ['key' => 'recurring-email-receiver', 'list' => static fn (BillomatClient $b): array => $b->recurrings->emailReceivers->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->recurrings->emailReceivers->get(1)],
            'incomings' => ['key' => 'incoming', 'list' => static fn (BillomatClient $b): array => $b->incomings->list(), 'get' => static fn (BillomatClient $b): ?object => $b->incomings->get(1)],
            'incoming-payments' => ['key' => 'incoming-payment', 'list' => static fn (BillomatClient $b): array => $b->incomings->payments->list(), 'get' => static fn (BillomatClient $b): ?object => $b->incomings->payments->get(1)],
            'incoming-comments' => ['key' => 'incoming-comment', 'list' => static fn (BillomatClient $b): array => $b->incomings->comments->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->incomings->comments->get(1)],
            'incoming-tags' => ['key' => 'incoming-tag', 'list' => static fn (BillomatClient $b): array => $b->incomings->tags->list(1), 'get' => static fn (BillomatClient $b): ?object => $b->incomings->tags->get(1)],
            'incoming-properties' => ['key' => 'incoming-property', 'list' => static fn (BillomatClient $b): array => $b->incomings->properties->list(), 'get' => static fn (BillomatClient $b): ?object => $b->incomings->properties->get(1)],
            'incoming-property-values' => ['key' => 'incoming-property-value', 'list' => static fn (BillomatClient $b): array => $b->incomings->propertyValues->list(), 'get' => static fn (BillomatClient $b): ?object => $b->incomings->propertyValues->get(1)],
            'incoming-categories' => ['key' => 'incoming-category', 'list' => static fn (BillomatClient $b): array => $b->incomingCategories->list(), 'get' => static fn (BillomatClient $b): ?object => $b->incomingCategories->get('accounting_costs')],
            'inbox-documents' => ['key' => 'inbox-document', 'list' => static fn (BillomatClient $b): array => $b->inboxDocuments->list(), 'get' => static fn (BillomatClient $b): ?object => $b->inboxDocuments->get(1)],
            'templates' => ['key' => 'template', 'list' => static fn (BillomatClient $b): array => $b->templates->list(), 'get' => static fn (BillomatClient $b): ?object => $b->templates->get(1)],
            'free-texts' => ['key' => 'free-text', 'list' => static fn (BillomatClient $b): array => $b->freeTexts->list(), 'get' => static fn (BillomatClient $b): ?object => $b->freeTexts->get(1)],
            'email-templates' => ['key' => 'email-template', 'list' => static fn (BillomatClient $b): array => $b->emailTemplates->list(), 'get' => static fn (BillomatClient $b): ?object => $b->emailTemplates->get(1)],
            'reminder-texts' => ['key' => 'reminder-text', 'list' => static fn (BillomatClient $b): array => $b->reminderTexts->list(), 'get' => static fn (BillomatClient $b): ?object => $b->reminderTexts->get(1)],
            'country-taxes' => ['key' => 'country-tax', 'list' => static fn (BillomatClient $b): array => $b->countryTaxes->list(), 'get' => static fn (BillomatClient $b): ?object => $b->countryTaxes->get(1)],
            'countries' => ['key' => 'country', 'list' => static fn (BillomatClient $b): array => $b->countries->list(), 'get' => null],
            'currencies' => ['key' => 'currency', 'list' => static fn (BillomatClient $b): array => $b->currencies->list(), 'get' => null],
            'users' => ['key' => 'user', 'list' => static fn (BillomatClient $b): array => $b->users->list(), 'get' => static fn (BillomatClient $b): ?object => $b->users->get(1)],
            'user-properties' => ['key' => 'user-property', 'list' => static fn (BillomatClient $b): array => $b->users->properties->list(), 'get' => static fn (BillomatClient $b): ?object => $b->users->properties->get(1)],
            'roles' => ['key' => 'role', 'list' => static fn (BillomatClient $b): array => $b->roles->list(), 'get' => static fn (BillomatClient $b): ?object => $b->roles->get(1)],
            'activity-feed' => ['key' => 'activity', 'list' => static fn (BillomatClient $b): array => $b->activities->list(), 'get' => null],
            'settings' => ['key' => 'settings', 'list' => null, 'get' => static fn (BillomatClient $b): object => $b->settings->get()],
        ];
    }

    /**
     * Rohdatensätze aus dem Envelope, in derselben Reihenfolge wie die hydrierten Models.
     *
     * @param array<mixed> $raw
     *
     * @return list<array<mixed>>
     */
    private function records(array $raw, string $resource, string $key, string $kind): array
    {
        if ('get' === $kind) {
            $record = $raw[$key] ?? null;
            self::assertIsArray($record, "{$resource}/get: Envelope-Key {$key} fehlt");

            return [$record];
        }

        $wrapper = $raw[$resource] ?? null;
        self::assertIsArray($wrapper, "{$resource}/list: Envelope-Key {$resource} fehlt");
        $inner = $wrapper[$key] ?? [];
        self::assertIsArray($inner);

        $records = array_is_list($inner) ? $inner : [$inner];

        return array_values(array_filter($records, is_array(...)));
    }

    /**
     * Unbekannte Felder pro Model-Pfad: `$extra` der generierten Models (rekursiv)
     * bzw. für handgeschriebene Models ohne `$extra` die Felder, die `toArray()` nicht kennt.
     *
     * @param array<mixed> $record
     *
     * @return array<string, list<string>>
     */
    private static function unknownFields(object $model, array $record, ?string $path = null): array
    {
        $path ??= new ReflectionObject($model)->getShortName();
        $reflection = new ReflectionObject($model);

        if (!$reflection->hasProperty('extra')) {
            $unknown = array_keys(array_diff_key($record, self::wireView($model)));

            return [] === $unknown ? [] : [$path => array_map(strval(...), $unknown)];
        }

        $result = [];
        $extra = $reflection->getProperty('extra')->getValue($model);
        if (\is_array($extra) && [] !== $extra) {
            $result[$path] = array_map(strval(...), array_keys($extra));
        }

        foreach (self::publicProperties($model) as $property) {
            $value = $property->getValue($model);
            $children = \is_object($value) ? [$value] : (\is_array($value) ? $value : []);

            foreach ($children as $i => $child) {
                if (\is_object($child) && new ReflectionObject($child)->hasProperty('extra')) {
                    $childPath = \is_array($value) ? "{$path}.{$property->getName()}[{$i}]" : "{$path}.{$property->getName()}";
                    $result += self::unknownFields($child, [], $childPath);
                }
            }
        }

        return $result;
    }

    /**
     * Nicht-leere Rohwerte, die nach der Hydration `null` sind (unbekannter Enum-Wert,
     * nicht parsebares Datum, …) oder bei Listenfeldern (z. B. `payment_types`) Einträge verloren haben.
     *
     * @param array<mixed> $record
     *
     * @return array<string, string>
     */
    private function lostValues(object $model, array $record): array
    {
        $view = self::wireView($model);
        $lost = [];

        foreach ($record as $field => $value) {
            if (!\is_string($value) || '' === trim($value) || !\array_key_exists($field, $view)) {
                continue;
            }

            $hydrated = $view[$field];
            if (null === $hydrated) {
                $lost[(string) $field] = $value;
            } elseif (\is_string($hydrated) && str_contains($value, ',') && \count(explode(',', $value)) !== \count(explode(',', $hydrated))) {
                $lost[(string) $field] = $value;
            }
        }

        return $lost;
    }

    /**
     * Model als Billomat-Feldnamen; ohne `toArray()` aus den öffentlichen Properties abgeleitet.
     *
     * @return array<mixed>
     */
    private static function wireView(object $model): array
    {
        // Bereiche ohne Rechte (leerer String) fehlen in Role::$permissions, sind aber bekannte Felder.
        if ($model instanceof Role) {
            return [...array_fill_keys(Role::AREAS, null), ...$model->toArray()];
        }

        if (method_exists($model, 'toArray')) {
            $view = $model->toArray();
            self::assertIsArray($view);

            return $view;
        }

        $view = [];
        foreach (self::publicProperties($model) as $property) {
            $value = $property->getValue($model);
            $field = strtolower((string) preg_replace('/[A-Z]/', '_$0', $property->getName()));
            $view[$field] = $value instanceof BackedEnum ? $value->value : $value;
        }

        if ($model instanceof Settings) {
            unset($view['price_groups']);
            foreach ($model->priceGroups as $n => $label) {
                $view["price_group{$n}"] = $label;
            }
        }

        return $view;
    }

    /**
     * @return list<ReflectionProperty>
     */
    private static function publicProperties(object $model): array
    {
        return array_values(array_filter(
            new ReflectionObject($model)->getProperties(ReflectionProperty::IS_PUBLIC),
            static fn (ReflectionProperty $p): bool => !$p->isStatic(),
        ));
    }
}
