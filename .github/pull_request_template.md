<!--
Vielen Dank für deinen Pull Request. Bitte fülle die Felder unten aus.
Detaillierte Erwartungen stehen in CONTRIBUTING.md.
-->

## Beschreibung

<!-- Was ändert sich und warum? (Fokus auf das Warum — das Was ergibt sich aus dem Diff.) -->

## Verlinktes Issue

<!-- "Closes #123" oder "Refs #456". Wenn kein Issue existiert: bitte zuerst eines eröffnen, damit Scope und Ansatz vorab geklärt sind. -->

Closes #

## Art der Änderung

- [ ] Bugfix (Verhalten ändert sich, kein API-Bruch)
- [ ] Neues Feature / additive Erweiterung
- [ ] Refactoring / interne Aufräumarbeit (kein Verhaltens- oder API-Wechsel)
- [ ] Dokumentation
- [ ] Breaking Change (vorher in Issue abgestimmt — nur Major-Bump)

## Checkliste

- [ ] `composer ci` läuft lokal grün (Lint + PHPStan + Rector-Dry + Tests)
- [ ] Unit-Tests für neuen Code vorhanden (`#[Test]` + `#[CoversClass]`)
- [ ] Bei neuen Ressourcen: Dokumentation unter `docs/resources/` + README-Eintrag
- [ ] `CHANGELOG.md` unter `[Unreleased]` aktualisiert
- [ ] Kommentare auf Deutsch (ä/ö/ü/ß statt ae/oe/ue/ss), Identifier auf Englisch
- [ ] Keine neuen `phpstan-baseline.neon`-Einträge ohne Rückfrage
- [ ] Keine Breaking Changes ohne vorherige Abstimmung

## Zusätzliche Hinweise

<!-- Migrations-Hinweise, Screenshots, Performance-Daten, etc. -->
