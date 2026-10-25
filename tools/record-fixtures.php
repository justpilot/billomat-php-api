<?php

declare(strict_types=1);

/*
 * Nimmt Contract-Fixtures gegen die Billomat-Sandbox auf.
 *
 * Aufruf:
 *   composer fixtures:record                       # alle Ressourcen aufnehmen
 *   composer fixtures:record -- --cache=/tmp/raw   # Roh-Antworten zwischenspeichern bzw. wiederverwenden
 *
 * Führt ausschließlich GET-Requests aus (eine Liste mit wenigen Einträgen und,
 * wenn Daten vorhanden sind, ein Einzelabruf pro Ressource) und legt die
 * anonymisierten Antworten unter tests/Fixtures/Contract/<ressource>/<list|get>.json ab.
 * tests/Contract/ContractFixturesTest.php prüft damit ohne Netz, ob die Models
 * jedes Feld der echten API kennen.
 *
 * Zugangsdaten kommen aus .env.test.local (BILLOMAT_ID, BILLOMAT_API_KEY, optional
 * BILLOMAT_APP_ID/BILLOMAT_APP_SECRET) und werden nie ausgegeben.
 *
 * Anonymisiert werden Namen, Firmen, E-Mails, Telefon-/Fax-/Mobilnummern,
 * Anschriften, Bankdaten, Steuer-/USt-IDs, URLs, Freitexte und die Billomat-ID.
 * Typen, Formate, Enum-Werte, Beträge, Datumsangaben, IDs und die Anwesenheit
 * der Felder bleiben unverändert; leere Strings bleiben leer.
 */

namespace Justpilot\Billomat\Tools\Fixtures;

use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\Psr18Client;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

const PROJECT_ROOT = __DIR__.'/..';
const FIXTURE_DIR = PROJECT_ROOT.'/tests/Fixtures/Contract';

// Obergrenze pro Lauf; das Sandbox-Limit liegt bei 1000 Requests / 15 Minuten.
const MAX_REQUESTS = 150;

// Einträge pro Listen-Request: klein halten, aber genug für Varianten.
const PER_PAGE = 3;

/*
 * Ressourcen mit eigenem Endpunkt. `get` = Einzelabruf des ersten Listeneintrags,
 * `children` = Sub-Ressourcen, die über `{parent}_id` am ersten Eintrag hängen.
 * `list: false` = Endpunkt liefert ein einzelnes Objekt (Settings).
 */
