<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\EmailTemplate;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für E-Mail-Vorlagen.
 *
 * Doku: https://www.billomat.com/en/api/settings/email-templates/
 */
final class EmailTemplatesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<EmailTemplate>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/email-templates', 'email-templates', 'email-template', EmailTemplate::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<EmailTemplate>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/email-templates', 'email-templates', 'email-template', EmailTemplate::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle EmailTemplate und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, EmailTemplate>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/email-templates', 'email-templates', 'email-template', EmailTemplate::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?EmailTemplate
    {
        return $this->getResource("/email-templates/{$id}", 'email-template', EmailTemplate::fromArray(...), 'fetching email template');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): EmailTemplate
    {
        return $this->get($id) ?? throw $this->notFound("/email-templates/{$id}");
    }

    /**
     * Legt eine neue E-Mail-Vorlage an.
     */
    public function create(EmailTemplateCreateOptions $options): EmailTemplate
    {
        $data = $this->postJson('/email-templates', ['email-template' => $options->toArray()]);

        return EmailTemplate::fromArray($this->unwrapEnvelope($data, 'email-template', 'creating email template'));
    }

    /**
     * Ändert eine E-Mail-Vorlage (PUT /email-templates/{id}).
     *
     * Nur die in den Options gesetzten Felder werden gesendet.
     */
    public function update(int $id, EmailTemplateUpdateOptions $options): EmailTemplate
    {
        $data = $this->putJson("/email-templates/{$id}", ['email-template' => $options->toArray()]);

        return EmailTemplate::fromArray($this->unwrapEnvelope($data, 'email-template', 'updating email template'));
    }

    /**
     * Löscht eine E-Mail-Vorlage.
     */
    public function delete(int $id): void
    {
        $this->deleteVoid("/email-templates/{$id}");
    }
}
