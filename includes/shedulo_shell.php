<?php
/**
 * Shared Shedulo UI App Shell (Header + Collapsible Sidebar + Theme Toggle)
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = !empty($_SESSION['is']['username']) ? htmlspecialchars($_SESSION['is']['username']) : 'Admin Manoj';
$userRole = $_SESSION['is']['role'] ?? (($_SESSION['is']['dept_id'] == 4 || ($_SESSION['is']['dept'] ?? 0) == 4) ? 'Admin' : 'Faculty');
$userRole = ucfirst(strtolower($userRole));
if ($userRole === 'Counsellor') $userRole = 'Counselor';
$myTeacherId = intval($_SESSION['is']['teacher_id'] ?? 0);
?>
<!-- Responsive Viewport Meta & Head Protection -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
<meta name="theme-color" content="#0b0f17" />
<script>
if (!document.querySelector('meta[name="viewport"]')) {
  var meta = document.createElement('meta');
  meta.name = 'viewport';
  meta.content = 'width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes';
  document.getElementsByTagName('head')[0].appendChild(meta);
}
</script>
<!-- Google Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

<style>
:root {
  /* Light Mode Kinetic Campus tokens */
  --app-bg: #f8f9ff;
  --app-shell-bg: #ffffff;
  --app-sidebar-bg: #ffffff;
  --app-sidebar-hover: #f1f5f9;
  --app-text: #0b1c30;
  --app-text-muted: #404941;
  --app-border: #e2e8f0;
  --app-primary: #003b1b;
  --app-primary-container: #14532d;
  --app-secondary-container: #6cf8bb;
  --app-on-secondary: #00714d;
  --app-surface-container-low: #eff4ff;
  --app-surface-high: #dce9ff;
  --app-card-bg: #ffffff;
  --app-shadow: rgba(0, 0, 0, 0.05);
}

/* Midnight Command Center Theme System */
html.dark {
  /* Foundation Surfaces */
  --app-bg: #07090e;
  --app-shell-bg: #0b0f17;
  --app-sidebar-bg: #080c14;
  --app-sidebar-hover: #161f33;
  --app-text: #dfe2ee;
  --app-text-muted: #bbcac6;
  --app-border: rgba(255, 255, 255, 0.08);
  
  /* Tactical Accents */
  --app-primary: #4fdbc8;
  --app-primary-container: #14b8a6;
  --app-on-primary: #003731;
  --app-secondary: #d0bcff;
  --app-secondary-container: #571bc1;
  --app-on-secondary: #c4abff;
  --app-tertiary: #ffb0cd;
  --app-tertiary-container: #ff75b2;
  --app-on-tertiary: #760045;
  
  /* Elevated Layers */
  --app-surface-container-lowest: #0a0e16;
  --app-surface-container-low: #111726;
  --app-surface-container: #181c24;
  --app-surface-high: #262a33;
  --app-surface-highest: #31353e;
  --app-card-bg: #111726;
  --app-shadow: rgba(0, 0, 0, 0.7);
  
  /* Luminous Auras */
  --aura-teal: 0 0 24px -4px rgba(20, 184, 166, 0.25), 0 0 1px 1px rgba(20, 184, 166, 0.5);
  --aura-violet: 0 0 24px -4px rgba(139, 92, 246, 0.25), 0 0 1px 1px rgba(139, 92, 246, 0.5);
}

body {
  background-color: var(--app-bg) !important;
  color: var(--app-text) !important;
  font-family: 'Inter', sans-serif !important;
  margin: 0 !important;
  padding: 16px !important;
  min-height: 100vh !important;
  transition: background-color 0.25s ease, color 0.25s ease;
}

/* Master App Container */
#container {
  display: flex !important;
  flex-direction: row !important;
  width: 100% !important;
  max-width: 1540px !important;
  min-height: calc(100vh - 32px) !important;
  margin: 0 auto !important;
  background: var(--app-shell-bg) !important;
  border-radius: 28px !important;
  border: 1px solid var(--app-border) !important;
  box-shadow: 0 10px 40px -10px var(--app-shadow) !important;
  overflow: hidden !important;
  position: relative !important;
}

/* Hide the old header completely */
#header {
  display: none !important;
}

/* =========================================================
   COLLAPSIBLE SIDEBAR: ICON ONLY -> FULL OPEN ON HOVER
   ========================================================= */
.shedulo-sidebar {
  width: 72px; /* Collapsed width */
  min-width: 72px;
  background: var(--app-sidebar-bg);
  border-right: 1px solid var(--app-border);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 20px 12px;
  transition: all 0.32s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 50;
  position: relative;
  overflow: hidden;
  white-space: nowrap;
  user-select: none;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.02);
  backdrop-filter: blur(16px);
}

/* On Mouse Hover: Expand smooth animation with tactical glow */
.shedulo-sidebar:hover {
  width: 250px;
  box-shadow: 12px 0 36px -8px rgba(0, 0, 0, 0.45);
}
html.dark .shedulo-sidebar:hover {
  border-right-color: rgba(79, 219, 200, 0.25);
  box-shadow: 14px 0 40px -4px rgba(0, 0, 0, 0.75), 2px 0 16px -2px rgba(20, 184, 166, 0.2);
}

