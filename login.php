<?php
include 'config.php';
session_start();

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['admin_id'])) {
    $role = $_SESSION['role'] ?? 'kasir';
    if ($role == 'admin') {
        header('Location: admin.php');
    } elseif ($role == 'kasir') {
        header('Location: kasir.php');
    } elseif ($role == 'dapur') {
        header('Location: dapur.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $sql = "SELECT * FROM admin WHERE username = ? AND password = MD5(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // SET SESSION
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama_lengkap'] ?? $admin['username'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = $admin['role'] ?? 'kasir';
            
            // Redirect sesuai role
            if ($admin['role'] == 'admin') {
                header('Location: admin.php');
            } elseif ($admin['role'] == 'kasir') {
                header('Location: kasir.php');
            } elseif ($admin['role'] == 'dapur') {
                header('Location: dapur.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Caffe Adin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        
        /* ===== BACKGROUND DENGAN GAMBAR ===== */
        .login-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            background: #0f172a;
            overflow: hidden;
        }
        
        /* ===== BACKGROUND IMAGE (Pattern/Gambar) ===== */
        .bg-image {
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px),
                radial-gradient(ellipse at 50% 100%, rgba(37, 99, 235, 0.15) 0%, transparent 60%),
                radial-gradient(ellipse at 50% 0%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%233b82f6' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-size: 60px 60px, 60px 60px, 100% 100%, 100% 100%, auto;
            pointer-events: none;
            z-index: 0;
        }
        
        /* ===== EFEK ORBS ===== */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.3;
            animation: floatOrb 20s ease-in-out infinite alternate;
            z-index: 1;
        }
        .orb:nth-child(2) {
            width: 500px;
            height: 500px;
            background: #2563eb;
            top: -150px;
            right: -150px;
            animation-delay: 0s;
            opacity: 0.2;
        }
        .orb:nth-child(3) {
            width: 400px;
            height: 400px;
            background: #1e3a5f;
            bottom: -100px;
            left: -100px;
            animation-delay: 3s;
            opacity: 0.25;
        }
        .orb:nth-child(4) {
            width: 300px;
            height: 300px;
            background: #60a5fa;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 6s;
            opacity: 0.1;
        }
        
        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -50px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
            100% { transform: translate(20px, -20px) scale(1.05); }
        }
        
        /* ===== LOGIN CARD ===== */
        .login-card {
            position: relative;
            z-index: 10;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 40px;
            padding: 48px 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: 
                0 30px 80px rgba(0,0,0,0.4),
                inset 0 1px 0 rgba(255,255,255,0.3);
            animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0; 
                transform: translateY(40px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header img {
            height: 64px;
            width: 64px;
            object-fit: contain;
            margin-bottom: 16px;
            border-radius: 16px;
            background: rgba(37, 99, 235, 0.05);
            padding: 8px;
        }
        .login-header .icon-wrapper {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #2563eb, #1e3a5f);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.3);
        }
        .login-header .icon-wrapper i {
            font-size: 32px;
            color: white;
        }
        .login-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
        }
        .login-header p {
            color: #94a3b8;
            font-size: 14px;
            margin-top: 4px;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }
        .form-group .input-wrapper {
            position: relative;
        }
        .form-group .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: #1e293b;
        }
        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: white;
            outline: none;
        }
        .form-group input:focus ~ i,
        .form-group input:focus + i {
            color: #2563eb;
        }
        
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }
        .login-btn::after {
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
        .login-btn:hover::after {
            left: 100%;
        }
        .login-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.4);
        }
        .login-btn:active {
            transform: scale(0.97);
        }
        .login-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 16px;
            border: 1px solid #fecaca;
            display: <?= !empty($error) ? 'flex' : 'none' ?>;
            align-items: center;
            gap: 10px;
        }
        .error-message i {
            font-size: 18px;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
            color: #94a3b8;
        }
        .demo-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 12px;
            color: #475569;
        }
        .demo-badge i {
            color: #2563eb;
        }
        .demo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin-top: 8px;
        }
        .demo-grid .role-item {
            font-size: 11px;
            padding: 4px 6px;
            border-radius: 8px;
            background: #f8fafc;
        }
        .demo-grid .role-item .role-name {
            font-weight: 600;
        }
        .demo-grid .role-item .role-detail {
            color: #94a3b8;
            font-size: 10px;
        }
        .badge-admin { color: #1e40af; }
        .badge-kasir { color: #065f46; }
        .badge-dapur { color: #92400e; }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
                border-radius: 28px;
            }
            .login-header h1 {
                font-size: 24px;
            }
            .login-header .icon-wrapper {
                width: 60px;
                height: 60px;
            }
            .login-header .icon-wrapper i {
                font-size: 26px;
            }
            .form-group input {
                padding: 12px 14px 12px 44px;
                font-size: 13px;
            }
            .demo-grid {
                grid-template-columns: 1fr;
                gap: 4px;
            }
        }
    </style>
</head>
<body>

<div class="login-bg">
    <!-- Background Image / Pattern -->
    <div class="bg-image"></div>
    
    <!-- Orbs -->
    <div class="orb"></div>
    <div class="orb"></div>
    <div class="orb"></div>

    <!-- Login Card -->
    <div class="login-card">
        <div class="login-header">
            <?php 
            $logo = 'assets/logo.png';
            if (file_exists($logo)): 
            ?>
                <img src="<?= $logo ?>" alt="Caffe Adin">
            <?php else: ?>
                <div class="icon-wrapper">
                    <i class="fas fa-mug-saucer"></i>
                </div>
            <?php endif; ?>
            <h1>Caffe Adin</h1>
            <p>Login untuk mengelola restoran</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-user mr-2 text-blue-500"></i> Username</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Masukkan username" required autofocus value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock mr-2 text-blue-500"></i> Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                </div>
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="footer-text">
            <div class="demo-badge">
                <i class="fas fa-info-circle"></i>
                Demo Akun:
            </div>
            <div class="demo-grid">
                <div class="role-item">
                    <div class="role-name badge-admin">🔑 Admin</div>
                    <div class="role-detail">admin / admin123</div>
                </div>
                <div class="role-item">
                    <div class="role-name badge-kasir">💳 Kasir</div>
                    <div class="role-detail">kasir / kasir123</div>
                </div>
                <div class="role-item">
                    <div class="role-name badge-dapur">🍳 Dapur</div>
                    <div class="role-detail">dapur / dapur123</div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>