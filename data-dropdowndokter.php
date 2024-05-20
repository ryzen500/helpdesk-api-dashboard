<?php
// Koneksi ke database PostgreSQL menggunakan PDO
$dsn = 'pgsql:host=192.168.214.222;port=5121;dbname=db_rswb_simulasi_20221227'; // Sesuaikan dengan informasi PostgreSQL Anda
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
    $sql = "SELECT gelardepan ,nama_pegawai ,gelarbelakang_m.gelarbelakang_nama  FROM pegawai_m inner join gelarbelakang_m  on pegawai_m.gelarbelakang_id  = gelarbelakang_m.gelarbelakang_id WHERE pegawai_m.kelompokpegawai_id  = 1"; // Sesuaikan dengan tabel dan kolom Anda
    if ($query) {
        // Tambahkan kondisi WHERE jika ada parameter pencarian
        $sql .= " AND nama_pegawai ILIKE :search"; // ILIKE mendukung pencarian case-insensitive
    }

    $stmt = $pdo->prepare($sql);
    if ($query) {
        $search = '%' . $query . '%';
        $stmt->bindValue(':search', $search, PDO::PARAM_STR); // Gunakan wildcard untuk pencarian
    }

    $stmt->execute();

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }

    // Mengembalikan respons JSON
    echo json_encode($data);

} catch (PDOException $e) {
    // Tangani kesalahan jika koneksi atau query gagal
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
