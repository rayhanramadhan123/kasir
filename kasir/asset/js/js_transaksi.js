let totalHarga = 0;
let orders = [];
let currentEditIndex = null;
let currentDeleteIndex = null;
let namaBarangChoices = null;
let editNamaBarangChoices = null;

const showToast = (message, isError = false) => {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.background = isError ? '#ef4444' : '#10b981';
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
};

const formatRupiah = (number) => {
    return `Rp ${number.toLocaleString('id-ID')}`;
};

const fetchWithErrorHandling = async (url, options) => {
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status} ${response.statusText}`);
        }
        return await response.json();
    } catch (error) {
        console.error(`Fetch error: ${error.message}`);
        throw error;
    }
};

const loadBarang = async () => {
    try {
        console.log("Mengambil data barang dari backend...");
        const result = await fetchWithErrorHandling('../backend/api.php?action=get_barang', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });

        console.log("Data barang diterima:", result);

        if (result.success) {
            const selectBarang = document.getElementById('nama-barang');
            selectBarang.innerHTML = '<option value=""></option>';

            if (result.data.length === 0) {
                showToast('Tidak ada barang dengan stok tersedia.', true);
                return;
            }

            // Initialize Choices.js for searchable dropdown
            const choicesOptions = result.data.map(barang => ({
                value: barang.id_barang,
                label: `${barang.kode_barang} (Stok: ${barang.stok})`,
                customProperties: {
                    harga: barang.harga_per_pcs,
                    nama: barang.nama_barang,
                    merk: barang.merk,
                    stok: barang.stok
                }
            }));

            if (namaBarangChoices) {
                namaBarangChoices.destroy();
            }

            namaBarangChoices = new Choices(selectBarang, {
                searchEnabled: true,
                searchChoices: true,
                itemSelectText: '',
                placeholderValue: 'Pilih Barang',
                searchPlaceholderValue: 'Cari Kode Barang',
                choices: choicesOptions
            });

            selectBarang.addEventListener('change', function () {
                const selectedItem = namaBarangChoices.getValue();
                const harga = selectedItem ? selectedItem.customProperties.harga : 0;
                document.getElementById('harga-barang').value = formatRupiah(parseFloat(harga));
            });
        } else {
            console.error("Gagal memuat data barang:", result.message);
            showToast(`Gagal memuat data barang: ${result.message}`, true);
        }
    } catch (error) {
        console.error("Terjadi kesalahan saat memuat barang:", error);
        showToast(`Terjadi kesalahan saat memuat barang: ${error.message}`, true);
    }
};

const loadBarangEdit = async () => {
    try {
        console.log("Mengambil data barang untuk edit modal...");
        const result = await fetchWithErrorHandling('../backend/api.php?action=get_barang', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });

        console.log("Data barang untuk edit diterima:", result);

        if (result.success) {
            const selectBarang = document.getElementById('edit-nama-barang');
            selectBarang.innerHTML = '<option value="">Pilih Kode Barang</option>';

            if (result.data.length === 0) {
                showToast('Tidak ada barang dengan stok tersedia untuk edit.', true);
                return;
            }

            // Initialize Choices.js for searchable dropdown in edit modal
            const choicesOptions = result.data.map(barang => ({
                value: barang.id_barang,
                label: `${barang.kode_barang} (Stok: ${barang.stok})`,
                customProperties: {
                    harga: barang.harga_per_pcs,
                    nama: barang.nama_barang,
                    merk: barang.merk,
                    stok: barang.stok
                }
            }));

            if (editNamaBarangChoices) {
                editNamaBarangChoices.destroy();
            }

            editNamaBarangChoices = new Choices(selectBarang, {
                searchEnabled: true,
                searchChoices: true,
                itemSelectText: '',
                placeholderValue: 'Pilih Barang',
                searchPlaceholderValue: 'Cari Kode Barang',
                choices: choicesOptions
            });

            selectBarang.addEventListener('change', function () {
                const selectedItem = editNamaBarangChoices.getValue();
                const harga = selectedItem ? selectedItem.customProperties.harga : 0;
                document.getElementById('edit-harga-barang').value = formatRupiah(parseFloat(harga));
            });
        } else {
            console.error("Gagal memuat data barang untuk edit:", result.message);
            showToast(`Gagal memuat data barang untuk edit: ${result.message}`, true);
        }
    } catch (error) {
        console.error("Terjadi kesalahan saat memuat barang untuk edit:", error);
        showToast(`Terjadi kesalahan saat memuat barang untuk edit: ${error.message}`, true);
    }
};

document.addEventListener('DOMContentLoaded', loadBarang);

const tambahPesanan = async () => {
    const selectedItem = namaBarangChoices.getValue();
    if (!selectedItem) {
        showToast('Harap pilih barang!', true);
        return;
    }

    const idBarang = selectedItem.value;
    const kodeBarang = selectedItem.label.split(' (')[0];
    const namaBarang = selectedItem.customProperties.nama || '';
    const merk = selectedItem.customProperties.merk || '';
    const hargaBarang = parseFloat(selectedItem.customProperties.harga) || 0;
    const stokBarang = parseInt(selectedItem.customProperties.stok) || 0;
    const quantityBarang = parseInt(document.getElementById('quantity-barang').value) || 0;

    if (isNaN(quantityBarang) || quantityBarang < 1) {
        showToast('Masukkan jumlah barang yang valid!', true);
        return;
    }

    if (quantityBarang > stokBarang) {
        showToast(`Stok barang (${kodeBarang}) tidak cukup! Stok tersedia: ${stokBarang}`, true);
        return;
    }

    const totalHargaBarang = hargaBarang * quantityBarang;
    totalHarga += totalHargaBarang;

    const order = { id_barang: idBarang, kodeBarang, namaBarang, merk, hargaBarang, quantityBarang, totalHargaBarang };
    orders.push(order);

    renderTable();
    resetInput();
    showToast('Pesanan berhasil ditambahkan!');
};

const renderTable = () => {
    const daftarPesanan = document.getElementById('daftar-pesanan');
    daftarPesanan.innerHTML = '';

    orders.forEach((order, index) => {
        const row = document.createElement('tr');
        row.dataset.index = index;
        row.innerHTML = `
            <td class="py-3 px-4">${order.kodeBarang}</td>
            <td class="py-3 px-4">${order.namaBarang}</td>
            <td class="py-3 px-4">${order.merk}</td>
            <td class="py-3 px-4">${formatRupiah(order.hargaBarang)}</td>
            <td class="py-3 px-4">${order.quantityBarang}</td>
            <td class="py-3 px-4">${formatRupiah(order.totalHargaBarang)}</td>
            <td class="py-3 px-4 flex gap-2">
                <button class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded" onclick="openEditModal(${index})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded" onclick="openDeleteModal(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        daftarPesanan.appendChild(row);
    });

    document.getElementById('total-harga').value = formatRupiah(totalHarga);
};

