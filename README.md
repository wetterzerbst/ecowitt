Es gibt ein neues Script für Ecowitt Stationsbetreiber, die nur einen freien Port haben, dennoch aber ihre Daten auf ihrem Webspace sichern und auch gleichzeitig an andere Wetterdienste weiter leiten möchten, ohne die typischen Portsperren der Webhostingbetreiber und das mühsame einrichten von Cronjobs. Das entfällt komplett, da Euer heimisches IOT diese Aufgabe nun anstößt, während das Script die Daten empfängt und auch weiter verteilt.

Dabei wird in diesme Script streng darauf geachtet, die API-Anfrage so zu geschalten, das nicht jede Datenfütterung im Minutentakt zur jeweiligen API durchgelassen wird. Sondern der entsperechenden Vorgabe genüge getan wird.

Dieses Script empfängt die Wetterdaten im folgenden Format:
archiv/live.csv -> Livedatei
archiv/YYYY-MM/YYYY-MM-DD.csv -> Tagesdatei
archiv/YYYY-MM/YYYY-MM.csv -> Monatsdatei
archiv/YYYY/YYYY.csv -> Jahresdatei

Folgende Wetterdienste können mit diesem Script bedient werden:
WOW Metoffice UK
WOW Meteo BE
WeatherUnderground
CWOP/NOAA
Wetternetzwerk.pro
Meteo-Services
Windy
PWSWeather

Alle oder auch einzelne Weiterleitungen können ein- oder auch abgeschaltet werden!

Zum PHP-Script: https://github.com/wetterzerbst/ecowitt
Dieses Script wurde von wetter-zerbst.org | info@wetter-zerbst.org programmiert.
