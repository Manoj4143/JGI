<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>JGI - JAIN INSTITUTE OF TECHNOLOGY &mdash; Timetable Portal</title>
<meta name="description" content="JGI - JAIN INSTITUTE OF TECHNOLOGY &mdash; Academic Timetable & Faculty Orchestration Suite" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
<style>
  :root {
    /* Kinetic Campus Color Tokens */
    --surface: #f8f9ff;
    --surface-dim: #cbdbf5;
    --surface-bright: #f8f9ff;
    --surface-container-lowest: #ffffff;
    --surface-container-low: #eff4ff;
    --surface-container: #e5eeff;
    --surface-container-high: #dce9ff;
    --surface-container-highest: #d3e4fe;
    --on-surface: #0b1c30;
    --on-surface-variant: #404941;
    --inverse-surface: #213145;
    --inverse-on-surface: #eaf1ff;
    --outline: #717970;
    --outline-variant: #c0c9be;
    --surface-tint: #2e6a41;
    --primary: #003b1b;
    --on-primary: #ffffff;
    --primary-container: #14532d;
    --on-primary-container: #87c695;
    --inverse-primary: #96d5a3;
    --secondary: #006c49;
    --on-secondary: #ffffff;
    --secondary-container: #6cf8bb;
    --on-secondary-container: #00714d;
    --tertiary: #492c00;
    --on-tertiary: #ffffff;
    --tertiary-container: #674000;
    --on-tertiary-container: #fda417;
    --error: #ba1a1a;
    --on-error: #ffffff;
    --error-container: #ffdad6;
    --on-error-container: #93000a;
    --primary-fixed: #b1f2be;
    --primary-fixed-dim: #96d5a3;
    --on-primary-fixed: #00210d;
    --on-primary-fixed-variant: #12512c;
    --secondary-fixed: #6ffbbe;
    --secondary-fixed-dim: #4edea3;
    --on-secondary-fixed: #002113;
    --on-secondary-fixed-variant: #005236;
    --tertiary-fixed: #ffddb8;
    --tertiary-fixed-dim: #ffb95f;
    --on-tertiary-fixed: #2a1700;
    --on-tertiary-fixed-variant: #653e00;
    --background: #f8f9ff;
    --on-background: #0b1c30;
    --surface-variant: #d3e4fe;
    
    /* Radii */
    --r-sm: 0.25rem;
    --r-default: 0.5rem;
    --r-md: 0.75rem;
    --r-lg: 1rem;
    --r-xl: 1.5rem;
    --r-full: 9999px;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  html, body {
    height: 100%;
    font-family: 'Inter', sans-serif;
    background: var(--background);
    color: var(--on-surface);
    overflow-x: hidden;
  }

  /* Full Viewport Canvas */
  .viewport-canvas {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: radial-gradient(circle at 10% 10%, #eff4ff 0%, #f8f9ff 50%, #e5eeff 100%);
    position: relative;
  }

  /* Ambient background blobs for subtle depth */
  .canvas-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;
    z-index: 0;
  }
  .cg-1 {
    width: 480px;
    height: 480px;
    top: -100px;
    left: -100px;
    background: rgba(108, 248, 187, 0.25);
  }
  .cg-2 {
    width: 420px;
    height: 420px;
    bottom: -80px;
    right: -80px;
    background: rgba(203, 219, 245, 0.4);
  }

  /* Master Outer Bento Frame (28px radius) */
  .master-shell {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1160px;
    min-height: 640px;
    background: var(--surface-container-lowest);
    border-radius: 28px;
    border: 1px solid rgba(192, 201, 190, 0.55);
    box-shadow: 0px 20px 45px -8px rgba(11, 28, 48, 0.08), 0px 4px 16px rgba(11, 28, 48, 0.03);
    display: flex;
    overflow: hidden;
  }

  /* ===================================================
     LEFT PANEL — Organic Bento Stage with Illustration
     =================================================== */
  .panel-showcase {
    flex: 0 0 54%;
    background: linear-gradient(155deg, #eff4ff 0%, #e5eeff 50%, #dce9ff 100%);
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    padding: 40px;
    overflow: hidden;
    border-right: 1px solid rgba(192, 201, 190, 0.4);
  }

  /* Top micro header on left */
  .showcase-header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 3;
  }
  .brand-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #ffffff;
    padding: 6px 14px 6px 8px;
    border-radius: var(--r-full);
    border: 1px solid rgba(192, 201, 190, 0.5);
    box-shadow: 0 2px 8px rgba(11, 28, 48, 0.04);
  }
  .brand-icon {
    width: 28px;
    height: 28px;
    border-radius: var(--r-full);
    background: var(--primary);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 800;
    font-size: 14px;
    box-shadow: 0 2px 6px rgba(0, 59, 27, 0.3);
  }
  .brand-text-wrap {
    display: flex;
    flex-direction: column;
  }
  .brand-title {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--on-surface);
    letter-spacing: -0.01em;
    line-height: 1.2;
  }
  .brand-subtitle {
    font-size: 10px;
    color: var(--on-surface-variant);
    font-weight: 500;
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    height: 24px;
    border-radius: var(--r-full);
    background: var(--secondary-container);
    color: var(--on-secondary-container);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  .status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--secondary);
    box-shadow: 0 0 0 2px rgba(0, 108, 73, 0.2);
  }

  /* Center stage: organic morphing blob */
  .blob-stage {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 20px 0;
    z-index: 2;
  }

  .organic-blob {
    position: relative;
    width: 360px;
    height: 360px;
    background: linear-gradient(135deg, #b1f2be 0%, #6cf8bb 40%, #d3e4fe 100%);
    border-radius: 60% 40% 55% 45% / 50% 55% 45% 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 20px 40px -10px rgba(0, 108, 73, 0.22), inset 0 0 40px rgba(255, 255, 255, 0.5);
    animation: kineticMorph 9s ease-in-out infinite alternate;
    overflow: hidden;
  }

  @keyframes kineticMorph {
    0%   { border-radius: 60% 40% 55% 45% / 50% 55% 45% 50%; }
    33%  { border-radius: 48% 52% 42% 58% / 58% 42% 58% 42%; }
    66%  { border-radius: 42% 58% 60% 40% / 46% 62% 38% 54%; }
    100% { border-radius: 58% 42% 48% 52% / 52% 48% 52% 48%; }
  }

  .blob-glow-ring {
    position: absolute;
    width: 78%;
    height: 78%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.7) 0%, rgba(203,219,245,0.15) 100%);
    pointer-events: none;
  }

  .blob-img {
    position: relative;
    z-index: 3;
    width: 84%;
    height: 84%;
    object-fit: contain;
    object-position: center center;
    filter: drop-shadow(0 12px 24px rgba(0, 59, 27, 0.15));
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
  .blob-stage:hover .blob-img {
    transform: scale(1.04) translateY(-4px);
  }

  /* Floating micro KPI pill inside left panel */
  .floating-kpi-chip {
    position: absolute;
    bottom: 25px;
    right: 25px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(192, 201, 190, 0.6);
    border-radius: var(--r-full);
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 8px 20px -4px rgba(11, 28, 48, 0.1);
    z-index: 4;
  }
  .kpi-icon {
    font-size: 18px;
    color: var(--secondary);
  }
  .kpi-text {
    font-size: 11px;
    font-weight: 600;
    color: var(--on-surface);
  }
  .kpi-sub {
    font-size: 9px;
    color: var(--on-surface-variant);
  }

  /* Showcase footer text */
  .showcase-footer {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 3;
    font-size: 11px;
    color: var(--on-surface-variant);
  }
  .accent-tag {
    color: var(--primary-container);
    font-weight: 600;
  }

  /* ===================================================
     RIGHT PANEL — Login Console & Role Segment Control
     =================================================== */
  .panel-form {
    flex: 1;
    background: var(--surface-container-lowest);
    padding: 48px 52px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
  }

  .form-header-block {
    margin-bottom: 24px;
  }

  .portal-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: var(--r-full);
    background: var(--surface-container-high);
    color: var(--primary);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.02em;
    margin-bottom: 12px;
  }

  .login-title {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 32px;
    font-weight: 700;
    line-height: 38px;
    letter-spacing: -0.025em;
    color: var(--on-surface);
    margin-bottom: 6px;
  }

  .login-desc {
    font-size: 14px;
    color: var(--on-surface-variant);
    line-height: 20px;
  }

  /* ---- SEGMENTED ROLE TOGGLE PILL (Admin / Faculty / Counsellor) ---- */
  .role-segment-wrap {
    margin-bottom: 24px;
  }
  .role-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--on-surface-variant);
    margin-bottom: 8px;
  }
  .role-segment-capsule {
    display: flex;
    background: var(--surface-container-low);
    border: 1px solid var(--outline-variant);
    border-radius: var(--r-full);
    padding: 4px;
    gap: 4px;
    box-shadow: inset 0 2px 4px rgba(11, 28, 48, 0.03);
  }

  .role-pill-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: var(--r-full);
    border: none;
    background: transparent;
    font-family: 'Inter', sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--on-surface-variant);
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    user-select: none;
  }
  .role-pill-btn .role-icon {
    font-size: 16px;
    transition: transform 0.2s;
  }
  .role-pill-btn:hover {
    color: var(--on-surface);
    background: rgba(255, 255, 255, 0.6);
  }
  .role-pill-btn.active {
    background: var(--surface-container-lowest);
    color: var(--primary);
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(11, 28, 48, 0.08);
    border: 1px solid rgba(192, 201, 190, 0.4);
  }
  .role-pill-btn.active .role-icon {
    color: var(--secondary);
    transform: scale(1.1);
  }

  /* Role hint strip */
  .role-badge-strip {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    padding: 6px 12px;
    border-radius: var(--r-md);
    background: var(--surface-container);
    font-size: 11.5px;
    color: var(--on-surface-variant);
    transition: all 0.2s;
  }
  .role-badge-strip .info-icon {
    font-size: 14px;
    color: var(--secondary);
  }
  .role-badge-strip strong {
    color: var(--primary);
  }

  /* Error alert */
  .error-bento {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--error-container);
    border: 1px solid rgba(186, 26, 26, 0.3);
    border-radius: var(--r-md);
    padding: 10px 16px;
    color: var(--on-error-container);
    font-size: 12.5px;
    font-weight: 500;
    margin-bottom: 20px;
    animation: fadeIn 0.3s ease;
  }
  .error-bento .mat-err {
    font-size: 18px;
    color: var(--error);
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Form Fields */
  .kinetic-form {
    width: 100%;
  }

  .field-group {
    margin-bottom: 16px;
  }
  .field-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    font-weight: 600;
    color: var(--on-surface);
    margin-bottom: 6px;
  }
  .field-hint {
    font-size: 11px;
    color: var(--on-surface-variant);
    font-weight: 400;
  }

  .input-capsule {
    position: relative;
    display: flex;
    align-items: center;
  }
  .input-lead-icon {
    position: absolute;
    left: 16px;
    font-size: 18px;
    color: var(--outline);
    pointer-events: none;
    transition: color 0.2s;
  }

  .kinetic-input {
    width: 100%;
    height: 46px;
    padding: 0 46px 0 44px;
    background: var(--surface-container-low);
    border: 1px solid var(--outline-variant);
    border-radius: var(--r-full);
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    color: var(--on-surface);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  }
  .kinetic-input::placeholder {
    color: var(--outline);
    font-weight: 400;
  }
  .kinetic-input:focus {
    background: var(--surface-container-lowest);
    border-color: var(--secondary);
    box-shadow: 0 0 0 3px rgba(0, 108, 73, 0.15);
  }
  .kinetic-input:focus + .input-lead-icon,
  .input-capsule:focus-within .input-lead-icon {
    color: var(--secondary);
  }

  /* Password eye toggle */
  .pwd-toggle-btn {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    color: var(--outline);
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
    outline: none;
    transition: color 0.2s;
  }
  .pwd-toggle-btn:hover {
    color: var(--on-surface);
  }

  .field-sub-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    font-size: 11.5px;
  }
  .remember-wrap {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--on-surface-variant);
    cursor: pointer;
    user-select: none;
  }
  .remember-cb {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    accent-color: var(--secondary);
    cursor: pointer;
  }
  .forgot-link {
    color: var(--secondary);
    font-weight: 600;
    text-decoration: none;
    transition: opacity 0.2s;
  }
  .forgot-link:hover {
    opacity: 0.8;
    text-decoration: underline;
  }

  /* Submit Button — Kinetic Pine Pill */
  .btn-kinetic-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    height: 48px;
    margin-top: 22px;
    background: var(--primary);
    color: var(--on-primary);
    border: none;
    border-radius: var(--r-full);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: -0.01em;
    cursor: pointer;
    box-shadow: 0 4px 16px -2px rgba(0, 59, 27, 0.35);
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .btn-kinetic-submit:hover {
    background: var(--primary-container);
    box-shadow: 0 8px 24px -2px rgba(0, 59, 27, 0.45);
    transform: translateY(-2px);
  }
  .btn-kinetic-submit:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(0, 59, 27, 0.3);
  }
  .btn-kinetic-submit .btn-arrow {
    font-size: 18px;
    transition: transform 0.2s;
  }
  .btn-kinetic-submit:hover .btn-arrow {
    transform: translateX(3px);
  }

  /* Secondary actions and Admin Settings Link */
  .form-auxiliary-row {
    margin-top: 20px;
    text-align: center;
    font-size: 12px;
    color: var(--on-surface-variant);
  }
  .form-auxiliary-row a {
    color: var(--secondary);
    font-weight: 600;
    text-decoration: none;
  }
  .form-auxiliary-row a:hover {
    text-decoration: underline;
  }

  .admin-settings-prompt {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px dashed var(--outline-variant);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 11px;
    color: var(--on-surface-variant);
  }
  .admin-settings-prompt a {
    color: var(--primary);
    font-weight: 700;
    text-decoration: none;
  }
  .admin-settings-prompt a:hover {
    text-decoration: underline;
  }

  /* Responsive Rules */
  @media (max-width: 992px) {
    .master-shell {
      flex-direction: column;
      max-width: 520px;
      min-height: auto;
    }
    .panel-showcase {
      flex: 0 0 auto;
      padding: 28px 24px;
      border-right: none;
      border-bottom: 1px solid rgba(192, 201, 190, 0.4);
    }
    .organic-blob {
      width: 240px;
      height: 240px;
    }
    .floating-kpi-chip {
      display: none;
    }
    .panel-form {
      padding: 36px 28px;
    }
  }

  @media (max-width: 480px) {
    .viewport-canvas {
      padding: 0.75rem;
    }
    .master-shell {
      border-radius: 20px;
    }
    .login-title {
      font-size: 24px;
      line-height: 30px;
    }
    .panel-form {
      padding: 28px 20px;
    }
    .role-pill-btn {
      padding: 6px 8px;
      font-size: 11px;
    }
  }