const RESOURCES = [
    'clients' => ['key' => 'client', 'get' => true, 'children' => ['contacts' => 'contact', 'client-tags' => 'tag']],
    'client-properties' => ['key' => 'client-property', 'get' => true],
    'client-property-values' => ['key' => 'client-property-value', 'get' => true],
    'suppliers' => ['key' => 'supplier', 'get' => true, 'children' => ['supplier-tags' => 'tag']],
    'supplier-properties' => ['key' => 'supplier-property', 'get' => true],
    'supplier-property-values' => ['key' => 'supplier-property-value', 'get' => true],
    'articles' => ['key' => 'article', 'get' => true, 'children' => ['article-tags' => 'tag']],
    'article-properties' => ['key' => 'article-property', 'get' => true],
    'article-property-values' => ['key' => 'article-property-value', 'get' => true],
    'units' => ['key' => 'unit', 'get' => true],
    'taxes' => ['key' => 'tax', 'get' => true],
    'invoices' => ['key' => 'invoice', 'get' => true, 'children' => ['invoice-items' => 'invoice-item', 'invoice-comments' => 'invoice-comment', 'invoice-tags' => 'tag']],
    'invoice-payments' => ['key' => 'invoice-payment', 'get' => true],
    'offers' => ['key' => 'offer', 'get' => true, 'children' => ['offer-items' => 'offer-item', 'offer-comments' => 'offer-comment', 'offer-tags' => 'tag']],
    'confirmations' => ['key' => 'confirmation', 'get' => true, 'children' => ['confirmation-items' => 'confirmation-item', 'confirmation-comments' => 'confirmation-comment', 'confirmation-tags' => 'tag']],
    'credit-notes' => ['key' => 'credit-note', 'get' => true, 'children' => ['credit-note-items' => 'credit-note-item', 'credit-note-comments' => 'credit-note-comment', 'credit-note-tags' => 'tag']],
    'credit-note-payments' => ['key' => 'credit-note-payment', 'get' => true],
    'delivery-notes' => ['key' => 'delivery-note', 'get' => true, 'children' => ['delivery-note-items' => 'delivery-note-item', 'delivery-note-comments' => 'delivery-note-comment', 'delivery-note-tags' => 'tag']],
    'reminders' => ['key' => 'reminder', 'get' => true, 'children' => ['reminder-items' => 'reminder-item', 'reminder-tags' => 'tag']],
    'letters' => ['key' => 'letter', 'get' => true, 'children' => ['letter-comments' => 'letter-comment', 'letter-tags' => 'tag']],
    'recurrings' => ['key' => 'recurring', 'get' => true, 'children' => ['recurring-items' => 'recurring-item', 'recurring-tags' => 'tag', 'recurring-email-receivers' => 'recurring-email-receiver']],
    'incomings' => ['key' => 'incoming', 'get' => true, 'children' => ['incoming-comments' => 'incoming-comment', 'incoming-tags' => 'tag']],
    'incoming-payments' => ['key' => 'incoming-payment', 'get' => true],
    'incoming-properties' => ['key' => 'incoming-property', 'get' => true],
    'incoming-property-values' => ['key' => 'incoming-property-value', 'get' => true],
    'incoming-categories' => ['key' => 'incoming-category', 'get' => true],
    'inbox-documents' => ['key' => 'inbox-document', 'get' => true],
    'templates' => ['key' => 'template', 'get' => true],
    'free-texts' => ['key' => 'free-text', 'get' => true],
    'email-templates' => ['key' => 'email-template', 'get' => true],
    'reminder-texts' => ['key' => 'reminder-text', 'get' => true],
    'country-taxes' => ['key' => 'country-tax', 'get' => true],
    'countries' => ['key' => 'country', 'get' => false],
    'currencies' => ['key' => 'currency', 'get' => false],
    'users' => ['key' => 'user', 'get' => true],
    'user-properties' => ['key' => 'user-property', 'get' => true],
    'roles' => ['key' => 'role', 'get' => true],
    'activity-feed' => ['key' => 'activity', 'get' => false],
    'settings' => ['key' => 'settings', 'list' => false],
];

/*
 * Feldnamen → Art des Ersatzwerts. Gilt auf jeder Ebene (auch in Positionen,
 * Steuerzeilen und Einstellungen); nicht aufgeführte Strings durchlaufen nur
 * den allgemeinen Scan nach E-Mails, IBANs, URLs und der Billomat-ID.
 */
const FIELD_KINDS = [
    'name' => 'name', 'company' => 'company', 'first_name' => 'first_name', 'last_name' => 'last_name',
    'firstname' => 'first_name', 'lastname' => 'last_name', 'bank_account_owner' => 'name',
    'email' => 'email', 'bcc_addresses' => 'email', 'sender' => 'email', 'sender_email' => 'email',
    'reply_to' => 'email', 'email_bcc' => 'email', 'email_sender' => 'email', 'bcc' => 'email',
    'phone' => 'phone', 'fax' => 'phone', 'mobile' => 'phone',
    'street' => 'street', 'zip' => 'zip', 'city' => 'city', 'state' => 'state', 'address' => 'address',
    'www' => 'url', 'url' => 'url', 'website' => 'url', 'avatar' => 'url', 'public_url' => 'url',
    'bank_name' => 'bank_name', 'bank_iban' => 'iban', 'iban' => 'iban', 'bank_swift' => 'bic', 'bic' => 'bic', 'swift' => 'bic',
    'bank_account_number' => 'digits', 'bank_number' => 'digits', 'creditor_identifier' => 'creditor_id', 'sepa_mandate' => 'text',
    'tax_number' => 'tax_number', 'vat_number' => 'vat_number',
    'note' => 'text', 'notes' => 'text', 'intro' => 'text', 'description' => 'text', 'comment' => 'text', 'text' => 'text',
    'subject' => 'text', 'body' => 'text', 'title' => 'text', 'label' => 'text', 'message' => 'text', 'signature' => 'text',
    'customfield' => 'text', 'value' => 'text', 'reference' => 'text', 'filename' => 'filename', 'file_name' => 'filename',
    'base64file' => 'base64', 'file' => 'base64', 'pdf' => 'base64', 'document' => 'base64',
];

