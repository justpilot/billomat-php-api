# Billomat-API-Spec ↔ SDK-Code: Audit

Generiert von `composer audit:spec` auf Basis von `docs/spec/billomat.json`
und der aktuellen `src/Api/` + `src/Model/Enum/` + `docs/resources/`.

Legende: ✓ vorhanden · ✗ fehlt · — nicht erwartet

## Ressourcen-Matrix

| Slug | Titel | Api | Model | CreateOpts | UpdateOpts | Doku | Notiz |
|---|---|:-:|:-:|:-:|:-:|:-:|---|
| `abo-rechnungen` | Abo-Rechnungen | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `abo-rechnungen/empfaenger` | E-Mail-Empfänger | ✓ | — | — | — | ✓ |  |
| `abo-rechnungen/positionen` | Positionen | ✓ | — | — | — | ✓ |  |
| `abo-rechnungen/schlagworte` | Schlagworte | ✓ | — | — | — | ✓ |  |
| `account` | Account | — | — | — | — | — | Account-Endpunkte – noch nicht im SDK. |
| `aktivitaeten` | Aktivitäten | — | — | — | — | — | Activities-Feed – noch nicht im SDK. |
| `angebote` | Angebote | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `angebote/kommentare` | Kommentare | ✓ | ✓ | ✓ | — | ✓ |  |
| `angebote/positionen` | Positionen | ✓ | ✓ | ✓ | — | ✓ |  |
| `angebote/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `artikel` | Artikel | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `artikel/attribute` | Attribute | ✓ | — | — | — | ✓ |  |
| `artikel/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `auftragsbestaetigungen` | Auftragsbestätigungen | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `benutzer` | Benutzer | ✓ | ✓ | — | — | ✓ |  |
| `benutzerdefinierte-attribute-filtern` | Benutzerdefinierte Attribute filtern | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `briefe` | Briefe | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `eingangsrechnungen` | Eingangsrechnungen | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `eingangsrechnungen/attribute` | Attribute | ✓ | — | — | — | ✓ |  |
| `eingangsrechnungen/inbox` | Inbox | ✓ | ✓ | ✓ | — | ✓ |  |
| `eingangsrechnungen/kategorien` | Kategorien | — | — | — | — | — | Kategorien (read-only Endpoint); noch kein dediziertes Api. |
| `eingangsrechnungen/kommentare` | Kommentare | ✓ | ✓ | ✓ | — | ✓ |  |
| `eingangsrechnungen/posten` | Posten | — | — | — | — | ✓ | Posten/Items werden inline über Incoming verwaltet. |
| `eingangsrechnungen/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `eingangsrechnungen/zahlungen` | Zahlungen | ✓ | ✓ | ✓ | — | ✓ |  |
| `einstellungen` | Einstellungen | ✓ | — | — | — | ✓ |  |
| `einstellungen/artikel-attribute` | Artikel-Attribute | ✓ | — | — | — | ✓ |  |
| `einstellungen/benutzer-attribute` | Benutzer-Attribute | — | — | — | — | — | Benutzer-Attribute – noch kein Api. |
| `einstellungen/eingangsrechnung-attribute` | Eingansgrechnung-Attribute | ✓ | — | — | — | ✓ |  |
| `einstellungen/einheiten` | Einheiten | ✓ | — | — | — | — |  |
| `einstellungen/email-vorlagen` | E-Mail-Vorlagen | ✓ | — | — | — | — |  |
| `einstellungen/freitexte` | Freitexte | ✓ | — | — | — | — |  |
| `einstellungen/kunden-attribute` | Kunden-Attribute | ✓ | — | — | — | ✓ |  |
| `einstellungen/lieferanten-attribute` | Lieferanten-Attribute | ✓ | — | — | — | ✓ |  |
| `einstellungen/mahnstufen` | Mahnstufen | ✓ | — | — | — | — |  |
| `einstellungen/rollen` | Rollen | — | — | — | — | — | Rollen-Verwaltung – noch kein Api. |
| `einstellungen/steuerfreie-laender` | Steuerfreie Länder | — | — | — | — | — | Steuerfreie Länder – noch kein Api. |
| `einstellungen/steuersaetze` | Steuersätze | ✓ | — | — | — | ✓ |  |
| `einstellungen/vorlagen` | Vorlagen | ✓ | — | ✓ | ✓ | ✓ |  |
| `grundlagen` | Grundlagen | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `grundlagen/api-sicherheit` | Sicherheit | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `grundlagen/authentifizierung` | Authentifizierung | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `grundlagen/daten-lesen` | Daten lesen | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `grundlagen/daten-schreiben` | Daten schreiben | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `grundlagen/eigene-meta-daten` | Eigene Meta-Daten | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `grundlagen/fehler` | Fehler | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `grundlagen/tools` | Tools | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `grundlagen/zugriffsbegrenzung` | Zugriffsbegrenzung | ✗ | ✗ | ✗ | ✗ | ✗ | Kein Mapping definiert – Audit-Skript erweitern. |
| `gutschriften` | Gutschriften | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `gutschriften/kommentare` | Kommentare | ✓ | ✓ | ✓ | — | ✓ |  |
| `gutschriften/positionen` | Positionen | ✓ | ✓ | ✓ | — | ✓ |  |
| `gutschriften/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `gutschriften/zahlungen` | Zahlungen | ✓ | ✓ | ✓ | — | ✓ |  |
| `kunden` | Kunden | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `kunden/attribute` | Attribute | ✓ | ✓ | — | — | ✓ | Geteilte Doku unter properties.md. |
| `kunden/kontakte` | Kontakte | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `kunden/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `laender` | Länder | ✓ | ✓ | — | — | ✓ |  |
| `lieferanten` | Lieferanten | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `lieferanten/attribute` | Attribute | ✓ | — | — | — | ✓ |  |
| `lieferanten/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `lieferscheine` | Lieferscheine | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `mahnungen` | Mahnungen | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `mahnungen/positionen` | Positionen | ✓ | — | — | — | ✓ |  |
| `mahnungen/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `rechnungen` | Rechnungen | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `rechnungen/kommentare` | Kommentare | ✓ | ✓ | ✓ | — | ✓ |  |
| `rechnungen/positionen` | Positionen | ✓ | ✓ | ✓ | — | ✓ |  |
| `rechnungen/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `rechnungen/zahlungen` | Zahlungen | ✓ | ✓ | ✓ | — | ✓ |  |
| `suche` | Suche | — | — | — | — | — | Globale Suche – noch nicht im SDK. |
| `waehrungen` | Währungen | ✓ | ✓ | — | — | ✓ |  |
| `webhooks` | Webhooks | ✗ | ✗ | ✗ | ✗ | ✗ |  |

