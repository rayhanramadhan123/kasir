<?php
ob_start();
session_start();
require_once '../database/koneksi.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/index.php");
    exit();
}


// Filter ringkasan info
try {
    $period = $_GET['period'] ?? 'month';
    $where_clause = '';
    switch ($period) {
        case 'today':
            $where_clause = "WHERE DATE(n.tanggal) = CURDATE()";
            break;
        case 'week':
            $where_clause = "WHERE YEARWEEK(n.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $where_clause = "WHERE MONTH(n.tanggal) = MONTH(CURDATE()) AND YEAR(n.tanggal) = YEAR(CURDATE())";
            break;
        case 'custom':
            $start_date = $_GET['start_date'] ?? null;
            $end_date = $_GET['end_date'] ?? null;
            if ($start_date && $end_date) {
                $start_date = $conn->real_escape_string($start_date);
                $end_date = $conn->real_escape_string($end_date);
                $where_clause = "WHERE n.tanggal BETWEEN '$start_date' AND '$end_date'";
            } else {
                $where_clause = "WHERE MONTH(n.tanggal) = MONTH(CURDATE()) AND YEAR(n.tanggal) = YEAR(CURDATE())";
            }
            break;
    }

    // Total Penjualan
    $sql_total_penjualan = "SELECT SUM(t.total_harga) AS total_penjualan
                            FROM transaksi t
                            JOIN nota n ON t.id_nota = n.id_nota
                            $where_clause";
    $result = $conn->query($sql_total_penjualan);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $total_penjualan = $result->fetch_assoc()['total_penjualan'] ?? 0;

    // Produk Terjual
    $sql_produk_terjual = "SELECT SUM(t.quantity) AS produk_terjual
                           FROM transaksi t
                           JOIN nota n ON t.id_nota = n.id_nota
                           $where_clause";
    $result = $conn->query($sql_produk_terjual);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $produk_terjual = $result->fetch_assoc()['produk_terjual'] ?? 0;

    // Jumlah Transaksi
    $sql_jumlah_transaksi = "SELECT COUNT(DISTINCT t.id_nota) AS jumlah_transaksi
                             FROM transaksi t
                             JOIN nota n ON t.id_nota = n.id_nota
                             $where_clause";
    $result = $conn->query($sql_jumlah_transaksi);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $jumlah_transaksi = $result->fetch_assoc()['jumlah_transaksi'] ?? 0;

    
    // Rata-rata Transaksi
    $rata_transaksi = $jumlah_transaksi > 0 ? $total_penjualan / $jumlah_transaksi : 0;

    // Summary data
    $summary_data = [
        'total_penjualan' => $total_penjualan,
        'produk_terjual' => $produk_terjual,
        'jumlah_transaksi' => $jumlah_transaksi,
        'rata_transaksi' => $rata_transaksi
    ];

    // Fetch data for Sales Chart (default: weekly)
    $sql_sales = "SELECT 
                     DATE_FORMAT(n.tanggal, '%Y-%u') AS minggu_ke,
                     SUM(t.total_harga) AS total_penjualan
                  FROM transaksi t
                  JOIN nota n ON t.id_nota = n.id_nota
                  GROUP BY minggu_ke
                  ORDER BY minggu_ke DESC
                  LIMIT 6";
    $result = $conn->query($sql_sales);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $sales_data = [];
    while ($row = $result->fetch_assoc()) {
        $sales_data[] = $row;
    }

    // Fetch data for Top Products Chart (Stok Sisa per Barang)
    $sql_top_products = "SELECT nama_barang, stok
                         FROM barang
                         ORDER BY stok ASC
                         LIMIT 6";
    $result = $conn->query($sql_top_products);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $top_products_data = [];
    while ($row = $result->fetch_assoc()) {
        $top_products_data[] = $row;
    }

    // Fetch data for Category Chart (Kategori Produk Berdasarkan Merk)
    $sql_category = "SELECT merk, COUNT(*) AS jumlah_produk
                     FROM barang
                     GROUP BY merk
                     ORDER BY jumlah_produk DESC";
    $result = $conn->query($sql_category);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $category_data = [];
    while ($row = $result->fetch_assoc()) {
        $category_data[] = $row;
    }

    // Fetch Stock Data
    $sql_stock = "SELECT 
                     id_barang,
                     kode_barang,
                     nama_barang,
                     harga_per_pcs,
                     merk,
                     stok,
                     CASE 
                         WHEN stok = 0 THEN 'Habis'
                         WHEN stok <= 3 THEN 'Stok Kritis'
                         WHEN stok <= 10 THEN 'Stok Cukup'
                         ELSE 'Aman'
                     END AS status
                  FROM barang
                  ORDER BY id_barang";
    $result = $conn->query($sql_stock);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $stock_data = [];
    while ($row = $result->fetch_assoc()) {
        $stock_data[] = $row;
    }

    // Fetch Transaction Data
    $sql_transactions = "SELECT 
                            n.id_nota,
                            b.nama_barang,
                            t.quantity,
                            t.total_harga,
                            n.tanggal
                         FROM transaksi t
                         JOIN barang b ON t.id_barang = b.id_barang
                         JOIN nota n ON t.id_nota = n.id_nota
                         ORDER BY n.id_nota DESC";
    $result = $conn->query($sql_transactions);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $transaction_data = [];
    while ($row = $result->fetch_assoc()) {
        $transaction_data[] = $row;
    }

} catch (Exception $e) {
    error_log("Database query failed: " . $e->getMessage(), 3, '/var/log/php_errors.log');
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit();
}

