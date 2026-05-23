<?php
// includes/head.php — shared <head> and global CSS (EVSU maroon & gold design)
$pageTitle = $pageTitle ?? 'EVSU-OC INC Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?> — EVSU-OC INC Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --maroon: #6B0F1A;
  --maroon-dark: #4A0A12;
  --maroon-light: #8B1A28;
  --gold: #C9A84C;
  --gold-light: #E8C97A;
  --gold-pale: #F5EDD3;
  --white: #FDFCFA;
  --off-white: #F8F5EE;
  --gray-100: #F1EDE4;
  --gray-200: #DDD6CA;
  --gray-400: #A09080;
  --gray-600: #6B5D52;
  --gray-900: #1C1410;
  --radius: 12px;
  --radius-sm: 8px;
  --shadow: 0 2px 12px rgba(107,15,26,0.08);
  --shadow-md: 0 8px 32px rgba(107,15,26,0.12);
  --success: #2A6E1A;
  --danger: #C0392B;
  --info: #1B5FA3;
}
body { font-family: 'DM Sans', sans-serif; background: var(--off-white); color: var(--gray-900); min-height: 100vh; }

/* ── SIDEBAR LAYOUT ── */
.layout { display: flex; min-height: 100vh; }
.sidebar {
  width: 240px; background: var(--maroon-dark); padding: 1.5rem 0;
  display: flex; flex-direction: column; flex-shrink: 0; min-height: 100vh;
  position: sticky; top: 0; height: 100vh; overflow-y: auto;
}
.sidebar-brand { padding: 0 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 0.75rem; }
.sidebar-brand .emblem {
  width: 36px; height: 36px; background: var(--gold); border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Playfair Display', serif; font-size: 13px; font-weight: 700;
  color: var(--maroon-dark); margin-bottom: 0.5rem;
}
.sidebar-brand h1 { font-family: 'Playfair Display', serif; font-size: 13px; color: var(--white); font-weight: 600; line-height: 1.3; }
.sidebar-brand span { font-family: 'DM Sans', sans-serif; font-size: 10.5px; color: rgba(255,255,255,0.4); font-weight: 300; display: block; }
.nav-section-label { font-size: 9.5px; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.3); font-weight: 500; padding: 0.75rem 1.25rem 0.25rem; }
.nav-item {
  display: flex; align-items: center; gap: 10px; padding: 9px 1.25rem;
  font-size: 13px; color: rgba(255,255,255,0.6); cursor: pointer;
  transition: background 0.12s, color 0.12s; border-left: 3px solid transparent;
  font-weight: 400; text-decoration: none;
}
.nav-item:hover { background: rgba(255,255,255,0.05); color: var(--white); }
.nav-item.active { color: var(--gold-light); border-left-color: var(--gold); background: rgba(201,168,76,0.08); font-weight: 500; }
.nav-item svg { opacity: 0.7; flex-shrink: 0; }
.nav-item.active svg { opacity: 1; }
.sidebar-footer { margin-top: auto; padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.08); }
.user-chip { display: flex; align-items: center; gap: 10px; }
.user-avatar { width: 32px; height: 32px; background: var(--maroon-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: var(--white); flex-shrink: 0; }
.user-info p { font-size: 12.5px; color: var(--white); font-weight: 500; }
.user-info span { font-size: 10.5px; color: rgba(255,255,255,0.4); }

/* ── ROLE SWITCHER ── */
.role-switcher { padding: 0 1.25rem; margin-bottom: 0.5rem; }
.role-switcher label { font-size: 9.5px; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.3); font-weight: 500; display: block; margin-bottom: 5px; }
.role-switcher select {
  width: 100%; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
  color: var(--gold-light); font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 500;
  padding: 6px 10px; border-radius: var(--radius-sm); cursor: pointer; outline: none;
}
.role-switcher select option { background: var(--maroon-dark); color: var(--white); }

/* ── MAIN CONTENT ── */
.main-content { flex: 1; padding: 1.5rem 2rem; min-width: 0; background: var(--gray-100); }
.top-bar { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem; }
.top-bar h2 { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--gray-900); }
.top-bar p { font-size: 13px; color: var(--gray-400); margin-top: 2px; }

