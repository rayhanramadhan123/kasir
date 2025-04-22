<?php 
ob_start();
session_start();
require_once '../database/koneksi.php';

// Fungsi untuk mendapatkan semua barang
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_barang') {
    $stmt = $conn->prepare("SELECT * FROM barang");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $barang = [];
    while ($row = $result->fetch_assoc()) {
        $barang[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $barang]);
    $stmt->close();
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
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$conn->close();
?>