/** Wie FIELD_KINDS, aber nach Namensendung (z. B. `offer_intro`, `default_email_sender`). */
const FIELD_SUFFIX_KINDS = [
    '_sender' => 'sender', '_email' => 'email', '_intro' => 'text', '_note' => 'text', '_text' => 'text',
    '_subject' => 'text', '_signature' => 'text', '_phone' => 'phone', '_fax' => 'phone',
];

/** Platzhalter-PDF für Base64-Felder (Inhalt der Sandbox-Dokumente geht nie ins Repo). */
const FAKE_PDF = "%PDF-1.4\n% anonymisiert\n%%EOF\n";

try {
    main($argv);
} catch (RuntimeException $e) {
    fwrite(STDERR, 'Fehler: '.$e->getMessage()."\n");
    exit(1);
}

/**
 * @param list<string> $argv
 */
function main(array $argv): void
{
    require PROJECT_ROOT.'/vendor/autoload.php';

    $cacheDir = null;
    foreach (\array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '--cache=')) {
            $cacheDir = rtrim(substr($arg, 8), '/');
        } else {
            throw new RuntimeException("Unbekannte Option {$arg}");
        }
    }

    $dotenv = new Dotenv();
    $dotenv->usePutenv();
    $dotenv->load(PROJECT_ROOT.'/.env.test');
    if (is_file(PROJECT_ROOT.'/.env.test.local')) {
        $dotenv->overload(PROJECT_ROOT.'/.env.test.local');
    }

    $billomatId = (string) getenv('BILLOMAT_ID');
    $apiKey = (string) getenv('BILLOMAT_API_KEY');
    if ('' === $billomatId || '' === $apiKey) {
        throw new RuntimeException('BILLOMAT_ID und BILLOMAT_API_KEY fehlen (.env.test.local).');
    }

    $config = new BillomatConfig(
        billomatId: $billomatId,
        apiKey: $apiKey,
        appId: ((string) getenv('BILLOMAT_APP_ID')) ?: null,
        appSecret: ((string) getenv('BILLOMAT_APP_SECRET')) ?: null,
        timeout: 30.0,
        retry: new RetryPolicy(maxRetries: 0),
    );
    $recorder = new Recorder(new BillomatHttpClient(new Psr18Client(), $config), $cacheDir);
    $anonymizer = new Anonymizer($billomatId);

    foreach (RESOURCES as $resource => $definition) {
        if (false === ($definition['list'] ?? true)) {
            $body = $recorder->fetch("/{$resource}");
            if (null !== $body) {
                write($resource, 'get', $anonymizer->anonymize($body));
            }

            continue;
        }

        $list = $recorder->fetch("/{$resource}", ['per_page' => PER_PAGE]);
        if (null === $list) {
            continue;
        }

        write($resource, 'list', $anonymizer->anonymize($list));
        $id = firstId($list, $resource, $definition['key']);
        if (null === $id) {
            echo "  {$resource}: keine Daten, kein Einzelabruf\n";

            continue;
        }

        if ($definition['get']) {
            $single = $recorder->fetch("/{$resource}/{$id}");
            if (null !== $single) {
                write($resource, 'get', $anonymizer->anonymize($single));
            }
        }

        $parentKey = str_replace('-', '_', $definition['key']).'_id';
        foreach ($definition['children'] ?? [] as $child => $childKey) {
            $childList = $recorder->fetch("/{$child}", [$parentKey => $id, 'per_page' => PER_PAGE]);
            if (null === $childList) {
                continue;
            }

            write($child, 'list', $anonymizer->anonymize($childList));
            $childId = firstId($childList, $child, $childKey);
            if (null !== $childId && 'tag' !== $childKey) {
                $childSingle = $recorder->fetch("/{$child}/{$childId}");
                if (null !== $childSingle) {
                    write($child, 'get', $anonymizer->anonymize($childSingle));
                }
            }
        }
    }

    echo "Fertig: {$recorder->requests} Requests.\n";
}

