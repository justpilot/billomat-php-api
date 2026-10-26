<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;
use Justpilot\Billomat\Model\Template;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für Vorlagen (Templates).
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/vorlagen/
 */
final class TemplatesApi extends AbstractApi
{
    /**
     * Listet Vorlagen.
     *
     * Entspricht GET /templates
     *
     * @param TemplateFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Template>
     */
    #[NoDiscard]
    public function list(TemplateFilter|array $filters = []): array
    {
        return $this->listResource('/templates', 'templates', 'template', Template::fromArray(...), $filters);
    }

    /**
     * Wie {@see list()}, liefert aber zusätzlich die Pagination-Metadaten.
     *
     * @param TemplateFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<Template>
     */
    #[NoDiscard]
    public function listPage(TemplateFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/templates', 'templates', 'template', Template::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy über alle Seiten.
     *
     * @param TemplateFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Template>
     */
    #[NoDiscard]
    public function iterateAll(TemplateFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/templates', 'templates', 'template', Template::fromArray(...), $filters, $pageSize);
    }

    /**
     * Holt eine Vorlage.
     *
     * Entspricht GET /templates/{id}
     *
     * - Bei UPLOADED kommt ggf. format/base64file mit
     */
    #[NoDiscard]
    public function get(int $id): ?Template
    {
        return $this->getResource("/templates/{$id}", 'template', Template::fromArray(...), 'fetching template');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Template
    {
        return $this->get($id) ?? throw $this->notFound("/templates/{$id}");
    }

    /**
     * Erstellt eine Vorlage.
     *
     * Entspricht POST /templates
     */
    public function create(TemplateCreateOptions $options): Template
    {
        $payload = [
            'template' => $options->toArray(),
        ];

        $data = $this->postJson('/templates', $payload);

        $tpl = $data['template'] ?? null;

        if (!self::isRecord($tpl)) {
            throw new UnexpectedResponseException('Unexpected response from Billomat when creating template.');
        }

        /** @var array<string,mixed> $tpl */
        return Template::fromArray($tpl);
    }

    /**
     * Aktualisiert eine Vorlage.
     *
     * Entspricht PUT /templates/{id}
     */
    public function update(int $id, TemplateUpdateOptions $options): Template
    {
        $payload = [
            'template' => $options->toArray(),
        ];

        $data = $this->putJson("/templates/{$id}", $payload);

        $tpl = $data['template'] ?? null;

        if (!self::isRecord($tpl)) {
            throw new UnexpectedResponseException('Unexpected response from Billomat when updating template.');
        }

        /** @var array<string,mixed> $tpl */
        return Template::fromArray($tpl);
    }

    /**
     * Löscht eine Vorlage.
     *
     * Entspricht DELETE /templates/{id}
     */
    public function delete(int $id): void
    {
        $this->deleteVoid("/templates/{$id}");
    }

    /**
     * Lädt das Vorschaubild (Thumb) einer Vorlage als Raw/Binary.
     *
     * Entspricht GET /templates/{id}/thumb
     */
    #[NoDiscard]
    public function thumb(int $id, TemplateThumbFormat $format = TemplateThumbFormat::PNG): string
    {
        return $this->getRaw("/templates/{$id}/thumb", [
            'format' => $format->value,
        ]);
    }
}