// Handle AJAX requests (Proses button add, edit, delete)
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    try {
        if ($_GET['action'] === 'add') {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate inputs
            if (empty($data['kode_barang']) || empty($data['nama_barang']) || 
                !is_numeric($data['harga_per_pcs']) || empty($data['merk']) || 
                !is_numeric($data['stok']) || $data['stok'] < 0 || $data['harga_per_pcs'] < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid input data']);
                exit();
            }

            // Sanitize inputs
            $kode_barang = $conn->real_escape_string($data['kode_barang']);
            $nama_barang = $conn->real_escape_string($data['nama_barang']);
            $harga_per_pcs = floatval($data['harga_per_pcs']);
            $merk = $conn->real_escape_string($data['merk']);
            $stok = intval($data['stok']);

            $sql = "INSERT INTO barang (kode_barang, nama_barang, harga_per_pcs, merk, stok) 
                    VALUES ('$kode_barang', '$nama_barang', $harga_per_pcs, '$merk', $stok)";
            if (!$conn->query($sql)) {
                throw new Exception("Insert failed: " . $conn->error);
            }

            $item = [
                'id_barang' => $conn->insert_id,
                'kode_barang' => $kode_barang,
                'nama_barang' => $nama_barang,
                'harga_per_pcs' => $harga_per_pcs,
                'merk' => $merk,
                'stok' => $stok,
                'status' => $stok == 0 ? 'Habis' : ($stok <= 3 ? 'Stok Kritis' : ($stok <= 10 ? 'Stok Cukup' : 'Aman'))
            ];

            echo json_encode(['success' => true, 'item' => $item]);
        } elseif ($_GET['action'] === 'edit') {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate inputs
            if (empty($data['id_barang']) || !is_numeric($data['id_barang']) || 
                empty($data['kode_barang']) || empty($data['nama_barang']) || 
                !is_numeric($data['harga_per_pcs']) || empty($data['merk']) || 
                !is_numeric($data['stok']) || $data['stok'] < 0 || $data['harga_per_pcs'] < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid input data']);
                exit();
            }

            // Sanitize inputs
            $id_barang = intval($data['id_barang']);
            $kode_barang = $conn->real_escape_string($data['kode_barang']);
            $nama_barang = $conn->real_escape_string($data['nama_barang']);
            $harga_per_pcs = floatval($data['harga_per_pcs']);
            $merk = $conn->real_escape_string($data['merk']);
            $stok = intval($data['stok']);

            $sql = "UPDATE barang 
                    SET kode_barang = '$kode_barang', nama_barang = '$nama_barang', 
                        harga_per_pcs = $harga_per_pcs, merk = '$merk', stok = $stok 
                    WHERE id_barang = $id_barang";
            if (!$conn->query($sql)) {
                throw new Exception("Update failed: " . $conn->error);
            }

            $item = [
                'id_barang' => $id_barang,
                'kode_barang' => $kode_barang,
                'nama_barang' => $nama_barang,
                'harga_per_pcs' => $harga_per_pcs,
                'merk' => $merk,
                'stok' => $stok,
                'status' => $stok == 0 ? 'Habis' : ($stok <= 3 ? 'Stok Kritis' : ($stok <= 10 ? 'Stok Cukup' : 'Aman'))
            ];

            echo json_encode(['success' => true, 'item' => $item]);
        } elseif ($_GET['action'] === 'delete') {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate input
            if (empty($data['id_barang']) || !is_numeric($data['id_barang'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
                exit();
            }

            $id_barang = intval($data['id_barang']);

            $sql = "DELETE FROM barang WHERE id_barang = $id_barang";
            if (!$conn->query($sql)) {
                throw new Exception("Delete failed: " . $conn->error);
            }

            echo json_encode(['success' => true]);
        } elseif ($_GET['action'] === 'get_sales_data') {
            $timeframe = $_GET['timeframe'] ?? 'weekly';
            $category = $_GET['category'] ?? 'all';
            $brand = $_GET['brand'] ?? 'all';

            $join_clause = "JOIN nota n ON t.id_nota = n.id_nota JOIN barang b ON t.id_barang = b.id_barang";
            $where_clause = '';

            if ($category !== 'all') {
                $category = $conn->real_escape_string($category);
                $where_clause .= " AND b.kategori = '$category'";
            }
            if ($brand !== 'all') {
                $brand = $conn->real_escape_string($brand);
                $where_clause .= " AND b.merk = '$brand'";
            }

            $sql = '';
            switch ($timeframe) {
                case 'daily':
                    $sql = "SELECT DATE(n.tanggal) AS label, SUM(t.total_harga) AS total_penjualan 
                            FROM transaksi t
                            $join_clause
                            WHERE 1=1 $where_clause
                            GROUP BY DATE(n.tanggal)
                            ORDER BY label DESC
                            LIMIT 14";
                    break;
                case 'weekly':
                    $sql = "SELECT CONCAT(YEAR(n.tanggal), '-', WEEK(n.tanggal)) AS label, SUM(t.total_harga) AS total_penjualan 
                            FROM transaksi t
                            $join_clause
                            WHERE 1=1 $where_clause
                            GROUP BY WEEK(n.tanggal), YEAR(n.tanggal)
                            ORDER BY label DESC
                            LIMIT 6";
                    break;
                case 'monthly':
                    $sql = "SELECT DATE_FORMAT(n.tanggal, '%Y-%m') AS label, SUM(t.total_harga) AS total_penjualan 
                            FROM transaksi t
                            $join_clause
                            WHERE 1=1 $where_clause
                            GROUP BY YEAR(n.tanggal), MONTH(n.tanggal)
                            ORDER BY label DESC
                            LIMIT 12";
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid timeframe']);
                    exit();
            }

            $result = $conn->query($sql);
           

 if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }

            $sales_data = [];
            while ($row = $result->fetch_assoc()) {
                $sales_data[] = $row;
            }

            echo json_encode(['success' => true, 'data' => $sales_data]);
            exit();
        } elseif ($_GET['action'] === 'get_summary_data') {
            $period = $_GET['period'] ?? 'month';
            $start_date = $_GET['start_date'] ?? null;
            $end_date = $_GET['end_date'] ?? null;

            $where_clause = '';
            switch ($period) {
                case 'today':
                    $where_clause = "WHERE DATE(n.tanggal) = CURDATE()";
                    break;
                case 'week':
                    $where_clause = "WHERE YEARWEEK(n.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 'month':
                    $where_clause = "WHERE MONTH(n.tanggal) = MONTH(CURDATE()) AND YEAR(n.tanggal) = YEAR(CURDATE())";
                    break;
                case 'custom':
                    if ($start_date && $end_date) {
                        $start_date = $conn->real_escape_string($start_date);
                        $end_date = $conn->real_escape_string($end_date);
                        $where_clause = "WHERE n.tanggal BETWEEN '$start_date' AND '$end_date'";
                    } else {
                        $where_clause = "WHERE MONTH(n.tanggal) = MONTH(CURDATE()) AND YEAR(n.tanggal) = YEAR(CURDATE())";
                    }
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid period']);
                    exit();
            }

            // Total Penjualan
            $sql_total_penjualan = "SELECT SUM(t.total_harga) AS total_penjualan
                                    FROM transaksi t
                                    JOIN nota n ON t.id_nota = n.id_nota
                                    $where_clause";
            $result = $conn->query($sql_total_penjualan);
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            $total_penjualan = $result->fetch_assoc()['total_penjualan'] ?? 0;

            // Produk Terjual
            $sql_produk_terjual = "SELECT SUM(t.quantity) AS produk_terjual
                                   FROM transaksi t
                                   JOIN nota n ON t.id_nota = n.id_nota
                                   $where_clause";
            $result = $conn->query($sql_produk_terjual);
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            $produk_terjual = $result->fetch_assoc()['produk_terjual'] ?? 0;

            // Jumlah Transaksi
            $sql_jumlah_transaksi = "SELECT COUNT(DISTINCT t.id_nota) AS jumlah_transaksi
                                     FROM transaksi t
                                     JOIN nota n ON t.id_nota = n.id_nota
                                     $where_clause";
            $result = $conn->query($sql_jumlah_transaksi);
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            $jumlah_transaksi = $result->fetch_assoc()['jumlah_transaksi'] ?? 0;

            // Rata-rata Transaksi
            $rata_transaksi = $jumlah_transaksi > 0 ? $total_penjualan / $jumlah_transaksi : 0;

            $summary_data = [
                'total_penjualan' => $total_penjualan,
                'produk_terjual' => $produk_terjual,
                'jumlah_transaksi' => $jumlah_transaksi,
                'rata_transaksi' => $rata_transaksi
            ];

            echo json_encode(['success' => true, 'data' => $summary_data]);
            exit();
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Database error in AJAX: " . $e->getMessage(), 3, '/var/log/php_errors.log');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit();
}
?>