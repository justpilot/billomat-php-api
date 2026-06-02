<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Offer;
use Justpilot\Billomat\Model\OfferPdf;
use RuntimeException;
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
        $data = $this->getJson('/offers', $filters);

        $node = $data['offers']['offer'] ?? [];

        if (null === $node || [] === $node) {
            return [];
        }

        if (\is_array($node) && array_is_list($node)) {
            $rows = $node;
        } elseif (\is_array($node)) {
            $rows = [$node];
        } else {
            $rows = [];
        }

        /** @var list<Offer> $models */
        $models = array_map(
            Offer::fromArray(...),
            $rows,
        );

        return $models;
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

        $created = $data['offer'] ?? null;

        if (!\is_array($created)) {
            throw new RuntimeException('Unexpected response from Billomat when creating offer.');
        }

        return Offer::fromArray($created);
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

        $offerData = $data['offer'] ?? null;
        if (!\is_array($offerData)) {
            throw new RuntimeException('Unexpected response from Billomat when updating offer.');
        }

        return Offer::fromArray($offerData);
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

        $response = $this->putEmptyResponse("/offers/{$id}/complete", $payload);

        return 200 === $response->getStatusCode();
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
        $response = $this->putEmptyResponse("/offers/{$id}/cancel");

        return 200 === $response->getStatusCode();
    }

    /** Markiert ein Angebot als gewonnen (Status → ACCEPTED). */
    public function win(int $id): bool
    {
        $response = $this->putEmptyResponse("/offers/{$id}/win");

        return 200 === $response->getStatusCode();
    }

    /** Markiert ein Angebot als verloren (Status → REJECTED). */
    public function lose(int $id): bool
    {
        $response = $this->putEmptyResponse("/offers/{$id}/lose");

        return 200 === $response->getStatusCode();
    }

    /** Markiert ein Angebot als erledigt (Status → CLEARED). */
    public function clear(int $id): bool
    {
        $response = $this->putEmptyResponse("/offers/{$id}/clear");

        return 200 === $response->getStatusCode();
    }

    /**
     * Setzt einen Status zurück auf OPEN (Rückgängigmachen von win/lose/clear/cancel).
     */
    public function undo(int $id): bool
    {
        $response = $this->putEmptyResponse("/offers/{$id}/undo");

        return 200 === $response->getStatusCode();
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

        $response = $this->putEmptyResponse("/offers/{$id}/upload-signature", $payload);

        return 200 === $response->getStatusCode();
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

        $pdfData = $data['pdf'] ?? null;

        if (!\is_array($pdfData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching offer PDF.');
        }

        return OfferPdf::fromArray($pdfData);
    }
}
