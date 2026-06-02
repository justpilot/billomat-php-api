<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ eines Credit-Note-Kommentars (actionkey).
 */
enum CreditNoteCommentActionKey: string
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
    case COMMENT = 'COMMENT';
}
