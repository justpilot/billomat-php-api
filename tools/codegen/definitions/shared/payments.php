<?php

declare(strict_types=1);

/*
 * Gemeinsame Codegen-Definition für die Zahlungen der Billomat-Dokumente
 * (/{resource}-payments).
 *
 * Rechnungen, Gutschriften und Eingangsrechnungen haben dieselben Zahlungsfelder und
 * teilen sich das Enum InvoicePaymentType. Pro Ressource entstehen das Read-Model und die
 * Create-Options. Liegt in einem Unterordner, damit der Generator die Datei nicht selbst
 * als Definition lädt.
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * @param string $prefix       Klassen-Präfix, z. B. 'Invoice'
 * @param string $label        Bezeichnung mit Artikel im Genitiv, z. B. 'einer Rechnung'
 * @param string $parentField  Billomat-Feld der Eltern-ID, z. B. 'invoice_id'
 * @param string $specResource Spec-Slug, z. B. 'rechnungen/zahlungen'
 */
return static function (string $prefix, string $label, string $parentField, string $specResource, string $docUrl): array {
    $resource = substr($parentField, 0, -3);
    $path = str_replace('_', '-', $resource) . '-payments';
    $markAsPaid = "mark_{$resource}_as_paid";

    return [
        'specResource' => $specResource,
        'docUrl' => $docUrl,

        'model' => [
            'class' => $prefix . 'Payment',
            'doc' => [
                "Zahlung {$label}.",
                '',
                "Dieses Model bildet die Struktur von GET /{$path}?{$parentField}={id} bzw. GET /{$path}/{id} ab.",
            ],
            // BC: toArray() war schon immer anders sortiert als der Konstruktor.
            'toArray' => [
                'id', 'created', $parentField, 'user_id', 'date', 'amount', 'type', 'comment', 'transaction_purpose',
                'customfield', 'currency_code', 'quote', 'payment_group', 'finapi_transaction_id', 'finapi_transaction_amount', 'document_title',
            ],
        ],

        'createOptions' => [
            'class' => $prefix . 'PaymentCreateOptions',
            'endpoint' => "POST /api/{$path}",
            // Billomat erwartet 1/0 statt true/false.
            'boolAsInt' => true,
            // BC: das Zahlungsdatum war schon immer ein DateTimeImmutable.
            'dateObjects' => true,
            'toArray' => [$parentField, 'amount', 'date', 'comment', 'transaction_purpose', 'type', $markAsPaid],
            'doc' => [
                "Typisierter Payload für POST /{$path} gemäß Billomat-API.",
                '',
                'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren',
                '(bis auf Pflichtfelder) entfernt, sodass Billomat Defaults verwenden kann.',
            ],
        ],

        // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
        'fields' => [
            'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne ID der Zahlung.'],
            $parentField => [
                'doc' => 'ID des Dokuments, zu dem die Zahlung gehört.',
                'model' => ['required' => true, 'nonNull' => true],
                'create' => ['constructor' => true],
            ],
            'date' => ['doc' => 'Zahlungsdatum.', 'model' => ['required' => true]],
            'amount' => [
                'doc' => 'Zahlungsbetrag.',
                'model' => ['required' => true, 'nonNull' => true],
                'create' => ['constructor' => true],
            ],
            'type' => ['enum' => 'InvoicePaymentType', 'doc' => 'Zahlungsart.', 'model' => ['required' => true]],
            'comment' => ['doc' => 'Kommentar zur Zahlung.', 'model' => ['required' => true]],
            'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Zahlung.'],
            'user_id' => ['type' => 'int', 'readOnly' => true, 'doc' => 'ID des Users, der die Zahlung erfasst hat.'],
            // Billomat dokumentiert `transaction_purpose` nicht beim Anlegen, nimmt ihn aber an.
            'transaction_purpose' => ['type' => 'string', 'writable' => true, 'doc' => 'Verwendungszweck (z. B. Buchungstext aus dem Bankexport).'],
            'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
            'currency_code' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Währung der Zahlung (ISO-Währungscode).'],
            'quote' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Umrechnungskurs zur Hauptwährung.'],
            'payment_group' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Gruppe zusammengehöriger Zahlungen (z. B. Sammelzahlung).'],
            'finapi_transaction_id' => ['type' => 'string', 'readOnly' => true, 'doc' => 'ID der Banktransaktion (finAPI), falls die Zahlung daraus stammt.'],
            'finapi_transaction_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Betrag der Banktransaktion (finAPI).'],
            'document_title' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Titel des zugehörigen Dokuments.'],
            $markAsPaid => [
                'optionsOnly' => true,
                'doc' => 'Ob das Dokument als bezahlt markiert werden soll.',
                'create' => ['nonNull' => true, 'default' => false],
            ],
        ],
    ];
};
