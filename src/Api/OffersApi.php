<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Offer;
use Justpilot\Billomat\Model\OfferPdf;
use Justpilot\Billomat\Pagination\Page;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

/**
 * API-Wrapper für die Billomat-Offers-Ressource (Angebote / Estimates).
 *
 * Doku: https://www.billomat.com/en/api/estimates/
 *
 * Endpoints:
 *  - GET    /offers
 *  - GET    /offers/{id}
 *  - POST   /offers
 *  - PUT    /offers/{id}
 *  - DELETE /offers/{id}
 *  - PUT    /offers/{id}/complete
 *  - PUT    /offers/{id}/cancel
 *  - PUT    /offers/{id}/win
 *  - PUT    /offers/{id}/lose
 *  - PUT    /offers/{id}/clear
 *  - PUT    /offers/{id}/undo (Status zurücksetzen)
 *  - POST   /offers/{id}/email
 *  - GET    /offers/{id}/pdf
 *  - PUT    /offers/{id}/upload-signature
 */
final class OffersApi extends AbstractApi
{
    /**
     * Listet Angebote mit optionalen Filtern.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Offer>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/offers', 'offers', 'offer', Offer::fromArray(...), $filters);
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
     * @return Page<Offer>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/offers', 'offers', 'offer', Offer::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Angebote und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Offer>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/offers', 'offers', 'offer', Offer::fromArray(...), $filters, $pageSize);
    }

    /**
     * Holt ein einzelnes Angebot, oder null wenn nicht gefunden.
     */
    public function get(int $id): ?Offer
    {
        $data = $this->getJsonOrNull("/offers/{$id}");

        if (null === $data) {
            return null;
        }

        $offerData = $data['offer'] ?? null;

        if (!\is_array($offerData)) {
            return null;
        }

        return Offer::fromArray($offerData);
    }

    /**
     * Legt ein neues Angebot an (Status: DRAFT).
     *
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(OfferCreateOptions $options): Offer
    {
        $payload = ['offer' => $options->toArray()];

        $data = $this->postJson('/offers', $payload);

        return Offer::fromArray($this->unwrapEnvelope($data, 'offer', 'creating offer'));
    }

    /**
     * Bearbeitet ein Angebot.
     *
     * Voll editierbar nur im Status DRAFT.
     */
    public function update(int $id, OfferUpdateOptions $options): Offer
    {
        $payload = ['offer' => $options->toArray()];

        $data = $this->putJson("/offers/{$id}", $payload);

        return Offer::fromArray($this->unwrapEnvelope($data, 'offer', 'updating offer'));
    }

    /**
     * Schließt ein Angebot ab (DRAFT → OPEN, vergibt Angebotsnummer).
     */
    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['offer' => $body];

        $this->putVoid("/offers/{$id}/complete", $payload);

        return true;
    }

    /**
     * Löscht ein Angebot (nur im Status DRAFT erlaubt).
     *
     * @throws ValidationException wenn das Angebot nicht DRAFT ist
     */
    public function delete(int $id): bool
    {
        $this->deleteVoid("/offers/{$id}");

        return true;
    }

    /** Storniert ein Angebot. */
    public function cancel(int $id): bool
    {
        $this->putVoid("/offers/{$id}/cancel");

        return true;
    }

    /** Markiert ein Angebot als gewonnen (Status → ACCEPTED). */
    public function win(int $id): bool
    {
        $this->putVoid("/offers/{$id}/win");

        return true;
    }

    /** Markiert ein Angebot als verloren (Status → REJECTED). */
    public function lose(int $id): bool
    {
        $this->putVoid("/offers/{$id}/lose");

        return true;
    }

    /** Markiert ein Angebot als erledigt (Status → CLEARED). */
    public function clear(int $id): bool
    {
        $this->putVoid("/offers/{$id}/clear");

        return true;
    }

    /**
     * Setzt einen Status zurück auf OPEN (Rückgängigmachen von win/lose/clear/cancel).
     */
    public function undo(int $id): bool
    {
        $this->putVoid("/offers/{$id}/undo");

        return true;
    }

    /** Versendet ein Angebot per E-Mail. */
    public function email(int $id, ?OfferEmailOptions $options = null): bool
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/offers/{$id}/email", $payload);

        return true;
    }

    /**
     * Lädt eine unterschriebene PDF-Version des Angebots hoch.
     */
    public function uploadSignature(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/offers/{$id}/upload-signature", $payload);

        return true;
    }

    /**
     * Ruft das PDF eines Angebots ab.
     *
     * @return OfferPdf|string OfferPdf im JSON-Modus oder binärer PDF-Inhalt im Raw-Modus
     */
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): OfferPdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            $response = $this->http->request('GET', "/offers/{$id}/pdf", $query);

            try {
                return $response->getContent();
            } catch (HttpExceptionInterface $e) {
                throw $this->mapHttpException($e);
            }
        }

        $data = $this->getJson("/offers/{$id}/pdf", $query);

        return OfferPdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching offer PDF'));
    }
}
