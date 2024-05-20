<?php
require_once 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$cons_id = "21540";

$timestamp = "1714113991";
$signature = "jfM0NnNA69VZIuoU2vdcvlqpytvJasjHD76Hpj93/aM=";
$secretKey = "4sL53AF14A";

// function decrypt


function stringDecrypt($key, $string)
{


    $encrypt_method = 'AES-256-CBC';

    // hash
    $key_hash = hex2bin(hash('sha256', $key));

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);

    $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

    return $output;
}

// function lzstring decompress 
// download libraries lzstring : https://github.com/nullpunkt/lz-string-php

$url = "MJyfeaETdoylAHYycpbx+0whK29MIBc/a+nhY5efrfA4snt7J6SbDgQkhO1rNeH/Wqzrq80KQydvVCmHcXHAunUboTQNTZGj1/9gfRqaj8hREISHlQ035B6Or6MXV4IkAOSgvd5823br0262ooGptFTZnyQeozy17QzSHnd2+0KgCJ31YfLNfLONW3SOcp/4Xy0IbR1khu5QKHZ+xmCRL+IqopogC3fPErAcAf1vSu+XfpOuYLk+kuCBt+eUnEDkVgPk1kiD26vf0SYhr7LQUsF1cNiGZM3GIhOtPN3iwrJ5VpJ89gJkycqe1UA5C3O3E50Lo5QLa3wi6ACQROt73gYIdmy67+AYvB45IyXHbQGgj5yABfl3HsNCa52oER7c7/m2cnByZzg+DvR63wP+rVCGjqbRTBwjGxoQWBCfkW9KMaG+AsVdyLWkQotUIM8ugLtTy5Hwcms4fnvZXSPVXECzGXcria+D2K7MD3J1HAVFMR/x+Gml5940Ue/xGARRPJznUMkxx4I8qP8k2GG416uskD4WHfmYb/Qdk5aUa+YH+jowoYj0TlVco/bPeiyrmPuqZWyy5R9IrpJVB7Wyba4K2ToYEoxqK30hrVh4qw9JkAs4vRXqPa7KGSwyPlQKhp3qGoNJY/K/Zr5yKLc5Y1Z8fXo/3UTnExPIsjvlV0GW29gApf6uzmPOYnk23q8h0e5SAp8vjqK3u687jflc3d6Ehda/Q8C79D1y4HtQqSrTIY9PKfDlmyejJbffvneKL0mz3j7ZhGPPYr2K1imm59tZQWw49tPUY5jncEu+TNyCnCqCTgyGH+FGle4tY7R8ZuZIP1TYPh3S5htVT5BPROgZeS+YtO+OiNtFaau1HM4InnFWQLBP4IlokRwvXbUnaY9wKFUg8dHhzl3m4WkS/izGi+HBh2+GQWYmRaY4B5Y=";

// $url = '';
$try = stringDecrypt($cons_id . $secretKey . $timestamp, $url);
$stringDerypt = decompress($try);

// Hilangkan informasi panjang dan tanda kutip
$cleanedJsonString = preg_replace('/string\(\d+\)\s/', '', $stringDerypt);
$cleanedJsonString = trim($cleanedJsonString, '"');

// Mendekode JSON menjadi objek PHP
$pesertaObj = json_decode($cleanedJsonString);

// Cek kesalahan decoding
if (json_last_error() === JSON_ERROR_NONE) {
    // Mengambil nama peserta
    $nama = $pesertaObj->peserta->nama;  // Output: HARI PRANOTO W
    
    // Membuat array untuk dikembalikan
    $result = [
        'status' => 'success',
        'data' => [
            'peserta' => $pesertaObj->peserta
        ]
    ];
    
    // Mengembalikan sebagai JSON
    header('Content-Type: application/json');

    echo json_encode($result);  // Mengembalikan hasil sebagai JSON
} else {
    // Mengembalikan kesalahan decoding
    $error = json_last_error_msg();
    $errorResponse = [
        'status' => 'error',
        'message' => $error
    ];
    
    return json_encode($errorResponse);  // Mengembalikan kesalahan sebagai JSON
}
// echo $try;
// if ($res2 != null) {
//     // echo "-";
//     var_dump($res2);
//     // echo $res2;

// } else {
//     echo "--";
// }
// var_dump($signature);
// echo $cons_id.$timestamp.$secretKey;
function decompress($string)
{

    return \LZCompressor\LZString::decompressFromEncodedURIComponent($string);

}

?>