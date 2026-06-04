Es gibt ein neues Script für Ecowitt Stationsbetreiber, die nur einen freien Port haben, dennoch aber ihre Daten auf ihrem Webspace sichern und auch gleichzeitig an andere Wetterdienste weiter leiten möchten, ohne die typischen Portsperren der Webhostingbetreiber und das mühsame einrichten von Cronjobs. Das entfällt komplett, da Euer heimisches IOT diese Aufgabe nun anstößt, während das Script die Daten empfängt und auch weiter verteilt.

Dabei wird in diesme Script streng darauf geachtet, die API-Anfrage so zu geschalten, das nicht jede Datenfütterung im Minutentakt zur jeweiligen API durchgelassen wird. Sondern der entsperechenden Vorgabe genüge getan wird.

1. Dieses Script empfängt die Wetterdaten im folgenden Format:
   archiv/live.csv                    -> Livedatei
   archiv/YYYY-MM/YYYY-MM-DD.csv      -> Tagesdatei
   archiv/YYYY-MM/YYYY-MM.csv         -> Monatsdatei
   archiv/YYYY/YYYY.csv               -> Jahresdatei

2. Folgende Wetterdienste können mit diesem Script bedient werden:
   WOW Metoffice UK
   WOW Meteo BE
   WeatherUndergroud
   CWOP/NOAA
   Wetternetzwerk.pro
   Meteo-Services
   Windy
   PWSWeather

Alle oder auch einzelne Weiterleitungen können ein- oder auch abgeschaltet werden!
Dieses Script wurde vor wetter-zerbst.org | info@wetter-zerbst.org programmiert.
