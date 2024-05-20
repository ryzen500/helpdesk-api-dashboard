<?php
// Koneksi ke database
$host = 'localhost';
$dbname = 'db_helpdesk';
$user = 'root';
$password = 'Shinigami_145';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Membuat koneksi
$mysqli = new mysqli($host, $user, $password, $dbname);

// Periksa koneksi
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Mendapatkan tanggal dari parameter GET
$date = isset($_GET['date']) ? $_GET['date'] : null;

// Validasi tanggal
if ($date && preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
    // Query untuk mengambil data tiket berdasarkan tanggal
    $query = $mysqli->prepare("SELECT * FROM tiket WHERE DATE(TANGGAL) = ?");
    $query->bind_param("s", $date);
    $query->execute();
    $result = $query->get_result();

    // Mengumpulkan data hasil query
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Mengirimkan response dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    // Mengirimkan error jika format tanggal salah
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing date parameter']);
}

$mysqli->close();
