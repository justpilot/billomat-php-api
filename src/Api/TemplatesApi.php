<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;
use Justpilot\Billomat\Model\Template;

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
     * @param array<string, scalar|array|null> $query
     * @return list<Template>
     */
    public function list(array $query = []): array
    {
        $data = $this->getJson('/templates', $query);

        $raw = $data['templates']['template'] ?? [];

        // Billomat kann bei 1 Element ein einzelnes Objekt liefern
        if (is_array($raw) && isset($raw['id'])) {
            $raw = [$raw];
        }

        if (!is_array($raw)) {
            return [];
        }

        $templates = [];
        foreach ($raw as $row) {
            if (is_array($row)) {
                /** @var array<string,mixed> $row */
                $templates[] = Template::fromArray($row);
            }
        }

        return $templates;
    }

    /**
     * Holt eine Vorlage.
     *
     * Entspricht GET /templates/{id}
     *
     * - Bei UPLOADED kommt ggf. format/base64file mit
     */
    public function get(int $id): ?Template
    {
        $data = $this->getJsonOrNull("/templates/{$id}");

        if ($data === null) {
            return null;
        }

        $tpl = $data['template'] ?? null;

        if (!is_array($tpl)) {
            throw new \RuntimeException('Unexpected response from Billomat when fetching template.');
        }

        /** @var array<string,mixed> $tpl */
        return Template::fromArray($tpl);
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

        if (!is_array($tpl)) {
            throw new \RuntimeException('Unexpected response from Billomat when creating template.');
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

        if (!is_array($tpl)) {
            throw new \RuntimeException('Unexpected response from Billomat when updating template.');
        }

        /** @var array<string,mixed> $tpl */
        return Template::fromArray($tpl);
    }

    /**
     * Löscht eine Vorlage.
     *
     * Entspricht DELETE /templates/{id}
     */
    public function delete(int $id): bool
    {
        $this->deleteVoid("/templates/{$id}");
        return true;
    }

    /**
     * Lädt das Vorschaubild (Thumb) einer Vorlage als Raw/Binary.
     *
     * Entspricht GET /templates/{id}/thumb
     */
    public function thumb(int $id, TemplateThumbFormat $format = TemplateThumbFormat::PNG): string
    {
        return $this->getRaw("/templates/{$id}/thumb", [
            'format' => $format->value,
        ]);
    }
}