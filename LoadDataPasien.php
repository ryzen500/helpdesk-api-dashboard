<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

$dsn = 'pgsql:host=192.168.214.225;port=5121;dbname=db_rswb_running_new';
$user = 'developer';
$password = 's6SpprwyLVqh7kFg';

try {
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    header("Content-Type: application/json; charset=UTF-8");

    $term = isset($_GET['term']) ? $_GET['term'] : '';
    $no_rekam_medik = isset($_GET['no_rekam_medik']) ? $_GET['no_rekam_medik'] : '';
    
    if ($term) {

    	$sql = "SELECT 
    pm.tanggal_lahir,
    pm.no_rekam_medik,
    pm.no_identitas_pasien,
    EXTRACT(YEAR FROM AGE(CURRENT_DATE, pm.tanggal_lahir)) AS umur,
    pm.alamat_pasien,
    COALESCE(km3.kelurahan_nama, '-') AS kelurahan_nama,
    COALESCE(km2.kecamatan_nama, '-') AS kecamatan_nama,
    COALESCE(km.kabupaten_nama, '-') AS kabupaten_nama,
    COALESCE(pm2.pekerjaan_nama, '-') AS pekerjaan_nama,
    pm.no_mobile_pasien,
    pt.tinggibadan_cm,
    pt.beratbadan_kg
    ,
    *
				FROM pasien_m pm
				LEFT JOIN pekerjaan_m pm2 ON pm.pekerjaan_id = pm2.pekerjaan_id
				LEFT JOIN kabupaten_m km ON pm.kabupaten_id = km.kabupaten_id
				LEFT JOIN kecamatan_m km2 ON pm.kecamatan_id = km2.kecamatan_id
				LEFT JOIN kelurahan_m km3 ON pm.kelurahan_id = km3.kelurahan_id
				LEFT JOIN pemeriksaanfisik_t pt ON pm.pasien_id = pt.pasien_id
				WHERE pm.no_rekam_medik = :term OR pm.no_identitas_pasien = :term OR pm.nama_pasien LIKE :term
				ORDER BY pt.pemeriksaanfisik_id DESC
				LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
    } elseif ($no_rekam_medik) {
        $sql = "SELECT 
    pm.tanggal_lahir,
    pm.no_rekam_medik,
    pm.no_identitas_pasien,
    EXTRACT(YEAR FROM AGE(CURRENT_DATE, pm.tanggal_lahir)) AS umur,
    pm.alamat_pasien,
    COALESCE(km3.kelurahan_nama, '-') AS kelurahan_nama,
    COALESCE(km2.kecamatan_nama, '-') AS kecamatan_nama,
    COALESCE(km.kabupaten_nama, '-') AS kabupaten_nama,
    COALESCE(pm2.pekerjaan_nama, '-') AS pekerjaan_nama,
    pm.no_mobile_pasien,
     pt.tinggibadan_cm,
    pt.beratbadan_kg
    ,
    *
FROM 
    pasien_m pm
LEFT JOIN 
    pekerjaan_m pm2 ON pm.pekerjaan_id = pm2.pekerjaan_id
LEFT JOIN 
    kabupaten_m km ON pm.kabupaten_id = km.kabupaten_id
LEFT JOIN 
    kecamatan_m km2 ON pm.kecamatan_id = km2.kecamatan_id
LEFT JOIN 
    kelurahan_m km3 ON pm.kelurahan_id = km3.kelurahan_id
LEFT JOIN 
    pemeriksaanfisik_t pt ON pm.pasien_id = pt.pasien_id
WHERE 
    pm.no_rekam_medik = :no_rekam_medik
ORDER BY 
    pt.pemeriksaanfisik_id desc limit 1
";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':no_rekam_medik', $no_rekam_medik, PDO::PARAM_STR);
    } else {
        $stmt = null;
    }

    if ($stmt) {
        $stmt->execute();
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'label' => $row['no_rekam_medik'] . ' - ' . $row['nama_pasien'],
                'value' => $row['no_rekam_medik'],
                'data' => $row,
                'umur' => $row['umur']
            ];
        }
        echo json_encode($results);
    } else {
        echo json_encode([]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