</style>
</head>
<?php 
// Suppress notice level outputs
error_reporting(error_reporting() & ~E_NOTICE);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require("../includes/dbconnection.php");

$error = '';
$selectedRole = $_POST['role_type'] ?? ($_GET['role'] ?? 'admin');
if (!in_array($selectedRole, ['admin', 'faculty', 'counsellor'])) {
    $selectedRole = 'admin';
}

// Handle Form Submission
if (isset($_POST['submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['pass'] ?? '');
    $roleType = $_POST['role_type'] ?? 'admin';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both your username and password.';
    } else {
        if (!get_magic_quotes_gpc()) {
            $username = addslashes($username);
        }
        
        $sql = "SELECT * FROM user WHERE username = '" . $username . "' OR email = '" . $username . "'";
        $check = mysqli_query($conn, $sql);
        
        if (!$check || mysqli_num_rows($check) == 0) {
            $error = 'User account not found. Please contact the administrator.';
        } else {
            $userFound = false;
            while ($info = mysqli_fetch_array($check)) {
                $userPass = stripslashes($info['userpass']);
                if ($password === $userPass) {
                    $userFound = true;
                    
                    // Determine authoritative user role from database or selected tab
                    $dbRole = !empty($info['role']) ? ucfirst(strtolower($info['role'])) : (($info['dept_id'] == '4') ? 'Admin' : 'Faculty');
                    $activeRole = ucfirst(strtolower($roleType));
                    if ($activeRole === 'Counsellor') $activeRole = 'Counselor';
                    
                    // If user is Admin in DB, allow Admin access; else prevent unauthorized Admin login
                    if ($activeRole === 'Admin' && $dbRole !== 'Admin') {
                        $error = 'Access denied: This user account does not have Administrator privileges.';
                    } else {
                        // Set session cookies
                        $hour = time() + 3600;
                        setcookie('ID_my_site', $info['username'], $hour, '/');
                        setcookie('Key_my_site', $info['userpass'], $hour, '/');
                        
                        $_SESSION['is'] = [];
                        $_SESSION['is']['login'] = TRUE;
                        $_SESSION['is']['username'] = $info['username'];
                        $_SESSION['is']['dept_id'] = $info['dept_id'];
                        $_SESSION['is']['role'] = $activeRole;
                        if (!empty($info['teacher_id'])) {
                            $_SESSION['is']['teacher_id'] = intval($info['teacher_id']);
                        }

                        // Role-Based Routing
                        if ($activeRole === 'Admin') {
                            header("Location: admin.php");
                            exit;
                        } elseif ($activeRole === 'Faculty') {
                            $targetTeacherId = !empty($info['teacher_id']) ? intval($info['teacher_id']) : 0;
                            if ($targetTeacherId <= 0) {
                                $pCheck = mysqli_query($conn, "SELECT teacher_id FROM profile WHERE email = '" . $username . "' OR teacher_name LIKE '%" . $username . "%' LIMIT 1");
                                if ($pCheck && $pRow = mysqli_fetch_assoc($pCheck)) {
                                    $targetTeacherId = intval($pRow['teacher_id']);
                                    $_SESSION['is']['teacher_id'] = $targetTeacherId;
                                }
                            }

                            if ($targetTeacherId > 0) {
                                header("Location: search_t_result.php?pT=" . $targetTeacherId);
                                exit;
                            } else {
                                header("Location: my_profile.php");
                                exit;
                            }
                        } elseif ($activeRole === 'Counselor') {
                            header("Location: mastertt.php");
                            exit;
                        }
                    }
                    break;
                }
            }
            if (!$userFound && empty($error)) {
                $error = 'Invalid credentials. Please verify your password.';
            }
        }
    }
}

// Detect current login illustration from images/ directory
$imgExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$illustrationSrc = '';
foreach ($imgExtensions as $ext) {
    if (file_exists(dirname(__DIR__) . '/images/login_illustration.' . $ext)) {
        $illustrationSrc = '../images/login_illustration.' . $ext;
        break;
    }
}
?>
<body>

<div class="viewport-canvas">

  <!-- Ambient Glows -->
  <div class="canvas-glow cg-1"></div>
  <div class="canvas-glow cg-2"></div>

  <!-- Master Outer Bento Shell (28px radius) -->
  <div class="master-shell">

    <!-- ===================================================
         LEFT PANEL — Showcase Bento Stage
         =================================================== -->
    <div class="panel-showcase">

      <!-- Header with Logo and Realtime Status Badge -->
      <div class="showcase-header">
        <div class="brand-chip" style="padding: 6px 14px 6px 10px; background: #ffffff; display: flex; align-items: center; gap: 10px; border-radius: var(--r-full); box-shadow: 0 2px 10px rgba(11, 28, 48, 0.06); border: 1px solid rgba(192, 201, 190, 0.5);">
          <img src="../images/jgi_jain_logo.png" alt="JGI - JAIN INSTITUTE OF TECHNOLOGY" style="height: 32px; width: auto; max-width: 180px; object-fit: contain; display: block;" />
        </div>
        <div class="status-badge">
          <span class="status-dot"></span>
          <span>Portal Online</span>
        </div>
      </div>

      <!-- Center Stage: Morphing Organic Pill Blob -->
      <div class="blob-stage">
        <div class="organic-blob" id="organicBlob">
          <div class="blob-glow-ring"></div>
          
          <?php if (!empty($illustrationSrc)): ?>
            <img src="<?php echo htmlspecialchars($illustrationSrc); ?>" alt="Campus Illustration" class="blob-img" id="loginIllustration" />
          <?php else: ?>
            <!-- Inline Kinetic Campus SVG Illustration -->
            <svg class="blob-img" viewBox="0 0 320 320" fill="none" xmlns="http://www.w3.org/2000/svg" id="loginIllustration">
              <ellipse cx="160" cy="275" rx="100" ry="18" fill="rgba(0, 108, 73, 0.15)"/>
              <rect x="148" y="180" width="24" height="95" rx="6" fill="#674000"/>
              <!-- Canopy leaves -->
              <ellipse cx="160" cy="155" rx="72" ry="78" fill="#14532d"/>
              <ellipse cx="160" cy="135" rx="58" ry="64" fill="#006c49"/>
              <ellipse cx="160" cy="115" rx="44" ry="48" fill="#6cf8bb"/>
              <!-- Decorative academic fruit/dots -->
              <circle cx="135" cy="148" r="6" fill="#fda417"/>
              <circle cx="185" cy="140" r="7" fill="#f43f5e"/>
              <circle cx="152" cy="115" r="5" fill="#ffffff"/>
              <circle cx="170" cy="165" r="5" fill="#b1f2be"/>
              <!-- Student figure -->
              <circle cx="105" cy="245" r="9" fill="#0b1c30"/>
              <rect x="99" y="254" width="12" height="22" rx="4" fill="#006c49"/>
              <!-- Counsellor figure -->
              <circle cx="215" cy="248" r="8" fill="#0b1c30"/>
              <rect x="210" y="256" width="10" height="18" rx="3" fill="#14532d"/>
            </svg>
          <?php endif; ?>
        </div>

        <!-- Floating Bento KPI Pill -->
        <div class="floating-kpi-chip">
          <span class="material-symbols-outlined kpi-icon">schedule</span>
          <div>
            <div class="kpi-text">Conflict-Free Engine</div>
            <div class="kpi-sub">Genetic Matrix Alg v2.4</div>
          </div>
        </div>
      </div>

      <!-- Showcase Micro Footer -->
      <div class="showcase-footer">
        <span>Designed for <strong class="accent-tag">Higher Education Excellence</strong></span>
        <span>Secure &bull; 256-Bit</span>
      </div>
    </div>

    <!-- ===================================================
         RIGHT PANEL — Login Console with Segmented Role Pills
         =================================================== -->
    <div class="panel-form">

      <div class="form-header-block">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 10px;">
          <img src="../images/jgi_jain_logo.png" alt="JGI - JAIN INSTITUTE OF TECHNOLOGY" style="height: 38px; width: auto; max-width: 190px; object-fit: contain; background: #ffffff; padding: 4px 8px; border-radius: 8px; border: 1px solid rgba(192, 201, 190, 0.45); box-shadow: 0 1px 4px rgba(11, 28, 48, 0.04);" />
          <div class="portal-pill" style="margin: 0;">
            <span class="material-symbols-outlined" style="font-size:14px;">verified_user</span>
            JIT Gateway
          </div>
        </div>
        <h1 class="login-title">Sign In</h1>
        <p class="login-desc" id="loginSubtitle">Select your role to access your personalized campus workspace.</p>
      </div>

      <!-- Error notification -->
      <?php if (!empty($error)): ?>
      <div class="error-bento" role="alert">
        <span class="material-symbols-outlined mat-err">error</span>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
      <?php endif; ?>

      <!-- Segmented Role Selector Capsule (Admin | Faculty | Counsellor) -->
      <div class="role-segment-wrap">
        <label class="role-label">Choose Workspace Role</label>
        <div class="role-segment-capsule" role="tablist" aria-label="Login Role Selection">
          
          <button type="button" class="role-pill-btn <?php echo ($selectedRole === 'admin') ? 'active' : ''; ?>" data-role="admin" id="roleBtnAdmin" role="tab" aria-selected="<?php echo ($selectedRole === 'admin') ? 'true' : 'false'; ?>">
            <span class="material-symbols-outlined role-icon">admin_panel_settings</span>
            <span>Admin</span>
          </button>

          <button type="button" class="role-pill-btn <?php echo ($selectedRole === 'faculty') ? 'active' : ''; ?>" data-role="faculty" id="roleBtnFaculty" role="tab" aria-selected="<?php echo ($selectedRole === 'faculty') ? 'true' : 'false'; ?>">
            <span class="material-symbols-outlined role-icon">school</span>
            <span>Faculty</span>
          </button>

          <button type="button" class="role-pill-btn <?php echo ($selectedRole === 'counsellor') ? 'active' : ''; ?>" data-role="counsellor" id="roleBtnCounsellor" role="tab" aria-selected="<?php echo ($selectedRole === 'counsellor') ? 'true' : 'false'; ?>">
            <span class="material-symbols-outlined role-icon">support_agent</span>
            <span>Counsellor</span>
          </button>

        </div>

        <!-- Role description badge -->
        <div class="role-badge-strip" id="roleBadgeStrip">
          <span class="material-symbols-outlined info-icon">info</span>
          <span id="roleBadgeText">Signing in as <strong>Administrator</strong>: Master schedule generator, settings & rooms.</span>
        </div>
      </div>

      <!-- Kinetic Form -->
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="kinetic-form" id="kineticLoginForm" autocomplete="off">
        
        <!-- Hidden input storing selected role -->
        <input type="hidden" name="role_type" id="hiddenRoleType" value="<?php echo htmlspecialchars($selectedRole); ?>" />

        <!-- Username Input -->
        <div class="field-group">
          <div class="field-label">
            <span id="userFieldLabel">Username</span>
            <span class="field-hint" id="userFieldHint">e.g. admin or staff ID</span>
          </div>
          <div class="input-capsule">
            <span class="material-symbols-outlined input-lead-icon">person</span>
            <input type="text" id="username" name="username" class="kinetic-input" placeholder="Enter your username" maxlength="50" required autocomplete="username" />
          </div>
        </div>

        <!-- Password Input -->
        <div class="field-group">
          <div class="field-label">
            <span>Password</span>
            <span class="field-hint">Secured Credentials</span>
          </div>
          <div class="input-capsule">
            <span class="material-symbols-outlined input-lead-icon">lock</span>
            <input type="password" id="pass" name="pass" class="kinetic-input" placeholder="Enter your password" maxlength="50" required autocomplete="current-password" />
            <button type="button" class="pwd-toggle-btn" id="pwdToggleBtn" title="Toggle password visibility" aria-label="Toggle password visibility">
              <span class="material-symbols-outlined" id="pwdIcon">visibility_off</span>
            </button>
          </div>
          <div class="field-sub-actions">
            <label class="remember-wrap">
              <input type="checkbox" name="remember" class="remember-cb" />
              <span>Remember this session</span>
            </label>
            <a href="#" class="forgot-link" onclick="alert('Please contact the campus system administrator to reset your credentials.'); return false;">Forgot Password?</a>
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" name="submit" class="btn-kinetic-submit" id="btnKineticSubmit">
          <span id="btnSubmitText">Sign In to Admin Workspace</span>
          <span class="material-symbols-outlined btn-arrow">arrow_forward</span>
        </button>

      </form>

      <!-- Bottom Auxiliary Links -->
      <div class="form-auxiliary-row">
        Need assistance? <a href="mailto:admin@shedulo.edu">Contact Campus Helpdesk</a>
      </div>

      <!-- Admin Image Management Direct Hint -->
      <div class="admin-settings-prompt" id="adminSettingsHint">
        <span class="material-symbols-outlined" style="font-size:15px; color:var(--secondary);">settings</span>
        <span>Admin: Change the login illustration anytime in <a href="settings.php">Admin Settings</a></span>
      </div>

    </div><!-- .panel-form -->

  </div><!-- .master-shell -->

</div><!-- .viewport-canvas -->

<script>
  // Role Configs
  var roleConfigs = {
    'admin': {
      title: 'Sign In to Admin Workspace',
      badge: 'Signing in as <strong>Administrator</strong>: Master schedule generator, settings & rooms.',
      hint: 'e.g. admin or root',
      placeholder: 'Enter administrator username',
      btnText: 'Sign In to Admin Workspace'
    },
    'faculty': {
      title: 'Sign In to Faculty Portal',
      badge: 'Signing in as <strong>Faculty</strong>: Access your personalized timetable with your registered email and password.',
      hint: 'Registered email or faculty ID',
      placeholder: 'Enter your email or username',
      btnText: 'Sign In to Faculty Portal'
    },
    'counsellor': {
      title: 'Sign In to Counsellor Console',
      badge: 'Signing in as <strong>Counsellor</strong>: Real-time schedule lookups & consultation slots.',
      hint: 'e.g. counsellor or staff ID',
      placeholder: 'Enter counsellor username',
      btnText: 'Sign In as Counsellor'
    }
  };

  var hiddenRoleInput = document.getElementById('hiddenRoleType');
  var roleButtons = document.querySelectorAll('.role-pill-btn');
  var roleBadgeText = document.getElementById('roleBadgeText');
  var userFieldHint = document.getElementById('userFieldHint');
  var usernameInput = document.getElementById('username');
  var btnSubmitText = document.getElementById('btnSubmitText');
  var adminSettingsHint = document.getElementById('adminSettingsHint');

  function updateRoleUI(role) {
    if (!roleConfigs[role]) role = 'admin';
    hiddenRoleInput.value = role;

    // Update active tab buttons
    roleButtons.forEach(function(btn) {
      if (btn.getAttribute('data-role') === role) {
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
      } else {
        btn.classList.remove('active');
        btn.setAttribute('aria-selected', 'false');
      }
    });

    // Update dynamic text
    var cfg = roleConfigs[role];
    roleBadgeText.innerHTML = cfg.badge;
    userFieldHint.textContent = cfg.hint;
    usernameInput.placeholder = cfg.placeholder;
    btnSubmitText.textContent = cfg.btnText;

    // Admin hint visibility
    if (role === 'admin') {
      adminSettingsHint.style.display = 'flex';
    } else {
      adminSettingsHint.style.display = 'none';
    }
  }

  // Click handlers for role pills
  roleButtons.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var targetRole = this.getAttribute('data-role');
      updateRoleUI(targetRole);
    });
  });

  // Initialize role on load
  updateRoleUI(hiddenRoleInput.value || 'admin');

  // Password visibility toggle
  var pwdToggleBtn = document.getElementById('pwdToggleBtn');
  var pwdInput = document.getElementById('pass');
  var pwdIcon = document.getElementById('pwdIcon');
  pwdToggleBtn.addEventListener('click', function() {
    if (pwdInput.type === 'password') {
      pwdInput.type = 'text';
      pwdIcon.textContent = 'visibility';
    } else {
      pwdInput.type = 'password';
      pwdIcon.textContent = 'visibility_off';
    }
  });

  // Form submit state
  document.getElementById('kineticLoginForm').addEventListener('submit', function() {
    var btn = document.getElementById('btnKineticSubmit');
    btnSubmitText.textContent = 'Verifying credentials...';
    btn.style.opacity = '0.85';
    btn.style.pointerEvents = 'none';
  });
</script>

</body>
</html>
