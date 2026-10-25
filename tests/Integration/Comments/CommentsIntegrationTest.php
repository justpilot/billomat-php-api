<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Comments;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCommentCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\CreditNoteCommentCreateOptions;
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCommentCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\IncomingCommentCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\LetterCommentCreateOptions;
use Justpilot\Billomat\Api\LetterCreateOptions;
use Justpilot\Billomat\Api\OfferCommentCreateOptions;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\DeliveryNoteComment;
use Justpilot\Billomat\Model\IncomingComment;
use Justpilot\Billomat\Model\InvoiceComment;
use Justpilot\Billomat\Model\LetterComment;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\OfferComment;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Sammel-Test: isolierte Comment-CRUD-Pfade je Resource-Typ.
 */
#[CoversNothing]
final class CommentsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private function ensureClientId(BillomatClient $billomat): int
    {
        $clients = $billomat->clients->list(['per_page' => 1]);
        if ([] !== $clients) {
            $existing = $clients[0]->id;
            self::assertNotNull($existing);

            return $existing;
        }

        $opts = new ClientCreateOptions(
            name: $this->faker()->company(),
            countryCode: 'DE',
            email: $this->faker()->unique()->safeEmail(),
        );
        $client = $billomat->clients->create($opts);

        return $client->id;
    }

    private function ensureSupplierId(BillomatClient $billomat): int
    {
        $suppliers = $billomat->suppliers->list(['per_page' => 1]);
        if ([] !== $suppliers) {
            $existing = $suppliers[0]->id;
            self::assertNotNull($existing);

            return $existing;
        }

        $opts = new SupplierCreateOptions(name: 'IT-Lieferant-CommentTest '.date('His'), countryCode: 'DE');
        $created = $billomat->suppliers->create($opts);

        return $created->id;
    }

    #[Group('integration')]
    #[Test]
    public function canManageInvoiceComments(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $invOpts = new InvoiceCreateOptions(
            clientId: $this->ensureClientId($billomat),
            title: 'IT-CommentTest '.date('His'),
            currencyCode: 'EUR',
        );
        $item = new InvoiceItemCreateOptions(1.0, Money::of('10'), unit: 'Stück', title: 'Pos');
        $invOpts = $invOpts->withItem($item);
        $invoice = $billomat->invoices->create($invOpts);

        try {
            $comment = $billomat->invoices->comments->create($invoice->id, new InvoiceCommentCreateOptions('IT-Comment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->invoices->comments->list($invoice->id);
            self::assertContainsOnlyInstancesOf(InvoiceComment::class, $comments);
            self::assertGreaterThanOrEqual(1, \count($comments));

            $billomat->invoices->comments->delete($comment->id);
        } finally {
            $billomat->invoices->delete($invoice->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageOfferComments(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $offer = $billomat->offers->create(new OfferCreateOptions(clientId: $this->ensureClientId($billomat)));

        try {
            $comment = $billomat->offers->comments->create($offer->id, new OfferCommentCreateOptions('IT-OffComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->offers->comments->list($offer->id);
            self::assertContainsOnlyInstancesOf(OfferComment::class, $comments);
            self::assertGreaterThanOrEqual(1, \count($comments));

            $billomat->offers->comments->delete($comment->id);
        } finally {
            $billomat->offers->delete($offer->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageConfirmationComments(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $conf = $billomat->confirmations->create(new ConfirmationCreateOptions(clientId: $this->ensureClientId($billomat)));

        try {
            $comment = $billomat->confirmations->comments->create($conf->id, new ConfirmationCommentCreateOptions('IT-ConfComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->confirmations->comments->list($conf->id);
            self::assertContainsOnlyInstancesOf(ConfirmationComment::class, $comments);

            $billomat->confirmations->comments->delete($comment->id);
        } finally {
            $billomat->confirmations->delete($conf->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageCreditNoteComments(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $note = $billomat->creditNotes->create(new CreditNoteCreateOptions(clientId: $this->ensureClientId($billomat)));

        try {
            $comment = $billomat->creditNotes->comments->create($note->id, new CreditNoteCommentCreateOptions('IT-GsComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->creditNotes->comments->list($note->id);
            self::assertContainsOnlyInstancesOf(CreditNoteComment::class, $comments);

            $billomat->creditNotes->comments->delete($comment->id);
        } finally {
            $billomat->creditNotes->delete($note->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageDeliveryNoteComments(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $note = $billomat->deliveryNotes->create(new DeliveryNoteCreateOptions(clientId: $this->ensureClientId($billomat)));

        try {
            $comment = $billomat->deliveryNotes->comments->create($note->id, new DeliveryNoteCommentCreateOptions('IT-LsComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->deliveryNotes->comments->list($note->id);
            self::assertContainsOnlyInstancesOf(DeliveryNoteComment::class, $comments);

            $billomat->deliveryNotes->comments->delete($comment->id);
        } finally {
            $billomat->deliveryNotes->delete($note->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageLetterComments(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $letter = $billomat->letters->create(new LetterCreateOptions(clientId: $this->ensureClientId($billomat)));

        try {
            $comment = $billomat->letters->comments->create($letter->id, new LetterCommentCreateOptions('IT-BrComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->letters->comments->list($letter->id);
            self::assertContainsOnlyInstancesOf(LetterComment::class, $comments);

            $billomat->letters->comments->delete($comment->id);
        } finally {
            $billomat->letters->delete($letter->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageIncomingComments(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $incomingOpts = new IncomingCreateOptions(
            supplierId: $this->ensureSupplierId($billomat),
            incomingNumber: 'IT-IN-Comment-'.date('His'),
        );
        $incoming = $billomat->incomings->create($incomingOpts);

        try {
            $comment = $billomat->incomings->comments->create($incoming->id, new IncomingCommentCreateOptions('IT-InComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->incomings->comments->list($incoming->id);
            self::assertContainsOnlyInstancesOf(IncomingComment::class, $comments);

            $billomat->incomings->comments->delete($comment->id);
        } finally {
            $billomat->incomings->delete($incoming->id);
        }
    }
}
