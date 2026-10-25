<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;
use Justpilot\Billomat\Model\Incoming;
use Justpilot\Billomat\Model\IncomingComment;
use Justpilot\Billomat\Model\IncomingPayment;
use Justpilot\Billomat\Model\IncomingProperty;
use Justpilot\Billomat\Model\IncomingPropertyValue;
use Justpilot\Billomat\Model\IncomingTag;
use Justpilot\Billomat\Model\IncomingTagCloudEntry;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Billomat-Incomings-Ressource (Eingangsrechnungen).
 *
 * Doku: https://www.billomat.com/en/api/incomings/
 *
 * Endpoints:
 *  - GET    /incomings
 *  - GET    /incomings/{id}
 *  - POST   /incomings
 *  - PUT    /incomings/{id}
 *  - DELETE /incomings/{id}
 *  - PUT    /incomings/{id}/cancel
 *  - PUT    /incomings/{id}/uncancel
 *  - PUT    /incomings/{id}/upload  (PDF des Belegs hochladen)
 */
final class IncomingsApi extends AbstractApi
{
    /**
     * Kommentare, siehe {@see CommentsApi}.
     *
     * @var CommentsApi<IncomingComment, IncomingCommentCreateOptions, IncomingCommentActionKey>
     */
    public private(set) CommentsApi $comments {
        get => $this->comments ??= new CommentsApi($this->http, 'incoming', IncomingComment::fromArray(...), IncomingCommentCreateOptions::class, IncomingCommentActionKey::class);
    }

    /**
     * Zahlungen, siehe {@see PaymentsApi}.
     *
     * @var PaymentsApi<IncomingPayment, IncomingPaymentCreateOptions>
     */
    public private(set) PaymentsApi $payments {
        get => $this->payments ??= new PaymentsApi($this->http, 'incoming', IncomingPayment::fromArray(...), IncomingPaymentCreateOptions::class);
    }

    /**
     * Schlagworte, siehe {@see TagsApi}.
     *
     * @var TagsApi<IncomingTag, IncomingTagCloudEntry, IncomingTagCreateOptions>
     */
    public private(set) TagsApi $tags {
        get => $this->tags ??= new TagsApi($this->http, 'incoming', IncomingTag::fromArray(...), IncomingTagCloudEntry::fromArray(...), IncomingTagCreateOptions::class);
    }

    /**
     * Attribut-Definitionen, siehe {@see PropertiesApi}.
     *
     * @var PropertiesApi<IncomingProperty>
     */
    public private(set) PropertiesApi $properties {
        get => $this->properties ??= new PropertiesApi($this->http, 'incoming', IncomingProperty::fromArray(...));
    }

    /**
     * Attributwerte, siehe {@see PropertyValuesApi}.
     *
     * @var PropertyValuesApi<IncomingPropertyValue, IncomingPropertyValueCreateOptions>
     */
    public private(set) PropertyValuesApi $propertyValues {
        get => $this->propertyValues ??= new PropertyValuesApi($this->http, 'incoming', IncomingPropertyValue::fromArray(...), IncomingPropertyValueCreateOptions::class);
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Incoming>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/incomings', 'incomings', 'incoming', Incoming::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<Incoming>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/incomings', 'incomings', 'incoming', Incoming::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Eingangsrechnungen und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Incoming>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/incomings', 'incomings', 'incoming', Incoming::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Incoming
    {
        $data = $this->getJsonOrNull("/incomings/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Incoming::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(IncomingCreateOptions $options): Incoming
    {
        $payload = ['incoming' => $options->toArray()];

        $data = $this->postJson('/incomings', $payload);

        return Incoming::fromArray($this->unwrapEnvelope($data, 'incoming', 'creating incoming'));
    }

    public function update(int $id, IncomingUpdateOptions $options): Incoming
    {
        $payload = ['incoming' => $options->toArray()];

        $data = $this->putJson("/incomings/{$id}", $payload);

        return Incoming::fromArray($this->unwrapEnvelope($data, 'incoming', 'updating incoming'));
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/incomings/{$id}");
    }

    public function cancel(int $id): void
    {
        $this->putVoid("/incomings/{$id}/cancel");
    }

    public function uncancel(int $id): void
    {
        $this->putVoid("/incomings/{$id}/uncancel");
    }

    /**
     * Lädt ein PDF zur Eingangsrechnung hoch.
     */
    public function upload(int $id, string $base64Pdf): void
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/incomings/{$id}/upload", $payload);
    }
}
