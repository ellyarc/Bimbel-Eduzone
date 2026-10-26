<!-- =====================================================
     head.html — EduZone Global Styles
     Load via: fetch('templates/head.html')
     atau copy isi <style> nya ke halaman kamu
     ===================================================== -->
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; font-family: 'Segoe UI', sans-serif; background: #f0f4f8; color: #1a2a3a; }

  /* ── LAYOUT ── */
  .app   { display: flex; height: 100vh; }
  .main  { flex: 1; display: flex; flex-direction: column; min-width: 0; overflow: hidden; }
  .content { flex: 1; overflow-y: auto; padding: 24px 28px; }

  /* ── SIDEBAR ── */
  .sidebar { width: 220px; flex-shrink: 0; background: #fff; border-right: 1px solid #e4eaf2; display: flex; flex-direction: column; padding: 24px 0 0; }
  .sidebar-logo { padding: 0 20px 20px; display: flex; align-items: center; gap: 10px; }
  .logo-icon { width: 32px; height: 32px; border-radius: 8px; background: #E6F1FB; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #185FA5; }
  .logo-text { font-size: 15px; font-weight: 700; color: #1a2a3a; }
  .sidebar-user { margin: 0 12px 20px; padding: 10px 12px; background: #f5f7fa; border-radius: 8px; display: flex; align-items: center; gap: 10px; }
  .avatar { width: 32px; height: 32px; border-radius: 50%; background: #E6F1FB; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #0C447C; flex-shrink: 0; }
  .user-info p { font-size: 13px; font-weight: 600; color: #1a2a3a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
  .user-info span { font-size: 11px; color: #8a9ab0; }
  .nav-section { padding: 0 12px; flex: 1; }
  .nav-label { font-size: 10px; font-weight: 700; color: #9aabb8; letter-spacing: 0.6px; text-transform: uppercase; padding: 0 8px; margin-bottom: 6px; display: block; }
  .nav-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px; font-size: 13px; color: #5a6a7a; transition: all 0.15s; margin-bottom: 2px; text-decoration: none; }
  .nav-item:hover { background: #f5f7fa; color: #1a2a3a; }
  .nav-item.active { background: #E6F1FB; color: #185FA5; font-weight: 600; }
  .nav-item i { font-size: 17px; }
  .nav-badge { margin-left: auto; background: #E6F1FB; color: #0C447C; border-radius: 20px; padding: 1px 7px; font-size: 11px; font-weight: 700; }
  .nav-item.active .nav-badge { background: #B5D4F4; }
  .sidebar-bottom { padding: 12px; border-top: 1px solid #e4eaf2; }
  .btn-logout { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 8px; font-size: 13px; color: #c0392b; transition: all 0.15s; background: none; border: none; width: 100%; text-decoration: none; cursor: pointer; }
  .btn-logout:hover { background: #FCEBEB; }
  .btn-logout i { font-size: 17px; }

  /* ── TOPBAR ── */
  .topbar { display: flex; align-items: center; justify-content: space-between; padding: 16px 28px; border-bottom: 1px solid #e4eaf2; background: #fff; }
  .topbar-left h1 { font-size: 17px; font-weight: 700; color: #1a2a3a; }
  .topbar-left p  { font-size: 13px; color: #8a9ab0; margin-top: 1px; }
  .topbar-right { display: flex; align-items: center; gap: 10px; }
  .topbar-date { font-size: 12px; color: #8a9ab0; background: #f5f7fa; padding: 6px 12px; border-radius: 8px; border: 1px solid #e4eaf2; display: flex; align-items: center; gap: 5px; }
  .notif-btn { width: 34px; height: 34px; border-radius: 8px; background: #f5f7fa; border: 1px solid #e4eaf2; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #5a6a7a; position: relative; transition: background 0.15s; }
  .notif-btn:hover { background: #E6F1FB; color: #185FA5; }
  .notif-dot { width: 7px; height: 7px; border-radius: 50%; background: #e74c3c; border: 2px solid #fff; position: absolute; top: 5px; right: 5px; }

  /* ── KOMPONEN UMUM ── */

  /* Panel / Card */
  .panel { background: #fff; border: 1px solid #e4eaf2; border-radius: 12px; padding: 18px 20px; }
  .panel-title { font-size: 14px; font-weight: 700; color: #1a2a3a; margin-bottom: 14px; }

  /* Greeting banner */
  .greeting-banner { background: linear-gradient(120deg, #185FA5 0%, #2a84d4 60%, #378ADD 100%); border-radius: 14px; padding: 22px 28px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; position: relative; overflow: hidden; }
  .greeting-banner::before { content: ''; position: absolute; right: -30px; top: -40px; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,0.07); }
  .greeting-text h2 { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; }
  .greeting-text p  { font-size: 13px; color: rgba(255,255,255,0.75); }
  .greeting-emoji { font-size: 36px; }

  /* Stat grid */
  .stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; margin-bottom: 24px; }
  .stat-card { background: #fff; border: 1px solid #e4eaf2; border-radius: 12px; padding: 16px 18px; display: flex; align-items: flex-start; gap: 12px; transition: border-color 0.15s, transform 0.15s; }
  .stat-card:hover { border-color: #b0c8f0; transform: translateY(-2px); }
  .stat-icon { width: 38px; height: 38px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
  .si-blue   { background: #E6F1FB; color: #185FA5; }
  .si-green  { background: #E1F5EE; color: #0F6E56; }
  .si-orange { background: #FAEEDA; color: #854F0B; }
  .si-purple { background: #EEE8FB; color: #5B2D9E; }
  .si-red    { background: #FCEBEB; color: #A32D2D; }
  .si-teal   { background: #E6FBF8; color: #0C6E6A; }
  .stat-body { display: flex; flex-direction: column; }
  .stat-val   { font-size: 22px; font-weight: 700; color: #1a2a3a; line-height: 1.2; }
  .stat-label { font-size: 12px; color: #8a9ab0; margin-top: 2px; }
  .stat-sub   { font-size: 11px; color: #b0bec5; margin-top: 4px; }

  /* Section heading */
  .sec-head  { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
  .sec-title { font-size: 14px; font-weight: 700; color: #1a2a3a; }
  .sec-link  { font-size: 12px; color: #378ADD; text-decoration: none; display: flex; align-items: center; gap: 4px; }
  .sec-link:hover { text-decoration: underline; }

  /* Mapel tags */
  .mapel-tag { display: inline-flex; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
  .tag-mat { background: #E6F1FB; color: #185FA5; }
  .tag-kim { background: #E1F5EE; color: #0F6E56; }
  .tag-fis { background: #FAEEDA; color: #854F0B; }
  .tag-bio { background: #EAF3DE; color: #3B6D11; }

  /* Score / status pills */
  .score-pill { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
  .score-good  { background: #EAF3DE; color: #3B6D11; }
  .score-mid   { background: #FAEEDA; color: #854F0B; }
  .score-low   { background: #FCEBEB; color: #A32D2D; }

  /* Badge umum */
  .badge        { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
  .badge-belum  { background: #FCEBEB; color: #A32D2D; }
  .badge-lunas  { background: #EAF3DE; color: #3B6D11; }
  .badge-proses { background: #FAEEDA; color: #854F0B; }

  /* Tabel data */
  .data-table { width: 100%; border-collapse: collapse; }
  .data-table th { font-size: 11px; font-weight: 600; color: #8a9ab0; text-align: left; padding: 8px 12px; border-bottom: 1px solid #e4eaf2; text-transform: uppercase; letter-spacing: 0.4px; }
  .data-table td { font-size: 13px; color: #1a2a3a; padding: 10px 12px; border-bottom: 1px solid #f0f4f8; vertical-align: middle; }
  .data-table tr:last-child td { border-bottom: none; }
  .data-table tr:hover td { background: #f8fafc; }

  /* Empty state */
  .empty-state { text-align: center; padding: 48px; color: #9aabb8; font-size: 14px; }
  .empty-state i { font-size: 36px; display: block; margin-bottom: 10px; color: #c8d6e0; }
</style>
