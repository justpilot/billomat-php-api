<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ eines Artikels bzw. einer Dokumentposition (Rechnung, Angebot, Gutschrift,
 * Auftragsbestätigung, Lieferschein, Abo-Rechnung): Produkt oder Dienstleistung.
 */
enum ItemType: string
{
    use FromApi;

    case PRODUCT = 'PRODUCT';
    case SERVICE = 'SERVICE';
}
