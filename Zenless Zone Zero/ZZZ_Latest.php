<?php
header('Content-Type: application/json');
$curl = curl_init();

curl_setopt_array(
    $curl,
    array(
        CURLOPT_URL => "https://sg-public-api.serenetia.com/api/hyp_global?game_id=U5hbdsT9W7",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
    )
);

$ResponcURL = json_decode(curl_exec($curl), true);
curl_close($curl);

// Mendapatkan array Latest dan patch
$Latest = $ResponcURL['data']['game_packages'][0]['main'];
$patches = $Latest['patches'];

// Inisialisasi array untuk URL, versi, ukuran, dan bahasa dari Latest dan patch
$LatestFiles = [];
$patchFiles = [];

// Fungsi untuk mengkonversi ukuran file ke MB atau GB
function formatSize($size) {
    $size = (int)$size;
    if ($size >= 1073741824) {
        return number_format($size / 1073741824, 2) . ' GB';
    } elseif ($size >= 1048576) {
        return number_format($size / 1048576, 2) . ' MB';
    } else {
        return number_format($size / 1024, 2) . ' KB';
    }
}

// Mendapatkan URL, versi, ukuran, dan bahasa dari Latest game_pkgs
foreach ($Latest['major']['game_pkgs'] as $game_pkg) {
    $LatestFiles[] = [
        'version' => $Latest['major']['version'],
        'language' => 'N/A', // Karena ini adalah game package, tidak ada bahasa
        'url' => $game_pkg['url'],
        'size' => formatSize($game_pkg['size'])
    ];
}

// Mendapatkan URL, versi, ukuran, dan bahasa dari Latest audio_pkgs
foreach ($Latest['major']['audio_pkgs'] as $audio_pkg) {
    $LatestFiles[] = [
        'version' => $Latest['major']['version'],
        'language' => $audio_pkg['language'],
        'url' => $audio_pkg['url'],
        'size' => formatSize($audio_pkg['size'])
    ];
}

// Mendapatkan URL, versi, ukuran, dan bahasa dari patch game_pkgs dan audio_pkgs
foreach ($patches as $patch) {
    foreach ($patch['game_pkgs'] as $game_pkg) {
        $patchFiles[] = [
            'version' => $patch['version'],
            'language' => 'N/A', // Karena ini adalah game package, tidak ada bahasa
            'url' => $game_pkg['url'],
            'size' => formatSize($game_pkg['size'])
        ];
    }

    foreach ($patch['audio_pkgs'] as $audio_pkg) {
        $patchFiles[] = [
            'version' => $patch['version'],
            'language' => $audio_pkg['language'],
            'url' => $audio_pkg['url'],
            'size' => formatSize($audio_pkg['size'])
        ];
    }
}

// Menampilkan array Latest
echo "Latest:\n";
foreach ($LatestFiles as $file) {
    echo "Version: " . $file['version'] . "\n";
    echo "Language: " . $file['language'] . "\n";
    echo "URL: " . $file['url'] . "\n";
    echo "Size: " . $file['size'] . "\n\n";
}

// Menampilkan array patch dengan label "Patch"
echo "Patch:\n";
foreach ($patchFiles as $file) {
    echo "Version: " . $file['version'] . "\n";
    echo "Language: " . $file['language'] . "\n";
    echo "URL: " . $file['url'] . "\n";
    echo "Size: " . $file['size'] . "\n\n";
}
