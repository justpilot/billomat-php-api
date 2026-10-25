<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Config;

use InvalidArgumentException;
use Uri\Rfc3986\Uri;

/**
 * Zugangsdaten und Verbindungs-Einstellungen.
 *
 * `$timeout` gilt nur für den HTTP-Client, den das SDK selbst erzeugt. Wer einen
 * eigenen PSR-18-Client übergibt, konfiguriert den Timeout dort.
 */
final readonly class BillomatConfig
{
    /**
     * @throws InvalidArgumentException wenn die Billomat-ID kein gültiger Subdomain-Name ist
     *                                  oder sich daraus keine gültige Basis-URI ergibt
     */
    public function __construct(
        public string $billomatId,
        public string $apiKey,
        public ?string $appId = null,
        public ?string $appSecret = null,
        public string $baseUri = 'https://%s.billomat.net/api/',
        public float $timeout = 10.0,
        public RetryPolicy $retry = new RetryPolicy(),
    ) {
        // Die Billomat-ID ist die Subdomain des Accounts; Zeichen wie "/" oder "@" würden den Host verändern.
        if (1 !== preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i', $billomatId)) {
            throw new InvalidArgumentException(\sprintf('Invalid Billomat ID "%s": expected the subdomain of your Billomat account.', $billomatId));
        }

        $this->parseBaseUri();
    }

    public function getBaseUri(): string
    {
        return $this->parseBaseUri()->toRawString();
    }

    private function parseBaseUri(): Uri
    {
        $uri = Uri::parse(\sprintf($this->baseUri, $this->billomatId));

        if (!$uri instanceof Uri || null === $uri->getScheme() || null === $uri->getHost()) {
            throw new InvalidArgumentException(\sprintf('Invalid base URI "%s": expected an absolute URI such as "https://%%s.billomat.net/api/".', $this->baseUri));
        }

        return $uri;
    }
}
