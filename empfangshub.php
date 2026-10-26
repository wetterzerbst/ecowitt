<?php
date_default_timezone_set('Europe/Berlin');
// Empfangs- und Verteilerscript Version 4.1
// Wetternetzerk Zerbst (ZEWN)
// wetter-zerbst.org | info@wetter-zerbst.org
// ==========================================
// 1. ZENTRALE KONFIGURATION
// ==========================================
$mac = "XX:XX:XX:XX:XX:XX"; // MAC-Adresse der Wetterstation
$md5hash = strtoupper(md5($mac));

// Luftdruck-Offset zentral in hPa angeben (z.B. -0.9 für eine Absenkung um 0,9 hPa oder 0.3 für eine Anhebung)
$pressureOffsetHpa = -0.9;

$base_dir = __DIR__ . "/archiv/";
$cache_dir = __DIR__ . "/cache/";
$log_file = __DIR__ . "/debug.log";

function write_log($message) {
    global $log_file;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Erweiterte Berechnung für die Evapotranspiration (Meteo-Services)
function calculateET_Advanced($tempC, $solarradiation, $vpd, $windspeed) {
    $hour = (int)date('H');
    if ($hour < 5 || $hour > 20) {
        $sun_factor = 0.05;
    } elseif ($hour >= 11 && $hour <= 15) {
        $sun_factor = 1.0;
    } else {
        $sun_factor = 0.5;
    }
    $et_rate = $sun_factor * ((0.0005 * $solarradiation) + (0.0003 * $vpd * $windspeed));
    return round(max(0, $et_rate), 3);
}

// Konfiguration der Weiterleitungsserver (Offsets hier entfernt, da global geregelt!)
$forward_servers = [
    [
        "active"       => true, // true = aktiviert | false = deaktiviert
        "type"         => "wunderground",
        "url"          => "http://api.wetternetzwerk.pro/weatherstation/updateweatherstation.php",
        "name"         => "Wetternetzwerk Pro",
        "wu_id"        => "000", // Bitte ID eintragen
        "wu_key"       => "00000", // Bitte KEY eintragen
        "min_interval" => 600 // Intervall laut API-Vorgabe
    ],
    [
        "active"       => true, // true = aktiviert | false = deaktiviert
        "type"         => "wunderground",
        "url"          => "http://weatherstation.wunderground.com/weatherstation/updateweatherstation.php",
        "name"         => "Weather Underground",
        "wu_id"        => "IZERBSXX", // Bitte ID eintragen
        "wu_key"       => "XXXXXXXX" // Bitte KEY eintragen
        // Kein min_interval -> sendet ungebremst bei jedem IoT-Call (Rapid Fire). WU erlaubt alle 15 Sekunden.
    ],
    [
        "active"       => true, // true = aktiviert | false = deaktiviert
        "type"         => "cwop",
        "url"          => "https://send.cwop.rest/",
        "name"         => "CWOP/NOAA",
        "call"         => "GWXXXX", // Bitte CWOP-ID eintragen
        "lat"          => 51.XXXXXX, // Bitte Geo-Koordinaten eintragen
        "lon"          => 11.XXXXXX, // Bitte Geo-Koordinaten eintragen
        "min_interval" => 300 // Intervall laut API-Vorgabe
    ],
    [
        "active"       => true, // true = aktiviert | false = deaktiviert
        "type"         => "meteo-services",
        "url"          => "https://channel1.meteo-services.com/stations/index.php",
        "name"         => "Meteo-Services",
        "station_id"   => "WSXXXXXXXX", // Bitte ID eintragen
        "longitude"    => "11.XXXX",  // Bitte Geo-Koordinaten eintragen
        "latitude"     => "51.XXXX",  // Bitte Geo-Koordinaten eintragen
        "altitude"     => "61.0",  // Bitte Messhoehe eintragen
        "min_interval" => 300 // Intervall laut API-Vorgabe
    ],
    [
        "active"       => true, // true = aktiviert | false = deaktiviert
        "type"         => "meteo_be",
        "url"          => "https://wow.meteo.be/api/v2/send/wow",
        "name"         => "Meteo_Belgium",
        "mo_id"          => "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX", // Bitte ID eintragen
        "mo_pin"         => "XXXXXX", // Bitte PIN eintragen
        "min_interval"   => 300 // Intervall laut API-Vorgabe
    ],
    [
        "active"       => true, // true = aktiviert | false = deaktiviert
        "type"         => "metoffice",
        "url"            => "http://wow.metoffice.gov.uk/automaticreading",
        "name"           => "MetOffice UK",
        "mo_id"          => "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX", // Bitte ID eintragen
        "mo_pin"         => "XXXXXX", // Bitte PIN eintragen
        "min_interval"   => 300 // Intervall laut API-Vorgabe
    ],
    [
        "active"       => true, // true = aktiviert | false = deaktiviert
        "type"         => "windy",
        "url"          => "https://stations.windy.com/api/v2/observation/update",
        "name"         => "Windy (Stationsname)",
        "station_id"   => "HIER_NEUE_V2_STATION_ID", // z.B. der alphanumerische String von Windy
        "password"     => "HIER_NEUES_STATIONS_PASSWORT", // das neue Stations-Passwort
        "min_interval" => 300 // Intervall laut API-Vorgabe
    ],
    [
        "active"       => true, // true = aktiviert | false = deaktiviert
        "type"         => "pwsweather",
        "url"          => "https://pwsupdate.pwsweather.com/api/v1/submitwx",
        "name"         => "PWSWeather",
        "station_id"   => "XXXXXXXX", // Bitte Stations-Kennung eintragen
        "password"     => "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", // Bitte Passwort eintragen
        "min_interval" => 300 // Intervall laut API-Vorgabe
    ]
];

if (isset($_POST["PASSKEY"]) && $_POST["PASSKEY"] == $md5hash) {

// ==============================================
// AB HIER NICHTS MEHR AENDERN
// ==============================================
   
    // ==========================================
    // 1.1 ZENTRALE LUFTDRUCK-KORREKTUR (EINGANG)
    // ==========================================
    if ($pressureOffsetHpa != 0) {
        // Umrechnungsfaktor: 1 hPa = 1 / 33.8639 inHg
        $offsetInHg = $pressureOffsetHpa / 33.8639;
       
        if (isset($_POST['baromrelin']) && $_POST['baromrelin'] !== '') {
            $_POST['baromrelin'] = (float)$_POST['baromrelin'] + $offsetInHg;
        }
        if (isset($_POST['baromabsin']) && $_POST['baromabsin'] !== '') {
            $_POST['baromabsin'] = (float)$_POST['baromabsin'] + $offsetInHg;
        }
    }

    // ==========================================
    // 2. LOKALE SPEICHERUNG (CSV) -> nutzt autom. korrigierte Daten
    // ==========================================
    $csv_header = ""; $csv_data = ""; $csv_seperator = ";"; $csv_linebreak = "\n";
    foreach ($_POST as $key => $value) {
        if ($key != "PASSKEY") {
            $csv_header .= $key . $csv_seperator;
            $csv_data .= $value . $csv_seperator;
        }
    }
    $csv_header = rtrim($csv_header, $csv_seperator); $csv_data = rtrim($csv_data, $csv_seperator);
    if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); chmod($base_dir, 0777); }

    $targets = [
        [$base_dir, "live.csv"],
        [$base_dir . date('Y') . "/", date('Y') . ".csv"],
        [$base_dir . date('Y') . "/" . date('m') . "/", date('Y-m') . ".csv"],
        [$base_dir . date('Y') . "/" . date('m') . "/", date('Y-m-d') . ".csv"]
    ];
    foreach ($targets as $target) {
        if (!file_exists($target[0])) { mkdir($target[0], 0777, true); chmod($target[0], 0777); }
        $full_path = $target[0] . $target[1];
        if ($target[1] == "live.csv") {
            $handle = fopen($full_path, "w");
            if ($handle) { fwrite($handle, $csv_header . $csv_linebreak . $csv_data . $csv_linebreak); fclose($handle); }
        } else {
            $is_new = !file_exists($full_path) || filesize($full_path) == 0;
            $handle = fopen($full_path, "a");
            if ($handle) { if ($is_new) fwrite($handle, $csv_header . $csv_linebreak); fwrite($handle, $csv_data . $csv_linebreak); fclose($handle); }
        }
        if (file_exists($full_path)) chmod($full_path, 0777);
    }
   
    // ==========================================
    // 3. WEITERLEITUNG (FORWARDING HUB)
    // ==========================================
    if (!file_exists($cache_dir)) { mkdir($cache_dir, 0777, true); chmod($cache_dir, 0777); }

    foreach ($forward_servers as $server) {
        if ($server['active'] !== true) continue;

        $cache_file = $cache_dir . "last_" . md5($server['name']) . ".txt";

        // --- INTERVALL-SCHUTZ PRÜFEN ---
        if (isset($server['min_interval'])) {
            $last_send = file_exists($cache_file) ? (int)file_get_contents($cache_file) : 0;
            if ((time() - $last_send) < $server['min_interval']) {
                continue;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
       
        // --- LOGIK FORWARD-TYPEN ---
        if ($server['type'] === 'wunderground') {
            $wu_data = ["ID"=>$server['wu_id'], "PASSWORD"=>$server['wu_key'], "action"=>"updateraw", "dateutc"=>isset($_POST['dateutc']) ? $_POST['dateutc'] : gmdate("Y-m-d H:i:s")];
            foreach ($_POST as $key => $value) { if ($key != "PASSKEY") $wu_data[$key] = $value; }
           
            if (isset($_POST['baromrelin'])) {
                $wu_data['baromin'] = round((float)$_POST['baromrelin'], 2);
            }
           
            if (!isset($wu_data['dewptf']) && isset($wu_data['tempf']) && isset($wu_data['humidity'])) {
                $tf = floatval($wu_data['tempf']); $rh = floatval($wu_data['humidity']);
                if ($rh > 0) {
                    $tc = ($tf - 32) * 5 / 9;
                    $a = 17.625; $b = 243.04;
                    $alpha = (($a * $tc) / ($b + $tc)) + log($rh / 100);
                    $tdc = ($b * $alpha) / ($a - $alpha);
                    $wu_data['dewptf'] = round(($tdc * 9 / 5) + 32, 1);
                }
            }
            $wu_data['action'] = 'updateraw';
            $wu_data = array_filter($wu_data, function($value) { return $value !== null; });
           
            curl_setopt($ch, CURLOPT_URL, $server['url'] . "?" . http_build_query($wu_data));
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 PHP-WeatherStation');
        }
       
        elseif ($server['type'] === 'cwop') {
            $pressureRaw = isset($_POST['baromrelin']) ? (float)$_POST['baromrelin'] * 33.8639 : 0;
            $pressureFinal = round($pressureRaw, 1);

            $payload = [
                "id"             => (string)$server['call'],
                "lat"            => (float)$server['lat'],
                "long"           => (float)$server['lon'],
                "time"           => (int)(time() * 1000),
                "tempf"          => isset($_POST['tempf']) ? (float)$_POST['tempf'] : 0,
                "windspeedmph"   => isset($_POST['windspeedmph']) ? (float)$_POST['windspeedmph'] : 0,
                "windgustmph"    => isset($_POST['windgustmph']) ? (float)$_POST['windgustmph'] : 0,
                "winddir"        => isset($_POST['winddir']) ? (int)$_POST['winddir'] : 0,
                "pressure"       => $pressureFinal,
                "humidity"       => isset($_POST['humidity']) ? (int)$_POST['humidity'] : 0,
                "solarradiation" => isset($_POST['solarradiation']) ? (int)$_POST['solarradiation'] : 0,
                "rainin"         => isset($_POST['hourlyrainin']) ? (float)$_POST['hourlyrainin'] : 0,
                "dailyrainin"    => isset($_POST['dailyrainin']) ? (float)$_POST['dailyrainin'] : 0,
                "last24hrrainin" => isset($_POST['last24hrainin']) ? (float)$_POST['last24hrainin'] : 0
            ];

            $jsonString = json_encode($payload);
            curl_setopt($ch, CURLOPT_URL, $server['url']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($jsonString)]);
        }

        elseif ($server['type'] === 'meteo-services') {
            $getVal = function($key, $default = 0.0) { return isset($_POST[$key]) && $_POST[$key] !== '' ? (float)$_POST[$key] : $default; };

            $tempf          = $getVal('tempf', 32.0);
            $humidity       = $getVal('humidity', 0.0);
            $baromrelin     = $getVal('baromrelin', 29.92); // Nutzt den bereits korrigierten $_POST-Wert

            $windspeedmph   = $getVal('windspeedmph', 0.0);
            $windgustmph    = $getVal('windgustmph', 0.0);
            $winddir        = isset($_POST['winddir']) ? (int)round($_POST['winddir']) : 0;
            $solarradiation = $getVal('solarradiation', 0.0);
            $uv             = isset($_POST['uv']) ? (int)round($_POST['uv']) : 0;
            $rainratein     = $getVal('rainratein', 0.0);
            $dailyrainin    = $getVal('dailyrainin', 0.0);
            $hourlyrainin   = $getVal('hourlyrainin', 0.0);
            $vpd            = $getVal('vpd', 0.0);
            $dateutcStr     = isset($_POST['dateutc']) ? $_POST['dateutc'] : '';

            $tempC       = round(($tempf - 32) * 5 / 9, 1);
            $windspeed   = round($windspeedmph * 0.44704, 1);
            $windgust    = round($windgustmph * 0.44704, 1);
            $press       = round($baromrelin * 33.8639, 2);
            $rainrate_mm = round($rainratein * 25.4, 2);
            $raind_mm    = round($dailyrainin * 25.4, 2);
            $rainh_mm    = round($hourlyrainin * 25.4, 2);

            $rhFactor = $humidity <= 0 ? 0.01 : ($humidity > 100 ? 100 : $humidity);
            $gamma = log($rhFactor / 100.0) + (17.62 * $tempC) / (243.12 + $tempC);
            $dewpoint = round((243.12 * $gamma) / (17.62 - $gamma), 1);
            $cloudbase = max(0, round(($tempC - $dewpoint) * 125));

            $appTemp = ($windspeed < 0.1) ? $tempC : round(13.12 + 0.6215 * $tempC - 11.37 * pow($windspeed, 0.16) + 0.3965 * $tempC * pow($windspeed, 0.16), 1);
            $e = 6.11 * exp(5417.7530 * ((1 / 273.16) - (1 / ($dewpoint + 273.15))));
            $humidex = round($tempC + 0.5555 * ($e - 10.0), 1);

            $soilmoisture2 = $getVal('soilmoisture2', 0.0);
            $soilmoisture_cb = max(0.0, min(200.0, round(10000 * exp(-0.2 * $soilmoisture2), 1)));

            $timestamp = !empty($dateutcStr) ? strtotime($dateutcStr . " UTC") : time();
            $datum     = gmdate("YmdHi", $timestamp);
            $et_hourly = calculateET_Advanced($tempC, $solarradiation, $vpd, $windspeed);

            $postData = [
                'stationid'    => $server['station_id'], 'long' => $server['longitude'], 'lat' => $server['latitude'], 'alt' => $server['altitude'],
                'datum'        => $datum, 'utcstamp' => $timestamp, 't2m' => $tempC, 'dew2m' => $dewpoint, 'appTemp' => $appTemp,
                'wchill'       => ($tempC > 10 || $windspeed < 1.3) ? $tempC : $appTemp, 'humidex' => $humidex, 'cloudbase' => $cloudbase,
                'windspeed'    => $windspeed, 'windgust' => $windgust, 'winddir' => $winddir, 'press' => $press, 'relhum' => $humidity,
                'rainrate'     => $rainrate_mm, 'rainh' => $rainh_mm, 'raind' => $raind_mm, 'radi' => $solarradiation, 'uvi' => $uv,
                'vpd'          => $vpd, 'et' => $et_hourly, 'soilmoisture' => $soilmoisture_cb
            ];

            if (isset($_POST['leafwetness_ch1'])) $postData['leafwetness'] = (int)round($_POST['leafwetness_ch1']);
            if (isset($_POST['tf_ch1']) && $_POST['tf_ch1'] !== '') {
                $soiltemp1 = round(((float)$_POST['tf_ch1'] - 32) * 5 / 9, 1);
                $postData['soiltemp'] = $soiltemp1; $postData['t005m'] = $soiltemp1;
            }

            curl_setopt($ch, CURLOPT_URL, $server['url']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_USERAGENT, 'ZEWN-MeteoService-PHP');
        }

        elseif ($server['type'] === 'meteo_be' || $server['type'] === 'metoffice') {
            $tempF = isset($_POST['tempf']) ? (float)$_POST['tempf'] : 32.0;
            $humidity = isset($_POST['humidity']) ? (float)$_POST['humidity'] : 0.0;
           
            $tempC = ($tempF - 32) * 5 / 9;
            $rh = max(1, min(100, $humidity));
            $a = 17.625; $b = 243.04;
            $alpha = (($a * $tempC) / ($b + $tempC)) + log($rh / 100);
            $dewPointC = ($b * $alpha) / ($a - $alpha);
            $dewPointF = round(($dewPointC * 9 / 5) + 32, 1);

            $baromRelIn = isset($_POST['baromrelin']) ? (float)$_POST['baromrelin'] : 29.92;

            $moParams = [
                'siteid'                => $server['mo_id'],
                'siteAuthenticationKey' => $server['mo_pin'],
                'dateutc'               => isset($_POST['dateutc']) ? $_POST['dateutc'] : gmdate("Y-m-d H:i:s"),
                'winddir'               => isset($_POST['winddir']) ? (int)round($_POST['winddir']) : 0,
                'windspeedmph'          => isset($_POST['windspeedmph']) ? round((float)$_POST['windspeedmph'], 2) : 0.0,
                'windgustmph'           => isset($_POST['windgustmph']) ? round((float)$_POST['windgustmph'], 2) : 0.0,
                'tempf'                 => round($tempF, 1),
                'dewptf'                => $dewPointF,
                'humidity'              => (int)round($humidity),
                'baromin'               => round($baromRelIn, 2),
                'rainin'                => isset($_POST['hourlyrainin']) ? round((float)$_POST['hourlyrainin'], 2) : 0.0,
                'dailyrainin'           => isset($_POST['dailyrainin']) ? round((float)$_POST['dailyrainin'], 2) : 0.0,
                'solarradiation'        => isset($_POST['solarradiation']) ? round((float)$_POST['solarradiation'], 2) : 0.0,
                'uv'                    => isset($_POST['uv']) ? (int)round($_POST['uv']) : 0,
                'softwaretype'          => "ZEWN-PHP-Stationv1"
            ];

            $moUrl = $server['url'] . "?" . http_build_query($moParams, '', '&', PHP_QUERY_RFC3986);
            curl_setopt($ch, CURLOPT_URL, $moUrl);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 PHP-WeatherStation');
        }

       elseif ($server['type'] === 'windy') {
            $dewC = null;
            if (isset($_POST['tempf']) && isset($_POST['humidity']) && (float)$_POST['humidity'] > 0) {
                $tempC = ((float)$_POST['tempf'] - 32) * 5 / 9;
                $rh = (float)$_POST['humidity'];
                $a = 17.271; $b = 237.7;
                $alpha = (($a * $tempC) / ($b + $tempC)) + log($rh / 100);
                $dewC = round(($b * $alpha) / ($a - $alpha), 2);
            }

            // Basis-Parameter für Windy V2 API
            $windyParams = [
                'station'        => $server['station_id'],
                'PASSWORD'       => $server['password'],
                'ts'             => isset($_POST['dateutc']) ? strtotime($_POST['dateutc'] . " UTC") : time(),
                'tempf'          => isset($_POST['tempf']) && $_POST['tempf'] !== '' ? (float)$_POST['tempf'] : null,
                'rh'             => isset($_POST['humidity']) && $_POST['humidity'] !== '' ? (int)$_POST['humidity'] : null,
                'dewpoint'       => $dewC,
                'baromin'        => isset($_POST['baromrelin']) && $_POST['baromrelin'] !== '' ? (float)$_POST['baromrelin'] : null,
                'windspeedmph'   => isset($_POST['windspeedmph']) && $_POST['windspeedmph'] !== '' ? (float)$_POST['windspeedmph'] : null,
                'windgustmph'    => isset($_POST['windgustmph']) && $_POST['windgustmph'] !== '' ? (float)$_POST['windgustmph'] : null,
                'winddir'        => isset($_POST['winddir']) && $_POST['winddir'] !== '' ? (int)$_POST['winddir'] : null,
                'precip'         => isset($_POST['hourlyrainin']) && $_POST['hourlyrainin'] !== '' ? (float)$_POST['hourlyrainin'] : null,
                'mprecip'        => isset($_POST['dailyrainin']) && $_POST['dailyrainin'] !== '' ? round((float)$_POST['dailyrainin'] * 25.4, 1) : null,
                'solarradiation' => isset($_POST['solarradiation']) && $_POST['solarradiation'] !== '' ? (float)$_POST['solarradiation'] : null,
                'uv'             => isset($_POST['uv']) && $_POST['uv'] !== '' ? (float)$_POST['uv'] : null
            ];

            // Null-Werte herausfiltern, um saubere URLs zu erzeugen
            $windyParams = array_filter($windyParams, function($value) { return $value !== null; });

            curl_setopt($ch, CURLOPT_URL, $server['url'] . "?" . http_build_query($windyParams));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        elseif ($server['type'] === 'pwsweather') {
            $tempF = isset($_POST['tempf']) && $_POST['tempf'] !== '' ? (float)$_POST['tempf'] : 32.0;
            $humidity = isset($_POST['humidity']) && $_POST['humidity'] !== '' ? (float)$_POST['humidity'] : 0.0;
           
            $dewPointF = null;
            if ($humidity > 0) {
                $tempC = ($tempF - 32) * 5 / 9;
                $a = 17.271; $b = 237.7;
                $alpha = (($a * $tempC) / ($b + $tempC)) + log($humidity / 100);
                $dewPointC = ($b * $alpha) / ($a - $alpha);
                $dewPointF = round(($dewPointC * 9 / 5) + 32, 1);
            }

            $pwsParams = [
                'ID'             => $server['station_id'],
                'PASSWORD'       => $server['password'],
                'dateutc'        => isset($_POST['dateutc']) ? $_POST['dateutc'] : gmdate("Y-m-d H:i:s"),
                'action'         => 'updateraw',
                'softwaretype'   => 'ZEWN_PHP_v1',
                'tempf'          => isset($_POST['tempf']) && $_POST['tempf'] !== '' ? (float)$_POST['tempf'] : null,
                'humidity'       => isset($_POST['humidity']) && $_POST['humidity'] !== '' ? (int)$_POST['humidity'] : null,
                'dewptf'         => $dewPointF,
                'baromin'        => isset($_POST['baromrelin']) && $_POST['baromrelin'] !== '' ? (float)$_POST['baromrelin'] : null,
                'windspeedmph'   => isset($_POST['windspeedmph']) && $_POST['windspeedmph'] !== '' ? (float)$_POST['windspeedmph'] : null,
                'windgustmph'    => isset($_POST['windgustmph']) && $_POST['windgustmph'] !== '' ? (float)$_POST['windgustmph'] : null,
                'winddir'        => isset($_POST['winddir']) && $_POST['winddir'] !== '' ? (int)$_POST['winddir'] : null,
                'rainin'         => isset($_POST['hourlyrainin']) && $_POST['hourlyrainin'] !== '' ? (float)$_POST['hourlyrainin'] : null,
                'dailyrainin'    => isset($_POST['dailyrainin']) && $_POST['dailyrainin'] !== '' ? (float)$_POST['dailyrainin'] : null,
                'solarradiation' => isset($_POST['solarradiation']) && $_POST['solarradiation'] !== '' ? (float)$_POST['solarradiation'] : null,
                'UV'             => isset($_POST['uv']) && $_POST['uv'] !== '' ? (float)$_POST['uv'] : null
            ];

            $pwsParams = array_filter($pwsParams, function($value) { return $value !== null; });
            curl_setopt($ch, CURLOPT_URL, $server['url'] . "?" . http_build_query($pwsParams));
        }

        // --- AUSFÜHRUNG MIT DEBUGGING ---
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
       
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       
        curl_close($ch);

        if (isset($server['min_interval'])) {
            file_put_contents($cache_file, time());
        }

        if ($error) {
            write_log("FEHLER [" . $server['name'] . "]: " . $error);
        } else {
            write_log("ANTWORT [" . $server['name'] . "] HTTP " . $http_code . " | Body: " . trim($result));
        }
    }
   
    echo "Daten erfolgreich archiviert und verteilt.";
} else {
    header('HTTP/1.0 403 Forbidden');
    echo "Zugriff verweigert.";
}
?>
