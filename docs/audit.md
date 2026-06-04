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
| `account` | Account | ✓ | ✓ | — | — | ✓ |  |
| `aktivitaeten` | Aktivitäten | ✓ | ✓ | — | — | ✓ |  |
| `angebote` | Angebote | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `angebote/kommentare` | Kommentare | ✓ | ✓ | ✓ | — | ✓ |  |
| `angebote/positionen` | Positionen | ✓ | ✓ | ✓ | — | ✓ |  |
| `angebote/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `artikel` | Artikel | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `artikel/attribute` | Attribute | ✓ | — | — | — | ✓ |  |
| `artikel/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `auftragsbestaetigungen` | Auftragsbestätigungen | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `benutzer` | Benutzer | ✓ | ✓ | — | — | ✓ | Read-only; Billomat exponiert kein POST/PUT/DELETE für /users — Benutzerverwaltung ist UI-only. |
| `benutzerdefinierte-attribute-filtern` | Benutzerdefinierte Attribute filtern | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
| `briefe` | Briefe | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `eingangsrechnungen` | Eingangsrechnungen | ✓ | ✓ | ✓ | ✓ | ✓ |  |
| `eingangsrechnungen/attribute` | Attribute | ✓ | — | — | — | ✓ |  |
| `eingangsrechnungen/inbox` | Inbox | ✓ | ✓ | ✓ | — | ✓ |  |
| `eingangsrechnungen/kategorien` | Kategorien | ✓ | ✓ | — | — | ✓ | Read-only; Mutations-Endpunkte sind in der Spec nicht dokumentiert. |
| `eingangsrechnungen/kommentare` | Kommentare | ✓ | ✓ | ✓ | — | ✓ |  |
| `eingangsrechnungen/posten` | Posten | — | — | — | — | ✓ | Posten/Items werden inline über Incoming verwaltet. |
| `eingangsrechnungen/schlagworte` | Schlagworte | ✓ | ✓ | ✓ | — | ✓ |  |
| `eingangsrechnungen/zahlungen` | Zahlungen | ✓ | ✓ | ✓ | — | ✓ |  |
| `einstellungen` | Einstellungen | ✓ | — | — | — | ✓ |  |
| `einstellungen/artikel-attribute` | Artikel-Attribute | ✓ | — | — | — | ✓ |  |
| `einstellungen/benutzer-attribute` | Benutzer-Attribute | ✓ | ✓ | — | — | ✓ | Fünfter Parent neben article-/client-/supplier-/incoming-properties; nutzt geteilte PropertyCreateOptions. |
| `einstellungen/eingangsrechnung-attribute` | Eingansgrechnung-Attribute | ✓ | — | — | — | ✓ |  |
| `einstellungen/einheiten` | Einheiten | ✓ | ✓ | — | — | ✓ |  |
| `einstellungen/email-vorlagen` | E-Mail-Vorlagen | ✓ | ✓ | — | — | ✓ |  |
| `einstellungen/freitexte` | Freitexte | ✓ | ✓ | — | — | ✓ |  |
| `einstellungen/kunden-attribute` | Kunden-Attribute | ✓ | — | — | — | ✓ |  |
| `einstellungen/lieferanten-attribute` | Lieferanten-Attribute | ✓ | — | — | — | ✓ |  |
| `einstellungen/mahnstufen` | Mahnstufen | ✓ | ✓ | — | — | ✓ | Spec dokumentiert /reminder-texts; SDK ergänzt ein zweites DunningLevelsApi für /dunning-levels. |
| `einstellungen/rollen` | Rollen | ✓ | ✓ | — | — | ✓ | Read-only im SDK; Spec dokumentiert zusätzlich POST/PUT/DELETE auf /roles. |
| `einstellungen/steuerfreie-laender` | Steuerfreie Länder | ✓ | ✓ | — | — | ✓ | Read-only im SDK; Spec dokumentiert zusätzlich POST/PUT/DELETE auf /country-taxes. |
| `einstellungen/steuersaetze` | Steuersätze | ✓ | — | — | — | ✓ |  |
| `einstellungen/vorlagen` | Vorlagen | ✓ | — | ✓ | ✓ | ✓ |  |
| `grundlagen` | Grundlagen | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
| `grundlagen/api-sicherheit` | Sicherheit | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
| `grundlagen/authentifizierung` | Authentifizierung | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
| `grundlagen/daten-lesen` | Daten lesen | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
| `grundlagen/daten-schreiben` | Daten schreiben | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
| `grundlagen/eigene-meta-daten` | Eigene Meta-Daten | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
| `grundlagen/fehler` | Fehler | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
| `grundlagen/tools` | Tools | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
| `grundlagen/zugriffsbegrenzung` | Zugriffsbegrenzung | — | — | — | — | — | Konzept-Doku, kein Api erwartet. |
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
| `suche` | Suche | ✓ | ✓ | — | — | ✓ |  |
| `waehrungen` | Währungen | ✓ | ✓ | — | — | ✓ |  |
| `webhooks` | Webhooks | — | — | — | — | ✓ | Empfänger-seitiges Konzept – kein REST-Endpunkt; siehe docs/concepts/webhooks.md. |

## Feld-Lücken in `*Options`-Klassen

_Keine Lücken auf Basis der aktuellen Spec gefunden._

## Enum-Lücken

_Keine Enum-Lücken erkannt._
