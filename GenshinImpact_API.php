<?php
header('Content-Type: application/json');

$curl = curl_init();

curl_setopt_array(
    $curl,
    array(
        CURLOPT_URL => "https://sg-public-api.serenetia.com/api/hyp_global?game_id=gopR6Cufr3",
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

// Mendapatkan array main dan predownload
$main = $ResponcURL['data']['game_packages'][0]['main'];
$preDownload = isset($ResponcURL['data']['game_packages'][0]['pre_download']) ? $ResponcURL['data']['game_packages'][0]['pre_download'] : null;

// Inisialisasi array untuk URL, versi, ukuran, dan bahasa dari predownload dan main
$mainFiles = [];
$preDownloadFiles = [];

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

// Mendapatkan URL, versi, ukuran, dan bahasa dari main game_pkgs
foreach ($main['game_pkgs'] as $game_pkg) {
    $mainFiles[] = [
        'version' => $main['version'],
        'language' => 'N/A', // Karena ini adalah game package, tidak ada bahasa
        'url' => $game_pkg['url'],
        'size' => formatSize($game_pkg['size'])
    ];

    // Menambahkan patch jika tersedia
    if (!empty($main['patches'])) {
        foreach ($main['patches'] as $patch) {
            $mainFiles[] = [
                'label' => 'PATCH',
                'version' => $patch['version'],
                'language' => 'N/A', // Karena ini adalah game package, tidak ada bahasa
                'url' => $patch['game_pkgs'][0]['url'], // Mengambil URL dari game_pkgs patch pertama
                'size' => formatSize($patch['game_pkgs'][0]['size'])
            ];
        }
    }
}

// Mendapatkan URL, versi, ukuran, dan bahasa dari main audio_pkgs
foreach ($main['audio_pkgs'] as $audio_pkg) {
    $mainFiles[] = [
        'version' => $main['version'],
        'language' => $audio_pkg['language'],
        'url' => $audio_pkg['url'],
        'size' => formatSize($audio_pkg['size'])
    ];

    // Menambahkan patch jika tersedia
    if (!empty($main['patches'])) {
        foreach ($main['patches'] as $patch) {
            foreach ($patch['audio_pkgs'] as $patch_audio) {
                if ($patch_audio['language'] === $audio_pkg['language']) {
                    $mainFiles[] = [
                        'label' => 'PATCH',
                        'version' => $patch['version'],
                        'language' => $patch_audio['language'],
                        'url' => $patch_audio['url'],
                        'size' => formatSize($patch_audio['size'])
                    ];
                }
            }
        }
    }
}

// Mendapatkan URL, versi, ukuran, dan bahasa dari predownload jika tidak null
if ($preDownload) {
    foreach ($preDownload['major']['game_pkgs'] as $game_pkg) {
        $preDownloadFiles[] = [
            'version' => $preDownload['major']['version'],
            'language' => 'N/A', // Karena ini adalah game package, tidak ada bahasa
            'url' => $game_pkg['url'],
            'size' => formatSize($game_pkg['size'])
        ];

        // Menambahkan patch jika tersedia
        if (!empty($preDownload['patches'])) {
            foreach ($preDownload['patches'] as $patch) {
                $preDownloadFiles[] = [
                    'label' => 'PATCH',
                    'version' => $patch['version'],
                    'language' => 'N/A', // Karena ini adalah game package, tidak ada bahasa
                    'url' => $patch['game_pkgs'][0]['url'], // Mengambil URL dari game_pkgs patch pertama
                    'size' => formatSize($patch['game_pkgs'][0]['size'])
                ];
            }
        }
    }

    foreach ($preDownload['major']['audio_pkgs'] as $audio_pkg) {
        $preDownloadFiles[] = [
            'version' => $preDownload['major']['version'],
            'language' => $audio_pkg['language'],
            'url' => $audio_pkg['url'],
            'size' => formatSize($audio_pkg['size'])
        ];

        // Menambahkan patch jika tersedia
        if (!empty($preDownload['patches'])) {
            foreach ($preDownload['patches'] as $patch) {
                foreach ($patch['audio_pkgs'] as $patch_audio) {
                    if ($patch_audio['language'] === $audio_pkg['language']) {
                        $preDownloadFiles[] = [
                            'label' => 'PATCH',
                            'version' => $patch['version'],
                            'language' => $patch_audio['language'],
                            'url' => $patch_audio['url'],
                            'size' => formatSize($patch_audio['size'])
                        ];
                    }
                }
            }
        }
    }
}

// Menampilkan array predownload jika ada
if (!empty($preDownloadFiles)) {
    echo "PreDownload:\n";
    foreach ($preDownloadFiles as $file) {
        echo "Version: " . $file['version'] . "\n";
        echo "Language: " . $file['language'] . "\n";
        echo "Label: " . $file['label'] . "\n";
        echo "URL: " . $file['url'] . "\n";
        echo "Size: " . $file['size'] . "\n\n";
    }
}

// Menampilkan array main
echo "Main:\n";
foreach ($mainFiles as $file) {
    echo "Version: " . $file['version'] . "\n";
    echo "Language: " . $file['language'] . "\n";
    if (isset($file['label'])) {
        echo "Label: " . $file['label'] . "\n";
    }
    echo "URL: " . $file['url'] . "\n";
    echo "Size: " . $file['size'] . "\n\n";
}
