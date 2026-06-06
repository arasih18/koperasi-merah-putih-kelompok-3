<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Kasir')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

// Generate Kode Pembelian
$today = date('Ymd');
$q_kode = $conn->query("SELECT MAX(kode_pembelian) as max_kode FROM pembelian WHERE kode_pembelian LIKE 'PB$today%'");
$row = $q_kode->fetch_assoc();
$urutan = ($row['max_kode']) ? (int) substr($row['max_kode'], -4) + 1 : 1;
$kode_pembelian = 'PB' . $today . sprintf("%04s", $urutan);

include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-cart-plus me-2"></i> Tambah Transaksi Pembelian (Restock)</h1>
        <a href="pembelian.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>
</div>

<section class="content">
    <form action="pembelian_proses.php" method="POST" id="formPembelian">
        <div class="row">
            <!-- Informasi Utama -->
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-crimson text-white">
                        <h5 class="card-title mb-0">Info Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kode Pembelian</label>
                            <input type="text" class="form-control" name="kode_pembelian"
                                value="<?php echo $kode_pembelian; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Pembelian <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal" value="<?php echo date('Y-m-d'); ?>"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_supplier" required>
                                <option value="">-- Pilih Supplier --</option>
                                <?php
                                $q_sup = $conn->query("SELECT * FROM supplier ORDER BY nama_supplier ASC");
                                while ($sup = $q_sup->fetch_assoc()) {
                                    echo "<option value='{$sup['id_supplier']}'>" . htmlspecialchars($sup['nama_supplier']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body text-center bg-light">
                        <h5 class="text-muted text-uppercase mb-2">Total Pembayaran</h5>
                        <h2 class="mb-0 fw-bold text-danger" id="displayTotal">Rp 0</h2>
                        <input type="hidden" name="total_pembelian" id="inputTotal" value="0">
                        <button type="submit" class="btn btn-crimson btn-lg w-100 mt-4" id="btnSimpan" disabled><i
                                class="fas fa-save me-1"></i> Simpan Transaksi</button>
                    </div>
                </div>
            </div>

            <!-- Detail Barang -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Barang</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-info text-white me-2" data-bs-toggle="modal"
                                data-bs-target="#modalBarangBaru"><i class="fas fa-box-open me-1"></i> Barang
                                Baru</button>
                            <button type="button" class="btn btn-sm btn-light" id="btnTambahBarang"><i
                                    class="fas fa-plus me-1"></i> Tambah Item</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0" id="tableBarang">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="35%">Barang</th>
                                        <th width="25%">Harga Satuan (Rp)</th>
                                        <th width="15%">Qty</th>
                                        <th width="20%">Subtotal (Rp)</th>
                                        <th width="5%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Baris barang akan ditambahkan via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<!-- Modal Tambah Barang Baru -->
<div class="modal fade" id="modalBarangBaru" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-box-open me-2"></i>Tambah Barang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formBarangBaru">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="kode_barang" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_barang" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_kategori" id="selectKategori" required>
                            <option value="">Pilih Kategori...</option>
                            <?php
                            $q_kat = $conn->query("SELECT * FROM kategori_barang ORDER BY nama_kategori ASC");
                            while ($kat = $q_kat->fetch_assoc()) {
                                echo "<option value='{$kat['id_kategori']}'>" . htmlspecialchars($kat['nama_kategori']) . "</option>";
                            }
                            ?>
                            <option value="new" class="text-primary fw-bold">+ Tambah Kategori Baru</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="divKategoriBaru">
                        <label class="form-label fw-bold text-primary">Nama Kategori Baru <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control border-primary" name="nama_kategori_baru"
                            id="inputKategoriBaru" placeholder="Masukkan nama kategori baru">
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-bold">Harga Beli <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="harga_beli" min="0" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">Harga Jual <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="harga_jual" min="0" required>
                        </div>
                    </div>
                    <div class="alert alert-danger d-none" id="alertModal"></div>
                    <div class="d-grid text-end">
                        <button type="submit" class="btn btn-info text-white" id="btnSimpanBarangBaru">
                            <i class="fas fa-save me-1"></i> Simpan Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template Row (Hidden) -->
<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <select class="form-select select-barang" name="id_barang[]" required>
                <option value="">Pilih Barang...</option>
                <?php
                $q_brg = $conn->query("SELECT id_barang, nama_barang, harga_beli FROM barang ORDER BY nama_barang ASC");
                $data_barang = [];
                while ($brg = $q_brg->fetch_assoc()) {
                    $data_barang[$brg['id_barang']] = $brg['harga_beli'];
                    echo "<option value='{$brg['id_barang']}' data-harga='{$brg['harga_beli']}'>" . htmlspecialchars($brg['nama_barang']) . "</option>";
                }
                ?>
            </select>
        </td>
        <td>
            <input type="number" class="form-control input-harga" name="harga[]" min="0" required placeholder="0">
        </td>
        <td>
            <input type="number" class="form-control input-qty" name="qty[]" min="1" value="1" required>
        </td>
        <td>
            <input type="text" class="form-control input-subtotal text-end fw-bold" readonly value="0">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-hapus"><i class="fas fa-times"></i></button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.querySelector('#tableBarang tbody');
        const rowTemplate = document.getElementById('rowTemplate');
        const btnTambah = document.getElementById('btnTambahBarang');
        const displayTotal = document.getElementById('displayTotal');
        const inputTotal = document.getElementById('inputTotal');
        const btnSimpan = document.getElementById('btnSimpan');

        // Format number to Rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        // Hitung total keseluruhan
        function hitungTotal() {
            let total = 0;
            const rows = document.querySelectorAll('.item-row');

            rows.forEach(row => {
                const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
                const qty = parseInt(row.querySelector('.input-qty').value) || 0;
                const subtotal = harga * qty;

                row.querySelector('.input-subtotal').value = formatRupiah(subtotal);
                total += subtotal;
            });

            displayTotal.innerText = 'Rp ' + formatRupiah(total);
            inputTotal.value = total;

            // Disable simpan jika tidak ada barang atau total 0
            btnSimpan.disabled = (rows.length === 0 || total === 0);
        }

        // Tambah baris baru
        btnTambah.addEventListener('click', function () {
            const clone = rowTemplate.content.cloneNode(true);
            tableBody.appendChild(clone);
            hitungTotal();
        });

        // Delegasi event untuk tabel (karena dinamis)
        tableBody.addEventListener('change', function (e) {
            if (e.target.classList.contains('select-barang')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const hargaBeli = selectedOption.getAttribute('data-harga');
                const row = e.target.closest('tr');
                row.querySelector('.input-harga').value = hargaBeli || 0;
                hitungTotal();
            }
        });

        tableBody.addEventListener('input', function (e) {
            if (e.target.classList.contains('input-harga') || e.target.classList.contains('input-qty')) {
                hitungTotal();
            }
        });

        tableBody.addEventListener('click', function (e) {
            if (e.target.closest('.btn-hapus')) {
                e.target.closest('tr').remove();
                hitungTotal();
            }
        });

        // Tambah 1 baris kosong di awal
        btnTambah.click();

        // Toggle input kategori baru
        const selectKategori = document.getElementById('selectKategori');
        const divKategoriBaru = document.getElementById('divKategoriBaru');
        const inputKategoriBaru = document.getElementById('inputKategoriBaru');

        selectKategori.addEventListener('change', function () {
            if (this.value === 'new') {
                divKategoriBaru.classList.remove('d-none');
                inputKategoriBaru.required = true;
                inputKategoriBaru.focus();
            } else {
                divKategoriBaru.classList.add('d-none');
                inputKategoriBaru.required = false;
            }
        });

        // AJAX Tambah Barang Baru
        const formBarangBaru = document.getElementById('formBarangBaru');
        const alertModal = document.getElementById('alertModal');
        const btnSimpanBarangBaru = document.getElementById('btnSimpanBarangBaru');

        formBarangBaru.addEventListener('submit', function (e) {
            e.preventDefault();
            btnSimpanBarangBaru.disabled = true;
            btnSimpanBarangBaru.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

            const formData = new FormData(this);

            fetch('api_tambah_barang.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Tambahkan option baru ke template select
                        const optionHtml = `<option value='${data.data.id_barang}' data-harga='${data.data.harga_beli}'>${data.data.nama_barang}</option>`;

                        // Tambahkan ke template
                        const templateSelect = rowTemplate.content.querySelector('.select-barang');
                        templateSelect.insertAdjacentHTML('beforeend', optionHtml);

                        // Tambahkan ke semua select yang sudah ada di layar
                        document.querySelectorAll('.select-barang').forEach(select => {
                            select.insertAdjacentHTML('beforeend', optionHtml);
                        });

                        // Tutup modal dan reset form
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalBarangBaru')) || new bootstrap.Modal(document.getElementById('modalBarangBaru'));
                        modal.hide();
                        formBarangBaru.reset();
                        alertModal.classList.add('d-none');

                        // Optional: otomatis pilih barang baru ini di row terakhir
                        const allSelects = document.querySelectorAll('.select-barang');
                        if (allSelects.length > 0) {
                            const lastSelect = allSelects[allSelects.length - 1];
                            lastSelect.value = data.data.id_barang;
                            lastSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                    } else {
                        alertModal.innerText = data.message;
                        alertModal.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    alertModal.innerText = 'Terjadi kesalahan sistem.';
                    alertModal.classList.remove('d-none');
                })
                .finally(() => {
                    btnSimpanBarangBaru.disabled = false;
                    btnSimpanBarangBaru.innerHTML = '<i class="fas fa-save me-1"></i> Simpan Barang';
                });
        });
    });
</script>

<?php include 'views/layouts/footer.php'; ?>