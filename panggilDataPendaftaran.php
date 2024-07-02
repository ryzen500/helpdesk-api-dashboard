<?php
// Koneksi ke database PostgreSQL menggunakan PDO
$dsn = 'pgsql:host=192.168.214.225;port=5121;dbname=db_rswb_running_new';
$user = 'developer';
$password = 's6SpprwyLVqh7kFg';

try {
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Header untuk respons JSON dan CORS
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    // Ambil parameter pencarian dari query string
    $query = isset($_GET['q']) ? $_GET['q'] : '';

    // Query untuk mendapatkan data dari database
    $sql = "SELECT
                tanggal_lahir,
                infokunjunganrd_v.no_pendaftaran,
                infokunjunganrd_v.pendaftaran_id,
                COALESCE(DATE(pasienadmisi_t.tgladmisi), DATE(infokunjunganrd_v.tgl_pendaftaran)) as tgl_pendaftaran,
                nama_pasien,
                no_rekam_medik,
                diagnosa_rujukan,
                diagnosa_nama,
                kelompokdiagnosa_id,
                infokunjunganrd_v.pegawai_id,
                pegawai_m.gelardepan,
                pegawai_m.nama_pegawai,
                COALESCE(gelarbelakang_m.gelarbelakang_nama, '') AS gelarbelakang_nama
            FROM
                infokunjunganrd_v
            LEFT JOIN
                pegawai_m ON infokunjunganrd_v.pegawai_id = pegawai_m.pegawai_id
            LEFT JOIN
                gelarbelakang_m ON pegawai_m.gelarbelakang_id = gelarbelakang_m.gelarbelakang_id
            LEFT JOIN
                pasienadmisi_t ON infokunjunganrd_v.pendaftaran_id = pasienadmisi_t.pendaftaran_id";

    if ($query) {
        $sql .= " WHERE no_pendaftaran ILIKE :search";
    } else {
        $sql .= " LIMIT 10";
    }

    $stmt = $pdo->prepare($sql);
    if ($query) {
        $stmt->bindValue(':search', '%' . $query . '%', PDO::PARAM_STR);
    }
    $stmt->execute();

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pendaftaran_id = $row['pendaftaran_id'];

        // Fetch child data
        $row['child_diagnosa'] = fetchChildData($pdo, 'pasienmorbiditas_t', 'diagnosa_m.diagnosa_nama', 'diagnosa_m', 'diagnosa_id', $pendaftaran_id, 'kelompokdiagnosa_id = 3');
        $row['child_triase'] = fetchChildData($pdo, 'asesmentriase_t', 'tglasesmentriase,td_systolic, td_diastolic, detaknadi, pernapasan, suhutubuh, tinggibadan_cm, beratbadan_kg, spo2 AS spo, CONCAT(TRIM(COALESCE(CAST(gcs_eye AS CHAR(10)), \'\')), \', \', TRIM(COALESCE(CAST(gcs_verbal AS CHAR(10)), \'\')), \', \', TRIM(COALESCE(CAST(gcs_motorik AS CHAR(10)), \'\'))) AS gcs_nilai, lingkar_kepala, lingkar_lengan, lingkar_dada', 'pegawai_m', 'pegawai_id', $pendaftaran_id);
        $row['child_anamnesa'] = fetchChildData($pdo, 'anamnesa_t', 'keluhanutama, keterangananamesa, riwayatpenyakitterdahulu, riwayatalergiobat, reaksialergimakanan, tindakanmedis, konsultasidokter', null, null, $pendaftaran_id);
        $row['chilDataPenunjang'] = fetchChildData($pdo, 'datajknpenunjang_v', 'kelompoktindakanbpjs_nama, daftartindakan_nama', null, null, $pendaftaran_id);

        $data[] = $row;
    }

    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

/**
 * Fetch child data from the database
 * 
 * @param PDO $pdo
 * @param string $table
 * @param string $columns
 * @param string|null $joinTable
 * @param string|null $joinCondition
 * @param int $pendaftaran_id
 * @param string|null $additionalCondition
 * @return array
 */
function fetchChildData($pdo, $table, $columns, $joinTable = null, $joinCondition = null, $pendaftaran_id, $additionalCondition = null) {
    $sql = "SELECT $columns FROM $table";

    if ($joinTable && $joinCondition) {
        $sql .= " LEFT JOIN $joinTable ON $table.$joinCondition = $joinTable.$joinCondition";
    }

    $sql .= " WHERE pendaftaran_id = :pendaftaran_id";
    
    if ($additionalCondition) {
        $sql .= " AND $additionalCondition";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':pendaftaran_id', $pendaftaran_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
