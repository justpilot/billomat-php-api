<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\EmailTemplate;

/**
 * Read-only API-Wrapper für E-Mail-Vorlagen.
 *
 * Doku: https://www.billomat.com/en/api/settings/email-templates/
 */
final class EmailTemplatesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<EmailTemplate>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/email-templates', $filters);

        $node = $data['email-templates']['email-template'] ?? [];

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

        /** @var list<EmailTemplate> $models */
        $models = array_map(EmailTemplate::fromArray(...), $rows);

        return $models;
    }

    public function get(int $id): ?EmailTemplate
    {
        $data = $this->getJsonOrNull("/email-templates/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['email-template'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return EmailTemplate::fromArray($row);
    }
}
