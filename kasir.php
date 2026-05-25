<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Kasir')) {
    header("Location: index.php");
    exit;
}
require_once 'config/database.php';

// Generate Nomor Struk (JL-YYYYMMDD-XXX)
$date_prefix = 'JL-' . date('Ymd') . '-';
$q_no = $conn->query("SELECT MAX(CAST(SUBSTRING(kode_penjualan, 13) AS UNSIGNED)) as last_no FROM penjualan WHERE kode_penjualan LIKE '$date_prefix%'");
$d_no = $q_no->fetch_assoc();
$next_no = $d_no['last_no'] ? $d_no['last_no'] + 1 : 1;
$kode_transaksi = $date_prefix . str_pad($next_no, 3, '0', STR_PAD_LEFT);

include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0"><i class="fas fa-desktop me-2"></i> Transaksi Kasir</h1>
    </div>
</div>

<section class="content">
    <div class="row">
        <!-- Kolom Kiri: Input dan Keranjang -->
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="input-group input-group-lg mb-3">
                        <span class="input-group-text bg-white"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Ketik Nama Barang atau Scan Barcode..." autocomplete="off" autofocus>
                        <button class="btn btn-crimson" id="btnSearch" type="button"><i class="fas fa-search"></i> Cari</button>
                    </div>
                    <!-- Hasil Pencarian Dropdown -->
                    <div id="searchResults" class="list-group position-absolute w-100 z-3" style="display:none; max-height: 300px; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-0 pt-3">
                    <h3 class="card-title fw-bold">Keranjang Belanja</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped align-middle mb-0" id="cartTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Barang</th>
                                <th class="text-end" width="150">Harga</th>
                                <th class="text-center" width="120">Qty</th>
                                <th class="text-end" width="150">Subtotal</th>
                                <th class="text-center" width="60"><i class="fas fa-cog"></i></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody">
                            <tr><td colspan="5" class="text-center py-4 text-muted" id="emptyCart">Keranjang kosong.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Pembayaran -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body bg-light text-center rounded-top">
                    <h5 class="text-muted mb-2">TOTAL TAGIHAN</h5>
                    <h1 class="display-4 fw-bold text-danger mb-0" id="txtTotalTagihan">Rp 0</h1>
                </div>
                <div class="card-body">
                    <form id="formCheckout">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pelanggan / Anggota</label>
                            <select class="form-select" id="id_anggota" name="id_anggota">
                                <option value="">-- Pelanggan Umum --</option>
                                <?php
                                $q_anggota = $conn->query("SELECT id_anggota, no_anggota, nama_anggota FROM anggota WHERE status='aktif' ORDER BY nama_anggota ASC");
                                while($a = $q_anggota->fetch_assoc()) {
                                    echo '<option value="'.$a['id_anggota'].'">'.htmlspecialchars($a['nama_anggota']).' ('.htmlspecialchars($a['no_anggota']).')</option>';
                                }
                                ?>
                            </select>
                            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Pilih anggota untuk pencatatan SHU Jasa Usaha.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">No. Transaksi</label>
                            <input type="text" class="form-control" id="kode_transaksi" value="<?php echo $kode_transaksi; ?>" readonly>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Tunai (Rp)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text fw-bold">Rp</span>
                                <input type="number" id="inputBayar" class="form-control text-end fw-bold" style="font-size: 1.5rem;" min="0" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Kembalian (Rp)</label>
                            <input type="text" id="txtKembalian" class="form-control text-end text-success fw-bold bg-white" style="font-size: 1.5rem;" value="0" readonly>
                        </div>
                        
                        <button type="submit" id="btnProses" class="btn btn-success btn-lg w-100 fw-bold shadow-sm" disabled>
                            <i class="fas fa-check-circle me-1"></i> PROSES TRANSAKSI
                        </button>
                        <button type="button" id="btnReset" class="btn btn-outline-danger w-100 mt-2">
                            <i class="fas fa-trash me-1"></i> Batal / Kosongkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Notifikasi Berhasil -->
<div class="modal fade" id="modalSukses" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body py-4">
                <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                <h4 class="fw-bold">Transaksi Berhasil!</h4>
                <p class="text-muted mb-4" id="successMsg"></p>
                <button type="button" class="btn btn-primary w-100 mb-2" id="btnCetak"><i class="fas fa-print me-1"></i> Cetak Struk</button>
                <button type="button" class="btn btn-light w-100" onclick="window.location.reload();">Transaksi Baru</button>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>

<script>
let cart = [];
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const cartBody = document.getElementById('cartBody');
const emptyCart = document.getElementById('emptyCart');
const txtTotalTagihan = document.getElementById('txtTotalTagihan');
const inputBayar = document.getElementById('inputBayar');
const txtKembalian = document.getElementById('txtKembalian');
const btnProses = document.getElementById('btnProses');
const formCheckout = document.getElementById('formCheckout');
let totalGlobal = 0;
let lastPenjualanId = null;

// Format Rupiah
const formatRp = (angka) => new Intl.NumberFormat('id-ID').format(angka);

