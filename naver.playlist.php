<?php
// URL API terbaru
$url = "https://api-gw.sports.naver.com/schedule/lives";

// Ambil data dari API
$response = file_get_contents($url);
if ($response === FALSE) {
    die('Error occurred while fetching data');
}

// Decode JSON
$data = json_decode($response, true);
if ($data === null) {
    die('Error occurred while decoding JSON');
}

// Variabel hasil output
$outputLive = "#EXTM3U\n";
$outputUpcoming = "#EXTM3U\n";

// Zona waktu Indonesia
date_default_timezone_set('Asia/Jakarta');

// Pastikan ada data 'lives'
if (isset($data['result']['lives']) && is_array($data['result']['lives'])) {
    foreach ($data['result']['lives'] as $match) {
        $upperCategoryId = $match['upperCategoryId'] ?? '';
        if (!in_array($upperCategoryId, ['kbaseballetc', 'kvolleyball'])) {
            continue;
        }

        $gameId = $match['gameId'] ?? '';
        $title = $match['title'] ?? 'Unknown Match';
        $category = $match['categoryName'] ?? 'Sports';
        $gameOnAir = $match['gameOnAir'] ?? false;
        $startTimestamp = $match['startTime'] ?? null;

        // Konversi waktu mulai ke WIB
        $startTimeWIB = $startTimestamp ? date('H:i', $startTimestamp + (9 * 3600 - 7 * 3600)) . " WIB" : '';

        $status = $gameOnAir ? 'LIVE NOW' : 'UPCOMING';
        $translatedTitle = translateText($title);

        // Tambahkan waktu mulai untuk Upcoming
        if (!$gameOnAir && $startTimeWIB) {
            $translatedTitle .= " | " . $startTimeWIB;
        }

        // Format M3U
        $entry = "#EXTINF:-1 group-logo=\"https://i.ibb.co.com/sbCVKBG/20241230-031819.png\" "
                . "tvg-logo=\"https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSFv0ZO9Ny4_91GVvCd2bWJA6_dedTacMSmPF9p9uJ7q5vVDGEJLMDa1V5D&s=10\" "
                . "group-title=\"• Naver $category\", $translatedTitle | $status\n";
        $entry .= "#KODIPROP:inputstream.adaptive.manifest_type=hls\n";
        $entry .= "#EXTVLCOPT:http-user-agent=Mozilla/5.0 (Linux; Android 14; Google TV Streamer Build/UTT3.240625.001.K5; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/130.0.6723.60 Mobile Safari/537.36\n";
        $entry .= "https://bintangstreaming.my.id/naver/index.mpd?id=$gameId\n\n";

        if ($gameOnAir) {
            $outputLive .= $entry;
        } else {
            $outputUpcoming .= $entry;
        }
    }
}

// Gabungkan hasil
echo "# ====== 🔴 LIVE NOW ======\n\n";
echo !empty(trim($outputLive)) ? $outputLive : "# Tidak ada pertandingan live saat ini.\n\n";
echo "# ====== 🕐 UPCOMING ======\n\n";
echo !empty(trim($outputUpcoming)) ? $outputUpcoming : "# Tidak ada pertandingan upcoming saat ini.\n\n";


// Fungsi translate sederhana via Google Translate gratis
function translateText($text, $targetLanguage = 'en') {
    $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl='
         . $targetLanguage . '&dt=t&q=' . urlencode($text);
    $response = @file_get_contents($url);
    if ($response === FALSE) return $text;
    $translation = json_decode($response, true);
    return $translation[0][0][0] ?? $text;
}
?>
