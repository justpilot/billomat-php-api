<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Closure;
use Justpilot\Billomat\Api\CountryTaxCreateOptions;
use Justpilot\Billomat\Api\CountryTaxUpdateOptions;
use Justpilot\Billomat\Api\EmailTemplateCreateOptions;
use Justpilot\Billomat\Api\EmailTemplateUpdateOptions;
use Justpilot\Billomat\Api\FreeTextCreateOptions;
use Justpilot\Billomat\Api\FreeTextUpdateOptions;
use Justpilot\Billomat\Api\ReminderTextCreateOptions;
use Justpilot\Billomat\Api\ReminderTextUpdateOptions;
use Justpilot\Billomat\Api\RoleCreateOptions;
use Justpilot\Billomat\Api\RoleUpdateOptions;
use Justpilot\Billomat\Api\UnitCreateOptions;
use Justpilot\Billomat\Api\UnitUpdateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Model\Enum\AccessLevel;
use Justpilot\Billomat\Model\Enum\EmailTemplateType;
use Justpilot\Billomat\Model\Enum\FreeTextType;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * create(), update() und delete() der Einstellungs-Ressourcen (Einheiten, E-Mail-Vorlagen,
 * Freitexte, Mahnstufen, steuerfreie Länder, Rollen).
 */
#[CoversNamespace('Justpilot\\Billomat\\Api')]
#[UsesClass(BillomatClient::class)]
#[UsesNamespace('Justpilot\\Billomat\\Config')]
#[UsesNamespace('Justpilot\\Billomat\\Http')]
#[UsesNamespace('Justpilot\\Billomat\\Exception')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
final class SettingsWriteApiTest extends TestCase
{
    /**
     * @return iterable<string, array{Closure(BillomatClient): object, string, string, array<string, mixed>}>
     */
    public static function creates(): iterable
    {
        yield 'units' => [
            static fn (BillomatClient $b): object => $b->units->create(new UnitCreateOptions(name: 'Stunde')),
            '/units', 'unit', ['name' => 'Stunde'],
        ];
        yield 'email-templates' => [
            static fn (BillomatClient $b): object => $b->emailTemplates->create(new EmailTemplateCreateOptions(name: 'Standard', type: EmailTemplateType::INVOICES, subject: 'Ihre Rechnung', body: 'Hallo', isDefault: true)),
            '/email-templates', 'email-template', ['name' => 'Standard', 'subject' => 'Ihre Rechnung', 'text' => 'Hallo', 'is_default' => 1, 'type' => 'INVOICES'],
        ];
        yield 'free-texts' => [
            static fn (BillomatClient $b): object => $b->freeTexts->create(new FreeTextCreateOptions(type: FreeTextType::INVOICE, name: 'Gruß', intro: 'Danke', isDefault: false)),
            '/free-texts', 'free-text', ['intro' => 'Danke', 'type' => 'INVOICE', 'name' => 'Gruß', 'is_default' => 0],
        ];
        yield 'reminder-texts' => [
            static fn (BillomatClient $b): object => $b->reminderTexts->create(new ReminderTextCreateOptions(name: '1. Mahnung', subject: 'Erinnerung', chargeAmount: Money::of('5.00'))),
            '/reminder-texts', 'reminder-text', ['name' => '1. Mahnung', 'subject' => 'Erinnerung', 'charge_amount' => '5'],
        ];
        yield 'country-taxes' => [
            static fn (BillomatClient $b): object => $b->countryTaxes->create(new CountryTaxCreateOptions(countryCode: 'NO')),
            '/country-taxes', 'country-tax', ['country_code' => 'NO'],
        ];
        yield 'roles' => [
            static fn (BillomatClient $b): object => $b->roles->create(new RoleCreateOptions(name: 'Buchhaltung', permissions: ['invoices' => AccessLevel::DELETE, 'settings_documents' => AccessLevel::UPDATE])),
            '/roles', 'role', ['name' => 'Buchhaltung', 'invoices' => 'DELETE', 'settings_documents' => 'UPDATE'],
        ];
    }

