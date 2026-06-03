<!-- Quelle: https://www.billomat.com/api/grundlagen/eigene-meta-daten/ + https://www.billomat.com/api/benutzerdefinierte-attribute-filtern/ -->

# Eigene Meta-Daten

Billomat bietet **zwei** Wege, eigene Daten an Ressourcen zu hängen — beide werden hier oft verwechselt, weil sie ähnlich klingen, aber unterschiedlich funktionieren:

| Mechanismus | Was | Wofür |
|---|---|---|
| `customfield` | **ein** freier Text-Slot pro Datensatz | Schnelle 1:1-Verbindung zwischen Billomat-Datensatz und einem Fremd-System (z.B. eigene Primärschlüssel). |
| Property / PropertyValue | **definierte**, mehrwertige Custom-Attribute mit Name, Typ und ggf. Vorgabewerten | Fachliche Erweiterungen wie „Lieblingsfarbe", „CRM-ID", „Vertragsnummer". |

## `customfield` — der freie Text-Slot

Verfügbar pro Datensatz auf allen wichtigen Ressourcen (Kunden, Rechnungen, Eingangsrechnungen, Lieferanten, …).

### Lesen / Schreiben

```
GET  /api/clients/{id}/customfield
PUT  /api/clients/{id}/customfield
```

Body beim Schreiben:

```xml
<client>
  <customfield>bar</customfield>
</client>
```

### Filtern

Listen-Endpunkte akzeptieren den Such-Parameter `customfield`:

```
GET /api/clients?customfield={suche}
```

> Das SDK exponiert `customfield` aktuell **nicht** als dedizierte Methode. Wer es nutzen will, kann den Wert über den Filter-Parameter in `list(['customfield' => '…'])` mitgeben und beim Schreiben den generischen `BillomatHttpClient` ansprechen.

## Properties + PropertyValues — definierte Custom-Attribute

Pro Ressource gibt es zwei API-Schichten:

| API | Zweck |
|---|---|
| `*PropertiesApi` | Die **Definitionen** (Name, Typ, Pflichtflag). Werden im UI unter *Einstellungen → …-Attribute* gepflegt. |
| `*PropertyValuesApi` | Die **Werte** pro Datensatz — referenziert die Property per ID. |

SDK-Klassen:

- `ClientPropertiesApi`
- `ArticlePropertiesApi` + `ArticlePropertyValuesApi`
- `SupplierPropertiesApi` + `SupplierPropertyValuesApi`
- `IncomingPropertiesApi` + `IncomingPropertyValuesApi`

Siehe [`docs/resources/properties.md`](../resources/properties.md) für die geteilte Doku der Properties-Familie.

### Werte abfragen

`ArticlePropertyValuesApi::list([…])` & Co. liefern eine flache Liste aller Wert-Zuordnungen — gefiltert nach `article_id` oder `article_property_id`.

### Werte filtern in der Eltern-Liste

Listen-Endpunkte der Eltern-Ressource akzeptieren einen dynamisch benannten Parameter `property<id>`:

```
GET /api/clients?property55=grau
```

bedeutet: alle Kunden, deren Property mit der ID `55` (z.B. „Lieblingsfarbe") **exakt** den Wert `grau` hat. Der Filter ist case-sensitive und macht **keinen Teilstring-Match** — „graublau" wird nicht zurückgegeben.

Im SDK geht das über den generischen Filter:

```php
$billomat->clients->list(['property55' => 'grau']);
```

Unterstützt für die fünf Eltern-Ressourcen: **Users, Articles, Clients, Suppliers, Incomings**.

## Was wählen?

- **`customfield`** für ein einzelnes, unstrukturiertes Stück Fremd-Information pro Datensatz. Kein UI-Setup nötig.
- **Property + PropertyValue** für strukturierte, mehrfache, getypte Felder, die auch im Billomat-UI sichtbar sein sollen.
- Beide Mechanismen sind unabhängig — derselbe Datensatz kann beides nutzen.
