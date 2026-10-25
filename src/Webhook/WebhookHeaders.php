<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Webhook;

use Justpilot\Billomat\Exception\InvalidWebhookException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Die `X-Billomat-Webhook-*`-Header eines eingehenden Webhook-Requests.
 *
 * ```php
 * $headers = WebhookHeaders::fromServerRequest($request); // PSR-7
 * $headers = WebhookHeaders::fromArray($_SERVER);         // ohne PSR-7
 * $headers = WebhookHeaders::fromArray($symfonyRequest->headers->all());
 * ```
 */
final readonly class WebhookHeaders
{
    public function __construct(
        /** `X-Billomat-Webhook-Event` */
        public WebhookEvent $event,
        /** `X-Billomat-Webhook-Request-Id`: eindeutig und monoton steigend, geeignet für Idempotenz und Sortierung. */
        public int $requestId,
        /** `X-Billomat-Webhook-Id`: ID der Webhook-Konfiguration im Billomat-UI. */
        public ?int $webhookId = null,
    ) {
    }

    /**
     * Header aus einem Array, Groß-/Kleinschreibung egal.
     *
     * Akzeptiert Header-Namen (`X-Billomat-Webhook-Event`), `$_SERVER`-Schlüssel
     * (`HTTP_X_BILLOMAT_WEBHOOK_EVENT`) und Werte als String oder Liste (PSR-7, Symfony).
     *
     * @param array<array-key, mixed> $headers
     *
     * @throws InvalidWebhookException bei fehlendem oder ungültigem Event bzw. Request-ID
     */
    public static function fromArray(array $headers): self
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $name = str_replace('_', '-', strtolower((string) $name));
            if (\is_array($value)) {
                $value = array_first($value);
            }
            if (\is_string($value) || \is_int($value)) {
                $normalized[str_starts_with($name, 'http-') ? substr($name, 5) : $name] = trim((string) $value);
            }
        }

        $eventName = $normalized['x-billomat-webhook-event'] ?? '';
        if ('' === $eventName) {
            throw new InvalidWebhookException('Missing header X-Billomat-Webhook-Event.');
        }
        $event = WebhookEvent::tryFrom($eventName) ?? throw new InvalidWebhookException(\sprintf('Unknown Billomat webhook event "%s".', $eventName));

        $requestId = $normalized['x-billomat-webhook-request-id'] ?? '';
        if (!ctype_digit($requestId)) {
            throw new InvalidWebhookException(\sprintf('Missing or invalid header X-Billomat-Webhook-Request-Id: "%s".', $requestId));
        }

        $webhookId = $normalized['x-billomat-webhook-id'] ?? '';

        return new self($event, (int) $requestId, ctype_digit($webhookId) ? (int) $webhookId : null);
    }

    /**
     * @throws InvalidWebhookException bei fehlendem oder ungültigem Event bzw. Request-ID
     */
    public static function fromServerRequest(ServerRequestInterface $request): self
    {
        return self::fromArray($request->getHeaders());
    }
}
