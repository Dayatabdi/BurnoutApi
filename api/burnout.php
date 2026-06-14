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
    $stmt = $conn->prepare(
        "SELECT id, nama, jam_tidur, mudah_lelah, sulit_fokus, susah_tidur,
                mudah_marah, tidak_bersemangat, overwhelmed, stres_level,
                skor, tanggal, image_id,
                IF(user_id = ?, 1, 0) as mine
         FROM burnout_records
         ORDER BY tanggal DESC"
    );
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['mudah_lelah']        = (bool)$row['mudah_lelah'];
        $row['sulit_fokus']        = (bool)$row['sulit_fokus'];
        $row['susah_tidur']        = (bool)$row['susah_tidur'];
        $row['mudah_marah']        = (bool)$row['mudah_marah'];
        $row['tidak_bersemangat']  = (bool)$row['tidak_bersemangat'];
        $row['overwhelmed']        = (bool)$row['overwhelmed'];
        $row['mine']               = (int)$row['mine'];
        $row['jam_tidur']          = (float)$row['jam_tidur'];
        $row['skor']               = (int)$row['skor'];
        $data[] = $row;
    }

    echo json_encode($data);
    $conn->close();

} elseif ($method === 'POST') {
    $nama               = $_POST['nama'] ?? '';
    $jamTidur           = $_POST['jam_tidur'] ?? '';
    $mudahLelah         = isset($_POST['mudah_lelah']) ? (int)$_POST['mudah_lelah'] : 0;
    $sulitFokus         = isset($_POST['sulit_fokus']) ? (int)$_POST['sulit_fokus'] : 0;
    $susahTidur         = isset($_POST['susah_tidur']) ? (int)$_POST['susah_tidur'] : 0;
    $mudahMarah         = isset($_POST['mudah_marah']) ? (int)$_POST['mudah_marah'] : 0;
    $tidakBersemangat   = isset($_POST['tidak_bersemangat']) ? (int)$_POST['tidak_bersemangat'] : 0;
    $overwhelmed        = isset($_POST['overwhelmed']) ? (int)$_POST['overwhelmed'] : 0;
    $stresLevel         = $_POST['stres_level'] ?? '';
    $skor               = isset($_POST['skor']) ? (int)$_POST['skor'] : 0;

    if (empty($nama) || $jamTidur === '' || empty($stresLevel)) {
        echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
        exit;
    }

    // Handle upload gambar
    $imageId = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageId = uniqid('img_', true);
        $uploadDir = __DIR__ . '/images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $dest = $uploadDir . $imageId . '.jpg';
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            echo json_encode(["status" => "error", "message" => "Gagal upload gambar"]);
            exit;
        }
    }

    $id = uniqid('bo_', true);
    $conn = getConnection();
    $stmt = $conn->prepare(
        "INSERT INTO burnout_records
         (id, user_id, nama, jam_tidur, mudah_lelah, sulit_fokus, susah_tidur,
          mudah_marah, tidak_bersemangat, overwhelmed, stres_level, skor, image_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sssdiiiiiisis",
        $id, $userId, $nama, $jamTidur,
        $mudahLelah, $sulitFokus, $susahTidur,
        $mudahMarah, $tidakBersemangat, $overwhelmed,
        $stresLevel, $skor, $imageId
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Data berhasil disimpan"]);
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

    // Cek kepemilikan & ambil image_id
    $stmt = $conn->prepare("SELECT image_id FROM burnout_records WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ss", $id, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Data tidak ditemukan atau bukan milik kamu"]);
        $conn->close();
        exit;
    }

    $row = $result->fetch_assoc();
    $imageId = $row['image_id'];

    // Hapus record
    $stmt2 = $conn->prepare("DELETE FROM burnout_records WHERE id = ? AND user_id = ?");
    $stmt2->bind_param("ss", $id, $userId);

    if ($stmt2->execute()) {
        // Hapus file gambar kalau ada
        if ($imageId) {
            $imgPath = __DIR__ . '/images/' . $imageId . '.jpg';
            if (file_exists($imgPath)) unlink($imgPath);
        }
        echo json_encode(["status" => "success", "message" => "Data berhasil dihapus"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    $conn->close();

} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
