<?php
include 'config.php';

// ===== DITAMBAHKAN: Auth check untuk redirect jika user sudah login =====
session_start();

// Jika user sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['admin_id'])) {
    $role = $_SESSION['role'] ?? 'kasir';
    if ($role == 'admin') {
        header('Location: admin.php');
        exit;
    } elseif ($role == 'kasir') {
        header('Location: kasir.php');
        exit;
    } elseif ($role == 'dapur') {
        header('Location: dapur.php');
        exit;
    }
}

// ===== DITAMBAHKAN: Validasi meja lebih aman =====
$meja = isset($_GET['meja']) ? (int)$_GET['meja'] : 1;

// Validasi meja ada di database
$cekMeja = $conn->query("SELECT * FROM meja WHERE nomor_meja = $meja");
if ($cekMeja->num_rows == 0) {
    // Redirect ke meja default 1 atau tampilkan pesan
    header('Location: index.php?meja=1');
    exit;
}

$kategoriResult = $conn->query("SELECT * FROM kategori");

// AMBIL SEMUA MENU (TERMASUK YANG TIDAK TERSEDIA)
$menuResult = $conn->query("SELECT * FROM menu ORDER BY tersedia DESC, nama_menu ASC");
$menuData = [];
while ($row = $menuResult->fetch_assoc()) {
    $menuData[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Caffe Adin - Meja <?= $meja ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    * { 
        font-family: 'Inter', -apple-system, sans-serif;
        scroll-behavior: smooth;
    }
    body { background: #f0f4f8; }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    @keyframes slideUp {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .menu-card {
        animation: fadeInUp 0.6s ease forwards;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(59, 130, 246, 0.08);
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(59, 130, 246, 0.06);
    }
    .menu-card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 20px 50px rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
    }
    .menu-card.unavailable {
        opacity: 0.85;
        border-color: #e2e8f0;
    }
    .menu-card.unavailable:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    
    .menu-card:nth-child(1) { animation-delay: 0.05s; }
    .menu-card:nth-child(2) { animation-delay: 0.10s; }
    .menu-card:nth-child(3) { animation-delay: 0.15s; }
    .menu-card:nth-child(4) { animation-delay: 0.20s; }
    .menu-card:nth-child(5) { animation-delay: 0.25s; }
    .menu-card:nth-child(6) { animation-delay: 0.30s; }
    .menu-card:nth-child(7) { animation-delay: 0.35s; }
    .menu-card:nth-child(8) { animation-delay: 0.40s; }
    .menu-card:nth-child(9) { animation-delay: 0.45s; }
    .menu-card:nth-child(10) { animation-delay: 0.50s; }
    .menu-card:nth-child(11) { animation-delay: 0.55s; }
    .menu-card:nth-child(12) { animation-delay: 0.60s; }
    
    .gradient-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #3b82f6 100%);
        position: relative;
        overflow: hidden;
    }
    .gradient-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
        animation: spin-slow 30s linear infinite;
    }
    
    .category-btn {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        font-weight: 600;
        border-radius: 50px;
        padding: 10px 24px;
        font-size: 14px;
        white-space: nowrap;
        background: #e8edf5;
        color: #64748b;
        cursor: pointer;
    }
    .category-btn:hover {
        background: #dbeafe;
        color: #1e40af;
        transform: translateY(-2px);
    }
    .category-btn.active {
        background: linear-gradient(135deg, #1e3a5f, #2563eb);
        color: white;
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.35);
        border-color: #2563eb;
    }
    
    .add-btn {
        background: linear-gradient(135deg, #1e3a5f, #2563eb);
        color: white;
        padding: 10px;
        border-radius: 12px;
        border: none;
        width: 100%;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
    }
    .add-btn::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.5s ease;
    }
    .add-btn:hover::after {
        left: 100%;
    }
    .add-btn:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
    }
    .add-btn:active { transform: scale(0.95); }
    .add-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }
    
    .cart-drawer {
        animation: slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 32px 32px 0 0;
        background: white;
        box-shadow: 0 -20px 60px rgba(0,0,0,0.12);
    }
    
    .quantity-btn {
        transition: all 0.2s ease;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        border: none;
        cursor: pointer;
    }
    .quantity-btn.minus {
        background: #e8edf5;
        color: #475569;
    }
    .quantity-btn.minus:hover {
        background: #cbd5e1;
        transform: scale(1.05);
    }
    .quantity-btn.plus {
        background: linear-gradient(135deg, #1e3a5f, #2563eb);
        color: white;
    }
    .quantity-btn.plus:hover {
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        transform: scale(1.05);
    }
    
    .cart-item {
        background: #f8fafc;
        border-radius: 16px;
        padding: 12px 16px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        animation: fadeInUp 0.3s ease;
    }
    .cart-item:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    
    .menu-image-placeholder {
        background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
    }
    
    .badge-pulse {
        animation: pulse 2s infinite;
    }
    
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    
    .shadow-soft { box-shadow: 0 8px 32px rgba(0,0,0,0.06); }
    
    .qr-float {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: white;
        padding: 14px 18px;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        z-index: 50;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 12px;
        color: #64748b;
        border: 1px solid rgba(37, 99, 235, 0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    .qr-float:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(37, 99, 235, 0.15);
    }
    .qr-float i { font-size: 22px; color: #2563eb; }
    
    .price {
        font-size: 18px;
        font-weight: 800;
        color: #1e3a5f;
    }
    .price::before { content: 'Rp '; font-weight: 600; font-size: 14px; }
    
    .toast {
        position: fixed;
        top: 24px;
        right: 24px;
        padding: 16px 24px;
        border-radius: 16px;
        color: white;
        font-weight: 600;
        z-index: 999;
        transform: translateX(120%);
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        max-width: 400px;
        backdrop-filter: blur(10px);
    }
    .toast.show { transform: translateX(0); }
    .toast.success { background: linear-gradient(135deg, #059669, #10b981); }
    .toast.error { background: linear-gradient(135deg, #dc2626, #ef4444); }
    
    .badge-rating {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #1e3a5f;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .badge-unavailable {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-15deg);
        background: rgba(239, 68, 68, 0.9);
        color: white;
        padding: 6px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        z-index: 5;
        border: 2px solid white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        backdrop-filter: blur(4px);
    }
    
    .search-input {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 12px 16px 12px 48px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: white;
        width: 100%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .search-input:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .search-wrapper {
        position: relative;
    }
    .search-wrapper i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
    }
    
    @media (max-width: 640px) {
        .qr-float { bottom: 16px; right: 16px; padding: 10px 14px; font-size: 11px; }
        .qr-float i { font-size: 18px; }
    }
</style>
</head>
<body>

<!-- Floating QR Info -->
<div class="qr-float">
    <i class="fas fa-qrcode"></i>
    <div>
        <span class="font-semibold text-gray-800">Meja <?= $meja ?></span>
        <span class="text-xs block text-gray-400">Scan QR untuk pesan</span>
    </div>
</div>

<!-- Header -->
<header class="gradient-header text-white px-6 py-5 sticky top-0 z-20 shadow-xl">
  <div class="max-w-7xl mx-auto flex justify-between items-center">
    <div>
      <div class="flex items-center gap-3">
        <?php 
        $logo = 'assets/logo.png';
        if (file_exists($logo)): 
        ?>
            <img src="<?= $logo ?>" alt="Caffe Adin" class="h-11 w-11 object-contain rounded-xl bg-white/10 p-1.5">
        <?php else: ?>
            <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <i class="fas fa-mug-saucer text-xl"></i>
            </div>
        <?php endif; ?>
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight">Caffe Adin</h1>
          <div class="flex items-center gap-3 mt-0.5">
            <span class="text-xs bg-white/20 px-3 py-1 rounded-full flex items-center gap-1.5 backdrop-blur-sm">
              <i class="fas fa-chair text-xs"></i> Meja <?= $meja ?>
            </span>
            <span class="text-xs text-blue-200 flex items-center gap-1">
              <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
              Siap pesan
            </span>
          </div>
        </div>
      </div>
    </div>
    <button onclick="toggleCart()" class="relative bg-white/15 hover:bg-white/25 transition-all rounded-2xl p-3 backdrop-blur-sm border border-white/20 hover:scale-105 active:scale-95">
      <i class="fas fa-shopping-bag text-xl"></i>
      <span id="cartCount" class="badge-pulse absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center border-2 border-white">0</span>
    </button>
  </div>
</header>

<!-- Kategori Tabs -->
<div class="sticky top-[88px] z-10 bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-100/50">
  <div class="max-w-7xl mx-auto px-4 py-3">
    <div class="flex gap-2 overflow-x-auto scrollbar-hide">
      <button onclick="filterKategori('semua')" class="category-btn active" data-kat="semua">
        <i class="fas fa-th-large mr-1.5 text-xs"></i> Semua
      </button>
      <?php $kategoriResult->data_seek(0); while ($k = $kategoriResult->fetch_assoc()) { ?>
        <button onclick="filterKategori('<?= $k['id'] ?>')" class="category-btn" data-kat="<?= $k['id'] ?>">
          <i class="fas fa-tag mr-1.5 text-xs"></i> <?= $k['nama_kategori'] ?>
        </button>
      <?php } ?>
    </div>
  </div>
</div>

<!-- Search Bar -->
<div class="max-w-7xl mx-auto px-4 py-4">
    <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" id="searchMenu" placeholder="Cari menu..." class="search-input">
        <div id="searchResult" class="mt-2 hidden"></div>
    </div>
</div>

<!-- Menu Grid -->
<main class="max-w-7xl mx-auto px-4 py-4">
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6" id="menuGrid">
    <?php foreach ($menuData as $index => $item) { 
      $icons = ['🍛', '🍜', '🍲', '🥘', '🍗', '🥩', '🍣', '🍱', '🥙', '🌮', '🍝', '🧆'];
      $icon = $icons[$index % count($icons)];
      $isAvailable = $item['tersedia'] == 1;
      
      // 🔥 FIX: Path gambar
      $gambar = !empty($item['gambar']) ? 'uploads/' . $item['gambar'] : '';
      $hasGambar = $gambar && file_exists($gambar);
    ?>
      <div class="menu-card <?= !$isAvailable ? 'unavailable' : '' ?>" data-kat="<?= $item['kategori_id'] ?>">
        <div class="relative h-36 flex items-center justify-center overflow-hidden">
          <?php if ($hasGambar): ?>
            <img src="<?= $gambar ?>" alt="<?= $item['nama_menu'] ?>" 
                 class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
          <?php else: ?>
            <div class="w-full h-full menu-image-placeholder flex items-center justify-center text-6xl">
              <span class="drop-shadow-lg"><?= $icon ?></span>
            </div>
          <?php endif; ?>
          
          <?php if (!$isAvailable): ?>
            <div class="badge-unavailable">
              <i class="fas fa-times-circle mr-1"></i> Habis
            </div>
          <?php endif; ?>
          
          <div class="absolute top-3 right-3 badge-rating">
            <i class="fas fa-star text-yellow-400 mr-0.5 text-xs"></i> 4.8
          </div>
        </div>
        <div class="p-4">
          <h3 class="font-bold text-gray-800 text-sm line-clamp-1"><?= $item['nama_menu'] ?></h3>
          <p class="price text-sm mt-1"><?= number_format($item['harga'], 0, ',', '.') ?></p>
          <?php if ($isAvailable): ?>
            <button onclick='tambahKeranjang(<?= $item["id"] ?>, "<?= addslashes($item["nama_menu"]) ?>", <?= $item["harga"] ?>)'
              class="add-btn mt-3">
              <i class="fas fa-plus text-sm"></i> Tambah
            </button>
          <?php else: ?>
            <button disabled class="mt-3 w-full bg-gray-200 text-gray-500 text-sm font-semibold py-2.5 rounded-xl cursor-not-allowed flex items-center justify-center gap-2">
              <i class="fas fa-times-circle"></i> Tidak Tersedia
            </button>
          <?php endif; ?>
        </div>
      </div>
    <?php } ?>
  </div>
</main>

<!-- Cart Drawer -->
<div id="cartDrawer" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 hidden" onclick="toggleCart()">
  <div class="absolute bottom-0 left-0 right-0 max-h-[85vh] cart-drawer" onclick="event.stopPropagation()">
    <div class="bg-white rounded-t-3xl shadow-2xl p-6 overflow-y-auto max-h-[85vh]">
      <div class="flex justify-between items-center mb-5">
        <div class="flex items-center gap-3">
          <div class="bg-blue-50 p-3 rounded-2xl">
            <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
          </div>
          <div>
            <h2 class="font-extrabold text-xl text-gray-800">Keranjang</h2>
            <p id="cartItemCount" class="text-sm text-gray-400">Belum ada pesanan</p>
          </div>
        </div>
        <button onclick="toggleCart()" class="text-gray-400 hover:text-gray-600 text-2xl p-2 hover:bg-gray-100 rounded-full transition w-10 h-10 flex items-center justify-center">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div id="cartItems" class="space-y-3 max-h-[40vh] overflow-y-auto pr-2"></div>

      <div id="emptyCart" class="text-center py-12">
        <div class="text-7xl mb-4 opacity-30">🛒</div>
        <p class="text-gray-400 font-medium">Keranjang masih kosong</p>
        <p class="text-sm text-gray-300 mt-1">Yuk pilih menu favoritmu!</p>
      </div>

      <div class="border-t border-gray-100 mt-5 pt-5">
        <!-- Catatan -->
        <div class="mb-4">
          <label class="text-sm font-semibold text-gray-700 block mb-1.5">
            <i class="fas fa-pen text-blue-500 mr-1.5"></i> Catatan (opsional)
          </label>
          <textarea id="catatanPesanan" rows="2" placeholder="Contoh: Pedas level 2, jangan pakai bawang..." 
            class="w-full border border-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all bg-gray-50 focus:bg-white"></textarea>
          <p class="text-xs text-gray-400 mt-1.5"><i class="fas fa-info-circle mr-1"></i> Catatan akan dikirim ke dapur</p>
        </div>

        <div class="flex justify-between items-center mb-2">
          <span class="text-gray-600 font-medium">Total Pesanan</span>
          <span id="cartTotal" class="text-2xl font-extrabold text-blue-600">Rp 0</span>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-400 mb-4 bg-gray-50 px-4 py-2 rounded-2xl">
          <i class="fas fa-clock text-blue-400"></i>
          <span>Estimasi siap: <span class="font-semibold text-gray-600">15-20 menit</span></span>
        </div>
        <button onclick="submitPesanan()" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center gap-3 text-lg hover:scale-[1.02] active:scale-95">
          <i class="fas fa-paper-plane"></i> Pesan Sekarang
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="toast success">
  <div class="flex items-center gap-3">
    <i class="fas fa-check-circle text-2xl"></i>
    <div>
      <p class="font-semibold">Berhasil!</p>
      <p class="text-sm opacity-90" id="toastMessage">Pesanan ditambahkan</p>
    </div>
  </div>
</div>

<script>
const meja = <?= $meja ?>;
let cart = [];

function tambahKeranjang(id, nama, harga) {
  const existing = cart.find(item => item.id === id);
  if (existing) {
    existing.qty += 1;
  } else {
    cart.push({ id, nama, harga, qty: 1 });
  }
  renderCart();
  showToast(nama + ' ditambahkan ke keranjang');
}

function ubahQty(id, delta) {
  const item = cart.find(i => i.id === id);
  if (item) {
    item.qty += delta;
    if (item.qty <= 0) {
      cart = cart.filter(i => i.id !== id);
    }
    renderCart();
  }
}

function renderCart() {
  const cartItems = document.getElementById('cartItems');
  const emptyCart = document.getElementById('emptyCart');
  const cartCount = document.getElementById('cartCount');
  const cartTotal = document.getElementById('cartTotal');
  const cartItemCount = document.getElementById('cartItemCount');

  let total = 0;
  let count = 0;

  if (cart.length === 0) {
    cartItems.innerHTML = '';
    emptyCart.style.display = 'block';
    cartCount.innerText = '0';
    cartTotal.innerText = 'Rp 0';
    cartItemCount.innerText = 'Belum ada pesanan';
    return;
  }

  emptyCart.style.display = 'none';
  cartItems.innerHTML = '';

  cart.forEach((item) => {
    total += item.harga * item.qty;
    count += item.qty;
    cartItems.innerHTML += `
      <div class="cart-item">
        <div class="flex-1">
          <p class="text-sm font-semibold text-gray-800">${item.nama}</p>
          <p class="text-xs text-gray-500">Rp ${item.harga.toLocaleString('id-ID')}</p>
        </div>
        <div class="flex items-center gap-3">
          <button onclick="ubahQty(${item.id}, -1)" class="quantity-btn minus">
            <i class="fas fa-minus"></i>
          </button>
          <span class="font-bold text-gray-700 w-6 text-center text-sm">${item.qty}</span>
          <button onclick="ubahQty(${item.id}, 1)" class="quantity-btn plus">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      </div>
    `;
  });

  cartCount.innerText = count;
  cartTotal.innerText = 'Rp ' + total.toLocaleString('id-ID');
  cartItemCount.innerText = count + ' item dalam keranjang';
}

function toggleCart() {
  const drawer = document.getElementById('cartDrawer');
  drawer.classList.toggle('hidden');
  document.body.style.overflow = drawer.classList.contains('hidden') ? '' : 'hidden';
}

function filterKategori(katId) {
  document.querySelectorAll('.category-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  event.target.classList.add('active');

  document.getElementById('searchMenu').value = '';
  document.getElementById('searchResult').classList.add('hidden');

  document.querySelectorAll('.menu-card').forEach(card => {
    if (katId === 'semua' || card.dataset.kat === katId) {
      card.style.display = 'block';
      card.style.animation = 'fadeInUp 0.5s ease forwards';
    } else {
      card.style.display = 'none';
    }
  });
}

function submitPesanan() {
  if (cart.length === 0) {
    showToast('Keranjang masih kosong!', true);
    return;
  }

  const submitBtn = event.target;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

  const catatan = document.getElementById('catatanPesanan').value;

  fetch('place_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ 
      meja: meja, 
      items: cart,
      catatan: catatan 
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      window.location.href = 'payment.php?order_id=' + data.pesanan_id;
    } else {
      showToast('Gagal mengirim pesanan: ' + data.message, true);
    }
  })
  .catch((error) => {
    showToast('Terjadi kesalahan: ' + error.message, true);
  })
  .finally(() => {
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Pesan Sekarang';
  });
}

function showToast(message, isError = false) {
  const toast = document.getElementById('toast');
  const toastMessage = document.getElementById('toastMessage');
  toastMessage.textContent = message;
  
  toast.className = 'toast ' + (isError ? 'error' : 'success');
  toast.classList.add('show');
  
  clearTimeout(toast.timeout);
  toast.timeout = setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.getElementById('cartDrawer').classList.add('hidden');
  }
});

// ===== PENCARIAN MENU =====
const searchInput = document.getElementById('searchMenu');
const menuCards = document.querySelectorAll('.menu-card');

searchInput.addEventListener('keyup', function() {
    const keyword = this.value.toLowerCase().trim();
    let found = 0;
    const resultDiv = document.getElementById('searchResult');
    
    menuCards.forEach(card => {
        const namaMenu = card.querySelector('h3').textContent.toLowerCase();
        if (namaMenu.includes(keyword) || keyword === '') {
            card.style.display = 'block';
            found++;
        } else {
            card.style.display = 'none';
        }
    });
    
    if (keyword !== '' && found === 0) {
        resultDiv.innerHTML = `
            <div class="text-center py-10 bg-white rounded-2xl shadow-sm border border-gray-100">
                <i class="fas fa-utensils text-5xl text-gray-300 mb-4 block"></i>
                <p class="text-gray-400">Tidak ada menu yang cocok dengan "<strong class="text-gray-600">${keyword}</strong>"</p>
                <p class="text-sm text-gray-300 mt-1">Coba kata kunci lain</p>
            </div>
        `;
        resultDiv.classList.remove('hidden');
    } else {
        resultDiv.classList.add('hidden');
    }
});
</script>
</body>
</html>