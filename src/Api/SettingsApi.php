<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Settings;

/**
 * API-Wrapper für Account-Einstellungen.
 */
final class SettingsApi extends AbstractApi
{
    /**
     * GET /settings
     */
    public function get(): Settings
    {
        $data = $this->getJson('/settings');

        if (!isset($data['settings']) || !is_array($data['settings'])) {
            throw new \RuntimeException('Unexpected response from Billomat when fetching settings.');
        }

        return Settings::fromArray($data['settings']);
    }

    /**
     * PUT /settings
     *
     * Aktualisiert Account-Einstellungen.
     */
    public function update(SettingsUpdateOptions $options): Settings
    {
        $payload = [
            'settings' => $options->toArray(),
        ];

        $data = $this->putJson('/settings', $payload);

        if (!isset($data['settings']) || !is_array($data['settings'])) {
            throw new \RuntimeException('Unexpected response from Billomat when updating settings.');
        }

        return Settings::fromArray($data['settings']);
    }
}