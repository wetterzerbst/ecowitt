# Ecowitt Empfangshub & Verteilerskript / Receiving Hub & Forwarding Script

[DEUTSCH](#deutsch) | [ENGLISH](#english) | [FRANÇAIS](#français) | [中文](#中文-chinese) | [РУССКИЙ](#русский-russian)

---

## DEUTSCH

Ein zentrales PHP-Skript für Betreiber von Ecowitt-Wetterstationen. Es nimmt die Daten der Station auf dem eigenen Webspace entgegen, speichert sie lokal ab und leitet sie intelligent an verschiedene Wetterdienste weiter.

PS: Wenn dieses Skript deinen Workflow gerettet hat, lass gerne ein ABO da! Wirklich, das motiviert mich! ;-)

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

**Developed by:** [wetter-zerbst.org](https://wetter-zerbst.org) | info@wetter-zerbst.org  
**Repository & Code:** [GitHub - wetterzerbst/ecowitt](https://github.com/wetterzerbst/ecowitt)

---

## FRANÇAIS

Un script PHP central pour les opérateurs de stations météorologiques Ecowitt. Il reçoit les données météo sur votre propre espace web, les archive localement et les retransmet de manière intelligente à divers services météorologiques tiers.

### Caractéristiques principales
* **Contournement des restrictions de ports :** Résout les problèmes de blocage de ports imposés par les FAI ou les hébergeurs web.
* **Aucune tâche Cron requise :** Déclenché automatiquement par l'intervalle d'envoi de la passerelle de votre station IoT.
* **Limitation intelligente du débit :** Respecte les intervalles de mise à jour API individuels de chaque service afin d'éviter les dépassements de limite.

### Structure des données et des archives
Les mesures reçues sont structurées et enregistrées sous forme de fichiers CSV selon la disposition suivante :
* `archiv/live.csv` – Fichier de données en direct (écrasé à chaque mise à jour)
* `archiv/YYYY-MM/YYYY-MM-DD.csv` – Journal quotidien (données ajoutées)
* `archiv/YYYY-MM/YYYY-MM.csv` – Journal mensuel (données ajoutées)
* `archiv/YYYY/YYYY.csv` – Journal annuel (données ajoutées)

### Services météorologiques pris en charge
Le transfert peut être activé ou désactivé individuellement pour chaque service dans la configuration :
* Wetternetzwerk.pro
* Weather Underground
* CWOP / NOAA
* Meteo-Services
* Meteo Belgium (WOW)
* MetOffice UK (WOW)
* Windy
* PWSWeather

**Développé par:** [wetter-zerbst.org](https://wetter-zerbst.org) | info@wetter-zerbst.org  
**Dépôt et Code:** [GitHub - wetterzerbst/ecowitt](https://github.com/wetterzerbst/ecowitt)

---

## 中文 (CHINESE)

适用于 Ecowitt 气象站用户的核心 PHP 接收与转发脚本。该脚本可在您自己的 Web 空间上接收气象数据，进行本地归档，并智能地将数据转发至多个第三方气象服务平台。

### 核心功能与优势
* **绕过端口限制：** 解决网络运营商或 Web 托管服务商的端口封锁问题。
* **无需 Cron 定时任务：** 由物联网气象站网关的推送间隔自动触发执行。
* **智能发送频率控制：** 严格遵循各目标 API 的更新间隔要求，防止频繁请求导致被封禁。

### 数据与归档结构
接收到的气象数据将按以下结构自动保存为 CSV 文件：
* `archiv/live.csv` – 实时数据文件（每次更新自动覆盖）
* `archiv/YYYY-MM/YYYY-MM-DD.csv` – 每日日志文件（追加写入）
* `archiv/YYYY-MM/YYYY-MM.csv` – 每月日志文件（追加写入）
* `archiv/YYYY/YYYY.csv` – 每年日志文件（追加写入）

### 支持的气象服务
可以在配置中为每个服务单独启用或禁用转发功能：
* Wetternetzwerk.pro
* Weather Underground
* CWOP / NOAA
* Meteo-Services
* Meteo Belgium (WOW)
* MetOffice UK (WOW)
* Windy
* PWSWeather

**开发者:** [wetter-zerbst.org](https://wetter-zerbst.org) | info@wetter-zerbst.org  
**代码仓库:** [GitHub - wetterzerbst/ecowitt](https://github.com/wetterzerbst/ecowitt)

---

## РУССКИЙ (RUSSIAN)

Центральный PHP-скрипт для владельцев метеостанций Ecowitt. Он принимает метеоданные на вашем веб-хостинге, сохраняет их локально и автоматически пересылает в различные метеорологические сервисы.

### Основные возможности
* **Обход блокировки портов:** Решает проблему закрытых портов со стороны интернет-провайдеров и хостинг-провайдеров.
* **Без использования Cron:** Скрипт вызывается автоматически при каждой отправке данных с вашего IoT-шлюза.
* **Умный контроль интервалов:** Соблюдает требования API каждого сервиса по частоте запросов, предотвращая блокировку.

### Структура данных и архива
Поступающие данные сохраняются в формате CSV в следующей структуре:
* `archiv/live.csv` – Текущие данные в реальном времени (перезаписывается при каждом обновлении)
* `archiv/YYYY-MM/YYYY-MM-DD.csv` – Дневной архив (данные добавляются)
* `archiv/YYYY-MM/YYYY-MM.csv` – Месячный архив (данные добавляются)
* `archiv/YYYY/YYYY.csv` – Годовой архив (данные добавляются)

### Поддерживаемые метеосервисы
Пересылку можно включить или отключить отдельно для каждого сервиса в конфигурации:
* Wetternetzwerk.pro
* Weather Underground
* CWOP / NOAA
* Meteo-Services
* Meteo Belgium (WOW)
* MetOffice UK (WOW)
* Windy
* PWSWeather

**Разработчик:** [wetter-zerbst.org](https://wetter-zerbst.org) | info@wetter-zerbst.org  
**Репозиторий и код:** [GitHub - wetterzerbst/ecowitt](https://github.com/wetterzerbst/ecowitt)