/* ── CARDS ── */
.stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--radius); padding: 1.25rem 1.375rem; box-shadow: var(--shadow); }
.stat-label { font-size: 11.5px; font-weight: 500; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.06em; }
.stat-value { font-family: 'Playfair Display', serif; font-size: 30px; color: var(--gray-900); margin: 6px 0 4px; line-height: 1; }
.stat-sub { font-size: 11.5px; color: var(--gray-400); }
.content-card { background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 1.25rem; overflow: hidden; }
.card-head { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.375rem; border-bottom: 1px solid var(--gray-100); }
.card-head h3 { font-family: 'Playfair Display', serif; font-size: 16px; color: var(--gray-900); }
.card-body { padding: 1.375rem; }

/* ── TABLE ── */
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { text-align: left; padding: 10px 14px; font-size: 11px; font-weight: 600; letter-spacing: 0.07em; text-transform: uppercase; color: var(--gray-400); background: var(--gray-100); border-bottom: 1px solid var(--gray-200); }
.data-table td { padding: 11px 14px; border-bottom: 1px solid var(--gray-100); vertical-align: middle; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover td { background: var(--off-white); }

/* ── BADGES ── */
.badge { display: inline-flex; align-items: center; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 100px; }
.badge-success { background: #EBF5E7; color: var(--success); }
.badge-danger  { background: #FEF2F2; color: var(--danger); }
.badge-gold    { background: var(--gold-pale); color: #8B6914; }
.badge-info    { background: #EBF2FB; color: var(--info); }
.badge-gray    { background: var(--gray-100); color: var(--gray-400); }

/* ── ROLE CHIPS ── */
.role-chips { display: flex; flex-wrap: wrap; gap: 4px; }
.role-chip { display: inline-flex; font-size: 10.5px; font-weight: 600; padding: 2px 8px; border-radius: 100px; }
.rc-admin      { background: #F9EDF0; color: var(--maroon); }
.rc-instructor { background: #F0EDF8; color: #5C3F9C; }
.rc-dept_head  { background: #FEF3E6; color: #B76B00; }
.rc-registrar  { background: #EBF2FB; color: var(--info); }
.rc-student    { background: #EBF5E7; color: var(--success); }

/* ── BUTTONS ── */
.btn-primary { display: inline-flex; align-items: center; justify-content: center; height: 42px; padding: 0 1.25rem; background: var(--maroon); color: var(--white); border: none; border-radius: var(--radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13.5px; font-weight: 600; cursor: pointer; letter-spacing: 0.02em; transition: background 0.15s; text-decoration: none; gap: 6px; }
.btn-primary:hover { background: var(--maroon-dark); }
.btn-primary.full { width: 100%; }
.btn-sm { display: inline-flex; align-items: center; height: 30px; padding: 0 12px; border: 1px solid var(--gray-200); border-radius: 6px; font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 500; cursor: pointer; background: var(--white); color: var(--gray-600); transition: all 0.12s; text-decoration: none; gap: 5px; }
.btn-sm:hover { border-color: var(--gray-400); color: var(--gray-900); }
.btn-sm.maroon { background: var(--maroon); color: var(--white); border-color: var(--maroon); }
.btn-sm.maroon:hover { background: var(--maroon-dark); }
.btn-sm.danger { color: var(--danger); border-color: #FECACA; }
.btn-sm.danger:hover { background: #FEF2F2; }
.btn-sm.success { color: var(--success); border-color: #BBF7D0; }
.btn-sm.success:hover { background: #EBF5E7; }

/* ── FORMS ── */
.form-group { margin-bottom: 1.125rem; }
.form-label { display: block; font-size: 12.5px; font-weight: 500; color: var(--gray-600); margin-bottom: 6px; letter-spacing: 0.03em; }
.form-input { width: 100%; height: 42px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); padding: 0 14px; font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--gray-900); background: var(--white); transition: border-color 0.15s; outline: none; }
.form-input:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107,15,26,0.08); }
textarea.form-input { height: auto; padding: 10px 14px; resize: vertical; }
select.form-input { cursor: pointer; }
.form-hint { font-size: 11.5px; color: var(--gray-400); margin-top: 4px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-section-title { font-family: 'Playfair Display', serif; font-size: 15px; color: var(--gray-900); margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--gray-200); }

/* ── ALERTS ── */
.alert { padding: 0.75rem 1rem; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 8px; }
.alert-success { background: #EBF5E7; color: var(--success); border: 1px solid #BBF7D0; }
.alert-danger  { background: #FEF2F2; color: var(--danger); border: 1px solid #FECACA; }
.alert-info    { background: #EBF2FB; color: var(--info); border: 1px solid #BFDBFE; }
.alert-gold    { background: var(--gold-pale); color: #8B6914; border: 1px solid #FDE68A; }

/* ── TOGGLE SWITCHES ── */
.toggle { position: relative; width: 40px; height: 22px; flex-shrink: 0; cursor: pointer; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-track { position: absolute; inset: 0; background: var(--gray-200); border-radius: 11px; transition: background 0.2s; }
.toggle input:checked + .toggle-track { background: var(--maroon); }
.toggle-thumb { position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; background: var(--white); border-radius: 50%; transition: left 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.toggle input:checked ~ .toggle-thumb { left: 21px; }
.module-row { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.375rem; border-bottom: 1px solid var(--gray-100); gap: 1rem; }
.module-row:last-child { border-bottom: none; }
.module-info h4 { font-size: 13.5px; font-weight: 600; color: var(--gray-900); margin-bottom: 3px; display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
.module-info p { font-size: 12px; color: var(--gray-400); }

/* ── STEP TRACKER ── */
.step-tracker { display: flex; align-items: center; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 0.25rem; }
.step-item { display: flex; flex-direction: column; align-items: center; gap: 6px; flex-shrink: 0; }
.step-dot { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; border: 2px solid var(--gray-200); background: var(--white); color: var(--gray-400); }
.step-dot.done { background: var(--maroon); border-color: var(--maroon); color: var(--white); }
.step-dot.active { background: var(--white); border-color: var(--gold); color: var(--maroon); }
.step-label { font-size: 10.5px; color: var(--gray-400); text-align: center; max-width: 72px; }
.step-label.active { color: var(--maroon); font-weight: 600; }
.step-line { flex: 1; height: 2px; background: var(--gray-200); min-width: 20px; }
.step-line.done { background: var(--maroon); }

/* ── TAB ROW ── */
.tab-row { display: flex; gap: 0; border-bottom: 1px solid var(--gray-200); padding: 0 1.375rem; }
.tab { padding: 0.75rem 1rem; font-size: 13px; color: var(--gray-400); cursor: pointer; border-bottom: 2px solid transparent; transition: color 0.15s; }
.tab:hover { color: var(--gray-600); }
.tab.active { color: var(--maroon); border-bottom-color: var(--maroon); font-weight: 600; }

/* ── LOGIN PAGE ── */
.login-wrap { min-height: 100vh; display: flex; flex-direction: column; background: var(--off-white); }
.login-header { background: var(--maroon); padding: 0 2rem; display: flex; align-items: center; gap: 1rem; height: 64px; }
.login-header .logo-emblem { width: 36px; height: 36px; background: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; font-weight: 700; font-size: 13px; color: var(--maroon-dark); flex-shrink: 0; }
.login-header .logo-text { font-family: 'Playfair Display', serif; font-size: 14px; color: var(--white); font-weight: 600; line-height: 1.2; }
.login-header .logo-text span { font-family: 'DM Sans', sans-serif; font-weight: 300; font-size: 11px; color: var(--gold-light); letter-spacing: 0.05em; }
.login-body { flex: 1; display: flex; align-items: center; justify-content: center; padding: 3rem 1rem; }
.login-card { background: var(--white); border: 1px solid var(--gray-200); border-radius: 20px; padding: 2.5rem 2.25rem; width: 100%; max-width: 420px; box-shadow: var(--shadow-md); }
.login-card-header { text-align: center; margin-bottom: 2rem; }
.login-emblem { width: 52px; height: 52px; background: var(--maroon); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; font-weight: 700; font-size: 18px; color: var(--gold); }
.login-card-header h2 { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--gray-900); margin-bottom: 0.25rem; }
.login-card-header p { font-size: 13px; color: var(--gray-400); }
.back-link { display: inline-flex; align-items: center; gap: 5px; font-size: 13px; color: var(--maroon); font-weight: 500; text-decoration: none; margin-bottom: 1.5rem; }
.back-link:hover { color: var(--maroon-dark); }

/* ── LANDING HERO ── */
.landing-hero { background: linear-gradient(160deg, var(--maroon-dark) 0%, var(--maroon) 60%, #9B1E30 100%); padding: 5rem 2rem 4rem; text-align: center; position: relative; overflow: hidden; }
.hero-pattern { position: absolute; inset: 0; background-image: repeating-linear-gradient(45deg, transparent, transparent 24px, rgba(201,168,76,0.04) 24px, rgba(201,168,76,0.04) 25px); pointer-events: none; }
.hero-badge { display: inline-block; background: rgba(201,168,76,0.15); border: 1px solid rgba(201,168,76,0.35); color: var(--gold-light); font-size: 11px; font-weight: 500; letter-spacing: 0.12em; text-transform: uppercase; padding: 6px 16px; border-radius: 100px; margin-bottom: 1.5rem; }
.hero-title { font-family: 'Playfair Display', serif; font-size: clamp(28px, 5vw, 44px); color: var(--white); line-height: 1.2; margin-bottom: 1rem; }
.hero-sub { font-size: 15px; color: rgba(255,255,255,0.65); max-width: 480px; margin: 0 auto 3rem; line-height: 1.7; font-weight: 300; }
.role-cards { display: flex; gap: 1.25rem; justify-content: center; flex-wrap: wrap; position: relative; z-index: 1; }
.role-card { background: var(--white); border: 1.5px solid var(--gray-200); border-radius: 16px; padding: 2rem 1.75rem; width: 220px; cursor: pointer; transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s; text-align: center; text-decoration: none; color: inherit; display: block; }
.role-card:hover { transform: translateY(-4px); border-color: var(--gold); box-shadow: 0 12px 36px rgba(107,15,26,0.18); }
.role-card .icon-wrap { width: 56px; height: 56px; border-radius: 14px; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.role-card.student .icon-wrap { background: #FEF3E6; }
.role-card.employee .icon-wrap { background: #F0EDF8; }
.role-card h3 { font-family: 'Playfair Display', serif; font-size: 18px; color: var(--gray-900); margin-bottom: 0.4rem; }
.role-card p { font-size: 12.5px; color: var(--gray-400); line-height: 1.6; }
.role-card .cta { margin-top: 1.25rem; display: inline-block; font-size: 12px; font-weight: 600; color: var(--maroon); letter-spacing: 0.04em; }

/* ── SIGNATURE CANVAS ── */
.sig-canvas-wrap { border: 1px dashed var(--gray-200); border-radius: var(--radius-sm); background: #FAFAF9; padding: 4px; }
.sig-canvas-wrap canvas { display: block; touch-action: none; cursor: crosshair; }
.sig-actions { display: flex; gap: 8px; margin-top: 8px; }

/* ── MISC ── */
.divider { height: 1px; background: var(--gray-200); margin: 1.5rem 0; }
.empty-state { text-align: center; padding: 3rem 1rem; color: var(--gray-400); }
.empty-state svg { margin-bottom: 1rem; opacity: 0.4; }
.empty-state p { font-size: 13.5px; }
.page-header { font-family: 'Playfair Display', serif; font-size: 20px; color: var(--gray-900); margin-bottom: 0.25rem; }
.page-sub { font-size: 13px; color: var(--gray-400); margin-bottom: 1.5rem; }
.filter-bar { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1rem; }
.filter-bar .form-input { max-width: 220px; height: 36px; font-size: 13px; }

/* ── MODAL ── */
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 100; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.modal-backdrop.hidden { display: none; }
.modal { background: var(--white); border-radius: var(--radius); max-width: 500px; width: 100%; box-shadow: var(--shadow-md); max-height: 90vh; overflow-y: auto; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between; }
.modal-header h3 { font-family: 'Playfair Display', serif; font-size: 17px; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 0.75rem; }
.modal-close { background: none; border: none; cursor: pointer; color: var(--gray-400); font-size: 18px; }
</style>
</head>
