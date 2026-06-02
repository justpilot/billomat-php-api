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
        return $this->listResource('/email-templates', 'email-templates', 'email-template', EmailTemplate::fromArray(...), $filters);
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
