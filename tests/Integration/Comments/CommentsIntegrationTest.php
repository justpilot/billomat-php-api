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

        $opts = new ClientCreateOptions();
        $opts->name = $this->faker()->company();
        $opts->email = $this->faker()->unique()->safeEmail();
        $opts->countryCode = 'DE';
        $client = $billomat->clients->create($opts);
        $id = $client->id;
        self::assertNotNull($id);

        return $id;
    }

    private function ensureSupplierId(BillomatClient $billomat): int
    {
        $suppliers = $billomat->suppliers->list(['per_page' => 1]);
        if ([] !== $suppliers) {
            $existing = $suppliers[0]->id;
            self::assertNotNull($existing);

            return $existing;
        }

        $opts = new SupplierCreateOptions(name: 'IT-Lieferant-CommentTest '.date('His'));
        $opts->countryCode = 'DE';
        $created = $billomat->suppliers->create($opts);
        $id = $created->id;
        self::assertNotNull($id);

        return $id;
    }

    #[Group('integration')]
    #[Test]
    public function canManageInvoiceComments(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $invOpts = new InvoiceCreateOptions(clientId: $this->ensureClientId($billomat));
        $invOpts->currencyCode = 'EUR';
        $invOpts->title = 'IT-CommentTest '.date('His');
        $item = new InvoiceItemCreateOptions(1.0, 10.0);
        $item->title = 'Pos';
        $item->unit = 'Stück';
        $invOpts->addItem($item);
        $invoice = $billomat->invoices->create($invOpts);
        self::assertNotNull($invoice->id);

        try {
            $comment = $billomat->invoiceComments->create(new InvoiceCommentCreateOptions($invoice->id, 'IT-Comment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->invoiceComments->listByInvoice($invoice->id);
            self::assertContainsOnlyInstancesOf(InvoiceComment::class, $comments);
            self::assertGreaterThanOrEqual(1, \count($comments));

            self::assertTrue($billomat->invoiceComments->delete($comment->id));
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
        self::assertNotNull($offer->id);

        try {
            $comment = $billomat->offerComments->create(new OfferCommentCreateOptions($offer->id, 'IT-OffComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->offerComments->listByOffer($offer->id);
            self::assertContainsOnlyInstancesOf(OfferComment::class, $comments);
            self::assertGreaterThanOrEqual(1, \count($comments));

            self::assertTrue($billomat->offerComments->delete($comment->id));
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
        self::assertNotNull($conf->id);

        try {
            $comment = $billomat->confirmationComments->create(new ConfirmationCommentCreateOptions($conf->id, 'IT-ConfComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->confirmationComments->listByConfirmation($conf->id);
            self::assertContainsOnlyInstancesOf(ConfirmationComment::class, $comments);

            self::assertTrue($billomat->confirmationComments->delete($comment->id));
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
        self::assertNotNull($note->id);

        try {
            $comment = $billomat->creditNoteComments->create(new CreditNoteCommentCreateOptions($note->id, 'IT-GsComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->creditNoteComments->listByCreditNote($note->id);
            self::assertContainsOnlyInstancesOf(CreditNoteComment::class, $comments);

            self::assertTrue($billomat->creditNoteComments->delete($comment->id));
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
        self::assertNotNull($note->id);

        try {
            $comment = $billomat->deliveryNoteComments->create(new DeliveryNoteCommentCreateOptions($note->id, 'IT-LsComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->deliveryNoteComments->listByDeliveryNote($note->id);
            self::assertContainsOnlyInstancesOf(DeliveryNoteComment::class, $comments);

            self::assertTrue($billomat->deliveryNoteComments->delete($comment->id));
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
        self::assertNotNull($letter->id);

        try {
            $comment = $billomat->letterComments->create(new LetterCommentCreateOptions($letter->id, 'IT-BrComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->letterComments->listByLetter($letter->id);
            self::assertContainsOnlyInstancesOf(LetterComment::class, $comments);

            self::assertTrue($billomat->letterComments->delete($comment->id));
        } finally {
            $billomat->letters->delete($letter->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageIncomingComments(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $incomingOpts = new IncomingCreateOptions(supplierId: $this->ensureSupplierId($billomat));
        $incomingOpts->incomingNumber = 'IT-IN-Comment-'.date('His');
        $incoming = $billomat->incomings->create($incomingOpts);
        self::assertNotNull($incoming->id);

        try {
            $comment = $billomat->incomingComments->create(new IncomingCommentCreateOptions($incoming->id, 'IT-InComment-'.date('His')));
            self::assertNotNull($comment->id);

            $comments = $billomat->incomingComments->listByIncoming($incoming->id);
            self::assertContainsOnlyInstancesOf(IncomingComment::class, $comments);

            self::assertTrue($billomat->incomingComments->delete($comment->id));
        } finally {
            $billomat->incomings->delete($incoming->id);
        }
    }
}