/* Logo Area */
.shedulo-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 4px 6px;
  margin-bottom: 24px;
  cursor: pointer;
}
.shedulo-logo-icon {
  width: 38px;
  height: 38px;
  min-width: 38px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #07090e;
  font-size: 20px;
  font-weight: 800;
  box-shadow: 0 4px 16px rgba(20, 184, 166, 0.35);
  transition: transform 0.2s ease;
}
.shedulo-sidebar:hover .shedulo-logo-icon {
  transform: scale(1.05);
}
.shedulo-brand-info {
  display: flex;
  flex-direction: column;
  opacity: 0;
  transform: translateX(-10px);
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.shedulo-sidebar:hover .shedulo-brand-info {
  opacity: 1;
  transform: translateX(0);
}
.shedulo-brand-title {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 16px;
  font-weight: 800;
  color: var(--app-text);
  letter-spacing: -0.3px;
  line-height: 1.2;
}
.shedulo-badge-pro {
  font-size: 9px;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 9999px;
  background: var(--app-secondary-container);
  color: var(--app-on-secondary);
  display: inline-block;
  margin-top: 2px;
  width: fit-content;
}

/* Nav Headers */
.sidebar-section-title {
  font-size: 10px;
  font-weight: 800;
  color: var(--app-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 0 10px;
  margin: 14px 0 6px 0;
  opacity: 0;
  transition: opacity 0.2s ease;
}
.shedulo-sidebar:hover .sidebar-section-title {
  opacity: 1;
}

/* Nav Item Links */
.sidebar-nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 12px;
  text-decoration: none !important;
  color: var(--app-text-muted) !important;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 4px;
  transition: all 0.2s ease;
  position: relative;
}
.sidebar-nav-item:hover {
  background: var(--app-sidebar-hover);
  color: var(--app-text) !important;
  transform: translateX(2px);
}
.sidebar-nav-item .material-symbols-outlined {
  font-size: 22px;
  min-width: 24px;
  text-align: center;
  color: inherit;
  transition: transform 0.2s ease, color 0.2s ease;
}
.sidebar-nav-item:hover .material-symbols-outlined {
  transform: scale(1.1);
  color: var(--app-primary) !important;
}
.sidebar-nav-label {
  opacity: 0;
  transform: translateX(-8px);
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.shedulo-sidebar:hover .sidebar-nav-label {
  opacity: 1;
  transform: translateX(0);
}

/* Active Nav Item */
.sidebar-nav-item.active {
  background: var(--app-surface-container-low);
  color: var(--app-primary) !important;
  font-weight: 700;
  border: 1px solid rgba(79, 219, 200, 0.25);
  box-shadow: inset 0 0 12px rgba(20, 184, 166, 0.15);
}
.sidebar-nav-item.active::before {
  content: "";
  position: absolute;
  left: 0;
  top: 8px;
  bottom: 8px;
  width: 4px;
  background: var(--app-primary);
  border-radius: 0 4px 4px 0;
  box-shadow: 0 0 10px var(--app-primary);
}

/* Tooltip on collapsed state */
.sidebar-nav-item[data-tooltip]::after {
  content: attr(data-tooltip);
  position: absolute;
  left: 70px;
  background: #0f172a;
  color: #ffffff;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.15s ease;
  z-index: 100;
}
.shedulo-sidebar:not(:hover) .sidebar-nav-item:hover[data-tooltip]::after {
  opacity: 1;
}

/* Bottom AI Mini Card */
.sidebar-ai-box {
  background: linear-gradient(135deg, #064e3b 0%, #022c22 100%);
  border-radius: 16px;
  padding: 14px;
  color: #ffffff;
  overflow: hidden;
  position: relative;
  opacity: 0;
  transform: translateY(10px);
  transition: opacity 0.25s ease, transform 0.25s ease;
  margin-top: 15px;
}
.shedulo-sidebar:hover .sidebar-ai-box {
  opacity: 1;
  transform: translateY(0);
}
.ai-pulse-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #34d399;
  display: inline-block;
  box-shadow: 0 0 8px #34d399;
}

/* =========================================================
   TOP HEADER BAR: SEARCH + THEME TOGGLE + USER PROFILE
   ========================================================= */
.shedulo-main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  background: var(--app-shell-bg);
  overflow-y: auto;
}

.shedulo-topbar {
  height: 76px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  border-bottom: 1px solid var(--app-border);
  background: var(--app-shell-bg);
  position: sticky;
  top: 0;
  z-index: 40;
}

/* Pill Search */
.topbar-search-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--app-surface-container-low);
  padding: 8px 16px;
  border-radius: 9999px;
  width: 100%;
  max-width: 440px;
  border: 1px solid transparent;
  transition: all 0.2s ease;
}
.topbar-search-pill:focus-within {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
  background: var(--app-shell-bg);
}
.topbar-search-pill input {
  border: none !important;
  background: transparent !important;
  box-shadow: none !important;
  padding: 0 !important;
  color: var(--app-text);
  font-size: 13px;
  width: 100%;
  outline: none;
}

/* Theme Toggle Pill Switch */
.theme-switch-pill {
  display: flex;
  align-items: center;
  background: var(--app-surface-container-low);
  border: 1px solid var(--app-border);
  padding: 3px;
  border-radius: 9999px;
  cursor: pointer;
  user-select: none;
  gap: 2px;
}
.theme-switch-btn {
  border: none;
  background: transparent;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: var(--app-text-muted);
}
.theme-switch-btn.active {
  background: #ffffff;
  color: #0f172a;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}
html.dark .theme-switch-btn.active {
  background: #1e293b;
  color: #f8fafc;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
}

/* User Avatar Pill */
.topbar-user-chip {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 4px 8px;
  border-radius: 9999px;
  cursor: pointer;
}
.topbar-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
}

