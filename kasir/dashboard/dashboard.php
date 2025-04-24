<?php include '../backend/dashboard.php';?>

<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
    <title>Dashboard | Oke Shoes</title>
    <link rel="shortcut icon" href="../asset/image/logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: #f1f5f9;
        }
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background-color: rgba(100, 116, 139, 0.3);
            border-radius: 10px;
        }
        .modal {
            transition: opacity 0.3s ease, transform 0.3s ease;
            transform: scale(0.95);
        }
        .modal.show {
            transform: scale(1);
            opacity: 1;
        }
        .modal-content {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border-radius: 1.5rem;
            border: none;
            background: #ffffff;
        }
        .summary-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #ffffff;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .summary-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }
        .chart-container {
            background: #ffffff;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        html {
            font-size: 16px;
        }
        @media (max-width: 1024px) {
            html { font-size: 15px; }
        }
        @media (max-width: 768px) {
            html { font-size: 14px; }
            .summary-card {
                padding: 1rem;
            }
            .summary-card div.text-3xl {
                font-size: 1.75rem;
            }
            table {
                font-size: 0.875rem;
            }
            th, td {
                padding: 0.75rem;
            }
            .modal-content {
                width: 90%;
                padding: 1.5rem;
            }
        }
        @media (max-width: 640px) {
            html { font-size: 13px; }
            .summary-card div.text-3xl {
                font-size: 1.5rem;
            }
        }
        button, input, select {
            min-height: 48px;
            touch-action: manipulation;
            transition: all 0.3s ease;
        }
        table {
            width: 100%;
            table-layout: auto;
            border-collapse: separate;
            border-spacing: 0;
        }
        th, td {
            min-width: 100px;
        }
        @media (max-width: 768px) {
            th, td {
                min-width: 80px;
            }
        }
        .pagination button {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .input-error {
            border-color: #ef4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
        }
        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }       
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca, #6d28d9);
        }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Modal Overlay -->
        <div id="modal-overlay" class="modal fixed inset-0 bg-gray-900 bg-opacity-70 overflow-y-auto h-full w-full hidden opacity-0 z-50">
            <div class="modal-content relative top-20 mx-auto p-8 w-full max-w-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modal-title" class="text-xl font-semibold text-indigo-700"></h3>
                    <button id="modal-close" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="modal-content" class="mt-2 text-gray-600"></div>
                <div class="items-center mt-8 flex justify-end gap-3">
                <button id="modal-cancel" class="px-5 py-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400">Batal</button>

                    <button id="modal-confirm" class="px-5 py-2.5 btn-primary rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Konfirmasi</button>
                </div>
            </div>
        </div>

        <!-- Header -->
        <header class="bg-white shadow-sm p-6 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-indigo-700 flex items-center gap-2">
                    <i class="fas fa-shoe-prints"></i> Oke Store
                </h1>
                <div class="flex items-center gap-4">
                    <span id="current-date" class="text-gray-600 font-medium text-sm"></span>
                    <button id="logout-btn" class="btn-primary inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Ringkasan Utama -->
            <section aria-labelledby="summary-title">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                    <h2 class="text-2xl font-semibold text-indigo-700 flex items-center gap-2" id="summary-title">
                        <i class="fas fa-tachometer-alt"></i> Ringkasan Informasi
                    </h2>
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <select class="rounded-lg border border-gray-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-sm" id="period" name="period">
                            <option value="today">Hari ini</option>
                            <option value="week">Minggu ini</option>
                            <option selected value="month">Bulan ini</option>
                            <option value="custom">Custom</option>
                        </select>
                        <div id="custom-date-range" class="hidden flex gap-2">
                            <input type="date" id="start-date" class="rounded-lg border border-gray-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-sm">
                            <input type="date" id="end-date" class="rounded-lg border border-gray-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-sm">
                        </div>
                        <div class="flex items-center gap-3">
                            <button id="export-pdf" aria-label="Export to PDF" class="text-indigo-600 hover:text-indigo-800 p-2.5 rounded-lg hover:bg-indigo-50 transition" title="Export to PDF">
                                <i class="fas fa-file-pdf fa-lg"></i>
                            </button>
                            <button id="export-excel" aria-label="Export to Excel" class="text-green-600 hover:text-green-800 p-2.5 rounded-lg hover:bg-green-50 transition" title="Export to Excel">
                                <i class="fas fa-file-excel fa-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="summary-card flex flex-col items-center text-center">
                        <i class="fas fa-coins text-yellow-500 text-3xl mb-4"></i>
                        <div id="total-penjualan" class="text-indigo-700 text-3xl font-semibold"><?php echo 'Rp ' . number_format($summary_data['total_penjualan'], 0, ',', '.'); ?></div>
                        <div class="mt-3 text-gray-600 text-sm font-medium">Total Penjualan</div>
                    </div>
                    <div class="summary-card flex flex-col items-center text-center">
                        <i class="fas fa-box-open text-green-500 text-3xl mb-4"></i>
                        <div id="produk-terjual" class="text-indigo-700 text-3xl font-semibold"><?php echo $summary_data['produk_terjual']; ?></div>
                        <div class="mt-3 text-gray-600 text-sm font-medium">Produk Terjual</div>
                    </div>
                    <div class="summary-card flex flex-col items-center text-center">
                        <i class="fas fa-file-invoice-dollar text-blue-500 text-3xl mb-4"></i>
                        <div id="jumlah-transaksi" class="text-indigo-700 text-3xl font-semibold"><?php echo $summary_data['jumlah_transaksi']; ?></div>
                        <div class="mt-3 text-gray-600 text-sm font-medium">Jumlah Transaksi</div>
                    </div>
                    <div class="summary-card flex flex-col items-center text-center">
                        <i class="fas fa-chart-line text-pink-500 text-3xl mb-4"></i>
                        <div id="rata-transaksi" class="text-indigo-700 text-3xl font-semibold"><?php echo 'Rp ' . number_format($summary_data['rata_transaksi'], 0, ',', '.'); ?></div>
                        <div class="mt-3 text-gray-600 text-sm font-medium">Rata-rata Transaksi</div>
                    </div>
                </div>
            </section>

            <!-- Penjualan -->
            <section aria-labelledby="sales-title" class="chart-container">
                <h2 class="text-2xl font-semibold text-indigo-700 flex items-center gap-2 mb-6" id="sales-title">
                    <i class="fas fa-chart-bar"></i> Penjualan
                </h2>
                <div class="mb-10">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                        <h3 class="text-lg font-medium text-gray-700 flex items-center gap-2">
                            <i class="fas fa-chart-line text-indigo-600"></i> Grafik Penjualan
                        </h3>
                        <div class="flex flex-col sm:flex-row gap-4 items-center">
                            <select id="timeframe" aria-label="Pilih waktu grafik" class="rounded-lg border border-gray-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-sm">
                                <option value="daily">Harian</option>
                                <option selected value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                            </select>
                            <select id="category" aria-label="Filter kategori sepatu" class="rounded-lg border border-gray-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-sm">
                                <option selected value="all"></option>
                                <!-- <option value="olahraga">Olahraga</option>
                                <option value="formal">Formal</option>
                                <option value="boots">Boots</option>
                                <option value="casual">Casual</option> -->
                            </select>
                            <select id="brand" aria-label="Filter brand sepatu" class="rounded-lg border border-gray-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-sm">
                                <option selected value="all"></option>
                                <!-- <option value="nike">Nike</option>
                                <option value="adidas">Adidas</option>
                                <option value="puma">Puma</option>
                                <option value="reebok">Reebok</option> -->
                            </select>
                        </div>
                    </div>
                    <div>
                        <canvas id="sales-chart" height="120"></canvas>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-700 flex items-center gap-2 mb-6">
                        <i class="fas fa-chart-bar text-indigo-600"></i> Produk Terlaris
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="chart-container">
                            <canvas id="top-products-chart" height="120"></canvas>
                        </div>
                        <div class="chart-container">
                            <canvas id="category-chart" height="120"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stok & Inventori -->
            <section aria-labelledby="stock-title" class="bg-white rounded-xl shadow-sm p-8 overflow-x-auto scrollbar-thin">
                <div class="flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">
                    <h2 class="text-2xl font-semibold text-indigo-700 flex items-center gap-2" id="stock-title">
                        <i class="fas fa-boxes"></i> Inventori
                    </h2>
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="relative w-full sm:w-72">
                            <input id="stock-search" aria-label="Search inventory" class="w-full rounded-lg border border-gray-200 px-4 py-2.5 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-sm" placeholder="Cari " type="search"/>
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                        <button id="add-item" class="btn-primary inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <i class="fas fa-plus"></i> Tambah Data
                        </button>
                    </div>
                </div>
                <table id="stock-table" class="min-w-full text-sm rounded-lg overflow-hidden">
                    <thead class="bg-indigo-50">
                        <tr>
                            <th class="border border-gray-200 px-4 py-3 text-left font-medium text-indigo-700 whitespace-nowrap">Kode Barang</th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-medium text-indigo-700 whitespace-nowrap">Nama Barang</th>
                            <th class="border border-gray-200 px-4 py-3 text-right font-medium text-indigo-700 whitespace-nowrap">Harga per Pcs</th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-medium text-indigo-700 whitespace-nowrap">Merk</th>
                            <th class="border border-gray-200 px-4 py-3 text-center font-medium text-indigo-700 whitespace-nowrap">Stok</th>
                            <th class="border border-gray-200 px-4 py-3 text-center font-medium text-indigo-700 whitespace-nowrap">Status</th>
                            <th class="border border-gray-200 px-4 py-3 text-center font-medium text-indigo-700 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="stock-table-body"></tbody>
                </table>
                <div class="pagination flex justify-center gap-3 mt-6">
                    <button id="stock-prev" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 disabled:opacity-50" disabled>Sebelumnya</button>
                    <span id="stock-page" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">1</span>
                    <button id="stock-next" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Selanjutnya</button>
                </div>
            </section>

            <!-- Transaksi -->
            <section aria-labelledby="transaction-title" class="bg-white rounded-xl shadow-sm p-8 overflow-x-auto scrollbar-thin">
                <div class="flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">
                    <h2 class="text-2xl font-semibold text-indigo-700 flex items-center gap-2" id="transaction-title">
                        <i class="fas fa-file-invoice-dollar"></i> Transaksi
                    </h2>
                    <div class="relative w-full sm:w-72">
                        <input id="transaction-search" aria-label="Search transactions" class="w-full rounded-lg border border-gray-200 px-4 py-2.5 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-sm" placeholder="Cari" type="search"/>
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
                <table id="transaction-table" class="min-w-full text-sm rounded-lg overflow-hidden">
                    <thead class="bg-indigo-50">
                        <tr>
                            <th class="border border-gray-200 px-4 py-3 text-left font-medium text-indigo-700 whitespace-nowrap">ID Nota</th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-medium text-indigo-700 whitespace-nowrap">Tanggal</th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-medium text-indigo-700 whitespace-nowrap">Nama Barang</th>
                            <th class="border border-gray-200 px-4 py-3 text-center font-medium text-indigo-700 whitespace-nowrap">Quantity</th>
                            <th class="border border-gray-200 px-4 py-3 text-right font-medium text-indigo-700 whitespace-nowrap">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody id="transaction-table-body"></tbody>
                </table>
                <div class="pagination flex justify-center gap-3 mt-6">
                    <button id="transaction-prev" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 disabled:opacity-50" disabled>Sebelumnya</button>
                    <span id="transaction-page" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">1</span>
                    <button id="transaction-next" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Selanjutnya</button>
                </div>
            </section>
        </main>

        <footer class="bg-white border-t border-gray-200 py-4 text-center text-gray-500 text-sm">
            © <?= date('Y'); ?> Toko Sepatu | Oke Shoes. All rights reserved.
        </footer>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>


    <script>
    // Register chartjs-plugin-datalabels
    Chart.register(ChartDataLabels);

    // Utility functions
    const formatRupiah = (number) => `Rp ${Number(number).toLocaleString('id-ID')}`;

    // Data from PHP
    const summaryData = <?php echo json_encode($summary_data); ?>;
    const salesData = <?php echo json_encode($sales_data); ?>;
    const topProductsData = <?php echo json_encode($top_products_data); ?>;
    const categoryData = <?php echo json_encode($category_data); ?>;
    let stockData = <?php echo json_encode($stock_data); ?>;
    let transactionData = <?php echo json_encode($transaction_data); ?>;

    // Modal handling
    const modal = document.getElementById('modal-overlay');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');
    const modalCancel = document.getElementById('modal-cancel');
    const modalConfirm = document.getElementById('modal-confirm');
    const modalClose = document.getElementById('modal-close');

    function openModal(title, content, onConfirm = () => {}) {
        modalTitle.textContent = title;
        modalContent.innerHTML = content;
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.add('show'), 10);
        modalConfirm.onclick = () => {
            onConfirm();
            closeModal();
        };
        modalCancel.onclick = closeModal;
        modalClose.onclick = closeModal;
    }

    function closeModal() {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.classList.add('hidden');
            modalContent.innerHTML = '';
            modalConfirm.onclick = null;
        }, 300);
    }

    // Set current date
    const dateElement = document.getElementById('current-date');
    const today = new Date();
    dateElement.textContent = today.toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    // Load summary data
    async function loadSummary(period = 'month', startDate = null, endDate = null) {
        try {
            let url = `dashboard.php?action=get_summary_data&period=${period}`;
            if (period === 'custom' && startDate && endDate) {
                url += `&start_date=${startDate}&end_date=${endDate}`;
            }
            const response = await fetch(url);
            const result = await response.json();
            if (result.success) {
                const data = result.data;
                document.getElementById('total-penjualan').textContent = formatRupiah(data.total_penjualan);
                document.getElementById('produk-terjual').textContent = data.produk_terjual;
                document.getElementById('jumlah-transaksi').textContent = data.jumlah_transaksi;
                document.getElementById('rata-transaksi').textContent = formatRupiah(data.rata_transaksi);
            } else {
                openModal('Error', `<p>${result.message}</p>`);
            }
        } catch (error) {
            openModal('Error', `<p>Terjadi kesalahan: ${error.message}</p>`);
        }
    }

    // Inventory management
    let stockPage = 1;
    const stockRowsPerPage = 5;


    function loadStock() {
        renderStockTable();
    }

    function renderStockTable() {
        const tbody = document.getElementById('stock-table-body');
        tbody.innerHTML = '';
        const start = (stockPage - 1) * stockRowsPerPage;
        const end = start + stockRowsPerPage;
        const paginatedData = stockData.slice(start, end);

        paginatedData.forEach(item => {
            const statusClass = item.status === 'Habis' ? 'bg-gray-100 text-gray-700' : 
                              item.status === 'Stok Kritis' ? 'bg-red-100 text-red-700' : 
                              item.status === 'Stok Cukup' ? 'bg-yellow-100 text-yellow-700' : 
                              'bg-green-100 text-green-700';
            const statusIcon = item.status === 'Habis' ? 'fa-ban' : 
                              item.status === 'Stok Kritis' ? 'fa-times-circle' : 
                              item.status === 'Stok Cukup' ? 'fa-exclamation-triangle' : 
                              'fa-check-circle';
            tbody.innerHTML += `
                <tr class="hover:bg-indigo-50">
                    <td class="border border-gray-200 px-4 py-2 whitespace-nowrap">${item.kode_barang}</td>
                    <td class="border border-gray-200 px-4 py-2 whitespace-nowrap">${item.nama_barang}</td>
                    <td class="border border-gray-200 px-4 py-2 text-right whitespace-nowrap">${formatRupiah(item.harga_per_pcs)}</td>
                    <td class="border border-gray-200 px-4 py-2 whitespace-nowrap">${item.merk}</td>
                    <td class="border border-gray-200 px-4 py-2 text-center whitespace-nowrap">${item.stok}</td>
                    <td class="border border-gray-200 px-4 py-2 text-center whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full ${statusClass} font-medium text-xs">
                            <i class="fas ${statusIcon}"></i> ${item.status}
                        </span>
                    </td>
                    <td class="border border-gray-200 px-4 py-2 text-center whitespace-nowrap flex justify-center gap-2">
                        <button data-id="${item.id_barang}" class="edit-btn rounded bg-yellow-400 px-3 py-1 text-xs font-medium text-gray-800 hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button data-id="${item.id_barang}" class="delete-btn rounded bg-red-500 px-3 py-1 text-xs font-medium text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        document.getElementById('stock-page').textContent = stockPage;
        document.getElementById('stock-prev').disabled = stockPage === 1;
        document.getElementById('stock-next').disabled = end >= stockData.length;
    }

    document.getElementById('stock-prev').addEventListener('click', () => {
        if (stockPage > 1) {
            stockPage--;
            renderStockTable();
        }
    });

    document.getElementById('stock-next').addEventListener('click', () => {
        if ((stockPage * stockRowsPerPage) < stockData.length) {
            stockPage++;
            renderStockTable();
        }
    });

    // Transaction management
    let transactionPage = 1;
    const transactionRowsPerPage = 5;

    function loadTransactions() {
        renderTransactionTable();
    }

    function renderTransactionTable() {
        const tbody = document.getElementById('transaction-table-body');
        tbody.innerHTML = '';
        const start = (transactionPage - 1) * transactionRowsPerPage;
        const end = start + transactionRowsPerPage;
        const paginatedData = transactionData.slice(start, end);

        paginatedData.forEach(item => {
            const formattedDate = new Date(item.tanggal).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
            tbody.innerHTML += `
                <tr class="hover:bg-indigo-50">
                    <td class="border border-gray-200 px-4 py-2 whitespace-nowrap">${item.id_nota}</td>
                    <td class="border border-gray-200 px-4 py-2 whitespace-nowrap">${formattedDate}</td>    
                    <td class="border border-gray-200 px-4 py-2 whitespace-nowrap">${item.nama_barang}</td>
                    <td class="border border-gray-200 px-4 py-2 text-center whitespace-nowrap">${item.quantity}</td>
                    <td class="border border-gray-200 px-4 py-2 text-right whitespace-nowrap">${formatRupiah(item.total_harga)}</td>
                </tr>
            `;
        });

        document.getElementById('transaction-page').textContent = transactionPage;
        document.getElementById('transaction-prev').disabled = transactionPage === 1;
        document.getElementById('transaction-next').disabled = end >= transactionData.length;
    }

    document.getElementById('transaction-prev').addEventListener('click', () => {
        if (transactionPage > 1) {
            transactionPage--;
            renderTransactionTable();
        }
    });

    document.getElementById('transaction-next').addEventListener('click', () => {
        if ((transactionPage * transactionRowsPerPage) < transactionData.length) {
            transactionPage++;
            renderTransactionTable();
        }
    });

    // Search functionality
    function setupTableSearch(inputId, tableId, data, renderFn) {
        const originalData = [...data];
        document.getElementById(inputId).addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            let filteredData;
            if (searchTerm === '') {
                filteredData = [...originalData];
            } else {
                filteredData = originalData.filter(item =>
                    Object.values(item).some(val =>
                        val && val.toString().toLowerCase().includes(searchTerm)
                    )
                );
            }
            data.length = 0;
            data.push(...filteredData);
            renderFn();
        });
    }

    setupTableSearch('stock-search', 'stock-table', stockData, renderStockTable);
    setupTableSearch('transaction-search', 'transaction-table', transactionData, renderTransactionTable);

    // Add item
    document.getElementById('add-item').addEventListener('click', () => {
    openModal('Tambah Barang', `
        <form id="add-item-form">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Kode Barang</label>
                <input type="text" id="kode-barang" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3 uppercase" oninput="this.value = this.value.toUpperCase()" required>
                <p id="kode-barang-error" class="error-message hidden"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                <input type="text" id="nama-barang" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3" required>
                <p id="nama-barang-error" class="error-message hidden"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Harga</label>
                <input type="number" id="harga" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3" required min="0" step="0.01">
                <p id="harga-error" class="error-message hidden"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Merk</label>
                <input type="text" id="merk" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3" required>
                <p id="merk-error" class="error-message hidden"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Stok</label>
                <input type="number" id="stok" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3" required min="0">
                <p id="stok-error" class="error-message hidden"></p>
            </div>
        </form>
    `, async () => {
        const kode = document.getElementById('kode-barang');
        const nama = document.getElementById('nama-barang');
        const harga = document.getElementById('harga');
        const merk = document.getElementById('merk');
        const stok = document.getElementById('stok');

        let valid = true;
        const validate = (input, errorId, message) => {
            const error = document.getElementById(errorId);
            if (!input.value.trim() || (input.type === 'number' && input.value < 0)) {
                input.classList.add('input-error');
                error.textContent = message;
                error.classList.remove('hidden');
                valid = false;
            } else {
                input.classList.remove('input-error');
                error.classList.add('hidden');
            }
        };

        validate(kode, 'kode-barang-error', 'Kode barang wajib diisi');
        validate(nama, 'nama-barang-error', 'Nama barang wajib diisi');
        validate(harga, 'harga-error', 'Harga wajib diisi dan tidak boleh negatif');
        validate(merk, 'merk-error', 'Merk wajib diisi');
        validate(stok, 'stok-error', 'Stok wajib diisi dan tidak boleh negatif');

        if (!valid) return;

        try {
            const response = await fetch('dashboard.php?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    kode_barang: kode.value.trim(),
                    nama_barang: nama.value.trim(),
                    harga_per_pcs: parseFloat(harga.value),
                    merk: merk.value.trim(),
                    stok: parseInt(stok.value),
                })
            });

            const data = await response.json();
            if (data.success) {
                stockData.unshift(data.item);
                stockPage = 1;
                renderStockTable();
                closeModal();
                openModal('Sukses', '<p>Barang berhasil ditambahkan!</p>');
            } else {
                openModal('Error', `<p>${data.message}</p>`);
            }
        } catch (error) {
            openModal('Error', `<p>Terjadi kesalahan: ${error.message}</p>`);
        }
    });
});

    // Edit
    document.addEventListener('click', async (e) => {
    if (e.target.closest('.edit-btn')) {
        const id = e.target.closest('.edit-btn').dataset.id;
        const item = stockData.find(item => item.id_barang == id);
        openModal('Edit Barang', `
            <form id="edit-item-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Kode Barang</label>
                    <input type="text" id="kode-barang" value="${item.kode_barang}" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3 uppercase" oninput="this.value = this.value.toUpperCase()" required>
                    <p id="kode-barang-error" class="error-message hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                    <input type="text" id="nama-barang" value="${item.nama_barang}" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3" required>
                    <p id="nama-barang-error" class="error-message hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Harga</label>
                    <input type="number" id="harga" value="${item.harga_per_pcs}" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3" required min="0" step="0.01">
                    <p id="harga-error" class="error-message hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Merk</label>
                    <input type="text" id="merk" value="${item.merk}" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3" required>
                    <p id="merk-error" class="error-message hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Stok</label>
                    <input type="number" id="stok" value="${item.stok}" class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3" required min="0">
                    <p id="stok-error" class="error-message hidden"></p>
                </div>
            </form>
        `, async () => {
            const kode = document.getElementById('kode-barang');
            const nama = document.getElementById('nama-barang');
            const harga = document.getElementById('harga');
            const merk = document.getElementById('merk');
            const stok = document.getElementById('stok');

            let valid = true;
            const validate = (input, errorId, message) => {
                const error = document.getElementById(errorId);
                if (!input.value.trim() || (input.type === 'number' && input.value < 0)) {
                    input.classList.add('input-error');
                    error.textContent = message;
                    error.classList.remove('hidden');
                    valid = false;
                } else {
                    input.classList.remove('input-error');
                    error.classList.add('hidden');
                }
            };

            validate(kode, 'kode-barang-error', 'Kode barang wajib diisi');
            validate(nama, 'nama-barang-error', 'Nama barang wajib diisi');
            validate(harga, 'harga-error', 'Harga wajib diisi dan tidak boleh negatif');
            validate(merk, 'merk-error', 'Merk wajib diisi');
            validate(stok, 'stok-error', 'Stok wajib diisi dan tidak boleh negatif');

            if (!valid) return;

            try {
                const response = await fetch('dashboard.php?action=edit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_barang: id,
                        kode_barang: kode.value.trim(),
                        nama_barang: nama.value.trim(),
                        harga_per_pcs: parseFloat(harga.value),
                        merk: merk.value.trim(),
                        stok: parseInt(stok.value),
                    })
                });

                const data = await response.json();
                if (data.success) {
                    const index = stockData.findIndex(item => item.id_barang == id);
                    stockData[index] = data.item;
                    renderStockTable();
                    closeModal();
                    openModal('Sukses', '<p>Barang berhasil diupdate!</p>');
                } else {
                    openModal('Error', `<p>${data.message}</p>`);
                }
            } catch (error) {
                openModal('Error', `<p>Terjadi kesalahan: ${error.message}</p>`);
            }
        });
    }
});

// Delete
document.addEventListener('click', async (e) => {
    if (e.target.closest('.delete-btn')) {
        const id = e.target.closest('.delete-btn').dataset.id;
        openModal('Hapus Barang', `<p>Apakah Anda yakin ingin menghapus barang dengan ID ${id}?</p>`, async () => {
            try {
                const response = await fetch('dashboard.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_barang: id,
                    })
                });

                const data = await response.json();
                if (data.success) {
                    stockData = stockData.filter(item => item.id_barang != id);
                    renderStockTable();
                    closeModal();
                    openModal('Sukses', '<p>Barang berhasil dihapus!</p>');
                } else {
                    openModal('Error', `<p>${data.message}</p>`);
                }
            } catch (error) {
                openModal('Error', `<p>Terjadi kesalahan: ${error.message}</p>`);
            }
        });
    }
});

    // Logout
    document.getElementById('logout-btn').addEventListener('click', () => {
        openModal('Konfirmasi Logout', '<p>Apakah Anda yakin ingin logout dari sistem?</p>', () => {
            window.location.href = '../backend/logout.php';
        });
    });

    // Export functionality
    document.getElementById('export-pdf').addEventListener('click', () => {
    openModal('Export to PDF', '<p>Apakah Anda yakin ingin mengekspor data ke PDF?</p>', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Judul dan tanggal
        doc.setFont('helvetica');
        doc.setFontSize(16);
        doc.text('Oke Shoes Dashboard Report', 20, 20);
        doc.setFontSize(12);
        const today = new Date();
        doc.text(`Tanggal: ${today.toLocaleDateString('id-ID')}`, 20, 30);

        // Ringkasan
        doc.setFontSize(14);
        doc.text('Ringkasan Informasi', 20, 45);
        doc.setFontSize(11);
        doc.autoTable({
            startY: 50,
            theme: 'grid',
            head: [['Total Penjualan', 'Produk Terjual', 'Jumlah Transaksi', 'Rata-rata Transaksi']],
            body: [[
                formatRupiah(summaryData.total_penjualan),
                summaryData.produk_terjual,
                summaryData.jumlah_transaksi,
                formatRupiah(summaryData.rata_transaksi)
            ]]
        });

        // Data Inventori
        doc.setFontSize(14);
        doc.text('Inventori', 20, doc.lastAutoTable.finalY + 10);
        doc.autoTable({
            startY: doc.lastAutoTable.finalY + 15,
            theme: 'striped',
            head: [['Kode', 'Nama Barang', 'Harga/pcs', 'Merk', 'Stok', 'Status']],
            body: stockData.slice(0, 5).map(item => [
                item.kode_barang,
                item.nama_barang,
                formatRupiah(item.harga_per_pcs),
                item.merk,
                item.stok,
                item.status
            ])
        });

        // Export Data pdf
        doc.setFontSize(14);
        doc.text('Transaksi', 20, doc.lastAutoTable.finalY + 10);
        doc.autoTable({
            startY: doc.lastAutoTable.finalY + 15,
            theme: 'striped',
            head: [['ID Nota', 'Tanggal', 'Nama Barang', 'Qty', 'Total Harga']],
            body: transactionData.slice(0, 5).map(item => [
                item.id_nota,
                new Date(item.tanggal).toLocaleDateString('id-ID'),
                item.nama_barang,
                item.quantity,
                formatRupiah(item.total_harga)
            ])
        });

        doc.save('Laporan_Toko.pdf');
    });
});

    // Export Data pdf
    document.getElementById('export-excel').addEventListener('click', () => {
        openModal('Export to Excel', '<p>Apakah Anda yakin ingin mengekspor data ke Excel?</p>', () => {
            const stockSheet = XLSX.utils.json_to_sheet(stockData.map(item => ({
                Kode_Barang: item.kode_barang,
                Nama_Barang: item.nama_barang,
                Harga_per_Pcs: item.harga_per_pcs,
                Merk: item.merk,
                Stok: item.stok,
                Status: item.status
            })));
            const transactionSheet = XLSX.utils.json_to_sheet(transactionData.map(item => ({
                ID_Nota: item.id_nota,
                Tanggal: new Date(item.tanggal).toLocaleDateString('id-ID'),
                Nama_Barang: item.nama_barang,
                Quantity: item.quantity,
                Total_Harga: item.total_harga
            })));
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, stockSheet, 'Inventori');
            XLSX.utils.book_append_sheet(workbook, transactionSheet, 'Transaksi');
            XLSX.writeFile(workbook, 'Laporan.xlsx');
        });
    });

    // Chart setup
    function loadChartData() {
        const salesLabels = salesData.map(item => `Minggu ${item.minggu_ke.split('-')[1]}`);
        const salesValues = salesData.map(item => parseFloat(item.total_penjualan));

        salesChart.data.labels = salesLabels;
        salesChart.data.datasets[0].data = salesValues;
        salesChart.update();

        topProductsChart.data.labels = topProductsData.map(item => item.nama_barang);
        topProductsChart.data.datasets[0].data = topProductsData.map(item => item.stok);
        topProductsChart.update();

        categoryChart.data.labels = categoryData.map(item => item.merk);
        categoryChart.data.datasets[0].data = categoryData.map(item => item.jumlah_produk);
        categoryChart.update();
    }

    const salesChart = new Chart(document.getElementById('sales-chart'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Penjualan',
                data: [],
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { callback: value => formatRupiah(value) } 
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => `${context.dataset.label}: ${formatRupiah(context.raw)}`
                    }
                }
            }
        }
    });

    // Function to determine bar color based on stock value
    function getBarColor(stock) {
        if (stock <= 3) return '#3b82f6'; //blue
        if (stock >= 10) return '#ef4444'; //red
        return '#facc15'; // Gray for intermediate stock (3–9)
    }

    const topProductsChart = new Chart(document.getElementById('top-products-chart'), {
        type: 'bar',
        data: {
            labels: [], // Populated dynamically from stockData
            datasets: [{
                label: 'Stok',
                data: [], // Populated dynamically from stockData
                backgroundColor: [], // Will be set dynamically
                borderColor: [], // Optional: add border for better definition
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'STOK',
                        font: { size: 14, weight: 'bold' },
                        color: '#374151' // Tailwind gray-700
                    },
                    ticks: {
                        font: { size: 12 },
                        color: '#374151'
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937', // Tailwind gray-800
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                    padding: 10,
                    callbacks: {
                        label: context => `${context.label}: ${context.raw} unit`
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    font: {
                        size: 9,
                        weight: 'bold'
                    },
                    color: '#1f2937', // Tailwind gray-800 for contrast
                    formatter: (value) => value, // Display stock value
                    padding: 4
                }
            },
            layout: {
                padding: {
                    top: 20 // Extra space for data labels
                }
            },
            barPercentage: 0.8, // Adjust bar width
            categoryPercentage: 0.9 // Adjust spacing between bars
        },
        plugins: [ChartDataLabels] // Register datalabels plugin
    });

    // Function to update chart data
    function updateTopProductsChart() {
        const sortedData = stockData.slice().sort((a, b) => b.stok - a.stok).slice(0, 5); // Top 5 products by stock
        topProductsChart.data.labels = sortedData.map(item => item.nama_barang);
        topProductsChart.data.datasets[0].data = sortedData.map(item => item.stok);
        topProductsChart.data.datasets[0].backgroundColor = sortedData.map(item => getBarColor(item.stok));
        topProductsChart.data.datasets[0].borderColor = sortedData.map(item => getBarColor(item.stok));
        topProductsChart.update();
    }

// Call update function when stockData changes
document.addEventListener('DOMContentLoaded', () => {
    updateTopProductsChart();
    // ... (other initialization code)
});

    const categoryChart = new Chart(document.getElementById('category-chart'), {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: context => `${context.label}: ${context.raw} produk`
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 14 },
                    formatter: (value) => value,
                    anchor: 'center',
                    align: 'center'
                }
            }
        }
    });

    // Filter functionality
    async function updateChartByTimeframe() {
        const timeframe = document.getElementById('timeframe').value;
        const category = document.getElementById('category').value;
        const brand = document.getElementById('brand').value;

        try {
            const response = await fetch(`dashboard.php?action=get_sales_data&timeframe=${timeframe}&category=${category}&brand=${brand}`);
            const result = await response.json();
            if (!result.success) {
                openModal('Error', `<p>${result.message}</p>`);
                return;
            }

            const data = result.data;
            let labels = [];
            if (timeframe === 'daily') {
                labels = data.map(item => new Date(item.label).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }));
            } else if (timeframe === 'weekly') {
                labels = data.map(item => `Minggu ${item.label.split('-')[1]}`);
            } else if (timeframe === 'monthly') {
                labels = data.map(item => {
                    const [year, month] = item.label.split('-');
                    return new Date(year, month - 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                });
            }

            const values = data.map(item => parseFloat(item.total_penjualan));

            salesChart.data.labels = labels;
            salesChart.data.datasets[0].data = values;
            salesChart.update();
        } catch (error) {
            openModal('Error', `<p>Terjadi kesalahan: ${error.message}</p>`);
        }
    }

    document.getElementById('timeframe').addEventListener('change', updateChartByTimeframe);
    document.getElementById('category').addEventListener('change', updateChartByTimeframe);
    document.getElementById('brand').addEventListener('change', updateChartByTimeframe);

    document.getElementById('period').addEventListener('change', function() {
        const period = this.value;
        const customDateRange = document.getElementById('custom-date-range');
        if (period === 'custom') {
            customDateRange.classList.remove('hidden');
        } else {
            customDateRange.classList.add('hidden');
            loadSummary(period);
        }
    });

    document.getElementById('start-date').addEventListener('change', updateCustomPeriod);
    document.getElementById('end-date').addEventListener('change', updateCustomPeriod);

    function updateCustomPeriod() {
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;
        if (startDate && endDate && document.getElementById('period').value === 'custom') {
            loadSummary('custom', startDate, endDate);
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadSummary();
        loadStock();
        loadTransactions();
        loadChartData();
        updateChartByTimeframe();
    });
    </script>
</body>
</html>