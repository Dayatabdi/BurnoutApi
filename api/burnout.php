<?php
require_once 'db_config.php';

header("Content-Type: application/json");

$userId = getAuthEmail();

if (!$userId) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $conn = getConnection();
    // Tambahkan filter WHERE is_deleted = 0 agar data yang di-recycle bin tidak ikut tampil
   $stmt = $conn->prepare(
        "SELECT id, nama, jam_tidur, mudah_lelah, sulit_fokus, susah_tidur,
                mudah_marah, tidak_bersemangat, overwhelmed, stres_level,
                skor, image_data, tanggal,
                IF(user_id = ?, 1, 0) as mine
         FROM burnout_records
         WHERE is_deleted = 0
         ORDER BY tanggal DESC"
    );
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['mudah_lelah']       = (bool)$row['mudah_lelah'];
        $row['sulit_fokus']       = (bool)$row['sulit_fokus'];
        $row['susah_tidur']       = (bool)$row['susah_tidur'];
        $row['mudah_marah']       = (bool)$row['mudah_marah'];
        $row['tidak_bersemangat'] = (bool)$row['tidak_bersemangat'];
        $row['overwhelmed']       = (bool)$row['overwhelmed'];
        $row['mine']              = (int)$row['mine'];
        $row['jam_tidur']         = (float)$row['jam_tidur'];
        $row['skor']              = (int)$row['skor'];
        $row['image_data']        = $row['image_data'];
        $data[] = $row;
    }

    echo json_encode($data);
    $conn->close();

} elseif ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
    $id               = $_POST['id'] ?? '';
    $nama             = $_POST['nama'] ?? '';
    $jamTidur         = (float)($_POST['jam_tidur'] ?? 0);
    $mudahLelah       = isset($_POST['mudah_lelah']) ? (int)$_POST['mudah_lelah'] : 0;
    $sulitFokus       = isset($_POST['sulit_fokus']) ? (int)$_POST['sulit_fokus'] : 0;
    $susahTidur       = isset($_POST['susah_tidur']) ? (int)$_POST['susah_tidur'] : 0;
    $mudahMarah       = isset($_POST['mudah_marah']) ? (int)$_POST['mudah_marah'] : 0;
    $tidakBersemangat = isset($_POST['tidak_bersemangat']) ? (int)$_POST['tidak_bersemangat'] : 0;
    $overwhelmed      = isset($_POST['overwhelmed']) ? (int)$_POST['overwhelmed'] : 0;
    $stresLevel       = $_POST['stres_level'] ?? '';
    $skor             = isset($_POST['skor']) ? (int)$_POST['skor'] : 0;

    if (empty($id) || empty($nama) || empty($stresLevel)) {
        echo json_encode(["status" => "error", "message" => "Data tidak lengkap", "id" => $id, "userId" => $userId]);
        exit;
    }

    $conn = getConnection();

    $check = $conn->prepare("SELECT id FROM burnout_records WHERE id = ? AND user_id = ?");
    $check->bind_param("ss", $id, $userId);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows === 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Data tidak ditemukan",
            "id" => $id,
            "userId" => $userId
        ]);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE burnout_records SET
            nama=?, jam_tidur=?, mudah_lelah=?, sulit_fokus=?,
            susah_tidur=?, mudah_marah=?, tidak_bersemangat=?,
            overwhelmed=?, stres_level=?, skor=?
         WHERE id=? AND user_id=?"
    );
    
    // FIX: String bind_param dikoreksi menjadi 12 karakter "sdiiiiiisiss"
    $stmt->bind_param(
        "sdiiiiiisiss",
        $nama, $jamTidur,
        $mudahLelah, $sulitFokus, $susahTidur,
        $mudahMarah, $tidakBersemangat, $overwhelmed,
        $stresLevel, $skor, $id, $userId
    );

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Data berhasil diubah",
            "affected_rows" => $stmt->affected_rows
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    $conn->close();

} elseif ($method === 'POST') {

    // --- BLOK TAMBAHAN UNTUK RESTORE ---
    if (isset($_POST['action']) && $_POST['action'] === 'restore') {
        $id = $_POST['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(["status" => "error", "message" => "ID tidak ditemukan untuk di-restore"]);
            exit;
        }
        
        $conn = getConnection();
        // Ubah status is_deleted menjadi 0 (pulih)
        $stmt = $conn->prepare("UPDATE burnout_records SET is_deleted = 0 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ss", $id, $userId);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Data berhasil dipulihkan"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        $conn->close();
        exit; 
    }
    // -----------------------------------

    $nama             = $_POST['nama'] ?? '';
    $jamTidur         = $_POST['jam_tidur'] ?? '';
    $mudahLelah       = isset($_POST['mudah_lelah']) ? (int)$_POST['mudah_lelah'] : 0;
    $sulitFokus       = isset($_POST['sulit_fokus']) ? (int)$_POST['sulit_fokus'] : 0;
    $susahTidur       = isset($_POST['susah_tidur']) ? (int)$_POST['susah_tidur'] : 0;
    $mudahMarah       = isset($_POST['mudah_marah']) ? (int)$_POST['mudah_marah'] : 0;
    $tidakBersemangat = isset($_POST['tidak_bersemangat']) ? (int)$_POST['tidak_bersemangat'] : 0;
    $overwhelmed      = isset($_POST['overwhelmed']) ? (int)$_POST['overwhelmed'] : 0;
    $stresLevel       = $_POST['stres_level'] ?? '';
    $skor             = isset($_POST['skor']) ? (int)$_POST['skor'] : 0;

    if (empty($nama) || $jamTidur === '' || empty($stresLevel)) {
        echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
        exit;
    }

    $imageData = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageData = base64_encode(file_get_contents($_FILES['image']['tmp_name']));
    }

    $id = uniqid('bo_', true);
    $conn = getConnection();
    $stmt = $conn->prepare(
        "INSERT INTO burnout_records
         (id, user_id, nama, jam_tidur, mudah_lelah, sulit_fokus, susah_tidur,
          mudah_marah, tidak_bersemangat, overwhelmed, stres_level, skor, image_data)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sssdiiiiiisis",
        $id, $userId, $nama, $jamTidur,
        $mudahLelah, $sulitFokus, $susahTidur,
        $mudahMarah, $tidakBersemangat, $overwhelmed,
        $stresLevel, $skor, $imageData
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Data berhasil disimpan", "id" => $id]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    $conn->close();

} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(["status" => "error", "message" => "ID tidak ditemukan"]);
        exit;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM burnout_records WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ss", $id, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Data tidak ditemukan atau bukan milik kamu"]);
        $conn->close();
        exit;
    }

    // FIX: Mengubah DELETE menjadi UPDATE is_deleted (Soft Delete)
    $stmt2 = $conn->prepare("UPDATE burnout_records SET is_deleted = 1 WHERE id = ? AND user_id = ?");
    $stmt2->bind_param("ss", $id, $userId);

    if ($stmt2->execute()) {
        echo json_encode(["status" => "success", "message" => "Data berhasil dihapus"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    $conn->close();

} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
