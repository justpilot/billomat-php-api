# Billomat PHP API SDK

**Modernes PHP 8.4+ SDK für die Billomat API — mit Symfony Components, PSR-Standards und sauberem Fehlerhandling**

> ⚠️ **Unoffizielles SDK**
>
> Dieses Projekt ist **kein offizielles SDK** von [Billomat](chatgpt://generic-entity?number=0)  
> und steht in keiner offiziellen Verbindung zum Anbieter.  
> Es wird unabhängig entwickelt und gepflegt.

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-8892BF.svg)]()
[![License](https://img.shields.io/badge/license-MIT-lightgrey.svg)]()
[![Tests](https://img.shields.io/badge/tests-PHPUnit-blue.svg)]()

Dieses Paket bietet ein modernes, typisiertes und erweiterbares PHP-SDK zur Arbeit mit der  
[Billomat API](https://www.billomat.com/api/).

Es nutzt ausschließlich moderne PHP-Features (Readonly-Models, Enums, Named Arguments)  
und bewährte Symfony-Komponenten.

---

## 🚀 Features

- ✔ PHP 8.4+
- ✔ Symfony HttpClient
- ✔ Eigene Exception-Klassen
- ✔ Typisierte Modelle (z. B. `Client`)
- ✔ Write-Modelle (`ClientCreateOptions`)
- ✔ Klare API-Struktur (`$billomat->clients->list()`, `->get()`, `->create()` …)
- ✔ Vollständig testbar (Unit + Integration mit Sandbox)
- ✔ Saubere PSR-4-Architektur
- ✔ Kein Overengineering – schlank, stabil, erweiterbar

---

## 📦 Installation

```bash
composer require justpilot/billomat-php-api