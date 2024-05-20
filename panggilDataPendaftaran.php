<?php
// Koneksi ke database PostgreSQL menggunakan PDO
// $dsn = 'pgsql:host=192.168.214.222;port=5121;dbname=db_rswb_simulasi_20221227'; // Sesuaikan dengan informasi PostgreSQL Anda
$dsn ='pgsql:host=192.168.214.225;port=5121;dbname=db_rswb_running_new';
$user = 'developer'; // Nama pengguna PostgreSQL
$password = 's6SpprwyLVqh7kFg'; // Kata sandi PostgreSQL

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
                CASE WHEN pasienadmisi_id IS NOT NULL THEN DATE(pasienadmisi_t.tgladmisi) ELSE DATE(infokunjunganrd_v.tgl_pendaftaran) END as tgl_pendaftaran,
                nama_pasien, 
                no_rekam_medik, 
                diagnosa_rujukan, 
                diagnosa_nama, 
                kelompokdiagnosa_id 
            FROM 
                infokunjunganrd_v 
            LEFT JOIN 
                pasienadmisi_t ON infokunjunganrd_v.pendaftaran_id = pasienadmisi_t.pendaftaran_id"; // Sesuaikan dengan tabel dan kolom Anda
    if ($query) {
        // Tambahkan kondisi WHERE jika ada parameter pencarian
        $sql .= " WHERE no_rekam_medik ILIKE :search"; // ILIKE mendukung pencarian case-insensitive
    } else {
        $sql .= " LIMIT 10"; // ILIKE mendukung pencarian case-insensitive

    }

    $stmt = $pdo->prepare($sql);
    if ($query) {
        $search = '%' . $query . '%';
        $stmt->bindValue(':search', $search, PDO::PARAM_STR); // Gunakan wildcard untuk pencarian
    }

    $stmt->execute();

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Query tambahan untuk child_diagnosa
        $sqlChildDiagnosa = "SELECT diagnosa_m.diagnosa_nama FROM pasienmorbiditas_t LEFT JOIN diagnosa_m  on pasienmorbiditas_t.diagnosa_id = diagnosa_m.diagnosa_id  WHERE kelompokdiagnosa_id = 3 and pendaftaran_id = " . $row['pendaftaran_id'] . ""; // Sesuaikan dengan kondisi Anda
        $stmtChildDiagnosa = $pdo->query($sqlChildDiagnosa);
        $childDiagnosa = $stmtChildDiagnosa->fetchAll(PDO::FETCH_ASSOC);

        // Query Tambahan untuk child triase 

        $sqlChildTriase = "
    SELECT 
        td_systolic, 
        td_diastolic, 
        detaknadi, 
        pernapasan, 
        suhutubuh, 
        tinggibadan_cm, 
        beratbadan_kg, 
        spo2 as spo,
        CONCAT(
            TRIM(COALESCE(CAST(gcs_eye AS CHAR(10)), '')), ', ', 
            TRIM(COALESCE(CAST(gcs_verbal AS CHAR(10)), '')), ', ', 
            TRIM(COALESCE(CAST(gcs_motorik AS CHAR(10)), ''))
        ) AS gcs_nilai,
        lingkar_kepala, 
        lingkar_lengan, 
        lingkar_dada,
        pegawai_m.gelardepan, 
        pegawai_m.nama_pegawai, 
        COALESCE(gelarbelakang_m.gelarbelakang_nama, '') AS gelarbelakang_nama
        FROM 
        asesmentriase_t 
    LEFT JOIN 
        pegawai_m  ON asesmentriase_t.dpjp_id = pegawai_m.pegawai_id 
    LEFT JOIN 
        gelarbelakang_m  ON pegawai_m.gelarbelakang_id = gelarbelakang_m.gelarbelakang_id
    WHERE 
        pendaftaran_id = " . $row['pendaftaran_id'];

        // $sqlChildTriase = "SELECT td_systolic, pegawai_m.nama_pegawai as  dpjp_id,td_diastolic,detaknadi,pernapasan,suhutubuh,tinggibadan_cm,beratbadan_kg,gcs_nilai,lingkar_kepala,lingkar_lengan,lingkar_dada FROM asesmentriase_t left join pegawai_m on asesmentriase_t.dpjp_id = pegawai_m.pegawai_id WHERE pendaftaran_id = ".$row['pendaftaran_id'].""; // Sesuaikan dengan kondisi Anda
        $stmtChildTriase = $pdo->query($sqlChildTriase);
        $childTriase = $stmtChildTriase->fetchAll(PDO::FETCH_ASSOC);


        // Query Tambahan untuk child Anamnesa 

        $sqlChildAnamnesa = "SELECT keluhanutama,keterangananamnesa,riwayatpenyakitterdahulu,riwayatalergiobat,reaksialergimakanan,riwayatalergiobat,tindakanmedis,konsultasidokter from anamnesa_t WHERE pendaftaran_id = " . $row['pendaftaran_id'] . ""; // Sesuaikan dengan kondisi Anda
        $stmtChildAnamnesa = $pdo->query($sqlChildAnamnesa);
        $childAnamnesa = $stmtChildAnamnesa->fetchAll(PDO::FETCH_ASSOC);

        // Tambahkan objek child_diagnosa ke dalam $row
        $row["child_diagnosa"] = $childDiagnosa;
        $row["child_triase"] = $childTriase;
        $row["child_anamnesa"] = $childAnamnesa;

        // Tambahkan $row yang sudah dimodifikasi ke dalam array $data
        $data[] = $row;
    }

    // Mengembalikan respons JSON
    echo json_encode($data);
} catch (PDOException $e) {
    // Tangani kesalahan jika koneksi atau query gagal
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
