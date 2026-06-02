<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ eines Incoming-Kommentars (actionkey).
 */
enum IncomingCommentActionKey: string
{
    case CREATE = 'CREATE';
    case EDIT = 'EDIT';
    case OPEN = 'OPEN';
    case COMPLETE = 'COMPLETE';
    case CANCEL = 'CANCEL';
    case UNCANCEL = 'UNCANCEL';
    case CHANGE_STATUS = 'CHANGE_STATUS';
    case PAYMENT = 'PAYMENT';
    case UPLOAD = 'UPLOAD';
    case COMMENT = 'COMMENT';
}
