<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Settings;
use NoDiscard;

/**
 * API-Wrapper für Account-Einstellungen.
 */
final class SettingsApi extends AbstractApi
{
    /**
     * GET /settings.
     */
    #[NoDiscard]
    public function get(): Settings
    {
        $data = $this->getJson('/settings');

        return Settings::fromArray($this->unwrapEnvelope($data, 'settings', 'fetching settings'));
    }

    /**
     * PUT /settings.
     *
     * Aktualisiert Account-Einstellungen.
     */
    public function update(SettingsUpdateOptions $options): Settings
    {
        $payload = [
            'settings' => $options->toArray(),
        ];

        $data = $this->putJson('/settings', $payload);

        return Settings::fromArray($this->unwrapEnvelope($data, 'settings', 'updating settings'));
    }
}
