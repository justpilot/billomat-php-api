<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\DocumentPdf;
use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;
use Justpilot\Billomat\Model\Enum\IncomingGroupBy;
use Justpilot\Billomat\Model\Incoming;
use Justpilot\Billomat\Model\IncomingComment;
use Justpilot\Billomat\Model\IncomingGroup;
use Justpilot\Billomat\Model\IncomingItem;
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
 *  - GET    /incomings/{id}/pdf
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
     * Posten, siehe {@see ItemsApi}.
     *
     * @var ItemsApi<IncomingItem, IncomingItemCreateOptions, IncomingItemUpdateOptions>
     */
    public private(set) ItemsApi $items {
        get => $this->items ??= new ItemsApi($this->http, 'incoming', IncomingItem::fromArray(...), IncomingItemCreateOptions::class, IncomingItemUpdateOptions::class);
    }

    /**
     * Zahlungen, siehe {@see PaymentsApi}.
     *
     * @var PaymentsApi<IncomingPayment, IncomingPaymentCreateOptions, IncomingPaymentFilter>
     */
    public private(set) PaymentsApi $payments {
        get => $this->payments ??= new PaymentsApi($this->http, 'incoming', IncomingPayment::fromArray(...), IncomingPaymentCreateOptions::class, IncomingPaymentFilter::class);
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
     * @var PropertyValuesApi<IncomingPropertyValue, IncomingPropertyValueCreateOptions, IncomingPropertyValueFilter>
     */
    public private(set) PropertyValuesApi $propertyValues {
        get => $this->propertyValues ??= new PropertyValuesApi($this->http, 'incoming', IncomingPropertyValue::fromArray(...), IncomingPropertyValueCreateOptions::class, IncomingPropertyValueFilter::class);
    }

    /**
     * @param IncomingFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Incoming>
     */
    #[NoDiscard]
    public function list(IncomingFilter|array $filters = []): array
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
     * @param IncomingFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<Incoming>
     */
    #[NoDiscard]
    public function listPage(IncomingFilter|array $filters = []): Page
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
     * @param IncomingFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Incoming>
     */
    #[NoDiscard]
    public function iterateAll(IncomingFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/incomings', 'incomings', 'incoming', Incoming::fromArray(...), $filters, $pageSize);
    }

    /**
     * Listet Eingangsrechnungen aggregiert nach einem oder mehreren Kriterien.
     *
     * Entspricht GET /incomings?group_by=...
     *
     * Mehrere Werte werden in der Reihenfolge der Aggregation als CSV gesendet
     * ("supplier,year" gruppiert zuerst nach Lieferant, dann nach Jahr).
     *
     * @param IncomingGroupBy|non-empty-list<IncomingGroupBy>              $groupBy
     * @param IncomingFilter|array<string, scalar|array<scalar|null>|null> $filters optionale zusätzliche Filter (analog zu list())
     *
     * @return list<IncomingGroup>
     */
    #[NoDiscard]
    public function listGrouped(IncomingGroupBy|array $groupBy, IncomingFilter|array $filters = []): array
    {
        $values = \is_array($groupBy) ? $groupBy : [$groupBy];

        $csv = implode(',', array_map(static fn (IncomingGroupBy $g): string => $g->value, $values));

        $query = self::filterQuery($filters);
        $query['group_by'] = $csv;

        return $this->listResource('/incomings', 'incoming-groups', 'incoming-group', IncomingGroup::fromArray(...), $query);
    }

    #[NoDiscard]
    public function get(int $id): ?Incoming
    {
        return $this->getResource("/incomings/{$id}", 'incoming', Incoming::fromArray(...), 'fetching incoming');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Incoming
    {
        return $this->get($id) ?? throw $this->notFound("/incomings/{$id}");
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

    /**
     * Ruft das hinterlegte PDF einer Eingangsrechnung als JSON ab (Metadaten und base64-kodierter Inhalt).
     *
     * Entspricht GET /incomings/{id}/pdf. Ohne hinterlegte Datei antwortet Billomat mit 404
     * ({@see NotFoundException}). Die rohen Bytes liefert {@see pdfContent()}.
     */
    #[NoDiscard]
    public function pdf(int $id): DocumentPdf
    {
        $data = $this->getJson("/incomings/{$id}/pdf");

        return DocumentPdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching incoming PDF'));
    }

    /**
     * Ruft das hinterlegte PDF einer Eingangsrechnung als binären Inhalt ab (GET /incomings/{id}/pdf?format=pdf).
     *
     * @throws UnexpectedResponseException wenn Billomat kein PDF liefert
     */
    #[NoDiscard]
    public function pdfContent(int $id): string
    {
        return $this->getPdf("/incomings/{$id}/pdf", ['format' => 'pdf']);
    }

    /**
     * Liest das eigene Metadaten-Feld `customfield` (z. B. die ID im eigenen System); null, wenn es leer ist.
     *
     * Entspricht GET /incomings/{id}/customfield. Doku: https://www.billomat.com/api/grundlagen/eigene-meta-daten/
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getCustomField(int $id): ?string
    {
        return $this->fetchCustomField("/incomings/{$id}", 'incoming');
    }

    /**
     * Setzt das eigene Metadaten-Feld `customfield`; ein leerer String leert es.
     *
     * Entspricht PUT /incomings/{id}/customfield. Über die Update-Options lässt sich das Feld nicht setzen.
     */
    public function setCustomField(int $id, string $value): void
    {
        $this->storeCustomField("/incomings/{$id}", 'incoming', $value);
    }
}
