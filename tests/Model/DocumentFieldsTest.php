<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Closure;
use DateTimeImmutable;
use Generator;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationItem;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\CreditNoteItem;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\DeliveryNoteItem;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;
use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\LetterStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\OfferStatus;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\ReminderStatus;
use Justpilot\Billomat\Model\Enum\SupplyDateType;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoiceItem;
use Justpilot\Billomat\Model\Letter;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\Offer;
use Justpilot\Billomat\Model\OfferItem;
use Justpilot\Billomat\Model\Recurring;
use Justpilot\Billomat\Model\RecurringItem;
use Justpilot\Billomat\Model\Reminder;
use Justpilot\Billomat\Model\ReminderItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Neue Beleg-Felder aus den Billomat-Antworten (Stand 2026-10): Summen, Mahnstufe, Abo-Versandoptionen, Verknüpfungen.
 */
#[CoversClass(Confirmation::class)]
#[CoversClass(CreditNote::class)]
#[CoversClass(CreditNoteItem::class)]
#[CoversClass(DeliveryNote::class)]
#[CoversClass(Invoice::class)]
#[CoversClass(Letter::class)]
#[CoversClass(Offer::class)]
#[CoversClass(OfferItem::class)]
#[CoversClass(Recurring::class)]
#[CoversClass(Reminder::class)]
#[CoversClass(ReminderItem::class)]
#[UsesClass(ConfirmationStatus::class)]
#[UsesClass(CreditNoteStatus::class)]
#[UsesClass(DeliveryNoteStatus::class)]
#[UsesClass(ItemType::class)]
#[UsesClass(InvoiceStatus::class)]
#[UsesClass(LetterStatus::class)]
#[UsesClass(NetGross::class)]
#[UsesClass(OfferStatus::class)]
#[UsesClass(RecurringAction::class)]
#[UsesClass(RecurringCycle::class)]
#[UsesClass(ReminderStatus::class)]
#[UsesClass(SupplyDateType::class)]
#[UsesClass(ConfirmationItem::class)]
#[UsesClass(DeliveryNoteItem::class)]
#[UsesClass(InvoiceItem::class)]
#[UsesClass(RecurringItem::class)]
#[UsesClass(ListCaster::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class DocumentFieldsTest extends TestCase
{
    /** Pflichtfelder, die Billomat immer liefert (`id`, bei Positionen `unit_price`, bei Zahlungen `amount`). */
    private const array REQUIRED = ['id' => '1', 'unit_price' => '1', 'amount' => '1'];

    /**
     * Pro Model: `fromArray()`, Rohdaten wie von Billomat (alles Strings) und
     * die erwarteten Property-Werte. Datumswerte werden als `Y-m-d` verglichen.
     *
     * @return Generator<string, array{Closure(array<string,mixed>): object, array<string,mixed>, array<string,mixed>}>
     */
    public static function models(): Generator
    {
        yield 'Confirmation' => [
            Confirmation::fromArray(...),
            ['id' => '1', 'total_reduction' => '12.5'],
            ['totalReduction' => '12.5'],
        ];
        yield 'CreditNote' => [
            CreditNote::fromArray(...),
            ['id' => '1', 'total_reduction' => '12.5'],
            ['totalReduction' => '12.5'],
        ];
        yield 'CreditNoteItem' => [
            CreditNoteItem::fromArray(...),
            ['id' => '1', 'tax_rate_reverse_charge' => '12.5'],
            ['taxRateReverseCharge' => 12.5],
        ];
        yield 'DeliveryNote' => [
            DeliveryNote::fromArray(...),
            ['id' => '1', 'offer_id' => '42'],
            ['offerId' => 42],
        ];
        yield 'Invoice' => [
            Invoice::fromArray(...),
            ['id' => '1', 'total_reduction' => '12.5', 'dig_proceeded' => '1'],
            ['totalReduction' => '12.5', 'digProceeded' => true],
        ];
        yield 'Letter' => [
            Letter::fromArray(...),
            ['id' => '1', 'supplier_id' => '42'],
            ['supplierId' => 42],
        ];
        yield 'Offer' => [
            Offer::fromArray(...),
            ['id' => '1', 'validity_date' => '2026-11-17', 'total_reduction' => '12.5'],
            ['validityDate' => '2026-11-17', 'totalReduction' => '12.5'],
        ];
        yield 'OfferItem' => [
            OfferItem::fromArray(...),
            ['id' => '1', 'optional' => '1'],
            ['optional' => true],
        ];
        yield 'Recurring' => [
            Recurring::fromArray(...),
            ['id' => '1', 'total_reduction' => '12.5', 'total_net_unreduced' => '12.5', 'total_gross_unreduced' => '12.5', 'discount_amount' => '12.5', 'status' => 'Text', 'in_progress' => '1', 'ultimo' => '1', 'email_filename' => 'Text', 'email_bcc' => '1', 'letter_color' => '1', 'letter_duplex' => '1', 'letter_paper_weight' => '42', 'offer_id' => '42', 'confirmation_id' => '42'],
            ['totalReduction' => '12.5', 'totalNetUnreduced' => '12.5', 'totalGrossUnreduced' => '12.5', 'discountAmount' => '12.5', 'status' => 'Text', 'inProgress' => true, 'ultimo' => true, 'emailFilename' => 'Text', 'emailBcc' => true, 'letterColor' => true, 'letterDuplex' => true, 'letterPaperWeight' => 42, 'offerId' => 42, 'confirmationId' => 42],
        ];
        yield 'Reminder' => [
            Reminder::fromArray(...),
            ['id' => '1', 'paid_amount' => '12.5', 'open_amount' => '12.5', 'reminder_level' => '42', 'reminder_level_name' => 'Text', 'is_old' => '1'],
            ['paidAmount' => '12.5', 'openAmount' => '12.5', 'reminderLevel' => 42, 'reminderLevelName' => 'Text', 'isOld' => true],
        ];
        yield 'ReminderItem' => [
            ReminderItem::fromArray(...),
            ['id' => '1', 'total' => '12.5', 'is_charge' => '1'],
            ['total' => '12.5', 'isCharge' => true],
        ];
    }

    /**
     * @param Closure(array<string,mixed>): object $hydrate
     * @param array<string,mixed>                  $data
     * @param array<string,mixed>                  $expected
     */
    #[Test]
    #[DataProvider('models')]
    public function itHydratesNewFields(Closure $hydrate, array $data, array $expected): void
    {
        $props = get_object_vars($hydrate([...self::REQUIRED, ...$data]));

        foreach ($expected as $property => $value) {
            $actual = $props[$property];
            if ($actual instanceof DateTimeImmutable) {
                $actual = $actual->format('Y-m-d');
            }
            if ($actual instanceof Money) {
                $actual = $actual->toString();
            }

            self::assertSame($value, $actual, $property);
        }
    }

    /**
     * @param Closure(array<string,mixed>): object $hydrate
     * @param array<string,mixed>                  $data
     * @param array<string,mixed>                  $expected
     */
    #[Test]
    #[DataProvider('models')]
    public function itExportsNewFieldsInToArray(Closure $hydrate, array $data, array $expected): void
    {
        $model = $hydrate([...self::REQUIRED, ...$data]);
        self::assertTrue(method_exists($model, 'toArray'));
        $array = $model->toArray();
        self::assertIsArray($array);

        foreach (array_keys($data) as $key) {
            self::assertArrayHasKey($key, $array);
        }
    }

    /**
     * @param Closure(array<string,mixed>): object $hydrate
     * @param array<string,mixed>                  $data
     * @param array<string,mixed>                  $expected
     */
    #[Test]
    #[DataProvider('models')]
    public function itTreatsEmptyValuesAsNull(Closure $hydrate, array $data, array $expected): void
    {
        $empty = array_map(static fn (): string => '', $data);
        $props = get_object_vars($hydrate([...$empty, ...array_diff_key(self::REQUIRED, $expected)]));

        foreach (array_keys($expected) as $property) {
            self::assertNull($props[$property], $property);
        }
    }
}
