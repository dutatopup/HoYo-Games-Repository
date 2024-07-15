<?php
header('Content-Type: application/json'); 

function formatSize($size) {
    if ($size >= 1073741824) {
        return number_format($size / 1073741824, 2) . ' GB';
    } elseif ($size >= 1048576) {
        return number_format($size / 1048576, 2) . ' MB';
    } else {
        return number_format($size / 1024, 2) . ' KB';
    }
}

function formatOutput($data) {
    $output = "";
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $output .= formatOutput($value);
        } else {
            $output .= "$key: $value\n";
        }
    }
    return $output;
}

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://id-public-api.serenetia.com/api/bh3_global",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
));

$response = curl_exec($curl);

if ($response === false) {
    $error_message = curl_error($curl);
    echo "cURL Error: $error_message";
} else {
    $responseData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if (isset($responseData['data']['game']['latest'])) {
            $latest = $responseData['data']['game']['latest'];
            $latestResult = array(
                'info' => 'Latest Version in Game',
                'version' => $latest['version'],
                'path' => $latest['path'],
                'size' => formatSize($latest['size'])
            );

            if (is_null($latest['pre_download_game'])) {
                echo formatOutput(array('latest' => $latestResult));
            } else {
                $preDownload = $latest['pre_download_game'];
                $preDownloadResult = array(
                    'info' => 'Predownload Version',
                    'version' => $preDownload['version'],
                    'path' => $preDownload['path'],
                    'size' => formatSize($preDownload['size'])
                );
                echo formatOutput(array('pre_download_game' => $preDownloadResult));
            }
        } else {
            echo "Latest game data not found";
        }
    } else {
        echo "Invalid JSON received";
    }
}

curl_close($curl);
?>