/**
 * ID des ersten Eintrags einer Liste (`{ "clients": { "client": [...] | {...} } }`).
 *
 * @param array<mixed> $body
 */
function firstId(array $body, string $listKey, string $key): ?string
{
    $inner = $body[$listKey][$key] ?? null;
    if (!\is_array($inner) || [] === $inner) {
        return null;
    }

    $first = array_is_list($inner) ? $inner[0] : $inner;
    $id = \is_array($first) ? ($first['id'] ?? null) : null;

    return \is_string($id) || \is_int($id) ? (string) $id : null;
}

/**
 * @param array<mixed> $body
 */
function write(string $resource, string $kind, array $body): void
{
    $dir = FIXTURE_DIR.'/'.$resource;
    if (!is_dir($dir) && !mkdir($dir, 0o777, true)) {
        throw new RuntimeException("Kann {$dir} nicht anlegen");
    }

    $json = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    file_put_contents("{$dir}/{$kind}.json", $json."\n");
    echo "  {$resource}/{$kind}.json\n";
}

/**
 * Führt die GETs aus, zählt sie und speichert Roh-Antworten optional zwischen.
 */
final class Recorder
{
    public int $requests = 0;

    public function __construct(
        private readonly BillomatHttpClient $http,
        private readonly ?string $cacheDir,
    ) {
    }

    /**
     * @param array<string, scalar> $query
     *
     * @return array<mixed>|null null bei Fehlern (404, fehlende Berechtigung, …) oder leerem Body
     */
    public function fetch(string $path, array $query = []): ?array
    {
        $cacheFile = null;
        if (null !== $this->cacheDir) {
            $cacheFile = $this->cacheDir.'/'.trim(str_replace('/', '_', $path), '_').([] === $query ? '' : '_'.md5(serialize($query))).'.json';
            if (is_file($cacheFile)) {
                return self::decode((string) file_get_contents($cacheFile));
            }
        }

        if ($this->requests >= MAX_REQUESTS) {
            throw new RuntimeException('Request-Budget von '.MAX_REQUESTS.' erschöpft');
        }

        ++$this->requests;

        try {
            $body = (string) $this->http->request('GET', $path, $query)->getBody();
        } catch (BillomatException $e) {
            // Nur Klasse und Status ausgeben; die Message kann Kundendaten enthalten.
            echo "  {$path}: übersprungen (".new \ReflectionClass($e)->getShortName().")\n";

            return null;
        }

        if (null !== $cacheFile) {
            if (!is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0o700, true)) {
                throw new RuntimeException("Kann {$this->cacheDir} nicht anlegen");
            }
            file_put_contents($cacheFile, $body);
        }

        return self::decode($body);
    }

    /**
     * @return array<mixed>|null
     */
    private static function decode(string $body): ?array
    {
        if ('' === trim($body)) {
            return null;
        }

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        return \is_array($data) ? $data : null;
    }
}

/**
 * Ersetzt personenbezogene Werte deterministisch durch Platzhalter gleichen Formats.
 *
 * Gleicher Originalwert → gleicher Ersatz (pro Art), damit Bezüge innerhalb der
 * Fixtures erhalten bleiben und ein erneuter Lauf stabile Diffs liefert.
 */
