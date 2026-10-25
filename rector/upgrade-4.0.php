<?php

declare(strict_types=1);

/*
 * Rector-Set für das Upgrade von justpilot/billomat-php-api 3.x auf 4.0.
 *
 * Direkt aufrufen:
 *   vendor/bin/rector process src --config vendor/justpilot/billomat-php-api/rector/upgrade-4.0.php
 *
 * Oder in eine eigene rector.php aufnehmen:
 *   ->withSets([__DIR__ . '/vendor/justpilot/billomat-php-api/rector/upgrade-4.0.php'])
 *
 * Automatisiert nur mechanische Änderungen; was Urteilsvermögen braucht (float → Money, Options mit
 * Property-Zuweisung, `if ($api->delete(…))`), listet UPGRADE-4.0.md als manuelle Schritte.
 */
use Justpilot\Billomat\Api\AbstractApi;
use Justpilot\Billomat\Api\ContactsApi;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\OfferStatus;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Model\ReminderText;
use Justpilot\Billomat\Rector\GetItemsToPropertyRector;
use Justpilot\Billomat\Rector\NestedSubResourceRector;
use Justpilot\Billomat\Rector\ParentIdToCreateArgumentRector;
use Justpilot\Billomat\Rector\PdfContentRector;
use Justpilot\Billomat\Rector\SubResourceMap;
use Justpilot\Billomat\Rector\SubResourceMethodRector;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;

return static function (RectorConfig $rectorConfig): void {
    // Erst die Aufrufe auf die alten Klassen anpassen, solange PHPStan ihre Typen noch kennt,
    // dann die Klassennamen ersetzen.
    $rectorConfig->rules([
        SubResourceMethodRector::class,
        NestedSubResourceRector::class,
        GetItemsToPropertyRector::class,
        ParentIdToCreateArgumentRector::class,
        PdfContentRector::class,
    ]);

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename(ContactsApi::class, 'listByClient', 'list'),
        new MethodCallRename(AbstractApi::class, 'putEmptyResponse', 'putVoid'),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [
        new RenameClassConstFetch(OfferStatus::class, 'ACCEPTED', 'WON'),
        new RenameClassConstFetch(OfferStatus::class, 'REJECTED', 'LOST'),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Justpilot\Billomat\Model\Enum\InvoicePaymentType' => PaymentType::class,
        'Justpilot\Billomat\Model\Enum\InvoicePdfType' => PdfType::class,
        'Justpilot\Billomat\Model\Enum\InvoiceItemType' => ItemType::class,
        'Justpilot\Billomat\Model\Enum\ArticleType' => ItemType::class,
        'Justpilot\Billomat\Model\DunningLevel' => ReminderText::class,
        'Justpilot\Billomat\Api\InvoiceEmailOptions' => EmailOptions::class,
        'Justpilot\Billomat\Api\OfferEmailOptions' => EmailOptions::class,
        'Justpilot\Billomat\Api\ConfirmationEmailOptions' => EmailOptions::class,
        'Justpilot\Billomat\Api\CreditNoteEmailOptions' => EmailOptions::class,
        'Justpilot\Billomat\Api\DeliveryNoteEmailOptions' => EmailOptions::class,
        'Justpilot\Billomat\Api\ReminderEmailOptions' => EmailOptions::class,
        'Justpilot\Billomat\Api\LetterEmailOptions' => EmailOptions::class,
        ...SubResourceMap::classRenames(),
    ]);
};
