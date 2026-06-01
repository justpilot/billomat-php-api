<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ eines Rechnungskommentars (actionkey).
 *
 * Billomat setzt diesen Wert automatisch für jede Aktion, die einen Kommentar
 * erzeugt (Statuswechsel, Mailversand, Zahlungserfassung, …). Für manuell
 * angelegte Kommentare ist das Feld typischerweise leer / nicht relevant.
 *
 * Die hier abgebildeten Werte sind die im API-Output beobachteten — Billomat
 * dokumentiert keine geschlossene Liste. Unbekannte Werte werden vom Model
 * über `tryFrom` als null behandelt; der Roh-String bleibt zusätzlich erhalten.
 *
 * Doku: https://www.billomat.com/api/rechnungen/kommentare/
 */
enum InvoiceCommentActionKey: string
{
    case CREATE = 'CREATE';
    case EDIT = 'EDIT';
    case OPEN = 'OPEN';
    case COMPLETE = 'COMPLETE';
    case CANCEL = 'CANCEL';
    case UNCANCEL = 'UNCANCEL';
    case CHANGE_STATUS = 'CHANGE_STATUS';
    case PAYMENT = 'PAYMENT';
    case EMAIL = 'EMAIL';
    case MAIL = 'MAIL';
    case DUNNING = 'DUNNING';
    case COMMENT = 'COMMENT';
}
