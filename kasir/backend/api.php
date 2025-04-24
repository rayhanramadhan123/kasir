<?php
ob_start();
session_start();
require_once '../database/koneksi.php';


// Set header untuk API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Ganti dengan domain spesifik di produksi
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Debugging: Log request
error_log("Request received: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

// Fungsi untuk mendapatkan semua barang dengan stok > 0
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_barang') {
    try {
        $stmt = $conn->prepare("SELECT * FROM barang WHERE stok > 0");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $barang = [];
        while ($row = $result->fetch_assoc()) {
            $barang[] = $row;
        }
        
        if (empty($barang)) {
            error_log("Tidak ada barang dengan stok tersedia di database.");
            echo json_encode(['success' => false, 'message' => 'Tidak ada barang dengan stok tersedia di database']);
        } else {
            error_log("Data barang ditemukan: " . json_encode($barang));
            echo json_encode(['success' => true, 'data' => $barang]);
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error in get_barang: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Fungsi untuk menyimpan transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simpan_transaksi') {
    $orders = json_decode($_POST['orders'], true);
    $tanggal = date('Y-m-d');

    if (empty($orders)) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada pesanan untuk disimpan']);
        exit;
    }

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // Insert ke tabel nota
        $stmt = $conn->prepare("INSERT INTO nota (tanggal) VALUES (?)");
        $stmt->bind_param('s', $tanggal);
        $stmt->execute();
        $id_nota = $conn->insert_id;
        $stmt->close();

        // Insert setiap pesanan ke tabel transaksi
        $stmt = $conn->prepare("INSERT INTO transaksi (id_barang, id_nota, quantity, total_harga) VALUES (?, ?, ?, ?)");
        $stmt_update = $conn->prepare("UPDATE barang SET stok = stok - ? WHERE id_barang = ?");

        foreach ($orders as $order) {
            $id_barang = (int)$order['id_barang'];
            $quantity = (int)$order['quantityBarang'];
            $total_harga = (float)$order['totalHargaBarang'];

            // Cek apakah barang ada dan stok cukup
            $stmt_check = $conn->prepare("SELECT id_barang, stok FROM barang WHERE id_barang = ?");
            $stmt_check->bind_param('i', $id_barang);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Barang dengan ID $id_barang tidak ditemukan");
            }

            $barang = $result->fetch_assoc();
            if ($barang['stok'] < $quantity) {
                throw new Exception("Stok barang tidak cukup untuk barang dengan ID $id_barang");
            }
            $stmt_check->close();

            // Insert ke tabel transaksi
            $stmt->bind_param('iiid', $id_barang, $id_nota, $quantity, $total_harga);
            $stmt->execute();

            // Update stok barang
            $stmt_update->bind_param('ii', $quantity, $id_barang);
            $stmt_update->execute();
        }
        
        $stmt->close();
        $stmt_update->close();

        // Commit transaksi
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Transaksi berhasil disimpan']);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in simpan_transaksi: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$conn->close();
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>