## Feld-Lücken in `*Options`-Klassen

Felder, die laut Spec dokumentiert sind, aber in der zugeordneten
`*Options`-Klasse nicht als Property existieren (Vergleich auf snake_case).

| Options-Klasse | Slug | Fehlende Felder |
|---|---|---|
| `ArticleCreateOptions` | `artikel` | `purchase_price_net_gross`, `type` |
| `ContactCreateOptions` | `kunden/kontakte` | `name`, `www` |
| `CreditNoteCommentCreateOptions` | `gutschriften/kommentare` | `public` |
| `DeliveryNoteCreateOptions` | `lieferscheine` | `offer_id` |
| `InboxDocumentCreateOptions` | `eingangsrechnungen/inbox` | `document_type`, `metadata` |
| `IncomingCreateOptions` | `eingangsrechnungen` | `base64file`, `category`, `client_number`, `expense_account_number`, `number` |
| `InvoiceCommentCreateOptions` | `rechnungen/kommentare` | `public` |
| `LetterCreateOptions` | `briefe` | `supplier_id` |
| `OfferCommentCreateOptions` | `angebote/kommentare` | `public` |
| `OfferCreateOptions` | `angebote` | `validity_date` |
| `OfferItemCreateOptions` | `angebote/positionen` | `optional` |
| `RecurringCreateOptions` | `abo-rechnungen` | `confirmation_id`, `email_bcc`, `email_filename`, `letter_color`, `letter_duplex`, `letter_paper_weight`, `next_creation_date`, `offer_id` |
| `SupplierCreateOptions` | `lieferanten` | `bank_swift`, `client_number`, `creditor_identifier` |

## Enum-Lücken

_Keine Enum-Lücken erkannt._
