# Notenberechner

Webbasierter Notenberechner für Lehrkräfte — sieben kleine Werkzeuge unter einem Dach. Alles läuft direkt im Browser, nichts wird auf einem Server gespeichert oder versendet.

**Live:** [noten.brosemedien.de](https://noten.brosemedien.de/)

## Werkzeuge

1. **Notenschlüssel** — linearen Punkte-zu-Noten-Schlüssel generieren, frei skalierbar, mit CSV- und PDF-Export
2. **Punkte → Note** — aus erreichten Punkten direkt die Note ablesen
3. **Note → Punkte** — rückwärts: wie viele Punkte sind für eine Zielnote nötig?
4. **Klausur-Statistik** — Werte einfügen, Kennzahlen und Verteilung erhalten, CSV- und PDF-Export
5. **Blocknoten** — mehrere Bewertungsblöcke gewichtet zusammenrechnen
6. **Beurteilung** — Textbausteine für Schüler-Beurteilungen
7. **Note würfeln** — zufällige Note ziehen, optional mit Tendenz

## Eigenschaften

- Komplett clientseitig, keine Anmeldung, keine Server-Speicherung
- Konfigurationen lassen sich per Permalink teilen
- Snapshots im LocalStorage des Browsers
- CSV- und PDF-Export, QR-Codes für Permalinks

## Stack

- PHP für Seitenstruktur und Includes (kein Backend nötig)
- Vanilla JavaScript pro Werkzeug
- [jsPDF](https://github.com/parallax/jsPDF) für PDF-Export
- [qrcode.js](https://github.com/davidshimjs/qrcodejs) für QR-Codes

## Lokal starten

PHP-fähigen Webserver starten — kein Build-Schritt nötig:

```bash
php -S localhost:8000
```

Dann `http://localhost:8000/` öffnen.

## Lizenz

MIT
