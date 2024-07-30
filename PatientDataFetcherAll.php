<?php

class PatientDataFetcherAll {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function fetchPatientData($term) {
        $sql = "SELECT no_rekam_medik, no_identitas_pasien, nama_pasien 
                FROM pasien_m 
                WHERE no_rekam_medik ILIKE :term OR no_identitas_pasien ILIKE :term  OR nama_pasien ILIKE :term
                LIMIT 10";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'label' => $row['no_rekam_medik'] . ' - ' . $row['nama_pasien'],
                'value' => $row['no_rekam_medik'],
                'data' => $row
            ];
        }

        return $results;
    }
}

// Usage in your script
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

$dsn = 'pgsql:host=192.168.214.225;port=5121;dbname=db_rswb_running_new';
$user = 'developer';
$password = 's6SpprwyLVqh7kFg';

try {
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $term = isset($_GET['term']) ? $_GET['term'] : '';
    
    $fetcher = new PatientDataFetcherAll($pdo);
    $results = $fetcher->fetchPatientData($term);

    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
