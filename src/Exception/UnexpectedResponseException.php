<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

/**
 * Billomat hat erfolgreich (2xx) geantwortet, der Body passt aber nicht zur
 * erwarteten Struktur – z. B. fehlt das Wrapper-Element `client` oder `invoice`.
 *
 * Sollte in der Praxis nicht auftreten; deutet meist auf eine geänderte API hin.
 */
class UnexpectedResponseException extends BillomatException
{
}