const resetInput = () => {
    namaBarangChoices.setChoiceByValue('');
    document.getElementById('harga-barang').value = '';
    document.getElementById('quantity-barang').value = '';
};

const resetForm = () => {
    orders = [];
    totalHarga = 0;
    renderTable();
    resetInput();
    document.getElementById('uang-dibayarkan').value = '';
    document.getElementById('kembalian').value = '';
    showToast('Form telah direset!');
};

const openEditModal = async (index) => {
    currentEditIndex = index;
    const order = orders[index];

    await loadBarangEdit();
    editNamaBarangChoices.setChoiceByValue(order.id_barang);
    document.getElementById('edit-harga-barang').value = formatRupiah(order.hargaBarang);
    document.getElementById('edit-quantity-barang').value = order.quantityBarang;

    document.getElementById('edit-modal').style.display = 'flex';
};

const closeEditModal = () => {
    document.getElementById('edit-modal').style.display = 'none';
    currentEditIndex = null;
};

const updatePesanan = async () => {
    if (currentEditIndex === null) return;

    const selectedItem = editNamaBarangChoices.getValue();
    if (!selectedItem) {
        showToast('Harap pilih barang!', true);
        return;
    }

    const idBarang = selectedItem.value;
    const kodeBarang = selectedItem.label.split(' (')[0];
    const namaBarang = selectedItem.customProperties.nama || '';
    const merk = selectedItem.customProperties.merk || '';
    const hargaBarang = parseFloat(selectedItem.customProperties.harga) || 0;
    const stokBarang = parseInt(selectedItem.customProperties.stok) || 0;
    const quantityBarang = parseInt(document.getElementById('edit-quantity-barang').value) || 0;

    if (isNaN(quantityBarang) || quantityBarang < 1) {
        showToast('Masukkan jumlah barang yang valid!', true);
        return;
    }

    if (quantityBarang > stokBarang) {
        showToast(`Stok barang (${kodeBarang}) tidak cukup! Stok tersedia: ${stokBarang}`, true);
        return;
    }

    const oldOrder = orders[currentEditIndex];
    totalHarga -= oldOrder.totalHargaBarang;

    const totalHargaBarang = hargaBarang * quantityBarang;
    totalHarga += totalHargaBarang;

    orders[currentEditIndex] = {
        id_barang: idBarang,
        kodeBarang,
        namaBarang,
        merk,
        hargaBarang,
        quantityBarang,
        totalHargaBarang
    };

    renderTable();
    closeEditModal();
    showToast('Pesanan berhasil diperbarui!');
};