// Pencarian Barang
searchInput.addEventListener('input', function() {
    let q = this.value;
    if(q.length < 2) {
        searchResults.style.display = 'none';
        return;
    }
    
    fetch('kasir_api.php?q=' + encodeURIComponent(q))
    .then(res => res.json())
    .then(res => {
        if(res.status === 'success' && res.data.length > 0) {
            let html = '';
            res.data.forEach(item => {
                html += `<button type="button" class="list-group-item list-group-item-action" onclick='addToCart(${JSON.stringify(item)})'>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">${item.kode_barang} - ${item.nama_barang}</span>
                                <span class="text-success fw-bold">Rp ${formatRp(item.harga_jual)}</span>
                            </div>
                            <small class="text-muted">Stok: ${item.stok}</small>
                         </button>`;
            });
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
        } else {
            searchResults.innerHTML = `<div class="list-group-item text-muted">Barang tidak ditemukan atau stok habis.</div>`;
            searchResults.style.display = 'block';
        }
    });
});

// Sembunyikan hasil pencarian saat klik di luar
document.addEventListener('click', function(e) {
    if(!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

function addToCart(item) {
    let existingIndex = cart.findIndex(c => c.id_barang === item.id_barang);
    if(existingIndex >= 0) {
        if(cart[existingIndex].qty < item.stok) {
            cart[existingIndex].qty += 1;
            cart[existingIndex].subtotal = cart[existingIndex].qty * cart[existingIndex].harga;
        } else {
            alert('Stok tidak mencukupi!');
        }
    } else {
        cart.push({
            id_barang: item.id_barang,
            kode_barang: item.kode_barang,
            nama_barang: item.nama_barang,
            harga: item.harga_jual,
            qty: 1,
            stok_max: item.stok,
            subtotal: item.harga_jual
        });
    }
    searchInput.value = '';
    searchResults.style.display = 'none';
    searchInput.focus();
    renderCart();
}

function updateQty(index, newQty) {
    let qty = parseInt(newQty);
    if(isNaN(qty) || qty <= 0) qty = 1;
    if(qty > cart[index].stok_max) {
        alert('Melebihi stok yang ada!');
        qty = cart[index].stok_max;
    }
    cart[index].qty = qty;
    cart[index].subtotal = qty * cart[index].harga;
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function renderCart() {
    if(cart.length === 0) {
        cartBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Keranjang kosong.</td></tr>';
        totalGlobal = 0;
        btnProses.disabled = true;
    } else {
        let html = '';
        totalGlobal = 0;
        cart.forEach((c, idx) => {
            totalGlobal += c.subtotal;
            html += `<tr>
                        <td>
                            <div class="fw-bold">${c.nama_barang}</div>
                            <small class="text-muted">${c.kode_barang}</small>
                        </td>
                        <td class="text-end">Rp ${formatRp(c.harga)}</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center mx-auto" style="width: 70px;" min="1" max="${c.stok_max}" value="${c.qty}" onchange="updateQty(${idx}, this.value)">
                        </td>
                        <td class="text-end fw-bold">Rp ${formatRp(c.subtotal)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger" onclick="removeFromCart(${idx})"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>`;
        });
        cartBody.innerHTML = html;
        btnProses.disabled = false;
    }
    txtTotalTagihan.innerText = 'Rp ' + formatRp(totalGlobal);
    hitungKembalian();
}

inputBayar.addEventListener('input', hitungKembalian);

function hitungKembalian() {
    let bayar = parseInt(inputBayar.value) || 0;
    let kembalian = bayar - totalGlobal;
    if(kembalian < 0) {
        txtKembalian.value = 'Kurang Bayar!';
        txtKembalian.classList.replace('text-success', 'text-danger');
        btnProses.disabled = true;
    } else {
        txtKembalian.value = formatRp(kembalian);
        txtKembalian.classList.replace('text-danger', 'text-success');
        if(cart.length > 0) btnProses.disabled = false;
    }
}

document.getElementById('btnReset').addEventListener('click', function() {
    if(confirm('Yakin ingin mengosongkan keranjang?')) {
        cart = [];
        inputBayar.value = '';
        renderCart();
    }
});

// Proses Checkout
formCheckout.addEventListener('submit', function(e) {
    e.preventDefault();
    if(cart.length === 0) return;
    
    let bayar = parseInt(inputBayar.value) || 0;
    if(bayar < totalGlobal) {
        alert('Pembayaran kurang dari total tagihan!');
        return;
    }

    btnProses.disabled = true;
    btnProses.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    const data = {
        kode_penjualan: document.getElementById('kode_transaksi').value,
        id_anggota: document.getElementById('id_anggota').value,
        total: totalGlobal,
        bayar: bayar,
        cart: cart
    };

    fetch('kasir_proses.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if(res.status === 'success') {
            lastPenjualanId = res.id_penjualan;
            document.getElementById('successMsg').innerText = 'Kembalian: Rp ' + formatRp(bayar - totalGlobal);
            var myModal = new bootstrap.Modal(document.getElementById('modalSukses'));
            myModal.show();
        } else {
            alert('Gagal: ' + res.error);
            btnProses.disabled = false;
            btnProses.innerHTML = '<i class="fas fa-check-circle me-1"></i> PROSES TRANSAKSI';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan sistem.');
        btnProses.disabled = false;
    });
});

document.getElementById('btnCetak').addEventListener('click', function() {
    if(lastPenjualanId) {
        window.open('kasir_struk.php?id=' + lastPenjualanId, '_blank', 'width=400,height=600');
    }
});

</script>
