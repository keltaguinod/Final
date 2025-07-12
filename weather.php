<?php
$apiKey = "85c245a3efe7e1693efd88bb6251e51f";
$city = "Manila";
$apiUrl = "https://api.openweathermap.org/data/2.5/forecast?q=$city&appid=$apiKey&units=metric";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if ($data && $data["cod"] == "200") {
    // Current temperature (from first record)
    $currentTemp = round($data["list"][0]["main"]["temp"]);
    echo "<div class='weather-widget'>";
    echo "<div class='current-temp'>{$currentTemp} °C</div>";
    echo "<div class='temp-graph'>";
    
    // Line chart using points (6 time segments)
    for ($i = 0; $i < 6; $i++) {
        $tempPoint = round($data["list"][$i]["main"]["temp"]);
        echo "<div class='dot' title='{$tempPoint}°C'></div>";
    }
    
    echo "</div>";
    echo "<div class='forecast'>";
    
    // 3-day forecast (first 3 days)
    $forecastDays = [];
    foreach ($data["list"] as $forecast) {
        $date = date("D", strtotime($forecast["dt_txt"]));
        if (!isset($forecastDays[$date])) {
            $forecastDays[$date] = [
                'max' => $forecast["main"]["temp_max"],
                'min' => $forecast["main"]["temp_min"],
                'icon' => $forecast["weather"][0]["icon"]
            ];
        }
        if (count($forecastDays) >= 3) break;
    }

    foreach ($forecastDays as $day => $info) {
        echo "<div class='day-forecast'>";
        echo "<img src='https://openweathermap.org/img/wn/{$info["icon"]}@2x.png' width='40'>";
        echo "<div class='day'>{$day}</div>";
        echo "<div class='temps'>".round($info["max"])."°C / ".round($info["min"])."°C</div>";
        echo "</div>";
    }

    echo "</div></div>";
} else {
    echo "<p style='color:white;'>Unable to fetch weather data.</p>";
}
?>