/* Override existing Content styling to match clean Donezo app surface */
#content {
  background: transparent !important;
  width: 100% !important;
  max-width: 100% !important;
  margin: 0 !important;
  padding: 24px 28px 40px 28px !important;
  box-sizing: border-box !important;
}

/* Dark mode overrides for Timetable cards & grids */
html.dark .tt-grid {
  border-color: #232b38 !important;
  background: #101520 !important;
}
html.dark .tt-grid th {
  background: #0b0f17 !important;
  border-bottom-color: #1e2532 !important;
  border-right-color: #151b24 !important;
}
html.dark .tt-day-col {
  background: #0f141d !important;
  color: #f1f5f9 !important;
  border-right-color: #232b38 !important;
}
html.dark .tt-grid td {
  border-color: #1a2230 !important;
}
html.dark .tt-grid tbody tr:hover {
  background-color: #131924 !important;
}
html.dark .card-lecture {
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
}
html.dark .card-theory {
  background: #0d1e38 !important;
  border-color: #1e3a8a !important;
}
html.dark .card-lab {
  background: #062b1d !important;
  border-color: #047857 !important;
}
html.dark .card-nss {
  background: #2b1f06 !important;
  border-color: #b45309 !important;
}
html.dark .card-project {
  background: #2c0b1f !important;
  border-color: #be185d !important;
}
html.dark .card-credit {
  background: #1c113b !important;
  border-color: #6d28d9 !important;
}
html.dark .card-name {
  color: #cbd5e1 !important;
}
html.dark .tt-hero-banner {
  background: #121824 !important;
  border-color: #1e293b !important;
}
html.dark .form-panel {
  background: #121824 !important;
  border-color: #1e293b !important;
}
html.dark input[type="text"],
html.dark input[type="password"],
html.dark input[type="number"],
html.dark select,
html.dark textarea {
  background-color: #0c1017 !important;
  border-color: #232d3d !important;
  color: #f1f5f9 !important;
}
html.dark #footerline,
html.dark #footer {
  background: #0c1017 !important;
  border-top-color: #1e293b !important;
  color: #64748b !important;
}