    /**
     * @return iterable<string, array{Closure(BillomatClient): object, string, string, array<string, mixed>}>
     */
    public static function updates(): iterable
    {
        yield 'units' => [
            static fn (BillomatClient $b): object => $b->units->update(7, new UnitUpdateOptions(name: 'Std.')),
            '/units/7', 'unit', ['name' => 'Std.'],
        ];
        yield 'email-templates' => [
            static fn (BillomatClient $b): object => $b->emailTemplates->update(7, new EmailTemplateUpdateOptions(bcc: true)),
            '/email-templates/7', 'email-template', ['bcc' => 1],
        ];
        yield 'free-texts' => [
            static fn (BillomatClient $b): object => $b->freeTexts->update(7, new FreeTextUpdateOptions(note: 'Neu')),
            '/free-texts/7', 'free-text', ['note' => 'Neu'],
        ];
        yield 'reminder-texts' => [
            static fn (BillomatClient $b): object => $b->reminderTexts->update(7, new ReminderTextUpdateOptions(sort: 2)),
            '/reminder-texts/7', 'reminder-text', ['sorting' => 2],
        ];
        yield 'country-taxes' => [
            static fn (BillomatClient $b): object => $b->countryTaxes->update(7, new CountryTaxUpdateOptions(countryCode: 'IS')),
            '/country-taxes/7', 'country-tax', ['country_code' => 'IS'],
        ];
        yield 'roles' => [
            static fn (BillomatClient $b): object => $b->roles->update(7, new RoleUpdateOptions(permissions: ['articles' => null, 'clients' => AccessLevel::READ])),
            '/roles/7', 'role', ['articles' => '', 'clients' => 'READ'],
        ];
    }

    /**
     * @return iterable<string, array{Closure(BillomatClient): void, string}>
     */
    public static function deletes(): iterable
    {
        yield 'units' => [static fn (BillomatClient $b) => $b->units->delete(7), '/units/7'];
        yield 'email-templates' => [static fn (BillomatClient $b) => $b->emailTemplates->delete(7), '/email-templates/7'];
        yield 'free-texts' => [static fn (BillomatClient $b) => $b->freeTexts->delete(7), '/free-texts/7'];
        yield 'reminder-texts' => [static fn (BillomatClient $b) => $b->reminderTexts->delete(7), '/reminder-texts/7'];
        yield 'country-taxes' => [static fn (BillomatClient $b) => $b->countryTaxes->delete(7), '/country-taxes/7'];
        yield 'roles' => [static fn (BillomatClient $b) => $b->roles->delete(7), '/roles/7'];
    }

    /**
     * @param Closure(BillomatClient): object $call
     * @param array<string, mixed>            $payload
     */
    #[Test]
    #[DataProvider('creates')]
    public function createPostsTheEnvelopeAndHydratesTheAnswer(Closure $call, string $path, string $key, array $payload): void
    {
        $captured = new CapturedRequest();
        $model = $call($this->client($captured, (string) json_encode([$key => ['id' => '12', 'name' => 'x']])));

        self::assertSame('POST', $captured->method);
        self::assertSame('/api'.$path, $captured->path());
        self::assertSame([$key => $payload], $captured->json());
        self::assertSame(12, $model->id ?? null);
    }

    /**
     * @param Closure(BillomatClient): object $call
     * @param array<string, mixed>            $payload
     */
    #[Test]
    #[DataProvider('updates')]
    public function updatePutsOnlyTheGivenFields(Closure $call, string $path, string $key, array $payload): void
    {
        $captured = new CapturedRequest();
        $model = $call($this->client($captured, (string) json_encode([$key => ['id' => '7']])));

        self::assertSame('PUT', $captured->method);
        self::assertSame('/api'.$path, $captured->path());
        self::assertSame([$key => $payload], $captured->json());
        self::assertSame(7, $model->id ?? null);
    }

    /**
     * @param Closure(BillomatClient): object $call
     * @param array<string, mixed>            $payload
     */
    #[Test]
    #[DataProvider('creates')]
    public function createRejectsAnAnswerWithoutEnvelope(Closure $call, string $path, string $key, array $payload): void
    {
        $this->expectException(UnexpectedResponseException::class);

        $call($this->client(new CapturedRequest(), '{"something-else":{"id":"1"}}'));
    }

    /**
     * @param Closure(BillomatClient): object $call
     * @param array<string, mixed>            $payload
     */
    #[Test]
    #[DataProvider('updates')]
    public function updateRejectsAnAnswerWithoutEnvelope(Closure $call, string $path, string $key, array $payload): void
    {
        $this->expectException(UnexpectedResponseException::class);

        $call($this->client(new CapturedRequest(), '{"something-else":{"id":"1"}}'));
    }

    /**
     * @param Closure(BillomatClient): void $call
     */
    #[Test]
    #[DataProvider('deletes')]
    public function deleteSendsDelete(Closure $call, string $path): void
    {
        $captured = new CapturedRequest();
        $call($this->client($captured, ''));

        self::assertSame('DELETE', $captured->method);
        self::assertSame('/api'.$path, $captured->path());
    }

    private function client(CapturedRequest $captured, string $body): BillomatClient
    {
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured, $body): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse($body, ['http_code' => 'POST' === $method ? 201 : 200]);
        });

        return new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'), new Psr18Client($mock));
    }
}
