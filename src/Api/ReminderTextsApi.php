<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\ReminderText;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für Mahn-Textbausteine.
 *
 * Doku: https://www.billomat.com/en/api/settings/reminder-texts/
 */
final class ReminderTextsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<ReminderText>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/reminder-texts', 'reminder-texts', 'reminder-text', ReminderText::fromArray(...), $filters);
    }

    /**
     * Wie {@see list()}, liefert aber zusätzlich die Pagination-Metadaten.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<ReminderText>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/reminder-texts', 'reminder-texts', 'reminder-text', ReminderText::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy über alle Seiten.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, ReminderText>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/reminder-texts', 'reminder-texts', 'reminder-text', ReminderText::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?ReminderText
    {
        return $this->getResource("/reminder-texts/{$id}", 'reminder-text', ReminderText::fromArray(...), 'fetching reminder text');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): ReminderText
    {
        return $this->get($id) ?? throw $this->notFound("/reminder-texts/{$id}");
    }

    /**
     * Legt eine neue Mahnstufe an.
     */
    public function create(ReminderTextCreateOptions $options): ReminderText
    {
        $data = $this->postJson('/reminder-texts', ['reminder-text' => $options->toArray()]);

        return ReminderText::fromArray($this->unwrapEnvelope($data, 'reminder-text', 'creating reminder text'));
    }

    /**
     * Ändert eine Mahnstufe (PUT /reminder-texts/{id}).
     *
     * Nur die in den Options gesetzten Felder werden gesendet.
     */
    public function update(int $id, ReminderTextUpdateOptions $options): ReminderText
    {
        $data = $this->putJson("/reminder-texts/{$id}", ['reminder-text' => $options->toArray()]);

        return ReminderText::fromArray($this->unwrapEnvelope($data, 'reminder-text', 'updating reminder text'));
    }

    /**
     * Löscht eine Mahnstufe.
     */
    public function delete(int $id): void
    {
        $this->deleteVoid("/reminder-texts/{$id}");
    }
}
