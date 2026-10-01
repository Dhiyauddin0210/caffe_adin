<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'kasir';
?>
<!-- SIDEBAR - DINAMIS PER ROLE -->
<div class="sidebar w-64 p-6 fixed h-full overflow-y-auto z-10">
    <div class="flex items-center gap-3 mb-10">
        <?php 
        $logo = 'assets/logo.png';
        if (file_exists($logo)): 
        ?>
            <img src="<?= $logo ?>" alt="Caffe Adin" class="h-10 w-10 object-contain rounded-xl bg-white/10 p-1.5">
        <?php else: ?>
            <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center">
                <i class="fas fa-mug-saucer text-white text-xl"></i>
            </div>
        <?php endif; ?>
        <span class="text-xl font-bold text-white">Caffe Adin</span>
    </div>
    <div class="mb-4 px-3 py-2 bg-white/10 rounded-xl">
        <p class="text-xs text-white/50">👤 <?= $_SESSION['admin_nama'] ?? 'User' ?></p>
        <p class="text-xs font-semibold text-white/80">
            <?php if ($role == 'admin'): ?>
                <span class="bg-blue-500/30 px-2 py-0.5 rounded-full">🔑 Admin</span>
            <?php elseif ($role == 'kasir'): ?>
                <span class="bg-green-500/30 px-2 py-0.5 rounded-full">💳 Kasir</span>
            <?php else: ?>
                <span class="bg-yellow-500/30 px-2 py-0.5 rounded-full">🍳 Dapur</span>
            <?php endif; ?>
        </p>
    </div>
    <nav class="space-y-1.5">
        <?php if ($role == 'admin'): ?>
            <a href="admin.php" class="sidebar-item <?= $current_page == 'admin.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line w-5"></i> <span>Dashboard</span>
            </a>
        <?php endif; ?>

        <?php if ($role == 'kasir' || $role == 'admin'): ?>
            <a href="kasir.php" class="sidebar-item <?= $current_page == 'kasir.php' ? 'active' : '' ?>">
                <i class="fas fa-cash-register w-5"></i> <span>Kasir</span>
            </a>
        <?php endif; ?>

        <?php if ($role == 'dapur' || $role == 'admin'): ?>
            <a href="dapur.php" class="sidebar-item <?= $current_page == 'dapur.php' ? 'active' : '' ?>">
                <i class="fas fa-kitchen-set w-5"></i> <span>Dapur</span>
                <?php 
                $pesananBaru = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'menunggu'")->fetch_assoc()['total'];
                if($pesananBaru > 0): 
                ?>
                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full"><?= $pesananBaru ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <?php if ($role == 'admin'): ?>
            <a href="export.php" class="sidebar-item <?= $current_page == 'export.php' ? 'active' : '' ?>">
                <i class="fas fa-file-export w-5"></i> <span>Export Laporan</span>
            </a>
            <a href="menu.php" class="sidebar-item <?= $current_page == 'menu.php' ? 'active' : '' ?>">
                <i class="fas fa-utensils w-5"></i> <span>Manajemen Menu</span>
            </a>
            <a href="meja.php" class="sidebar-item <?= $current_page == 'meja.php' ? 'active' : '' ?>">
                <i class="fas fa-chair w-5"></i> <span>Manajemen Meja</span>
            </a>
            <a href="qr_generator.php" class="sidebar-item <?= $current_page == 'qr_generator.php' ? 'active' : '' ?>">
                <i class="fas fa-qrcode w-5"></i> <span>QR Code Meja</span>
            </a>
            <a href="user_management.php" class="sidebar-item <?= $current_page == 'user_management.php' ? 'active' : '' ?>">
                <i class="fas fa-users w-5"></i> <span>Manajemen User</span>
            </a>
        <?php endif; ?>

        <!-- 🔧 FIXED: Ubah dari index.php ke menu.php -->
        <a href="menu.php" class="sidebar-item <?= $current_page == 'menu.php' ? 'active' : '' ?>">
            <i class="fas fa-utensils w-5"></i> <span>Lihat Menu</span>
        </a>

        <a href="logout.php" class="sidebar-item logout">
            <i class="fas fa-sign-out-alt w-5"></i> <span>Logout</span>
        </a>
    </nav>
</div>