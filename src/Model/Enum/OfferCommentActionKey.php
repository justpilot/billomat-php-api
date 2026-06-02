<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ eines Angebots-Kommentars (actionkey).
 *
 * Billomat setzt diesen Wert automatisch bei System-Aktionen (Statuswechsel,
 * Versand, …). Für manuell angelegte Kommentare ist das Feld typischerweise leer.
 *
 * Unbekannte Werte werden über `tryFrom()` als null behandelt; der Roh-String
 * wird im Modell zusätzlich aufgehoben.
 *
 * Doku: https://www.billomat.com/en/api/estimates/comments/
 */
enum OfferCommentActionKey: string
{
    case CREATE = 'CREATE';
    case EDIT = 'EDIT';
    case OPEN = 'OPEN';
    case COMPLETE = 'COMPLETE';
    case CANCEL = 'CANCEL';
    case WIN = 'WIN';
    case LOSE = 'LOSE';
    case CLEAR = 'CLEAR';
    case CHANGE_STATUS = 'CHANGE_STATUS';
    case EMAIL = 'EMAIL';
    case MAIL = 'MAIL';
    case COMMENT = 'COMMENT';
}
