<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Contact;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Contact::class)]
final class ContactTest extends TestCase
{
    #[Test]
    public function itHydratesFullContactFromArray(): void
    {
        $contact = Contact::fromArray([
            'id' => '5',
            'client_id' => '42',
            'created' => '2024-06-01T10:00:00+02:00',
            'label' => 'Hauptansprechpartner',
            'salutation' => 'Herr',
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
            'street' => 'Musterstraße 1',
            'zip' => '12345',
            'city' => 'Berlin',
            'state' => 'BE',
            'country_code' => 'DE',
            'email' => 'max@example.com',
            'phone' => '+49 30 12345',
            'fax' => '+49 30 12346',
            'mobile' => '+49 170 1234567',
            'note' => 'CEO',
        ]);

        self::assertSame(5, $contact->id);
        self::assertSame(42, $contact->clientId);
        self::assertInstanceOf(DateTimeImmutable::class, $contact->created);
        self::assertSame('Max', $contact->firstName);
        self::assertSame('Mustermann', $contact->lastName);
        self::assertSame('12345', $contact->zip);
        self::assertSame('DE', $contact->countryCode);
        self::assertSame('max@example.com', $contact->email);
        self::assertSame('+49 170 1234567', $contact->mobile);
    }

    #[Test]
    public function itHandlesMissingOptionalFields(): void
    {
        $contact = Contact::fromArray(['id' => '1', 'client_id' => '42']);

        self::assertSame(1, $contact->id);
        self::assertSame(42, $contact->clientId);
        self::assertNull($contact->firstName);
        self::assertNull($contact->email);
        self::assertNull($contact->created);
    }

    #[Test]
    public function itHandlesInvalidCreatedDateGracefully(): void
    {
        $contact = Contact::fromArray(['id' => '1', 'client_id' => '1', 'created' => 'not-a-date']);

        self::assertNull($contact->created);
    }

    #[Test]
    public function toArrayMapsCamelCaseBackToSnakeCase(): void
    {
        $array = Contact::fromArray([
            'id' => '5',
            'client_id' => '42',
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
            'country_code' => 'DE',
        ])->toArray();

        self::assertSame('Max', $array['first_name']);
        self::assertSame('Mustermann', $array['last_name']);
        self::assertSame('DE', $array['country_code']);
        self::assertSame(42, $array['client_id']);
    }
}
