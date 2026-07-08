<?php
// 1. හැමදේටම කලින් Session එක Start කරන්න
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

// Session expired message එක අල්ලගන්න එක
if (isset($_GET['message']) && $_GET['message'] === 'session_expired') {
    $error = "ක්‍රියාකාරීත්වයක් නොමැති වීම නිසා සැසිය අවසන් විය. (Session expired due to inactivity.)";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ෆෝම් එක submit කරද්දී කලින් තිබ්බ message එක clear කරනවා override නොවෙන්න
    $error = ''; 
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $authenticated = false;
            $needs_rehash = false;

            // මුලින්ම බලනවා hash එකක්ද කියලා (හෑෂ් එකක් නම් දිග අකුරු 60ක් හෝ ඊට වැඩි විය යුතුයි)
            if (strlen($row['password']) >= 60 && password_verify($password, $row['password'])) {
                $authenticated = true;
            } 
            // ඊට පස්සේ පරණ plain text password එකක්ද කියලා චෙක් කරනවා
            elseif ($password === $row['password']) {
                $authenticated = true;
                $needs_rehash = true;
            }

            if ($authenticated) {
                if ($needs_rehash) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt_update->bind_param("si", $new_hash, $row['id']);
                    $stmt_update->execute();
                }

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['last_activity'] = time();

                header("Location: index.php");
                exit;
            } else {
                $error = "පරිශීලක නාමය හෝ මුද්‍රිත පදය වැරදියි. (Invalid username or password)";
            }
        } else {
            $error = "පරිශීලක නාමය හෝ මුද්‍රිත පදය වැරදියි. (Invalid username or password)";
        }
    } else {
        $error = "කරුණාකර ක්ෂේත්‍ර දෙකම සම්පූර්ණ කරන්න. (Please fill in both fields)";
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department of Social Services - NWP | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Sora', 'Noto Sans Sinhala', sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #05020a 0%, #0f0720 50%, #15092e 100%);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            overflow: hidden;
            position: relative;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Morphing Background Orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            animation: floatOrb 10s infinite alternate ease-in-out;
        }
        .orb-1 {
            width: 300px;
            height: 300px;
            background: rgba(116, 41, 238, 0.2);
            top: 10%;
            left: 15%;
        }
        .orb-2 {
            width: 400px;
            height: 400px;
            background: rgba(223, 46, 240, 0.12);
            bottom: 10%;
            right: 10%;
            animation-delay: -5s;
        }
        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 30px) scale(1.15); }
        }
        
        /* ───── Main Glassmorphic slanted container ───── */
        .main-container {
            position: relative;
            width: 780px;
            height: 480px;
            border: 2px solid #7429ee;
            background-color: transparent;
            box-shadow: 0 0 25px rgba(116, 41, 238, 0.45);
            overflow: hidden;
            border-radius: 20px;
            z-index: 10;
            animation: cardFadeIn 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }

        @keyframes cardFadeIn {
            0% {
                opacity: 0;
                transform: translateY(30px);
                filter: blur(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
                filter: blur(0);
            }
        }
        
        /* ───── Form boxes layout ───── */
        .main-container .form-box {
            position: absolute;
            top: 0;
            width: 50%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            z-index: 99;
        }
        .main-container .form-box.login {
            left: 0;
            padding: 0 50px 0 40px;
        }
        .main-container.active .form-box.login {
            pointer-events: none;
        }
        
        /* Prevent autofill styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-background-clip: text !important;
            background-clip: text !important;
            -webkit-text-fill-color: #ffffff !important;
            transition: background-color 5000s ease-in-out 0s;
            border-bottom: 2px solid #7429ee !important;
        }

        /* Staggered form elements slide-in on page load */
        .main-container .form-box.login .animation {
            opacity: 0;
            transform: translateX(-50px);
            filter: blur(5px);
            animation: slideInLeft 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            animation-delay: calc(0.06s * var(--j));
        }
        
        /* ───── Form elements ───── */
        .form-box h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            color: #fff;
            text-align: center;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        /* Error Box inside the Login form */
        .alert-box {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-left: 3px solid #ef4444;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 10px;
            color: #fca5a5;
            font-size: 0.78rem;
            line-height: 1.4;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .alert-box i {
            color: #ef4444;
            font-size: 14px;
            margin-top: 2px;
        }
        
        .input-box {
            position: relative;
            width: 100%;
            height: 45px;
            margin: 20px 0;
        }
        .input-box input {
            width: 100%;
            height: 100%;
            background: transparent;
            border: none;
            outline: none;
            border-bottom: 2px solid rgba(255, 255, 255, 0.45);
            padding-right: 25px;
            font-size: 15px;
            color: #fff;
            font-weight: 500;
            transition: .4s;
        }
        .input-box input:focus,
        .input-box input:valid {
            border-bottom: 2px solid #7429ee;
        }
        .input-box label {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            font-size: 15px;
            color: rgba(255, 255, 255, 0.75);
            pointer-events: none;
            transition: .4s;
        }
        .input-box input:focus ~ label,
        .input-box input:valid ~ label {
            top: -5px;
            font-size: 12px;
            color: #9f67ff;
            font-weight: 600;
        }
        .input-box i {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: rgba(255, 255, 255, 0.75);
            transition: .4s;
            z-index: 5;
        }
        .input-box i[style*="cursor: pointer"]:hover {
            color: #9f67ff;
            transform: translateY(-50%) scale(1.15);
        }
        .input-box input:focus ~ i,
        .input-box input:valid ~ i {
            color: #9f67ff;
        }
        
        .btn {
            position: relative;
            width: 100%;
            height: 42px;
            background: linear-gradient(90deg, #7429ee, #df2ef0);
            border: none;
            outline: none;
            border-radius: 40px;
            cursor: pointer;
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            color: #fff;
            font-weight: 600;
            z-index: 1;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(116, 41, 238, 0.35);
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn::before {
            content: '';
            position: absolute;
            top: -100%;
            left: 0;
            width: 100%;
            height: 300%;
            background: linear-gradient(#df2ef0, #7429ee, #df2ef0, #7429ee);
            z-index: -1;
            transition: .4s;
        }
        .btn:hover::before {
            top: 0;
        }
        
        /* ───── Info Panel Texts ───── */
        .info-text {
            position: absolute;
            top: 0;
            width: 50%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            z-index: 10;
        }
        .info-text.login {
            right: 0;
            text-align: right;
            padding: 0 45px 0 15px;
            pointer-events: none;
        }
        .info-text.login .animation {
            opacity: 0;
            transform: translateX(50px);
            filter: blur(5px);
            animation: slideInRight 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            animation-delay: calc(0.06s * var(--j));
        }
        
        .info-text h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            color: #fff;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .info-text p {
            font-family: 'Sora', sans-serif;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 15px;
            line-height: 1.6;
            letter-spacing: 0.3px;
        }
        
        /* ───── Background slanted layers ───── */
        .main-container .bg-animate {
            position: absolute;
            top: -4px;
            right: 0;
            width: 850px;
            height: 600px;
            background: linear-gradient(45deg, #08040c, #7429ee);
            border-bottom: 3px solid #7429ee;
            transform-origin: bottom right;
            z-index: 5;
            animation: slideBg1 1.6s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }
        
        .main-container .bg-animate2 {
            position: absolute;
            top: 100%;
            left: 250px;
            width: 850px;
            height: 700px;
            background: #08040c;
            border-top: 3px solid #7429ee;
            transform-origin: top left;
            z-index: 5;
            animation: slideBg2 1.6s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }

        /* Govt logo container inside Welcome Side */
        .gov-logo {
            width: 85px;
            height: 85px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            margin-bottom: 20px;
            margin-left: auto;
            margin-right: 0;
            box-shadow: 0 4px 20px rgba(116, 41, 238, 0.4);
            border: 2px solid #7429ee;
        }
        .gov-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            mix-blend-mode: multiply;
        }

        /* Custom Toast Notification */
        .custom-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 4px solid #ef4444;
            color: #fff;
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
            transform: translateX(150%);
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            font-size: 13.5px;
            font-weight: 500;
            max-width: 380px;
        }
        .custom-toast.show {
            transform: translateX(0);
        }
        .custom-toast i {
            font-size: 20px;
            color: #ef4444;
        }

        /* ───── Animation Keyframes ───── */
        @keyframes slideBg1 {
            0% { transform: rotate(0deg) skewY(0deg); }
            100% { transform: rotate(10deg) skewY(40deg); }
        }
        
        @keyframes slideBg2 {
            0% { transform: rotate(0deg) skewY(0deg); }
            100% { transform: rotate(-10deg) skewY(-40deg); }
        }
        
        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
                filter: blur(0);
            }
        }
        
        @keyframes slideInRight {
            to {
                opacity: 1;
                transform: translateX(0);
                filter: blur(0);
            }
        }

        /* ───── Media Queries for Responsive Design ───── */
        @media (max-width: 768px) {
            body {
                padding: 20px;
                overflow-y: auto;
            }
            .main-container {
                width: 100%;
                max-width: 420px;
                height: 520px;
                background: rgba(8, 4, 12, 0.85);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border-color: rgba(116, 41, 238, 0.7);
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            }
            .main-container .form-box {
                width: 100%;
                padding: 0 30px;
            }
            .main-container .form-box.login {
                left: 0;
            }
            .info-text {
                display: none;
            }
            .main-container .bg-animate,
            .main-container .bg-animate2 {
                display: none;
            }
            .form-box h2 {
                font-size: 26px;
            }
            .custom-toast {
                left: 20px;
                right: 20px;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <h1 style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;">Department of Social Services - NWP | Login</h1>

    <main class="main-container" id="loginCard">
        <span class="bg-animate"></span>
        <span class="bg-animate2"></span>

        <section class="form-box login">
            <h2 class="animation" style="--i:0; --j:21;">Sign In</h2>

            <?php if ($error): ?>
                <div class="alert-box animation" role="alert" style="--i:2; --j:23;">
                    <i class='bx bx-error-circle'></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm">
                <div class="input-box animation" style="--i:2; --j:23;">
                    <input type="text" name="username" id="loginUsername" required autocomplete="username">
                    <label>Username</label>
                    <i class='bx bxs-user'></i>
                </div>

                <div class="input-box animation" style="--i:3; --j:24;">
                    <input type="password" name="password" id="loginPassword" required autocomplete="current-password">
                    <label>Password</label>
                    <i class='bx bx-hide' id="togglePassword" style="cursor: pointer;"></i>
                </div>

                <button type="submit" class="btn animation" id="submitBtn" style="--i:4; --j:25;">
                    <span id="btnText">Sign In</span>
                    <i class='bx bx-log-in-circle'></i>
                </button>
            </form>
        </section>

        <article class="info-text login">
            <div class="gov-logo animation" style="--i:0; --j:20;">
                <img src="images/gov_logo.png" alt="NWP Logo">
            </div>
            <h2 class="animation" style="--i:0; --j:20;">WELCOME!</h2>
            <p class="animation" style="--i:1; --j:21;">
                Department of Social Services - NWP.<br>
                Monthly Progress Review System for secure data & tracking.
            </p>
        </article>
    </main>

    <div id="customToast" class="custom-toast">
        <i class='bx bx-info-circle'></i>
        <span id="toastMsg"></span>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');

        // 💡 FIXED: HTML5 validation එක pass වුණොත් විතරක් බටන් එක disable කරන්න හදන logic එක
        loginForm.addEventListener('submit', (e) => {
            if (loginForm.checkValidity()) {
                submitBtn.disabled = true;
                btnText.innerText = 'Connecting...';
            }
        });

        // Password visibility toggles
        const togglePassword = document.getElementById('togglePassword');
        const loginPassword = document.getElementById('loginPassword');
        if (togglePassword && loginPassword) {
            togglePassword.addEventListener('click', () => {
                const isPassword = loginPassword.type === 'password';
                loginPassword.type = isPassword ? 'text' : 'password';
                togglePassword.classList.toggle('bx-hide', !isPassword);
                togglePassword.classList.toggle('bx-show', isPassword);
            });
        }

        // Custom Toast Notification trigger
        function showToast(message, type = 'error') {
            const toast = document.getElementById('customToast');
            const msgSpan = document.getElementById('toastMsg');
            if (!toast || !msgSpan) return;

            msgSpan.innerText = message;
            
            if (type === 'success') {
                toast.style.borderLeftColor = '#10b981';
                const icon = toast.querySelector('i');
                if (icon) {
                    icon.className = 'bx bx-check-circle';
                    icon.style.color = '#10b981';
                }
            } else {
                toast.style.borderLeftColor = '#ef4444';
                const icon = toast.querySelector('i');
                if (icon) {
                    icon.className = 'bx bx-error-circle';
                    icon.style.color = '#ef4444';
                }
            }
            
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>