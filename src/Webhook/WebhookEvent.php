<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Webhook;

use Justpilot\Billomat\Model\Article;
use Justpilot\Billomat\Model\ArticleProperty;
use Justpilot\Billomat\Model\ArticlePropertyValue;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Model\ClientProperty;
use Justpilot\Billomat\Model\ClientPropertyValue;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\Contact;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\CreditNotePayment;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\DeliveryNoteComment;
use Justpilot\Billomat\Model\Incoming;
use Justpilot\Billomat\Model\IncomingComment;
use Justpilot\Billomat\Model\IncomingPayment;
use Justpilot\Billomat\Model\IncomingProperty;
use Justpilot\Billomat\Model\IncomingPropertyValue;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoiceComment;
use Justpilot\Billomat\Model\InvoicePayment;
use Justpilot\Billomat\Model\Offer;
use Justpilot\Billomat\Model\OfferComment;
use Justpilot\Billomat\Model\Recurring;
use Justpilot\Billomat\Model\Reminder;
use Justpilot\Billomat\Model\Supplier;
use Justpilot\Billomat\Model\SupplierProperty;
use Justpilot\Billomat\Model\SupplierPropertyValue;
use LogicException;

/**
 * Ereignis eines Billomat-Webhooks (Header `X-Billomat-Webhook-Event`).
 *
 * Enthält alle 83 Events, die Billomat sendet. Der Wert ist der Eventname,
 * z. B. `invoice_payment.create`.
 *
 * Doku: https://www.billomat.com/api/webhooks/
 */
enum WebhookEvent: string
{
    // Verkauf
    case INVOICE_CREATE = 'invoice.create';
    case INVOICE_UPDATE = 'invoice.update';
    case INVOICE_STATUS = 'invoice.status';
    case INVOICE_SEND = 'invoice.send';
    case INVOICE_DELETE = 'invoice.delete';
    case INVOICE_COMMENT_CREATE = 'invoice_comment.create';
    case INVOICE_COMMENT_DELETE = 'invoice_comment.delete';
    case INVOICE_PAYMENT_CREATE = 'invoice_payment.create';
    case INVOICE_PAYMENT_DELETE = 'invoice_payment.delete';
    case OFFER_CREATE = 'offer.create';
    case OFFER_UPDATE = 'offer.update';
    case OFFER_STATUS = 'offer.status';
    case OFFER_SEND = 'offer.send';
    case OFFER_DELETE = 'offer.delete';
    case OFFER_COMMENT_CREATE = 'offer_comment.create';
    case OFFER_COMMENT_DELETE = 'offer_comment.delete';
    case CONFIRMATION_CREATE = 'confirmation.create';
    case CONFIRMATION_UPDATE = 'confirmation.update';
    case CONFIRMATION_STATUS = 'confirmation.status';
    case CONFIRMATION_SEND = 'confirmation.send';
    case CONFIRMATION_DELETE = 'confirmation.delete';
    case CONFIRMATION_COMMENT_CREATE = 'confirmation_comment.create';
    case CONFIRMATION_COMMENT_DELETE = 'confirmation_comment.delete';
    case CREDIT_NOTE_CREATE = 'credit_note.create';
    case CREDIT_NOTE_UPDATE = 'credit_note.update';
    case CREDIT_NOTE_STATUS = 'credit_note.status';
    case CREDIT_NOTE_SEND = 'credit_note.send';
    case CREDIT_NOTE_DELETE = 'credit_note.delete';
    case CREDIT_NOTE_COMMENT_CREATE = 'credit_note_comment.create';
    case CREDIT_NOTE_COMMENT_DELETE = 'credit_note_comment.delete';
    case CREDIT_NOTE_PAYMENT_CREATE = 'credit_note_payment.create';
    case CREDIT_NOTE_PAYMENT_DELETE = 'credit_note_payment.delete';
    case DELIVERY_NOTE_CREATE = 'delivery_note.create';
    case DELIVERY_NOTE_UPDATE = 'delivery_note.update';
    case DELIVERY_NOTE_STATUS = 'delivery_note.status';
    case DELIVERY_NOTE_SEND = 'delivery_note.send';
    case DELIVERY_NOTE_DELETE = 'delivery_note.delete';
    case DELIVERY_NOTE_COMMENT_CREATE = 'delivery_note_comment.create';
    case DELIVERY_NOTE_COMMENT_DELETE = 'delivery_note_comment.delete';
    case REMINDER_CREATE = 'reminder.create';
    case REMINDER_UPDATE = 'reminder.update';
    case REMINDER_STATUS = 'reminder.status';
    case REMINDER_SEND = 'reminder.send';
    case REMINDER_DELETE = 'reminder.delete';
    case RECURRING_CREATE = 'recurring.create';
    case RECURRING_UPDATE = 'recurring.update';
    case RECURRING_DELETE = 'recurring.delete';