const openDeleteModal = (index) => {
    currentDeleteIndex = index;
    document.getElementById('delete-modal').style.display = 'flex';
};

const closeDeleteModal = () => {
    document.getElementById('delete-modal').style.display = 'none';
    currentDeleteIndex = null;
};

const deletePesanan = () => {
    if (currentDeleteIndex === null) return;

    totalHarga -= orders[currentDeleteIndex].totalHargaBarang;
    orders.splice(currentDeleteIndex, 1);
    renderTable();
    closeDeleteModal();
    showToast('Pesanan berhasil dihapus!');
};

const showPreview = () => {
    if (orders.length === 0) {
        showToast('Belum ada pesanan untuk ditampilkan!', true);
        return;
    }

    const previewContent = document.getElementById('preview-content');
    let html = `
        <table class="min-w-full">
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
            <tbody>
    `;
    orders.forEach(order => {
        html += `
            <tr>
                <td class="py-2">${order.kodeBarang}</td>
                <td class="py-2">${order.namaBarang}</td>
                <td class="py-2">${order.merk}</td>
                <td class="py-2">${order.quantityBarang}</td>
                <td class="py-2">${formatRupiah(order.hargaBarang)}</td>
                <td class="py-2">${formatRupiah(order.totalHargaBarang)}</td>
            </tr>
        `;
    });
    html += `
            </tbody>
        </table>
        <p class="mt-4 font-semibold">Total: ${formatRupiah(totalHarga)}</p>
    `;
    previewContent.innerHTML = html;

    document.getElementById('preview-modal').style.display = 'flex';
};

const closePreview = () => {
    document.getElementById('preview-modal').style.display = 'none';
};

const showPrintPreview = async () => {
    if (orders.length === 0) {
        showToast('Belum ada pesanan untuk dicetak!', true);
        return;
    }

    const uangDibayarkan = parseInt(document.getElementById('uang-dibayarkan').value) || 0;
    const kembalianText = document.getElementById('kembalian').value;
    const kembalian = kembalianText ? parseInt(kembalianText.replace('Rp ', '').replace(/\./g, '')) : 0;

    if (kembalian < 0) {
        showToast('Uang yang dibayarkan kurang! Silakan hitung kembalian terlebih dahulu.', true);
        return;
    }

    try {
        console.log("Mengirim data transaksi ke backend...");
        const formData = new FormData();
        formData.append('action', 'simpan_transaksi');
        formData.append('orders', JSON.stringify(orders));

        const result = await fetchWithErrorHandling('../backend/api.php', {
            method: 'POST',
            body: formData
        });

        console.log("Response dari backend:", result);

        if (result.success) {
            const printItems = document.getElementById('print-items');
            let html = '';
            orders.forEach(order => {
                html += `
                    <tr>
                        <td class="py-1">${order.kodeBarang}</td>
                        <td class="py-1">${order.namaBarang}</td>
                        <td class="py-1">${order.merk}</td>
                        <td class="py-1">${order.quantityBarang}</td>
                        <td class="py-1">${formatRupiah(order.hargaBarang)}</td>
                        <td class="py-1">${formatRupiah(order.totalHargaBarang)}</td>
                    </tr>
                `;
            });
            printItems.innerHTML = html;

            document.getElementById('print-total').textContent = formatRupiah(totalHarga);
            document.getElementById('print-paid').textContent = formatRupiah(uangDibayarkan);
            document.getElementById('print-change').textContent = formatRupiah(kembalian);
            document.getElementById('print-date').textContent = new Date().toLocaleString('id-ID');

            document.getElementById('print-modal').style.display = 'flex';

            resetForm();
            showToast('Transaksi berhasil disimpan dan siap dicetak!');
        } else {
            showToast(result.message, true);
        }
    } catch (error) {
        console.error("Terjadi kesalahan saat menyimpan transaksi:", error);
        showToast(`Terjadi kesalahan saat menyimpan transaksi: ${error.message}`, true);
    }
};

const closePrintPreview = () => {
    document.getElementById('print-modal').style.display = 'none';
};

const printReceipt = () => {
    window.print();
};

const hitungKembalian = () => {
    const uangDibayarkan = parseInt(document.getElementById('uang-dibayarkan').value) || 0;
    if (isNaN(uangDibayarkan) || uangDibayarkan < 0) {
        showToast('Masukkan jumlah uang yang valid!', true);
        return;
    }
    const kembalian = uangDibayarkan - totalHarga;
    document.getElementById('kembalian').value = formatRupiah(kembalian);
    if (kembalian < 0) {
        showToast('Uang yang dibayarkan kurang!', true);
    }
};