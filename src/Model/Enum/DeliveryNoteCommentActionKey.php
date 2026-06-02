<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ eines DeliveryNote-Kommentars (actionkey).
 *
 * Doku: https://www.billomat.com/en/api/delivery-notes/comments/
 */
enum DeliveryNoteCommentActionKey: string
{
    case CREATE = 'CREATE';
    case EDIT = 'EDIT';
    case OPEN = 'OPEN';
    case COMPLETE = 'COMPLETE';
    case CANCEL = 'CANCEL';
    case CLEAR = 'CLEAR';
    case CHANGE_STATUS = 'CHANGE_STATUS';
    case EMAIL = 'EMAIL';
    case MAIL = 'MAIL';
    case COMMENT = 'COMMENT';
}