    // Einkauf
    case INCOMING_CREATE = 'incoming.create';
    case INCOMING_UPDATE = 'incoming.update';
    case INCOMING_STATUS = 'incoming.status';
    case INCOMING_DELETE = 'incoming.delete';
    case INCOMING_COMMENT_CREATE = 'incoming_comment.create';
    case INCOMING_COMMENT_DELETE = 'incoming_comment.delete';
    case INCOMING_PAYMENT_CREATE = 'incoming_payment.create';
    case INCOMING_PAYMENT_DELETE = 'incoming_payment.delete';
    case INCOMING_PROPERTY_CREATE = 'incoming_property.create';
    case INCOMING_PROPERTY_UPDATE = 'incoming_property.update';
    case INCOMING_PROPERTY_DELETE = 'incoming_property.delete';
    case INCOMING_PROPERTY_VALUE_UPDATE = 'incoming_property_value.update';

    // Stammdaten
    case ARTICLE_CREATE = 'article.create';
    case ARTICLE_UPDATE = 'article.update';
    case ARTICLE_DELETE = 'article.delete';
    case ARTICLE_PROPERTY_CREATE = 'article_property.create';
    case ARTICLE_PROPERTY_UPDATE = 'article_property.update';
    case ARTICLE_PROPERTY_DELETE = 'article_property.delete';
    case ARTICLE_PROPERTY_VALUE_UPDATE = 'article_property_value.update';
    case CLIENT_CREATE = 'client.create';
    case CLIENT_UPDATE = 'client.update';
    case CLIENT_DELETE = 'client.delete';
    case CLIENT_PROPERTY_CREATE = 'client_property.create';
    case CLIENT_PROPERTY_UPDATE = 'client_property.update';
    case CLIENT_PROPERTY_DELETE = 'client_property.delete';
    case CLIENT_PROPERTY_VALUE_UPDATE = 'client_property_value.update';
    case CONTACT_CREATE = 'contact.create';
    case CONTACT_UPDATE = 'contact.update';
    case CONTACT_DELETE = 'contact.delete';
    case SUPPLIER_CREATE = 'supplier.create';
    case SUPPLIER_UPDATE = 'supplier.update';
    case SUPPLIER_DELETE = 'supplier.delete';
    case SUPPLIER_PROPERTY_CREATE = 'supplier_property.create';
    case SUPPLIER_PROPERTY_UPDATE = 'supplier_property.update';
    case SUPPLIER_PROPERTY_DELETE = 'supplier_property.delete';
    case SUPPLIER_PROPERTY_VALUE_UPDATE = 'supplier_property_value.update';

    /**
     * Ressource vor dem Punkt, z. B. `invoice_payment`.
     */
    public function resource(): string
    {
        return explode('.', $this->value)[0];
    }

    public function action(): WebhookAction
    {
        return WebhookAction::from(explode('.', $this->value)[1]);
    }

    /**
     * Schlüssel des Envelopes im Body, wie in der REST-API: `invoice_payment` → `invoice-payment`.
     */
    public function envelopeKey(): string
    {
        return str_replace('_', '-', $this->resource());
    }

    /**
     * Model, mit dem {@see WebhookPayload::model()} den Datensatz hydriert.
     *
     * @return class-string<Article|ArticleProperty|ArticlePropertyValue|Client|ClientProperty|ClientPropertyValue|Confirmation|ConfirmationComment|Contact|CreditNote|CreditNoteComment|CreditNotePayment|DeliveryNote|DeliveryNoteComment|Incoming|IncomingComment|IncomingPayment|IncomingProperty|IncomingPropertyValue|Invoice|InvoiceComment|InvoicePayment|Offer|OfferComment|Recurring|Reminder|Supplier|SupplierProperty|SupplierPropertyValue>
     */
    public function modelClass(): string
    {
        return match ($this->resource()) {
            'article' => Article::class,
            'article_property' => ArticleProperty::class,
            'article_property_value' => ArticlePropertyValue::class,
            'client' => Client::class,
            'client_property' => ClientProperty::class,
            'client_property_value' => ClientPropertyValue::class,
            'confirmation' => Confirmation::class,
            'confirmation_comment' => ConfirmationComment::class,
            'contact' => Contact::class,
            'credit_note' => CreditNote::class,
            'credit_note_comment' => CreditNoteComment::class,
            'credit_note_payment' => CreditNotePayment::class,
            'delivery_note' => DeliveryNote::class,
            'delivery_note_comment' => DeliveryNoteComment::class,
            'incoming' => Incoming::class,
            'incoming_comment' => IncomingComment::class,
            'incoming_payment' => IncomingPayment::class,
            'incoming_property' => IncomingProperty::class,
            'incoming_property_value' => IncomingPropertyValue::class,
            'invoice' => Invoice::class,
            'invoice_comment' => InvoiceComment::class,
            'invoice_payment' => InvoicePayment::class,
            'offer' => Offer::class,
            'offer_comment' => OfferComment::class,
            'recurring' => Recurring::class,
            'reminder' => Reminder::class,
            'supplier' => Supplier::class,
            'supplier_property' => SupplierProperty::class,
            'supplier_property_value' => SupplierPropertyValue::class,
            default => throw new LogicException(\sprintf('Kein Model für Webhook-Ressource "%s".', $this->resource())),
        };
    }
}
