<?php

declare(strict_types=1);

/*
 * Gemeinsame Codegen-Definition für die Kommentare der Billomat-Dokumente
 * (/{resource}-comments).
 *
 * Alle Kommentar-Ressourcen haben dieselben Felder; Eingangsrechnungen kennen die
 * Kunden-Felder (public, by_client, email_id, client_id) nicht. Pro Ressource entstehen
 * das Read-Model und die Create-Options. Liegt in einem Unterordner, damit der Generator
 * die Datei nicht selbst als Definition lädt.
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * @param string      $prefix       Klassen-Präfix, z. B. 'Invoice'
 * @param string      $label        Bezeichnung mit Artikel im Dativ, z. B. 'einer Rechnung'
 * @param string      $parentField  Billomat-Feld der Eltern-ID, z. B. 'invoice_id'
 * @param string|null $specResource Spec-Slug; null, wenn Billomat die Kommentare nicht dokumentiert
 * @param bool        $clientFields false, wenn die Ressource keine Kunden-Felder hat (Eingangsrechnungen)
 */
return static function (string $prefix, string $label, string $parentField, ?string $specResource, string $docUrl, bool $clientFields = true): array {
    $path = str_replace('_', '-', substr($parentField, 0, -3)) . '-comments';

    // Ohne Spec muss jedes Feld angeben, ob es beschreibbar ist.
    $writable = null === $specResource ? ['type' => 'string', 'writable' => true] : [];

    $fields = [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne ID des Kommentars.'],
        // Die Eltern-ID übergibt `CommentsApi::create()` als eigenes Argument.
        $parentField => [
            'type' => 'int',
            'readOnly' => true,
            'doc' => 'ID des Dokuments, an dem der Kommentar hängt.',
            'model' => ['nonNull' => true],
        ],
        'comment' => [...$writable, 'doc' => 'Kommentartext.', 'create' => ['constructor' => true]],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt.'],
        'user_id' => ['type' => 'int', 'readOnly' => true, 'doc' => 'ID des Users, der den Kommentar erzeugt hat (oder null bei System).'],
        // Billomat dokumentiert `actionkey` nicht beim Anlegen, nimmt ihn aber an.
        'actionkey' => [
            'type' => 'string',
            'writable' => true,
            'enum' => $prefix . 'CommentActionKey',
            'doc' => 'ActionKey als Enum, sofern bekannt.',
            'details' => [
                'Beim Anlegen normalerweise nicht relevant – Billomat setzt den Wert',
                'bei system-getriebenen Kommentaren selbst.',
            ],
        ],
        'actionkey_raw' => [
            'type' => 'string',
            'readOnly' => true,
            'doc' => 'Roher ActionKey-String aus der API (auch wenn das Enum den Wert nicht kennt).',
            'model' => ['property' => 'actionkeyRaw', 'sources' => ['actionkey'], 'key' => 'actionkey'],
        ],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
    ];

    if ($clientFields) {
        $fields += [
            // Ohne Spec bleibt `public` read-only (BC: die Create-Options hatten das Feld nie).
            'public' => [
                ...(null === $specResource ? ['type' => 'bool', 'readOnly' => true] : []),
                'doc' => 'Sichtbarkeit für die Aktivitäten-Liste des Kunden.',
            ],
            'by_client' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Hat der Kunde den Kommentar verfasst (Kundenportal)?'],
            'email_id' => ['type' => 'int', 'readOnly' => true, 'doc' => 'ID der zugehörigen E-Mail, falls der Kommentar aus einem Versand stammt.'],
            'client_id' => ['type' => 'int', 'readOnly' => true, 'doc' => 'ID des Kunden.'],
        ];
    }

    return [
        'specResource' => $specResource,
        'docUrl' => $docUrl,

        'model' => [
            'class' => $prefix . 'Comment',
            'doc' => [
                "Kommentar an {$label}.",
                '',
                "Dieses Model bildet die Struktur von GET /{$path}?{$parentField}={id} bzw. GET /{$path}/{id} ab.",
                '',
                'Hinweis zum `actionkey`:',
                ' - Bei System-Kommentaren (Statuswechsel, Versand, Zahlung) ist das Feld gesetzt.',
                ' - Bei manuell angelegten Kommentaren ist das Feld typischerweise leer.',
                ' - Unbekannte Werte werden über das Enum als null geparst; `actionkeyRaw`',
                '   enthält den ursprünglichen String aus der API.',
            ],
            // BC: toArray() gibt den rohen ActionKey unter `actionkey` aus und war schon immer so sortiert.
            'toArray' => [
                'id', 'created', $parentField, 'user_id', 'comment', 'actionkey_raw', 'customfield',
                ...($clientFields ? ['public', 'by_client', 'email_id', 'client_id'] : []),
            ],
        ],

        'createOptions' => [
            'class' => $prefix . 'CommentCreateOptions',
            'endpoint' => "POST /api/{$path}",
            'doc' => [
                "Typisierter Payload für POST /{$path} gemäß Billomat-API.",
                '',
                'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
            ],
        ],

        'fields' => $fields,
    ];
};
