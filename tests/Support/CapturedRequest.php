<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Support;

use const PHP_URL_PATH;
use const PHP_URL_QUERY;

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
     * Fertig befüllter Request, z. B. für Listen mehrerer mitgeschnittener Aufrufe.
     *
     * @param array<mixed> $options
     */
    public static function of(string $method, string $url, array $options = []): self
    {
        $request = new self();
        $request->record($method, $url, $options);

        return $request;
    }

    /**
     * Merkt sich einen ausgehenden Request (Aufruf aus dem `MockHttpClient`-Callback).
     *
     * @param array<mixed> $options
     */
    public function record(string $method, string $url, array $options = []): void
    {
        $this->method = $method;
        $this->url = $url;
        $this->options = $options;
    }

    /**
     * Dekodierter JSON-Body (Top-Level-Objekt) des Requests.
     *
     * @return array<string, mixed>
     */
    public function json(): array
    {
        return JsonPayload::fromOptions($this->options);
    }

    /**
     * Wert im JSON-Body unter dem angegebenen Pfad, oder null, wenn er fehlt.
     *
     * Ein Zwischenschritt, der kein Array ist, lässt den Test scheitern.
     */
    public function jsonAt(string|int ...$path): mixed
    {
        return Payload::at($this->json(), ...$path);
    }

    /**
     * Array im JSON-Body unter dem angegebenen Pfad; scheitert, wenn dort kein Array liegt.
     *
     * @return array<array-key, mixed>
     */
    public function jsonArray(string|int ...$path): array
    {
        return Payload::arrayAt($this->json(), ...$path);
    }

    /**
     * Geparste Query-Parameter der Request-URL.
     *
     * @return array<array-key, mixed>
     */
    public function query(): array
    {
        return self::queryOf($this->url);
    }

    /**
     * Skalarer Query-Parameter einer beliebigen URL, oder null, wenn er fehlt oder ein Array ist.
     */
    public static function queryParamOf(string $url, string $name): ?string
    {
        $value = self::queryOf($url)[$name] ?? null;

        return \is_string($value) ? $value : null;
    }

    /**
     * Geparste Query-Parameter einer beliebigen URL.
     *
     * @return array<array-key, mixed>
     */
    public static function queryOf(string $url): array
    {
        $raw = parse_url($url, PHP_URL_QUERY);
        $query = [];
        if (\is_string($raw)) {
            parse_str($raw, $query);
        }

        return $query;
    }

    /**
     * Pfad der Request-URL (ohne Host und Query).
     */
    public function path(): ?string
    {
        $path = parse_url($this->url, PHP_URL_PATH);

        return \is_string($path) ? $path : null;
    }

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
