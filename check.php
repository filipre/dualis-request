#!/usr/bin/php -f
<?php

/*
    CONFIG:
*/
$dualis_name = ""; //lastname.firstname%40dh-karlsruhe.de
$dualis_pass = ""; //your password
$adresses[] = ""; //email to contact to
$dateiname = "/home/pi/dualis/hash.txt"; //where to save the current version

/*
    SKRIPT:
*/
//LOGIN
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://dualis.dhbw.de/scripts/mgrqcgi");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIE, "cnsc=0;");
//Form Data
curl_setopt($ch, CURLOPT_POSTFIELDS, "usrname=".$dualis_name."&pass=".$dualis_pass."&APPNAME=CampusNet&PRGNAME=LOGINCHECK&ARGUMENTS=clino%2Cusrname%2Cpass%2Cmenuno%2Cmenu_type%2Cbrowser%2Cplatform&clino=000000000000001&menuno=000324&menu_type=classic&browser=&platform=");
curl_setopt($ch, CURLOPT_HEADER, true);                 //headers im output
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);         //ausgabe als string zurückgeben
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        //für HTTPS
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        //für HTTPS
$sResult = curl_exec($ch);
//Fehler abfangen und ggf. beenden
if (curl_errno($ch)) {
    echo curl_error($ch);
    exit(1);
}
curl_close($ch);
//SessionID auslesen
preg_match('/ARGUMENTS=-N(\d*)/', $sResult, $matches);
$id = $matches[1];

//ERGEBNISSE
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://dualis.dhbw.de/scripts/mgrqcgi?APPNAME=CampusNet&PRGNAME=STUDENT_RESULT&ARGUMENTS=-N".$id.",-N000310,-N0,-N000000000000000,-N000000000000000,-N000000000000000,-N0,-N000000000000000");
curl_setopt($ch, CURLOPT_COOKIE, "cnsc=0;");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);         //ausgabe als string zurückgeben
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        //für HTTPS
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        //für HTTPS
$sResult = curl_exec($ch);
if (curl_errno($ch)) {
    echo curl_error($ch);
    exit(1);
}
//echo $sResult;
curl_close($ch);

//Ergebnisse parsen
$doc = new DOMDocument();
$doc->loadHTML($sResult);
$table = $doc->getElementsByTagName('table');
foreach ($table as $table2) {
    $new_hash = md5($table2->nodeValue);
    break;
}

//string aus textdatei lesen
$old_hash = file_get_contents($dateiname);

if($old_hash != $new_hash) {
    //es gab eine änderung...
    //email benachrichtigen
    $to = implode(", ", $adresses);
    mail($to, "Note in Dualis", "Eine neue Note wurde in Dualis gesetzt.", "From: Raspberry Pi <pi@pi.org>");

    //neuen hash speichern
    file_put_contents($dateiname, $new_hash);
}

exit(0);

?>
