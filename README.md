# KVU Sponsoren — WordPress Plugin

Sponsoren- und Partnerseite für den KV Untertürkheim 1906 e.V.

**Version:** 1.0.0 · **Shortcode:** `[kvu_sponsoren]`

---

## Enthaltene Dateien

```
kvu-sponsoren-plugin/
├── kvu-sponsoren/
│   ├── kvu-sponsoren.php            ← Plugin-Hauptdatei (Shortcode, AJAX, Admin)
│   └── kvu-sponsoren-styles.css     ← Alle Styles
└── kvu-sponsoren-v1.0.0.zip         ← Fertige ZIP-Datei zum Upload
```

---

## Installation in WordPress

1. WordPress-Backend → **Plugins → Installieren → Plugin hochladen**
2. `kvu-sponsoren-v1.0.0.zip` auswählen und hochladen
3. Plugin **aktivieren**

---

## Einbindung auf einer Seite

```
[kvu_sponsoren]
```

Live-URL: [www.kv-untertuerkheim.de/sponsoren](https://www.kv-untertuerkheim.de/sponsoren)

---

## Abschnitte der Seite

| Abschnitt | Inhalt |
|---|---|
| **Hero** | Überschrift, Beschreibung, CTA-Buttons |
| **Stats** | 500+ Mitglieder, 120 Jahre, 12 Courts, 50+ Events |
| **Warum Partner?** | 6 Vorteile-Karten (Sichtbarkeit, Netzwerk, Prestige, Digital, Hospitality, CSR) |
| **Sponsorenpakete** | Bronze / Silber / Gold (empfohlen) / Platin mit Preisen |
| **Aktuelle Partner** | Logo-Karten nach Tier (Platin / Gold / Silber+Bronze) |
| **Kontaktformular** | Anfrage mit Paket-Auswahl, AJAX-Versand per E-Mail |

---

## Sponsorenpakete

| Paket | Preis | Highlights |
|---|---|---|
| **Bronze** – Förderer | ab 500 € / Jahr | Logo Website, 2 Freikarten |
| **Silber** – Partner | ab 1.500 € / Jahr | + Banner auf Anlage, 4 Freikarten, Social Media |
| **Gold** – Partner ⭐ | ab 3.500 € / Jahr | + Hospitality 4 Pers., monatl. Social Media |
| **Platin** – Hauptsponsor | ab 7.500 € / Jahr | Naming Right, VIP 10 Pers., unbegr. Freikarten |

---

## Admin-Bereich

Im WordPress-Backend unter **KVU Sponsoren**:
- E-Mail-Adresse für eingehende Sponsor-Anfragen konfigurieren (Standard: `info@kv-untertuerkheim.de`)

---

## Kontaktformular

Bei jeder Anfrage:
- Pflichtfelder: Unternehmen, Vorname, Nachname, E-Mail
- Optional: Paket-Interesse, Nachricht
- Versand per `wp_mail` an die konfigurierte Empfangsadresse
- AJAX-basiert, kein Seitenreload

---

## Menüposition

Eigener Reiter im Hauptmenü: **zwischen Gymnastik und Verschiedenes**

> Menüeintrag manuell anlegen: WordPress-Backend → **Darstellung → Menüs** → Seite „Sponsoren & Partner" (ID 5433) hinzufügen

---

## Changelog

### v1.0.0 (2026-05-23)
- Erstveröffentlichung: Hero, Stats, Vorteile, Pakete, Logos, Kontaktformular
- AJAX-Kontaktformular mit E-Mail-Versand
- Admin-Einstellungsseite für Empfänger-E-Mail

---

*KV Untertürkheim 1906 e.V. — Im Dietbach 3, 70734 Fellbach — info@kv-untertuerkheim.de*