final class Anonymizer
{
    /** @var array<string, array<string, string>> */
    private array $seen = [];

    public function __construct(private readonly string $billomatId)
    {
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public function anonymize(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = match (true) {
                \is_array($value) => $this->anonymize($value),
                \is_string($value) => $this->value((string) $key, $value),
                default => $value,
            };
        }

        return $result;
    }

    private function value(string $key, string $value): string
    {
        if ('' === trim($value)) {
            return $value;
        }

        $kind = FIELD_KINDS[$key] ?? self::kindBySuffix($key);
        // Numerische Freitext-Werte (z. B. Checkbox-Attribute "1") tragen keine Personendaten.
        if (null !== $kind && !('text' === $kind && is_numeric($value))) {
            return $this->fake($kind, $value);
        }

        return $this->scan($value);
    }

    /**
     * Einstellungen tragen viele Varianten (`invoice_intro`, `default_email_sender`, …).
     */
    private static function kindBySuffix(string $key): ?string
    {
        foreach (FIELD_SUFFIX_KINDS as $suffix => $kind) {
            if (str_ends_with($key, $suffix)) {
                return $kind;
            }
        }

        return null;
    }

    /**
     * Allgemeiner Scan für Felder ohne feste Regel.
     */
    private function scan(string $value): string
    {
        $value = (string) preg_replace_callback(
            '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
            fn (array $m): string => $this->fake('email', $m[0]),
            $value,
        );
        $value = (string) preg_replace_callback(
            '/\b[A-Z]{2}\d{2}(?: ?[A-Z0-9]{4}){3,7}(?: ?[A-Z0-9]{1,3})?\b/',
            fn (array $m): string => $this->fake('iban', $m[0]),
            $value,
        );
        $value = (string) preg_replace_callback(
            '#https?://[^\s"<>]+#i',
            fn (array $m): string => $this->fake('url', $m[0]),
            $value,
        );

        return str_ireplace($this->billomatId, 'mycompany', $value);
    }

    private function fake(string $kind, string $original): string
    {
        if ('email' === $kind && str_contains($original, ',')) {
            return implode(',', array_map(fn (string $part): string => '' === trim($part) ? $part : $this->fake('email', trim($part)), explode(',', $original)));
        }

        $n = $this->seen[$kind][$original] ??= (string) (\count($this->seen[$kind] ?? []) + 1);
        $i = (int) $n;

        return match ($kind) {
            'name' => "Name {$n}",
            'company' => "Firma {$n} GmbH",
            'first_name' => "Vorname{$n}",
            'last_name' => "Nachname{$n}",
            'email' => "kontakt{$n}@example.com",
            'sender' => "Firma {$n} GmbH <absender{$n}@example.com>",
            'phone' => \sprintf('+49 30 %07d', $i),
            'street' => "Musterstraße {$n}",
            'zip' => \sprintf('%05d', 10000 + $i),
            'city' => "Musterstadt {$n}",
            'state' => "Bundesland {$n}",
            'address' => "Firma {$n} GmbH\nMusterstraße {$n}\n".\sprintf('%05d', 10000 + $i)." Musterstadt {$n}",
            'url' => "https://www.example.com/{$n}",
            'bank_name' => "Testbank {$n}",
            'iban' => \sprintf('DE00%018d', $i),
            'bic' => 'TESTDEFFXXX',
            'digits' => \sprintf('%0'.max(1, \strlen(preg_replace('/\D/', '', $original) ?? '')).'d', $i),
            'creditor_id' => \sprintf('DE00ZZZ%011d', $i),
            'tax_number' => \sprintf('000/000/%05d', $i),
            'vat_number' => \sprintf('DE%09d', $i),
            'filename' => "dokument-{$n}".(preg_match('/\.[a-z0-9]{2,4}$/i', $original, $m) ? strtolower($m[0]) : ''),
            'base64' => base64_encode(FAKE_PDF),
            default => "Text {$n}",
        };
    }
}
