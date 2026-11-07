<?php

namespace Justpilot\Billomat\Tests\Rector\RenamedMemberRector\Fixture;

use Justpilot\Billomat\Api\InboxDocumentCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use Justpilot\Billomat\Model\InvoiceComment;

final class CamelCaseMembers
{
    public function run(InvoiceComment $comment, ?InvoiceComment $maybe, IncomingCreateOptions $incoming, ?IncomingCreateOptions $maybeIncoming): array
    {
        $options = new InvoiceCommentCreateOptions(comment: 'Hallo', actionkey: InvoiceCommentActionKey::COMMENT);
        $options = $options->withActionkey(actionkey: InvoiceCommentActionKey::EMAIL);
        $maybeOptions = $maybeIncoming?->withBase64file('QUJD');
        $upload = new InboxDocumentCreateOptions(filename: 'a.pdf', mimeType: 'application/pdf', base64file: 'QUJD');

        return [
            $comment->actionkey,
            $comment->actionkeyRaw,
            $comment->customfield,
            $maybe?->actionkey,
            $incoming->withBase64file('QUJD')->base64file,
            $comment->toArray()['actionkey'],
            $options,
            $maybeOptions,
            $upload,
        ];
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\RenamedMemberRector\Fixture;

use Justpilot\Billomat\Api\InboxDocumentCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use Justpilot\Billomat\Model\InvoiceComment;

final class CamelCaseMembers
{
    public function run(InvoiceComment $comment, ?InvoiceComment $maybe, IncomingCreateOptions $incoming, ?IncomingCreateOptions $maybeIncoming): array
    {
        $options = new InvoiceCommentCreateOptions(comment: 'Hallo', actionKey: InvoiceCommentActionKey::COMMENT);
        $options = $options->withActionKey(actionKey: InvoiceCommentActionKey::EMAIL);
        $maybeOptions = $maybeIncoming?->withBase64File('QUJD');
        $upload = new InboxDocumentCreateOptions(filename: 'a.pdf', mimeType: 'application/pdf', base64File: 'QUJD');

        return [
            $comment->actionKey,
            $comment->actionKeyRaw,
            $comment->customField,
            $maybe?->actionKey,
            $incoming->withBase64File('QUJD')->base64File,
            $comment->toArray()['actionkey'],
            $options,
            $maybeOptions,
            $upload,
        ];
    }
}
?>