/* Neon KPI Cards Grid (Affiliate / Stitch Dark Style) */
.kpi-neon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 18px;
  margin-top: 20px;
}
.kpi-neon-card {
  border-radius: 16px;
  padding: 22px 24px;
  position: relative;
  overflow: hidden;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.kpi-neon-card:hover {
  transform: translateY(-3px);
}
.kpi-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.kpi-icon-pill {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.kpi-icon-pill .material-symbols-outlined {
  font-size: 20px;
}
.kpi-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 9999px;
  backdrop-filter: blur(4px);
}
.kpi-label {
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 4px;
  opacity: 0.9;
}
.kpi-number {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 32px;
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.5px;
}
.kpi-subtext {
  font-size: 11.5px;
  margin-top: 6px;
  opacity: 0.75;
}

/* Specific Card Palettes - Light Mode */
.kpi-teal {
  background: linear-gradient(135deg, #0d9488 0%, #059669 100%);
  color: #ffffff;
}
.kpi-teal .kpi-icon-pill { background: rgba(255, 255, 255, 0.2); color: #ffffff; }
.kpi-teal .kpi-badge { background: rgba(255, 255, 255, 0.25); color: #ffffff; }

.kpi-purple {
  background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
  color: #ffffff;
}
.kpi-purple .kpi-icon-pill { background: rgba(255, 255, 255, 0.2); color: #ffffff; }
.kpi-purple .kpi-badge { background: rgba(255, 255, 255, 0.25); color: #ffffff; }

.kpi-orange {
  background: linear-gradient(135deg, #ea580c 0%, #d97706 100%);
  color: #ffffff;
}
.kpi-orange .kpi-icon-pill { background: rgba(255, 255, 255, 0.2); color: #ffffff; }
.kpi-orange .kpi-badge { background: rgba(255, 255, 255, 0.25); color: #ffffff; }

.kpi-pink {
  background: linear-gradient(135deg, #db2777 0%, #be185d 100%);
  color: #ffffff;
}
.kpi-pink .kpi-icon-pill { background: rgba(255, 255, 255, 0.2); color: #ffffff; }
.kpi-pink .kpi-badge { background: rgba(255, 255, 255, 0.25); color: #ffffff; }

/* Dark Mode Glowing Neon Palettes (Directly from User Screenshot) */
html.dark .kpi-teal {
  background: linear-gradient(135deg, #0f4c47 0%, #0a3532 100%) !important;
  border: 1px solid #14b8a6 !important;
  color: #e6fffa !important;
  box-shadow: 0 4px 20px rgba(20, 184, 166, 0.25) !important;
}
html.dark .kpi-teal .kpi-icon-pill { background: #14b8a6; color: #042f2e; }
html.dark .kpi-teal .kpi-badge { background: rgba(20, 184, 166, 0.25); color: #5eead4; }

html.dark .kpi-purple {
  background: linear-gradient(135deg, #372b6b 0%, #241a4a 100%) !important;
  border: 1px solid #8b5cf6 !important;
  color: #f5f3ff !important;
  box-shadow: 0 4px 20px rgba(139, 92, 246, 0.25) !important;
}
html.dark .kpi-purple .kpi-icon-pill { background: #8b5cf6; color: #1e1035; }
html.dark .kpi-purple .kpi-badge { background: rgba(139, 92, 246, 0.25); color: #c4b5fd; }

html.dark .kpi-orange {
  background: linear-gradient(135deg, #59351e 0%, #3d2314 100%) !important;
  border: 1px solid #f97316 !important;
  color: #fff7ed !important;
  box-shadow: 0 4px 20px rgba(249, 115, 22, 0.25) !important;
}
html.dark .kpi-orange .kpi-icon-pill { background: #f97316; color: #431407; }
html.dark .kpi-orange .kpi-badge { background: rgba(249, 115, 22, 0.25); color: #fdba74; }

html.dark .kpi-pink {
  background: linear-gradient(135deg, #5b1a3d 0%, #3e1129 100%) !important;
  border: 1px solid #ec4899 !important;
  color: #fdf2f8 !important;
  box-shadow: 0 4px 20px rgba(236, 72, 153, 0.25) !important;
}
html.dark .kpi-pink .kpi-icon-pill { background: #ec4899; color: #500724; }
html.dark .kpi-pink .kpi-badge { background: rgba(236, 72, 153, 0.25); color: #f472b6; }

/* Admin Feature Cards */
.admin-feature-card {
  background: var(--app-card-bg);
  border: 1px solid var(--app-border);
  border-radius: 14px;
  padding: 22px;
  box-shadow: 0 4px 14px var(--app-shadow);
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
}
.admin-feature-card:hover {
  transform: translateY(-2px);
  border-color: #10b981;
}
.admin-feature-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14px;
}
.admin-feature-title {
  margin: 0 0 8px 0;
  font-size: 15.5px;
  color: var(--app-text);
  font-weight: 700;
}
.admin-feature-desc {
  color: var(--app-text-muted);
  font-size: 12.5px;
  margin-bottom: 16px;
  line-height: 1.5;
  flex: 1;
}
.admin-feature-link {
  color: #2563eb;
  font-weight: 700;
  font-size: 12.5px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
html.dark .admin-feature-link {
  color: #38bdf8;
}

/* =========================================================
   MOBILE RESPONSIVENESS & TOUCH OPTIMIZATIONS
   ========================================================= */
.topbar-hamburger-btn {
  display: none;
  background: transparent;
  border: 1px solid var(--app-border);
  border-radius: 8px;
  color: var(--app-text);
  cursor: pointer;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  transition: all 0.2s ease;
  user-select: none;
}
.topbar-hamburger-btn:active {
  transform: scale(0.92);
}
.topbar-mobile-brand {
  display: none;
  align-items: center;
  gap: 8px;
}
.sidebar-mobile-close-row {
  display: none;
  align-items: center;
  justify-content: space-between;
  padding: 0 4px 14px 4px;
  border-bottom: 1px solid var(--app-border);
  margin-bottom: 16px;
}
.sidebar-mobile-close-btn {
  background: transparent;
  border: 1px solid var(--app-border);
  border-radius: 8px;
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--app-text);
}
.shedulo-mobile-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 9998;
  opacity: 0;
  transition: opacity 0.25s ease;
}
.shedulo-mobile-backdrop.active {
  display: block;
  opacity: 1;
}
.shedulo-mobile-nav {
  display: none;
}

@media (max-width: 768px) {
  body {
    padding: 0 !important;
    background-color: var(--app-bg) !important;
  }
  #container {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    border-radius: 0 !important;
    border: none !important;
    box-shadow: none !important;
    min-height: 100vh !important;
    flex-direction: column !important;
  }
  .shedulo-sidebar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    bottom: 0 !important;
    width: 290px !important;
    max-width: 86vw !important;
    z-index: 9999 !important;
    transform: translateX(-100%) !important;
    transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 12px 0 35px rgba(0, 0, 0, 0.35) !important;
    background: var(--app-sidebar-bg) !important;
    overflow-y: auto !important;
    padding: 18px 14px !important;
  }
  .shedulo-sidebar.mobile-open {
    transform: translateX(0) !important;
  }
  .sidebar-mobile-close-row {
    display: flex !important;
  }
  .shedulo-sidebar .sidebar-nav-label {
    opacity: 1 !important;
    transform: none !important;
  }
  .shedulo-sidebar .sidebar-section-title {
    opacity: 1 !important;
  }
  .shedulo-sidebar .sidebar-ai-box {
    opacity: 1 !important;
    transform: none !important;
  }
  .topbar-hamburger-btn {
    display: flex !important;
  }
  .topbar-mobile-brand {
    display: flex !important;
  }
  .shedulo-topbar {
    height: 60px !important;
    padding: 0 14px !important;
  }
  .topbar-search-pill {
    display: none !important;
  }
  .topbar-user-chip > div:last-child {
    display: none !important;
  }
  .topbar-user-chip .topbar-avatar {
    width: 32px !important;
    height: 32px !important;
    font-size: 13px !important;
  }
  #content {
    padding: 14px 14px 85px 14px !important; /* Buffer for bottom nav */
  }
  .tt-hero-banner {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 12px !important;
    padding: 16px !important;
  }
  .tt-hero-banner > div:last-child {
    width: 100% !important;
  }
  .tt-hero-banner a, .tt-hero-banner button {
    width: 100% !important;
    justify-content: center !important;
  }
  .shedulo-mobile-nav {
    display: flex !important;
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    height: 62px !important;
    background: var(--app-shell-bg) !important;
    border-top: 1px solid var(--app-border) !important;
    box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08) !important;
    z-index: 1000 !important;
    align-items: center !important;
    justify-content: space-around !important;
    padding: 0 6px !important;
    padding-bottom: env(safe-area-inset-bottom, 0px) !important;
  }
  .mobile-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    color: var(--app-text-muted) !important;
    font-size: 10px;
    font-weight: 600;
    gap: 2px;
    padding: 6px 4px;
    border-radius: 8px;
    flex: 1;
    text-align: center;
  }
  .mobile-nav-item.active {
    color: #10b981 !important;
    font-weight: 700;
  }
  .mobile-nav-item .material-symbols-outlined {
    font-size: 22px;
  }
  .mobile-nav-item:active {
    transform: scale(0.92);
  }
}
</style>

<!-- Mobile Overlay Backdrop -->
<div class="shedulo-mobile-backdrop" id="sheduloMobileBackdrop" onclick="toggleMobileSidebar()"></div>

<!-- =========================================================
     COLLAPSIBLE ANIMATED SIDEBAR (MOBILE DRAWER ON PHONES)
     ========================================================= -->
<aside class="shedulo-sidebar" id="sheduloSidebar">
  <div class="sidebar-top-group">
    
    <!-- Mobile Drawer Close Row -->
    <div class="sidebar-mobile-close-row">
      <div style="display: flex; align-items: center; gap: 8px;">
        <img src="../images/jgi_jain_logo.png" alt="JGI - JAIN INSTITUTE OF TECHNOLOGY" style="height: 26px; width: auto; max-width: 130px; object-fit: contain; background: #ffffff; padding: 2px 6px; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);" />
        <span style="font-size: 12.5px; font-weight: 800; color: var(--app-text);">JIT Portal Menu</span>
      </div>
      <button type="button" onclick="toggleMobileSidebar()" class="sidebar-mobile-close-btn" aria-label="Close Navigation Menu">
        <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
      </button>
    </div>

    <!-- Brand Logo -->
    <a href="<?php echo ($userRole === 'Faculty') ? (($myTeacherId > 0) ? 'search_t_result.php?pT='.$myTeacherId : 'my_profile.php') : (($userRole === 'Counselor') ? 'mastertt.php' : 'admin.php'); ?>" class="shedulo-brand" style="text-decoration: none;">
      <div class="shedulo-logo-icon" style="background: #0b132b; color: #ffffff; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 14px; position: relative; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.3); border: 1.5px solid rgba(255,255,255,0.15);">
        JGi
        <span style="position: absolute; top: 6px; right: 7px; width: 4.5px; height: 4.5px; background: #f59e0b; border-radius: 50%;"></span>
      </div>
      <div class="shedulo-brand-info">
        <div style="background: #ffffff; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
          <img src="../images/jgi_jain_logo.png" alt="JGI - JAIN INSTITUTE OF TECHNOLOGY" style="height: 22px; width: auto; max-width: 140px; object-fit: contain; display: block;" />
        </div>
        <span class="shedulo-badge-pro" style="margin-top: 4px;"><?php echo strtoupper($userRole); ?></span>
      </div>
    </a>

    <!-- Dashboard Item -->
    <div class="sidebar-section-title">PORTAL</div>
    <nav>
      <?php if ($userRole === 'Admin'): ?>
      <a href="admin.php" class="sidebar-nav-item <?php if ($current_page == 'admin.php') echo 'active'; ?>" data-tooltip="Dashboard">
        <span class="material-symbols-outlined">dashboard</span>
        <span class="sidebar-nav-label">Dashboard</span>
      </a>
      <?php elseif ($userRole === 'Faculty'): ?>
      <a href="<?php echo ($myTeacherId > 0) ? 'search_t_result.php?pT='.$myTeacherId : 'my_profile.php'; ?>" class="sidebar-nav-item <?php if (in_array($current_page, ['search_t_result.php', 'my_profile.php'])) echo 'active'; ?>" data-tooltip="My Timetable">
        <span class="material-symbols-outlined">dashboard</span>
        <span class="sidebar-nav-label">My Timetable</span>
      </a>
      <?php else: ?>
      <a href="mastertt.php" class="sidebar-nav-item <?php if ($current_page == 'mastertt.php') echo 'active'; ?>" data-tooltip="Campus Schedules">
        <span class="material-symbols-outlined">dashboard</span>
        <span class="sidebar-nav-label">Counselor Console</span>
      </a>
      <?php endif; ?>
    </nav>

    <!-- Student Section (Directly below Dashboard) -->
    <div class="sidebar-section-title">STUDENT</div>
    <nav>
      <a href="search_course.php" class="sidebar-nav-item <?php if (in_array($current_page, ['search_course.php', 'search_g_result.php'])) echo 'active'; ?>" data-tooltip="Student Schedules">
        <span class="material-symbols-outlined">groups</span>
        <span class="sidebar-nav-label">Student Schedules</span>
      </a>

      <a href="search_room.php" class="sidebar-nav-item <?php if (in_array($current_page, ['search_room.php', 'search_r_result.php'])) echo 'active'; ?>" data-tooltip="Rooms & Allocations">
        <span class="material-symbols-outlined">meeting_room</span>
        <span class="sidebar-nav-label">Rooms</span>
      </a>

      <a href="subjectlist-a.php" class="sidebar-nav-item <?php if (in_array($current_page, ['subject-a.php', 'subjectlist-a.php', 'subject-edit-a.php'])) echo 'active'; ?>" data-tooltip="Subjects Directory">
        <span class="material-symbols-outlined">menu_book</span>
        <span class="sidebar-nav-label">Subjects</span>
      </a>

      <a href="mastertt.php" class="sidebar-nav-item <?php if ($current_page == 'mastertt.php') echo 'active'; ?>" data-tooltip="Master Campus Timetable">
        <span class="material-symbols-outlined">calendar_month</span>
        <span class="sidebar-nav-label">Master Timetable</span>
      </a>
    </nav>

    <!-- Faculty Section (Directly below Student) -->
    <div class="sidebar-section-title">FACULTY</div>
    <nav>
      <a href="facultylist-a.php" class="sidebar-nav-item <?php if (in_array($current_page, ['faculty-a.php', 'facultylist-a.php', 'faculty-edit-a.php'])) echo 'active'; ?>" data-tooltip="Teacher List">
        <span class="material-symbols-outlined">badge</span>
        <span class="sidebar-nav-label">Teacher List</span>
      </a>

      <?php if ($userRole === 'Faculty'): ?>
        <a href="search_t_result.php?pT=<?php echo $myTeacherId; ?>" class="sidebar-nav-item <?php if ($current_page == 'search_t_result.php') echo 'active'; ?>" data-tooltip="My Teaching Schedule">
          <span class="material-symbols-outlined">calendar_today</span>
          <span class="sidebar-nav-label">Faculty Schedule</span>
        </a>
        <a href="request_timing.php" class="sidebar-nav-item <?php if ($current_page == 'request_timing.php') echo 'active'; ?>" data-tooltip="Request Timing Adjustment">
          <span class="material-symbols-outlined">edit_calendar</span>
          <span class="sidebar-nav-label">Faculty Timing</span>
        </a>
        <a href="my_profile.php" class="sidebar-nav-item <?php if ($current_page == 'my_profile.php') echo 'active'; ?>" data-tooltip="My Faculty Profile">
          <span class="material-symbols-outlined">person</span>
          <span class="sidebar-nav-label">My Profile</span>
        </a>
      <?php elseif ($userRole === 'Admin'): ?>
        <a href="search_teacher.php" class="sidebar-nav-item <?php if (in_array($current_page, ['search_teacher.php', 'search_t_result.php'])) echo 'active'; ?>" data-tooltip="Faculty Schedules Matrix">
          <span class="material-symbols-outlined">calendar_today</span>
          <span class="sidebar-nav-label">Faculty Schedule</span>
        </a>
        <a href="faculty_timing.php" class="sidebar-nav-item <?php if ($current_page == 'faculty_timing.php') echo 'active'; ?>" data-tooltip="Faculty Unavailability Matrix">
          <span class="material-symbols-outlined">alarm_on</span>
          <span class="sidebar-nav-label">Faculty Timing</span>
        </a>
        <a href="timing_requests_admin.php" class="sidebar-nav-item <?php if ($current_page == 'timing_requests_admin.php') echo 'active'; ?>" data-tooltip="Faculty Timing Change Requests">
          <span class="material-symbols-outlined">rate_review</span>
          <span class="sidebar-nav-label">Timing Requests</span>
        </a>
      <?php else: /* Counselor */ ?>
        <a href="search_teacher.php" class="sidebar-nav-item <?php if (in_array($current_page, ['search_teacher.php', 'search_t_result.php'])) echo 'active'; ?>" data-tooltip="Faculty Schedules Lookup">
          <span class="material-symbols-outlined">calendar_today</span>
          <span class="sidebar-nav-label">Faculty Schedule</span>
        </a>
      <?php endif; ?>
    </nav>

    <?php if ($userRole === 'Admin'): ?>
    <!-- Administration Section (Admin Specialty Only) -->
    <div class="sidebar-section-title">ADMINISTRATION</div>
    <nav>
      <a href="faculty_details.php" class="sidebar-nav-item <?php if ($current_page == 'faculty_details.php') echo 'active'; ?>" data-tooltip="Faculty Salaries & Qualifications" style="color: #6cf8bb !important;">
        <span class="material-symbols-outlined">payments</span>
        <span class="sidebar-nav-label">Faculty Details</span>
      </a>
      <a href="faculty_access.php" class="sidebar-nav-item <?php if ($current_page == 'faculty_access.php') echo 'active'; ?>" data-tooltip="Faculty Login Credentials">
        <span class="material-symbols-outlined">key</span>
        <span class="sidebar-nav-label">Faculty Access</span>
      </a>
      <a href="deptlist-a.php" class="sidebar-nav-item <?php if (in_array($current_page, ['dept-a.php', 'deptlist-a.php', 'dept-edit-a.php'])) echo 'active'; ?>" data-tooltip="Academic Departments">
        <span class="material-symbols-outlined">domain</span>
        <span class="sidebar-nav-label">Departments</span>
      </a>
      <a href="student-list-a.php" class="sidebar-nav-item <?php if (in_array($current_page, ['student-a.php', 'student-list-a.php', 'student-edit-a.php'])) echo 'active'; ?>" data-tooltip="Student Courses & Batches">
        <span class="material-symbols-outlined">school</span>
        <span class="sidebar-nav-label">Student Courses</span>
      </a>
      <a href="roomlist-a.php" class="sidebar-nav-item <?php if (in_array($current_page, ['room-a.php', 'roomlist-a.php', 'room-edit-a.php'])) echo 'active'; ?>" data-tooltip="Classrooms & Labs">
        <span class="material-symbols-outlined">meeting_room</span>
        <span class="sidebar-nav-label">Classrooms / Labs</span>
      </a>
      <a href="yearlist-a.php" class="sidebar-nav-item <?php if (in_array($current_page, ['year-a.php', 'yearlist-a.php', 'year-edit-a.php'])) echo 'active'; ?>" data-tooltip="Academic School Years">
        <span class="material-symbols-outlined">date_range</span>
        <span class="sidebar-nav-label">School Years</span>
      </a>
      <a href="userlist.php" class="sidebar-nav-item <?php if (in_array($current_page, ['user.php', 'userlist.php', 'user-edit.php'])) echo 'active'; ?>" data-tooltip="User Accounts & Roles">
        <span class="material-symbols-outlined">manage_accounts</span>
        <span class="sidebar-nav-label">User Accounts</span>
      </a>
      <a href="settings.php" class="sidebar-nav-item <?php if ($current_page == 'settings.php') echo 'active'; ?>" data-tooltip="Admin Settings">
        <span class="material-symbols-outlined">settings</span>
        <span class="sidebar-nav-label">Settings</span>
      </a>
    </nav>
    <?php endif; ?>

    <!-- General Section -->
    <div class="sidebar-section-title">GENERAL</div>
    <nav>
      <a href="help.php" class="sidebar-nav-item <?php if ($current_page == 'help.php') echo 'active'; ?>" data-tooltip="User Manual & Docs">
        <span class="material-symbols-outlined">description</span>
        <span class="sidebar-nav-label">User Manual</span>
      </a>
      <a href="about_dev.php" class="sidebar-nav-item <?php if ($current_page == 'about_dev.php') echo 'active'; ?>" data-tooltip="About Developer">
        <span class="material-symbols-outlined">terminal</span>
        <span class="sidebar-nav-label">About Dev</span>
      </a>
      <a href="logout.php" class="sidebar-nav-item" style="color: #ef4444 !important;" data-tooltip="Log Out">
        <span class="material-symbols-outlined">logout</span>
        <span class="sidebar-nav-label">Log Out</span>
      </a>
    </nav>
  </div>

  <!-- Bottom AI Status Card (Appears on Hover) -->
  <div class="sidebar-ai-box">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
      <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 700; color: #34d399; text-transform: uppercase;">
        <span class="ai-pulse-dot"></span> AI Active
      </span>
      <span class="material-symbols-outlined" style="font-size: 16px; color: #a7f3d0;">auto_awesome</span>
    </div>
    <div style="font-size: 12px; font-weight: 700; line-height: 1.3;">Clash Engine Active</div>
    <div style="font-size: 10.5px; color: #a7f3d0; margin-top: 3px; line-height: 1.3;">6 Days &bull; Batch Splitting &bull; 0 Conflicts</div>
  </div>
</aside>

<!-- =========================================================
     MAIN APP WRAPPER: TOPBAR + CONTENT INJECTION
     ========================================================= -->
<div class="shedulo-main-wrapper">
  <header class="shedulo-topbar">
    <!-- Left: Mobile Hamburger & Brand or Desktop Search -->
    <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
      <!-- Mobile Hamburger Button -->
      <button type="button" class="topbar-hamburger-btn" onclick="toggleMobileSidebar()" aria-label="Open Navigation Menu">
        <span class="material-symbols-outlined">menu</span>
      </button>
      
      <!-- Mobile Brand Logo -->
      <div class="topbar-mobile-brand" style="display: flex; align-items: center; gap: 8px;">
        <img src="../images/jgi_jain_logo.png" alt="JGI - JAIN INSTITUTE OF TECHNOLOGY" style="height: 28px; width: auto; max-width: 140px; object-fit: contain; background: #ffffff; padding: 2px 6px; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);" />
        <span class="badge-pill badge-green" style="font-size: 9.5px; padding: 2px 6px;"><?php echo strtoupper($userRole); ?></span>
      </div>

      <!-- Desktop Search Bar -->
      <div class="topbar-search-pill">
        <span class="material-symbols-outlined" style="font-size: 18px; color: var(--app-text-muted);">search</span>
        <input type="text" placeholder="Search courses, teachers, rooms..." id="globalSearchInput" onkeyup="filterTimetableTable(this.value)">
        <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: var(--app-shell-bg); color: var(--app-text-muted); border: 1px solid var(--app-border);">⌘F</span>
      </div>
    </div>

    <!-- Actions: Dark/Light Mode Switch + Notification + User -->
    <div style="display: flex; align-items: center; gap: 10px;">
      <!-- DARK / LIGHT THEME TOGGLE -->
      <div class="theme-switch-pill" id="themeSwitchPill" title="Toggle Light / Dark Mode">
        <button type="button" class="theme-switch-btn" id="btnThemeLight" onclick="setAppTheme('light')">☀️</button>
        <button type="button" class="theme-switch-btn" id="btnThemeDark" onclick="setAppTheme('dark')">🌙</button>
      </div>

      <!-- Quick Notification -->
      <a href="mastertt.php" style="width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--app-surface-container-low); text-decoration: none; color: var(--app-text-muted); position: relative;" title="Collisions Check">
        <span class="material-symbols-outlined" style="font-size: 18px;">notifications</span>
        <span style="position: absolute; top: 6px; right: 6px; width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
      </a>

      <!-- Profile Chip -->
      <div class="topbar-user-chip">
        <div class="topbar-avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
        <div style="display: flex; flex-direction: column;">
          <span style="font-size: 12.5px; font-weight: 700; color: var(--app-text); line-height: 1.2;"><?php echo $admin_name; ?></span>
          <span style="font-size: 10.5px; color: var(--app-text-muted);">
            <?php 
              $roleLabel = 'User';
              if (isset($_SESSION['is']['role'])) {
                $roleLabel = ucfirst($_SESSION['is']['role']);
              } elseif (isset($_SESSION['is']['dept_id']) && $_SESSION['is']['dept_id'] == '4') {
                $roleLabel = 'Administrator';
              }
              echo htmlspecialchars($roleLabel);
            ?>
          </span>
        </div>
      </div>
    </div>
  </header>

  <!-- Sticky Mobile Bottom Navigation Bar (Visible only on mobile) -->
  <nav class="shedulo-mobile-nav">
    <?php if ($userRole === 'Admin'): ?>
      <a href="admin.php" class="mobile-nav-item <?php if ($current_page == 'admin.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">dashboard</span>
        <span>Home</span>
      </a>
      <a href="mastertt.php" class="mobile-nav-item <?php if ($current_page == 'mastertt.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">calendar_month</span>
        <span>Master TT</span>
      </a>
      <a href="search_course.php" class="mobile-nav-item <?php if (in_array($current_page, ['search_course.php', 'search_g_result.php'])) echo 'active'; ?>">
        <span class="material-symbols-outlined">groups</span>
        <span>Students</span>
      </a>
      <a href="timing_requests_admin.php" class="mobile-nav-item <?php if ($current_page == 'timing_requests_admin.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">rate_review</span>
        <span>Requests</span>
      </a>
      <button type="button" class="mobile-nav-item" onclick="toggleMobileSidebar()" style="background: none; border: none; cursor: pointer;">
        <span class="material-symbols-outlined">menu</span>
        <span>Menu</span>
      </button>
    <?php elseif ($userRole === 'Faculty'): ?>
      <a href="<?php echo ($myTeacherId > 0) ? 'search_t_result.php?pT='.$myTeacherId : 'my_profile.php'; ?>" class="mobile-nav-item <?php if ($current_page == 'search_t_result.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">calendar_today</span>
        <span>Schedule</span>
      </a>
      <a href="request_timing.php" class="mobile-nav-item <?php if ($current_page == 'request_timing.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">edit_calendar</span>
        <span>Timing</span>
      </a>
      <a href="search_course.php" class="mobile-nav-item <?php if (in_array($current_page, ['search_course.php', 'search_g_result.php'])) echo 'active'; ?>">
        <span class="material-symbols-outlined">groups</span>
        <span>Classes</span>
      </a>
      <a href="my_profile.php" class="mobile-nav-item <?php if ($current_page == 'my_profile.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">person</span>
        <span>Profile</span>
      </a>
      <button type="button" class="mobile-nav-item" onclick="toggleMobileSidebar()" style="background: none; border: none; cursor: pointer;">
        <span class="material-symbols-outlined">menu</span>
        <span>Menu</span>
      </button>
    <?php else: /* Counselor */ ?>
      <a href="mastertt.php" class="mobile-nav-item <?php if ($current_page == 'mastertt.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">calendar_month</span>
        <span>Campus</span>
      </a>
      <a href="search_course.php" class="mobile-nav-item <?php if ($current_page == 'search_course.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">groups</span>
        <span>Students</span>
      </a>
      <a href="search_teacher.php" class="mobile-nav-item <?php if ($current_page == 'search_teacher.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">badge</span>
        <span>Faculty</span>
      </a>
      <a href="search_room.php" class="mobile-nav-item <?php if ($current_page == 'search_room.php') echo 'active'; ?>">
        <span class="material-symbols-outlined">meeting_room</span>
        <span>Rooms</span>
      </a>
      <button type="button" class="mobile-nav-item" onclick="toggleMobileSidebar()" style="background: none; border: none; cursor: pointer;">
        <span class="material-symbols-outlined">menu</span>
        <span>Menu</span>
      </button>
    <?php endif; ?>
  </nav>

  <!-- Real-time Theme Handler, Mobile Drawer Toggle & Search Script -->
  <script>
  function toggleMobileSidebar() {
    var sb = document.getElementById('sheduloSidebar');
    var bd = document.getElementById('sheduloMobileBackdrop');
    if (sb) {
      sb.classList.toggle('mobile-open');
    }
    if (bd) {
      bd.classList.toggle('active');
    }
  }

  // Close drawer on Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      var sb = document.getElementById('sheduloSidebar');
      var bd = document.getElementById('sheduloMobileBackdrop');
      if (sb && sb.classList.contains('mobile-open')) {
        sb.classList.remove('mobile-open');
      }
      if (bd && bd.classList.contains('active')) {
        bd.classList.remove('active');
      }
    }
  });

  function setAppTheme(theme) {
    const html = document.documentElement;
    const btnLight = document.getElementById('btnThemeLight');
    const btnDark = document.getElementById('btnThemeDark');

    if (theme === 'dark') {
      html.classList.add('dark');
      btnDark.classList.add('active');
      btnLight.classList.remove('active');
      localStorage.setItem('shedulo_theme', 'dark');
    } else {
      html.classList.remove('dark');
      btnLight.classList.add('active');
      btnDark.classList.remove('active');
      localStorage.setItem('shedulo_theme', 'light');
    }
  }

  // Initialize theme from storage
  (function() {
    const saved = localStorage.getItem('shedulo_theme') || 'light';
    setAppTheme(saved);
  })();

  // Instant in-page search filter for timetable
  function filterTimetableTable(keyword) {
    keyword = keyword.toLowerCase().trim();
    const cards = document.querySelectorAll('.card-lecture');
    cards.forEach(card => {
      if (!keyword) {
        card.style.opacity = '1';
        card.style.filter = 'none';
        return;
      }
      if (card.textContent.toLowerCase().includes(keyword)) {
        card.style.opacity = '1';
        card.style.filter = 'drop-shadow(0 0 6px #10b981)';
      } else {
        card.style.opacity = '0.25';
        card.style.filter = 'grayscale(80%)';
      }
    });
  }
  </script>
