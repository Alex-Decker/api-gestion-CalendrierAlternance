

<?php
//lancer le serveur php avec la commande
//php -S 127.0.0.1:8080
$date_deb = $_GET["datedeb"];
$nbrAnnee = $_GET["duree"];
header("Access-Control-Allow-Origin: *");
//header("Content-Type: application/json");
class Cycle
{
    public $date;
    public $data;
}
class Annee
{
    public $date;
    public $data;
}
class Mois
{
    public $date;
    public $data;
}

function ferier($dateF)
{
    $data = file_get_contents("ferier/" . $dateF . '.json', true);
    if (!$data) {
        $url = 'https://calendrier.api.gouv.fr/jours-feries/metropole/' . $dateF . '.json';
        $gouvURL = curl_init($url);

        curl_setopt_array($gouvURL, [
            CURLOPT_CAINFO => __DIR__ . DIRECTORY_SEPARATOR . 'certi.cer',
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 4
        ]);
        $data = curl_exec($gouvURL);
        file_put_contents("ferier/" . $dateF . '.json', $data);

        curl_close($gouvURL);
    }
    return $data;
}
function TabApi($StrDate, $duree)
{
    $donnees = file_get_contents("calendrier/" . $StrDate . '-' . $duree . '.json', true);
    if (!$donnees) {
        $DayString = $StrDate;
        $Date = strtotime($DayString);
        $Day = date('d', $Date);
        $Month = date('n', $Date);
        $Year = date('Y', $Date);
        $jour =  array("AM" => 0, "PM" => 0);
        $cycle = array();
        $jFerier = array_keys(json_decode(ferier($Year), true));
        for ($i = 1; $i <= $duree; $i++) {
            $annee = array();
            for ($j = 1; $j <= 12; $j++) {
                $mois = array();
                if ($Month > 12) {
                    $Month = 1;
                    $Year++;
                    $jFerier = array_keys(json_decode(ferier($Year), true));
                }
                $numberDay = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
                for ($k = 1; $k <= $numberDay; $k++) {
                    $text = $Year . '-' . $Month . '-' . $k;
                    $jour =  array("AM" => 0, "PM" => 0);

                    for ($y = 0; $y < count($jFerier); $y++) {


                        $verificationJF = strtotime($text) - strtotime($jFerier[$y]);
                        $verificationWeekend = idate('w', strtotime($text));

                        if ($verificationWeekend == 0 || $verificationWeekend == 6) {
                            $jour = array("AM" => 4, "PM" => 4);
                            $curentDate = new Mois();
                            $curentDate->date = $text;
                            $curentDate->data = $jour;
                            array_push($mois, $curentDate);
                            break;
                        } elseif ($verificationJF == 0) {
                            $jour = array("AM" => 3, "PM" => 3);
                            $curentDate = new Mois();
                            $curentDate->date = $text;
                            $curentDate->data = $jour;
                            array_push($mois, $curentDate);
                            break;
                        } elseif (count($jFerier) - 1 == $y) {
                            $curentDate = new Mois();
                            $curentDate->date = $text;
                            $curentDate->data = $jour;
                            array_push($mois, $curentDate);
                        }
                    }
                }

                $text = '' . $Year . '-' . $Month;
                $curentMonth = new Annee();
                $curentMonth->date = $text;
                $curentMonth->data = $mois;
                array_push($annee, $curentMonth);
                $Month++;
            }
            $text = '' . ($Year - 1) . '-' . $Year;
            $curentYear = new Cycle();
            $curentYear->date = $text;
            $curentYear->data = $annee;
            array_push($cycle, $curentYear);
            $donnees = json_encode($cycle);
            file_put_contents("calendrier/" . $StrDate . '-' . $duree . '.json', $donnees);
        }
    }
    return  $donnees;
}

print_r(TabApi($date_deb, intval($nbrAnnee)));
?>