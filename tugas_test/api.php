<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Konfigurasi koneksi database
$host = 'localhost'; // Ganti sesuai konfigurasi
$dbname = 'user_management'; // Nama database
$username = 'root'; // Username MySQL
$password = ''; // Password MySQL

try {
    // Membuat koneksi database dengan PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    sendErrorResponse("Database connection failed: " . $e->getMessage(), 500);
}

// Mendapatkan parameter 'action' dari URL
$action = $_GET['action'] ?? '';

try {
    // Switch berdasarkan nilai 'action'
    switch ($action) {
        case 'getAll':
            handleGetAll();
            break;

        case 'addUser':
            handleAddUser();
            break;

        case 'deleteUser':
            handleDeleteUser();
            break;

        case 'updateUser':
            handleUpdateUser();
            break;

        default:
            sendErrorResponse("Invalid action. Allowed actions: getAll, addUser, deleteUser, updateUser", 400);
            break;
    }
} catch (Exception $e) {
    sendErrorResponse($e->getMessage(), 500);
}

// Fungsi untuk mengambil semua data user
function handleGetAll() {
    global $pdo;

    $search = $_GET['search'] ?? ''; // Parameter pencarian opsional

    try {
        if (!empty($search)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE name LIKE :search OR email LIKE :search");
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM users"); // Ambil semua data jika tidak ada pencarian
        }

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "message" => "Data retrieved successfully",
            "data" => $users // Data pengguna dalam array
        ]);
    } catch (Exception $e) {
        sendErrorResponse("Failed to fetch data: " . $e->getMessage(), 500);
    }
}

// Fungsi untuk menambahkan user
function handleAddUser() {
    global $pdo;

    $postData = getPostData(['name', 'email', 'gender', 'departement', 'image']);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, gender, departement, image) VALUES (:name, :email, :gender, :departement, :image)");
        $stmt->execute($postData);

        $newUserId = $pdo->lastInsertId(); // Ambil ID pengguna baru
        $newUserStmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $newUserStmt->execute(['id' => $newUserId]);
        $newUser = $newUserStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "message" => "User added successfully",
            "data" => $newUser // Kembalikan data pengguna baru
        ]);
    } catch (Exception $e) {
        sendErrorResponse("Failed to add user: " . $e->getMessage(), 500);
    }
}

// Fungsi untuk menghapus user
function handleDeleteUser() {
    global $pdo;

    $id = $_GET['id'] ?? '';

    if (empty($id)) {
        sendErrorResponse("ID is required to delete a user.", 400);
        return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);

        echo json_encode(["message" => "User deleted successfully"]);
    } catch (Exception $e) {
        sendErrorResponse("Failed to delete user: " . $e->getMessage(), 500);
    }
}

// Fungsi untuk memperbarui user
function handleUpdateUser() {
    global $pdo;

    $postData = getPostData(['id', 'name', 'email', 'gender', 'departement', 'image']);
    $id = $postData['id'];
    unset($postData['id']);

    try {
        $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, gender = :gender, departement = :departement, image = :image WHERE id = :id");
        $stmt->execute(array_merge($postData, ['id' => $id]));

        echo json_encode(["message" => "User updated successfully"]);
    } catch (Exception $e) {
        sendErrorResponse("Failed to update user: " . $e->getMessage(), 500);
    }
}

// Fungsi untuk mendapatkan data POST
function getPostData($requiredFields) {
    $postData = [];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            sendErrorResponse("$field is required.", 400);
            exit;
        }
        $postData[$field] = $_POST[$field];
    }

    return $postData;
}

// Fungsi untuk mengirimkan respons error
function sendErrorResponse($message, $statusCode) {
    http_response_code($statusCode);
    echo json_encode(["error" => $message]);
    exit;
}
