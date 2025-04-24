<?php 
ob_start();
session_start();
require_once '../database/koneksi.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir</title>
    <link rel="shortcut icon" href="../asset/image/logo.png" />
    <link rel="stylesheet" href="../asset/css/style_transaksi.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>
<body class="p-4 sm:p-8">
    <div class="container mx-auto max-w-5xl">
        <h1 class="text-4xl sm:text-5xl font-bold mb-8 text-center text-gray-800"><a href="../backend/logout.php">Oke Shoes Kasir</a></h1>
        
        <!-- Tambah Pesanan -->
        <div class="card p-6 sm:p-8 mb-8">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Tambah Pesanan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-gray-600 text-sm font-medium mb-2" for="nama-barang">Kode Barang</label>
                    <select class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="nama-barang">
                        <option value="">Pilih Kode Barang</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 text-sm font-medium mb-2" for="harga-barang">Harga per pcs</label>
                    <input class="w-full px-4 py-2 border rounded-lg text-gray-700 bg-gray-50" id="harga-barang" type="text" placeholder="Rp. ..." readonly>
                </div>
                <div>
                    <label class="block text-gray-600 text-sm font-medium mb-2" for="quantity-barang">Quantity</label>
                    <input class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="quantity-barang" type="number" min="1" placeholder="Masukkan jumlah">
                </div>
            </div>
            <div class="flex gap-4">
                <button class="btn-primary text-white font-semibold py-2 px-4 rounded-lg btn-icon" type="button" onclick="tambahPesanan()">
                    <i class="fas fa-plus"></i> Tambah Pesanan
                </button>
                <button class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg btn-icon no-print" type="button" onclick="showPreview()">
                    <i class="fas fa-eye"></i> Pratinjau Pesanan
                </button>
                <button class="bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-lg btn-icon no-print" type="button" onclick="resetForm()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </div>

        <!-- Daftar Pesanan -->
        <div class="card p-6 sm:p-8 mb-8">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Daftar Pesanan</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr class="text-left text-gray-600">
                            <th class="py-3 px-4 font-medium">Kode Barang</th>
                            <th class="py-3 px-4 font-medium">Nama Barang</th>
                            <th class="py-3 px-4 font-medium">Merk</th>
                            <th class="py-3 px-4 font-medium">Harga per pcs</th>
                            <th class="py-3 px-4 font-medium">Quantity</th>
                            <th class="py-3 px-4 font-medium">Total Harga</th>
                            <th class="py-3 px-4 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="daftar-pesanan"></tbody>
                </table>
            </div>
        </div>

        <!-- Total Pembelian -->
        <div class="card p-6 sm:p-8">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Total Pembelian</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-gray-600 text-sm font-medium mb-2" for="uang-dibayarkan">Uang Dibayarkan</label>
                    <input class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="uang-dibayarkan" type="number" min="0" placeholder="Masukkan jumlah uang">
                </div>
                <div>
                    <label class="block text-gray-600 text-sm font-medium mb-2" for="total-harga">Total Harga</label>
                    <input class="w-full px-4 py-2 border rounded-lg text-gray-700 bg-gray-50" id="total-harga" type="text" value="Rp 0" readonly>
                </div>
                <div>
                    <label class="block text-gray-600 text-sm font-medium mb-2" for="kembalian">Kembalian</label>
                    <input class="w-full px-4 py-2 border rounded-lg text-gray-700 bg-gray-50" id="kembalian" type="text" readonly>
                </div>
            </div>
            <div class="flex gap-4">
                <button class="btn-primary text-white font-semibold py-2 px-4 rounded-lg btn-icon" type="button" onclick="hitungKembalian()">
                    <i class="fas fa-calculator"></i> Hitung Kembalian
                </button>
                <button class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg btn-icon no-print" type="button" onclick="showPrintPreview()">
                    <i class="fas fa-print"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Preview Modal -->
    <div id="preview-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Pratinjau Pesanan</h2>
            <div id="preview-content" class="mb-4"></div>
            <div class="flex justify-end gap-4">
                <button class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg" onclick="closePreview()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Edit Pesanan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-gray-600 text-sm font-medium mb-2" for="edit-nama-barang">Kode Barang</label>
                    <select class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="edit-nama-barang">
                        <option value="">Pilih Kode Barang</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 text-sm font-medium mb-2" for="edit-harga-barang">Harga per pcs</label>
                    <input class="w-full px-4 py-2 border rounded-lg text-gray-700 bg-gray-50" id="edit-harga-barang" type="text" readonly>
                </div>
                <div>
                    <label class="block text-gray-600 text-sm font-medium mb-2" for="edit-quantity-barang">Quantity</label>
                    <input class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="edit-quantity-barang" type="number" min="1">
                </div>
            </div>
            <div class="flex justify-end gap-4">
                <button class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg" onclick="closeEditModal()">Batal</button>
                <button class="btn-primary text-white py-2 px-4 rounded-lg" onclick="updatePesanan()">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Konfirmasi Hapus</h2>
            <p class="mb-4">Apakah Anda yakin ingin menghapus pesanan ini?</p>
            <div class="flex justify-end gap-4">
                <button class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg" onclick="closeDeleteModal()">Batal</button>
                <button class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg" onclick="deletePesanan()">Hapus</button>
            </div>
        </div>
    </div>

    <!-- Print Modal -->
    <div id="print-modal" class="modal">
        <div class="modal-content receipt">
            <h2 class="text-xl font-bold mb-4 text-center">Oke Shoes Store</h2>
            <p class="text-sm mb-2 text-center">Alamat</p>
            <p class="text-sm mb-4 text-center" id="print-date"></p>
            <table class="min-w-full mb-4">
                <thead>
                    <tr class="text-left border-b">
                        <th class="py-2">Kode</th>
                        <th class="py-2">Barang</th>
                        <th class="py-2">Merk</th>
                        <th class="py-2">Qty</th>
                        <th class="py-2">Harga</th>
                        <th class="py-2">Total</th>
                    </tr>
                </thead>
                <tbody id="print-items"></tbody>
            </table>
            <div class="border-t pt-2">
                <p class="flex justify-between"><span>Total Harga:</span><span id="print-total"></span></p>
                <p class="flex justify-between"><span>Uang Dibayarkan:</span><span id="print-paid"></span></p>
                <p class="flex justify-between"><span>Kembalian:</span><span id="print-change"></span></p>
            </div>
            <div class="flex justify-end gap-4 mt-4 no-print">
                <button class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg" onclick="closePrintPreview()">Tutup</button>
                <button class="btn-primary text-white py-2 px-4 rounded-lg" onclick="printReceipt()">Cetak</button>
            </div>
        </div>
    </div>

    <script src="../asset/js/js_transaksi.js"></script>
    
</body>
</html>