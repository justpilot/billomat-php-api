<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Webhook;

use Justpilot\Billomat\Webhook\WebhookAction;
use Justpilot\Billomat\Webhook\WebhookEvent;
use Justpilot\Billomat\Webhook\WebhookPayload;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

#[CoversClass(WebhookEvent::class)]
#[CoversClass(WebhookPayload::class)]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
final class WebhookEventTest extends TestCase
{
    #[Test]
    public function itKnowsAllEventsOfTheCatalog(): void
    {
        self::assertCount(83, WebhookEvent::cases());
    }

    #[Test]
    public function itSplitsResourceAndAction(): void
    {
        self::assertSame('invoice_payment', WebhookEvent::INVOICE_PAYMENT_CREATE->resource());
        self::assertSame(WebhookAction::CREATE, WebhookEvent::INVOICE_PAYMENT_CREATE->action());
        self::assertSame('invoice-payment', WebhookEvent::INVOICE_PAYMENT_CREATE->envelopeKey());
        self::assertSame('credit_note', WebhookEvent::CREDIT_NOTE_SEND->resource());
        self::assertSame(WebhookAction::SEND, WebhookEvent::CREDIT_NOTE_SEND->action());
        self::assertSame('supplier-property-value', WebhookEvent::SUPPLIER_PROPERTY_VALUE_UPDATE->envelopeKey());
    }

    /**
     * Jedes Event hydriert über das Model seiner Ressource; der Envelope-Schlüssel passt zum Eventnamen.
     */
    #[Test]
    #[DataProvider('events')]
    public function everyEventHydratesItsModel(WebhookEvent $event): void
    {
        self::assertSame(str_replace('.', '_', strtoupper($event->value)), $event->name);
        self::assertSame($event->resource().'.'.$event->action()->value, $event->value);
        self::assertSame(str_replace('_', '-', $event->resource()), $event->envelopeKey());

        $class = $event->modelClass();
        self::assertSame(
            'Justpilot\\Billomat\\Model\\'.str_replace('_', '', ucwords($event->resource(), '_')),
            $class,
        );

        $payload = WebhookPayload::fromArray($event, [$event->envelopeKey() => ['id' => '7', 'amount' => '1']]);
        $model = $payload->model();

        self::assertInstanceOf($class, $model);
        self::assertSame(7, $payload->id());
        self::assertObjectHasProperty('id', $model);
        self::assertSame(7, $model->id);
    }

    /**
     * @return iterable<string, array{WebhookEvent}>
     */
    public static function events(): iterable
    {
        foreach (WebhookEvent::cases() as $event) {
            yield $event->value => [$event];
        }
    }
}
