<?php
$id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;
if ($id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api-gw.sports.naver.com/schedule/{$id}/lives");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) { echo 'Error:' . curl_error($ch); exit; }
    curl_close($ch);
    
    $data = json_decode($response, true);
    if (isset($data['result']['lives'][0]['liveId'])) {
        $liveId = $data['result']['lives'][0]['liveId'];
        $newApiUrl = "https://proxy-gateway.sports.naver.com/livecloud/lives/{$liveId}/playback?countryCode=ID&devt=HTML5_MO&timeMachine=false&p2p=true&includeThumbnail=true&pollingStatus=true";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $newApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $playbackResponse = curl_exec($ch);
        if (curl_errno($ch)) { echo 'Error:' . curl_error($ch); exit; }
        curl_close($ch);

        if (preg_match('/https:\/\/livecloud\.akamaized\.net[^\s"]+live\.mpd\?[^"]+/', $playbackResponse, $matches)) {
            header("Location: {$matches[0]}");
            exit;
        } else {
            echo "MPD URL not found.";
        }
    } else {
        echo "liveId not found.";
    }
} else {
    echo "Invalid or missing ID.";
}
?>