<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Aktion, die Billomat bei jedem Lauf einer Abo-Rechnung ausführt.
 *
 *  - CREATE   → erstellt nur eine Entwurfs-Rechnung
 *  - COMPLETE → erstellt und schließt die Rechnung ab (PDF wird generiert)
 *  - EMAIL    → erstellt, schließt ab und versendet per E-Mail
 *  - MAIL     → erstellt, schließt ab und versendet postalisch (Pixelletter)
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/
 */
enum RecurringAction: string
{
    case CREATE = 'CREATE';
    case COMPLETE = 'COMPLETE';
    case EMAIL = 'EMAIL';
    case MAIL = 'MAIL';
}
