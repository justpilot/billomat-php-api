<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use Justpilot\Billomat\Model\InvoiceComment;
use RuntimeException;

/**
 * API-Wrapper für Rechnungskommentare (Invoice Comments).
 *
 * Endpoints:
 *  - GET    /invoice-comments?invoice_id={id}
 *  - GET    /invoice-comments/{id}
 *  - POST   /invoice-comments
 *  - DELETE /invoice-comments/{id}
 *
 * Doku: https://www.billomat.com/api/rechnungen/kommentare/
 */
final class InvoiceCommentsApi extends AbstractApi
{
    /**
     * Listet alle Kommentare einer Rechnung.
     *
     * `invoice_id` ist laut Doku Pflicht. Über `actionKeys` kann optional auf
     * bestimmte ActionKey-Typen gefiltert werden (CSV).
     *
     * @param list<InvoiceCommentActionKey>|null $actionKeys
     *
     * @return list<InvoiceComment>
     */
    public function listByInvoice(int $invoiceId, ?array $actionKeys = null): array
    {
        $query = ['invoice_id' => $invoiceId];

        if (null !== $actionKeys && [] !== $actionKeys) {
            $query['actionkey'] = implode(',', array_map(
                static fn (InvoiceCommentActionKey $a): string => $a->value,
                $actionKeys,
            ));
        }

        $data = $this->getJson('/invoice-comments', $query);

        $root = $data['invoice-comments'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['invoice-comment'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<InvoiceComment> $comments */
        $comments = array_map(
            InvoiceComment::fromArray(...),
            $rows,
        );

        return $comments;
    }

    public function get(int $id): ?InvoiceComment
    {
        $data = $this->getJsonOrNull("/invoice-comments/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['invoice-comment'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return InvoiceComment::fromArray($row);
    }

    public function create(InvoiceCommentCreateOptions $options): InvoiceComment
    {
        $payload = ['invoice-comment' => $options->toArray()];

        $data = $this->postJson('/invoice-comments', $payload);

        $row = $data['invoice-comment'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating invoice comment.');
        }

        return InvoiceComment::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/invoice-comments/{$id}");

        return true;
    }
}
