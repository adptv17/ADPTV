<?php

// Dapatkan parameter 'id' dari string kueri URL
$id = $_GET['id'] ?? ''; 

// Validasi parameter 'id'
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $id)) {
    die('Invalid ID parameter.');
}

// Inisialisasi cURL
$curl = curl_init();

// Header permintaan
$headers = array(
    'User-Agent: Oxygen TV/5.9 (Linux;Android 10) ExoPlayerLib/2.12.2',
    'Host: cdn2-reg2.mm.oxygen.id',
    'Connection: Keep-Alive'
);

// Pengaturan cURL
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://cdn2-reg2.mm.oxygen.id/hls/{$id}/index.m3u8",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => $headers
));

// Eksekusi permintaan
$response = curl_exec($curl);

// Periksa kesalahan cURL
if (curl_errno($curl)) {
    die('cURL Error: ' . curl_error($curl));
}

curl_close($curl);

// Hapus string yang tidak diinginkan
$response = str_replace(':MTExMTExMTExMTExMTExMQ==', '', $response);

// Kunci dan IV untuk dekripsi
$key = 'oxygenmultimedia';
$iv = '1111111111111111';
$encrypted_data = $response;

// Dekripsi data
$decrypted_data = openssl_decrypt($encrypted_data, 'AES-128-CBC', $key, 0, $iv);

// Periksa apakah dekripsi berhasil
if ($decrypted_data === false) {
    die('Failed to decrypt data.');
}

// Tambahkan path ke setiap file .ts
$path = "https://cdn2-reg2.mm.oxygen.id/hls/{$id}/";
$decrypted_data = preg_replace('/([^\s]+\.ts)/', "{$path}$1", $decrypted_data);

// Tampilkan data yang didekripsi
echo $decrypted_data;

?>