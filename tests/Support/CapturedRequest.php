<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Support;

/**
 * Hält den ausgehenden Request fest, den ein `MockHttpClient`-Callback gesehen hat.
 *
 * Methode, URL und rohe Optionen sind typisiert; Header und Body gibt es als
 * Zugriffsmethoden, damit die Tests nicht in lose `mixed`-Arrays greifen.
 */
final class CapturedRequest
{
    public string $method = '';

    public string $url = '';

    /**
     * Rohe Optionen, mit denen der `MockHttpClient`-Callback aufgerufen wurde.
     *
     * @var array<mixed>
     */
    public array $options = [];

    /**
     * Gesendeter Request-Body als String, oder null, wenn keiner gesendet wurde.
     */
    public function body(): ?string
    {
        $body = $this->options['body'] ?? null;

        return \is_string($body) ? $body : null;
    }

    /**
     * Gesendete Header mit kleingeschriebenen Namen.
     *
     * Symfony liefert sie entweder als Name-zu-Wert-Map oder als Liste fertiger
     * „Name: Wert“-Zeilen; mehrere Werte eines Headers werden mit Komma verbunden.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        $raw = $this->options['headers'] ?? [];
        if (!\is_array($raw)) {
            return [];
        }

        $headers = [];
        foreach ($raw as $key => $value) {
            if (\is_int($key)) {
                if (\is_string($value) && str_contains($value, ':')) {
                    [$name, $val] = explode(':', $value, 2);
                    $headers[strtolower(trim($name))] = trim($val);
                }

                continue;
            }

            if (\is_string($value)) {
                $headers[strtolower($key)] = $value;
            } elseif (\is_array($value)) {
                $headers[strtolower($key)] = implode(', ', array_filter($value, \is_string(...)));
            }
        }

        return $headers;
    }
}
