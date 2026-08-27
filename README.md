# Ecowitt Empfangshub & Verteilerskript / Receiving Hub & Forwarding Script

[DEUTSCH](#deutsch) | [ENGLISH](#english)

---

## DEUTSCH

Ein zentrales PHP-Skript für Betreiber von Ecowitt-Wetterstationen. Es nimmt die Daten der Station auf dem eigenen Webspace entgegen, speichert sie lokal ab und leitet sie intelligent an verschiedene Wetterdienste weiter.

### Highlights & Vorteile
* **Keine Portsperren:** Umgeht Einschränkungen von Internetanbietern/Webhostern.
* **Keine Cronjobs:** Das Skript wird automatisch durch den Push-Intervall des IoT-Gateways angestoßen.
* **Intelligente Intervall-Drosselung:** Achtet auf die API-Vorgaben der Zielplattformen und verhindert zu häufige Anfragen (Rate-Limiting).

### Daten- & Archivstruktur
Die empfangenen Messwerte werden in folgenden Formaten im CSV-Archiv strukturiert abgelegt:
* `archiv/live.csv` – Aktuelle Livedatei (wird fortlaufend überschrieben)
* `archiv/YYYY-MM/YYYY-MM-DD.csv` – Tagesdatei (Datensätze werden angehängt)
* `archiv/YYYY-MM/YYYY-MM.csv` – Monatsdatei (Datensätze werden angehängt)
* `archiv/YYYY/YYYY.csv` – Jahresdatei (Datensätze werden angehängt)

### Unterstützte Wetterdienste
Die Weiterleitung kann für jeden Dienst einzeln in der Konfiguration ein- oder ausgeschaltet werden:
* Wetternetzwerk.pro
* Weather Underground
* CWOP / NOAA
* Meteo-Services
* Meteo Belgium (WOW)
* MetOffice UK (WOW)
* Windy
* PWSWeather

---
**Entwickelt von:** [wetter-zerbst.org](https://wetter-zerbst.org) | info@wetter-zerbst.org  
**Repository & Code:** [GitHub - wetterzerbst/ecowitt](https://github.com/wetterzerbst/ecowitt)

---

## ENGLISH

A central PHP script for Ecowitt weather station operators. It receives weather data on your custom webspace, archives it locally, and intelligently forwards it to various third-party weather services.

### Key Features
* **Bypass Port Restrictions:** Solves port blocking issues enforced by ISPs or web hosting providers.
* **No Cron Jobs Required:** Automatically triggered by the push interval of your IoT station gateway.
* **Smart Rate Limiting:** Respects the individual API update intervals of each target service to prevent rate-limit violations.

### Data & Archive Structure
Received metrics are structured and saved as CSV files using the following layout:
* `archiv/live.csv` – Live data file (overwritten on every update)
* `archiv/YYYY-MM/YYYY-MM-DD.csv` – Daily log (appended)
* `archiv/YYYY-MM/YYYY-MM.csv` – Monthly log (appended)
* `archiv/YYYY/YYYY.csv` – Yearly log (appended)

### Supported Weather Services
Forwarding can be individually enabled or disabled per service in the script configuration:
* Wetternetzwerk.pro
* Weather Underground
* CWOP / NOAA
* Meteo-Services
* Meteo Belgium (WOW)
* MetOffice UK (WOW)
* Windy
* PWSWeather

---
**Developed by:** [wetter-zerbst.org](https://wetter-zerbst.org) | info@wetter-zerbst.org  
**Repository & Code:** [GitHub - wetterzerbst/ecowitt](https://github.com/wetterzerbst/ecowitt)
