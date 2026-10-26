<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EduZone – Premium Learning Ecosystem</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      height: 100%;
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #0b1329; /* Deep Dark Navy */
      color: #f8fafc;
      overflow-x: hidden;
    }

    /* ── PREMIUM NAVBAR ── */
    .navbar {
      background: rgba(11, 19, 41, 0.75);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 8%;
      height: 72px;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .logo-brand {
      font-size: 22px;
      font-weight: 800;
      letter-spacing: -0.5px;
      color: #fff;
    }
    .logo-brand span {
      background: linear-gradient(135deg, #378ADD 0%, #1d4ed8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .nav-links { display: flex; gap: 40px; }
    .nav-links a {
      color: #94a3b8;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: color 0.2s;
    }
    .nav-links a:hover { color: #fff; }
    
    .btn-signin {
      background: rgba(255, 255, 255, 0.05);
      color: #fff;
      border: 1px solid rgba(255, 255, 255, 0.15);
      padding: 10px 24px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-signin:hover { 
      background: #fff; 
      color: #0b1329; 
      border-color: #fff;
      box-shadow: 0 0 20px rgba(255,255,255,0.2);
    }

    /* ── HERO DYNAMIC SECTION ── */
    .hero {
      position: relative;
      padding: 100px 8% 120px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 60px;
      background: radial-gradient(circle at 90% 10%, rgba(55, 138, 221, 0.15), transparent 40%),
                  radial-gradient(circle at 10% 90%, rgba(29, 78, 216, 0.08), transparent 50%);
    }
    .hero-text {
      flex: 1 1 55%;
    }
    .hero-text h1 {
      font-size: clamp(32px, 5vw, 54px);
      font-weight: 800;
      line-height: 1.15;
      letter-spacing: -1px;
      margin-bottom: 24px;
    }
    .hero-text h1 span {
      background: linear-gradient(135deg, #60a5fa 0%, #378ADD 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .hero-text p {
      font-size: 16px;
      color: #94a3b8;
      line-height: 1.8;
      margin-bottom: 40px;
      max-width: 540px;
    }
    .btn-mulai {
      background: linear-gradient(135deg, #378ADD 0%, #1d4ed8 100%);
      color: #fff;
      border: none;
      padding: 16px 36px;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 8px 24px rgba(55, 138, 221, 0.35);
    }
    .btn-mulai:hover { 
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(55, 138, 221, 0.5);
    }

    /* ── GLASSMORPHISM ILLUSTRATION ── */
    .hero-illustration {
      flex: 1 1 40%;
      display: flex;
      justify-content: center;
      position: relative;
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 24px;
      width: 100%;
      max-width: 380px;
      padding: 24px;
      box-shadow: 0 40px 80px rgba(0,0,0,0.5);
    }
    .glass-header { display: flex; gap: 6px; margin-bottom: 20px; }
    .glass-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.1); }
    .glass-dot:nth-child(1) { background: #ff5f56; }
    .glass-dot:nth-child(2) { background: #ffbd2e; }
    .glass-dot:nth-child(3) { background: #27c93f; }
    
    .glass-display {
      height: 160px;
      background: rgba(255,255,255,0.02);
      border: 1px solid rgba(255,255,255,0.05);
      border-radius: 16px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 16px;
    }
    .premium-play {
      width: 54px; height: 54px; border-radius: 50%;
      background: #fff; display: flex; align-items: center; justify-content: center;
      box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    }
    .premium-play i { color: #0b1329; font-size: 20px; margin-left: 4px; }
    .glass-shimmer { height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px; margin-bottom: 8px; }
    .glass-shimmer.w-70 { width: 70%; }
    .glass-shimmer.w-40 { width: 40%; background: #378ADD; }

    /* ── FLOATING GLOW TAGS ── */
    .floating-pill {
      position: absolute;
      background: rgba(11, 19, 41, 0.8);
      border: 1px solid rgba(55, 138, 221, 0.3);
      padding: 10px 18px;
      border-radius: 30px;
      font-size: 13px;
      font-weight: 600;
      display: flex; align-items: center; gap: 8px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.3);
      animation: floating 4s ease-in-out infinite;
    }
    .floating-pill i { color: #60a5fa; }
    .floating-pill.pos-1 { top: -10px; right: -20px; animation-delay: 0s; }
    .floating-pill.pos-2 { bottom: 20px; left: -30px; animation-delay: 1.5s; }
    
    @keyframes floating {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    /* ── SECTION: ACADEMIC TRIUMPHS ── */
    .triumphs-section {
      padding: 80px 8%;
      background: linear-gradient(180deg, #0b1329 0%, #070d1e 100%);
      border-top: 1px solid rgba(255, 255, 255, 0.03);
    }
    .stats-container {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 30px;
      max-width: 1100px;
      margin: 0 auto 50px;
      text-align: center;
    }
    .stat-box {
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(55, 138, 221, 0.15);
      padding: 30px 20px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .stat-box h2 {
      font-size: 42px;
      font-weight: 800;
      color: #60a5fa;
      margin-bottom: 8px;
    }
    .stat-box p {
      font-size: 14px;
      color: #94a3b8;
      font-weight: 500;
    }
    .alumni-marquee {
      display: flex;
      gap: 20px;
      overflow: hidden;
      white-space: nowrap;
      position: relative;
      padding: 10px 0;
    }
    .alumni-badge {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      padding: 12px 24px;
      border-radius: 50px;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      color: #e2e8f0;
      flex-shrink: 0;
    }
    .alumni-badge strong { color: #fff; }
    .alumni-badge span {
      background: rgba(39, 201, 63, 0.15);
      color: #27c93f;
      font-size: 11px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 10px;
      text-transform: uppercase;
    }

    /* ── SECTION: EDUCATIONAL STAGES ── */
    .stages-section {
      padding: 100px 8%;
      background: #070d1e;
    }
    .stages-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .stage-card {
      background: rgba(11, 19, 41, 0.5);
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      padding: 30px;
      transition: all 0.3s;
    }
    .stage-card:hover {
      border-color: #378ADD;
      transform: translateY(-4px);
    }
    .stage-icon {
      font-size: 32px;
      color: #378ADD;
      margin-bottom: 20px;
    }
    .stage-card h3 {
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 10px;
      color: #fff;
    }
    .stage-card p {
      font-size: 13px;
      color: #94a3b8;
      line-height: 1.6;
    }

    /* ── EXCLUSIVE PREMIUM GRID ── */
    .features-section {
      padding: 100px 8%;
      background: #0b1329;
    }
    .section-header {
      text-align: center;
      margin-bottom: 60px;
    }
    .section-header p {
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #378ADD;
      font-weight: 700;
      margin-bottom: 12px;
    }
    .section-header h2 {
      font-size: 32px;
      font-weight: 800;
      letter-spacing: -0.5px;
    }
    
    .features-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .feature-card {
      background: rgba(255, 255, 255, 0.01);
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      padding: 40px 30px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    /* Hover Card Glow Effect */
    .feature-card::before {
      content: ''; position: absolute; inset: 0;
      background: radial-gradient(800px circle at var(--x, 0px) var(--y, 0px), rgba(55, 138, 221, 0.08), transparent 40%);
      opacity: 0; transition: opacity 0.3s;
    }
    .feature-card:hover::before { opacity: 1; }
    
    .feature-card:hover {
      border-color: rgba(55, 138, 221, 0.3);
      transform: translateY(-6px);
      background: rgba(255, 255, 255, 0.02);
      box-shadow: 0 20px 40px -15px rgba(7, 13, 30, 0.7);
    }
    
    .feat-icon-box {
      width: 52px; height: 52px; border-radius: 14px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 24px;
      transition: all 0.3s;
    }
    .feature-card:hover .feat-icon-box {
      background: #378ADD;
      border-color: #378ADD;
      box-shadow: 0 0 20px rgba(55, 138, 221, 0.4);
    }
    .feat-icon-box i { color: #fff; font-size: 24px; }
    
    .feature-card h3 {
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 12px;
      color: #fff;
    }
    .feature-card .arrow-link {
      font-size: 13px;
      font-weight: 600;
      color: #94a3b8;
      display: flex; align-items: center; gap: 4px;
      margin-top: auto;
      transition: color 0.2s;
    }
    .feature-card:hover .arrow-link { color: #60a5fa; }

    /* ── COMPREHENSIVE PREMIUM FOOTER ── */
    .premium-footer {
      background: #060a16;
      border-top: 1px solid rgba(255, 255, 255, 0.05);
      padding: 70px 8% 30px;
    }
    .footer-container {
      display: grid;
      grid-template-columns: 2fr 1fr 1.5fr;
      gap: 50px;
      max-width: 1200px;
      margin: 0 auto 50px;
    }
    .footer-brand-side h3 {
      font-size: 24px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 16px;
    }
    .footer-brand-side h3 span { color: #378ADD; }
    .footer-brand-side p {
      font-size: 14px;
      color: #94a3b8;
      line-height: 1.7;
      margin-bottom: 24px;
      max-width: 380px;
    }
    .footer-socials { display: flex; gap: 14px; }
    .footer-socials a {
      width: 38px; height: 38px; border-radius: 10px;
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.08);
      display: flex; align-items: center; justify-content: center;
      color: #94a3b8; text-decoration: none; transition: all 0.2s;
    }
    .footer-socials a:hover { background: #378ADD; color: #fff; border-color: #378ADD; }

    .footer-links-side h4 {
      font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 20px;
    }
    .footer-links-side ul { list-style: none; }
    .footer-links-side ul li { margin-bottom: 12px; }
    .footer-links-side ul li a { color: #94a3b8; text-decoration: none; font-size: 14px; transition: color 0.2s; }
    .footer-links-side ul li a:hover { color: #fff; }

    .footer-contact-side h4 {
      font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 20px;
    }
    .contact-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; font-size: 14px; color: #94a3b8; line-height: 1.5; }
    .contact-item i { color: #378ADD; font-size: 18px; margin-top: 2px; }
    .contact-item a { color: #94a3b8; text-decoration: none; }
    .contact-item a:hover { color: #60a5fa; }

    .footer-bottom {
      border-top: 1px solid rgba(255, 255, 255, 0.05);
      padding-top: 24px;
      display: flex; justify-content: space-between; align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      font-size: 13px;
      color: #64748b;
    }

    /* ── RESPONSIVE RESPONSIVENESS ── */
    @media (max-width: 1024px) {
      .features-grid, .stages-grid { grid-template-columns: repeat(2, 1fr); }
      .footer-container { grid-template-columns: 1fr 1fr; gap: 40px; }
      .footer-brand-side { grid-column: span 2; }
    }
    @media (max-width: 768px) {
      .hero { flex-direction: column; text-align: center; padding-top: 60px; }
      .hero-text p { margin-left: auto; margin-right: auto; }
      .hero-illustration { width: 100%; max-width: 340px; margin: 40px auto 0; }
      .floating-pill.pos-1 { right: 0; }
      .floating-pill.pos-2 { left: 0; }
      .stats-container { grid-template-columns: 1fr; }
    }
    @media (max-width: 540px) {
      .features-grid, .stages-grid { grid-template-columns: 1fr; }
      .navbar { padding: 0 5%; }
      .nav-links { display: none; }
      .footer-container { grid-template-columns: 1fr; }
      .footer-brand-side { grid-column: span 1; }
      .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
    }

    /* ── MODALS ARCHITECTURE ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(4, 9, 20, 0.85);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      z-index: 200; align-items: center; justify-content: center; padding: 20px;
    }
    .modal-overlay.open { display: flex; }
    .modal-window {
      background: #0b1329;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 24px;
      padding: 40px;
      width: min(440px, 100%);
      position: relative;
      box-shadow: 0 30px 70px rgba(0,0,0,0.6);
      animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalFadeIn {
      from { opacity: 0; transform: scale(0.96) translateY(10px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-window h3 {
      font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 14px;
    }
    .modal-window p {
      font-size: 15px; color: #94a3b8; line-height: 1.7; margin-bottom: 32px;
    }
    .modal-close-btn {
      position: absolute; top: 24px; right: 24px;
      background: none; border: none; cursor: pointer; color: #64748b; font-size: 20px;
      transition: color 0.2s;
    }
    .modal-close-btn:hover { color: #fff; }
    
    .btn-prime {
      width: 100%;
      background: #fff;
      color: #0b1329;
      border: none;
      padding: 14px;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-prime:hover { background: #378ADD; color: #fff; }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="logo-brand">Edu<span>Zone</span></div>
  <div class="nav-links">
    <a href="#">Ecosystem</a>
    <a href="#stages">Programs</a>
    <a href="#achievements">Achievements</a>
    <a href="#contact">Contact</a>
  </div>
  <button class="btn-signin" onclick="window.location.href='login.php'">Sign In</button>
</nav>

<section class="hero">
  <div class="hero-text">
    <h1>A Unified Hub For<br><span>Exclusive Learning</span></h1>
    <p>Limitless interactivity within a single digital workspace. Custom-built to streamline academic monitoring, administrative workflows, and daily learning materials with absolute clarity.</p>
    <button class="btn-mulai" onclick="openModal('mulaiModal')">Enter Platform →</button>
  </div>
  <div class="hero-illustration">
    <div class="glass-card">
      <div class="glass-header">
        <div class="glass-dot"></div><div class="glass-dot"></div><div class="glass-dot"></div>
      </div>
      <div class="glass-display">
        <div class="premium-play"><i class="ti ti-player-play-filled"></i></div>
      </div>
      <div class="glass-shimmer w-70"></div>
      <div class="glass-shimmer w-40"></div>
    </div>
    <div class="floating-pill pos-1"><i class="ti ti-circle-check-filled"></i> Attendance Validated</div>
    <div class="floating-pill pos-2"><i class="ti ti-credit-card"></i> Secured Gateway</div>
  </div>
</section>

<section class="triumphs-section" id="achievements">
  <div class="section-header">
    <p>Proven Results</p>
    <h2>Academic Triumphs &amp; Milestones</h2>
  </div>
  <div class="stats-container">
    <div class="stat-box">
      <h2>1,250+</h2>
      <p>Students Passed UTBK-SNBT</p>
    </div>
    <div class="stat-box">
      <h2>480+</h2>
      <p>Students Accepted via SNBP (Invitation)</p>
    </div>
    <div class="stat-box">
      <h2>98.4%</h2>
      <p>Student Satisfaction &amp; Success Rate</p>
    </div>
  </div>
  
  <div class="alumni-marquee">
    <div class="alumni-badge"><strong>Andi Wijaya</strong> – Universitas Indonesia (Accounting) <span>SNBP</span></div>
    <div class="alumni-badge"><strong>Siti Rahma</strong> – ITB (Informatics Engineering) <span>SNBT</span></div>
    <div class="alumni-badge"><strong>Budi Santoso</strong> – Universitas Gadjah Mada (Medicine) <span>SNBT</span></div>
    <div class="alumni-badge"><strong>Clara Diora</strong> – Universitas Airlangga (Law) <span>SNBP</span></div>
    <div class="alumni-badge"><strong>Rian Hidayat</strong> – STAN (Financial Accounting) <span>Kedinasan</span></div>
  </div>
</section>

<section class="stages-section" id="stages">
  <div class="section-header">
    <p>Learning Paths</p>
    <h2>Available Academic Levels</h2>
  </div>
  <div class="stages-grid">
    <div class="stage-card">
      <div class="stage-icon"><i class="ti ti-abc"></i></div>
      <h3>SD (Primary School)</h3>
      <p>Building a strong foundation in mathematical logic, critical reading skills, and interactive science using fun, conceptual methodology.</p>
    </div>
    <div class="stage-card">
      <div class="stage-icon"><i class="ti ti-school"></i></div>
      <h3>SMP (Junior High School)</h3>
      <p>Strengthening essential conceptual understandings, school exam preparations, and deeply adapting to the national curriculum.</p>
    </div>
    <div class="stage-card">
      <div class="stage-icon"><i class="ti ti-books"></i></div>
      <h3>SMA (Senior High School)</h3>
      <p>Focusing on advanced academic syllabus, boosting report card scores, and structural school graduation preparation.</p>
    </div>
    <div class="stage-card">
      <div class="stage-icon"><i class="ti ti-award"></i></div>
      <h3>Alumni &amp; Intensive UTBK</h3>
      <p>Accelerated special programs engineered for SNBT fighters, SIMAK UI, UTUL UGM, and official state academy selections.</p>
    </div>
  </div>
</section>

<section class="features-section">
  <div class="section-header">
    <p>SaaS Capabilities</p>
    <h2>Digital Workspace Core Benefits</h2>
  </div>
  
  <div class="features-grid">
    <div class="feature-card" onclick="openFeature('video')">
      <div class="feat-icon-box"><i class="ti ti-device-laptop"></i></div>
      <h3>Courses &amp; Media</h3>
      <span class="arrow-link">View details <i class="ti ti-arrow-right"></i></span>
    </div>
    <div class="feature-card" onclick="openFeature('latihan')">
      <div class="feat-icon-box"><i class="ti ti-certificate"></i></div>
      <h3>Practice Exams</h3>
      <span class="arrow-link">View details <i class="ti ti-arrow-right"></i></span>
    </div>
    <div class="feature-card" onclick="openFeature('kehadiran')">
      <div class="feat-icon-box"><i class="ti ti-user-check"></i></div>
      <h3>Student Attendance</h3>
      <span class="arrow-link">View details <i class="ti ti-arrow-right"></i></span>
    </div>
    <div class="feature-card" onclick="openFeature('pembayaran')">
      <div class="feat-icon-box"><i class="ti ti-wallet"></i></div>
      <h3>Billing Management</h3>
      <span class="arrow-link">View details <i class="ti ti-arrow-right"></i></span>
    </div>
  </div>
</section>

<footer class="premium-footer" id="contact">
  <div class="footer-container">
    <div class="footer-brand-side">
      <h3>Edu<span>Zone</span></h3>
      <p>A premium learning ecosystem driven by digital workspace technology. Merging seamless administrative control with high-caliber academic quality anywhere.</p>
      <div class="footer-socials">
        <a href="https://www.instagram.com/bimbel.eduzone?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="><i class="ti ti-brand-instagram"></i></a>
        <a href="#"><i class="ti ti-brand-youtube"></i></a>
        <a href="#"><i class="ti ti-brand-tiktok"></i></a>
      </div>
    </div>
    
    <div class="footer-links-side">
      <h4>Navigation</h4>
      <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#stages">Programs</a></li>
        <li><a href="#achievements">Achievements</a></li>
        <li><a href="login.php">Portal Login</a></li>
      </ul>
    </div>
    
    <div class="footer-contact-side">
      <h4>Operational Hub</h4>
      <div class="contact-item">
        <i class="ti ti-map-pin"></i>
        <p><a href="https://share.google/t2y8vP5zeS0OY9kTd" target="_blank">Jl. Kemurnian II No.13, RT.9/RW.1, Glodok, Kec. Taman Sari, Kota Jakarta Barat, Daerah Khusus Ibukota Jakarta 11120</a></p>
      </div>
      <div class="contact-item">
        <i class="ti ti-brand-whatsapp"></i>
        <p><a href="https://wa.me/6281999888777" target="_blank">+62 819-9988-8777 (Admissions Center)</a></p>
      </div>
      <div class="contact-item">
        <i class="ti ti-mail"></i>
        <p><a href="mailto:eduzonebimbel@gmail.com">eduzonebimbel@gmail.com</a></p>
      </div>
    </div>
  </div>
  
  <div class="footer-bottom">
    <p>&copy; 2026 EduZone Premium Learning Ecosystem. All rights reserved.</p>
    <p>Corporate Verified Link Dashboard</p>
  </div>
</footer>

<div class="modal-overlay" id="featureModal">
  <div class="modal-window">
    <button class="modal-close-btn" onclick="closeModal('featureModal')">✕</button>
    <h3 id="modalTitle"></h3>
    <p id="modalDesc"></p>
    <button class="btn-prime" onclick="window.location.href='login.php'">Access Core Feature</button>
  </div>
</div>

<div class="modal-overlay" id="mulaiModal">
  <div class="modal-window">
    <button class="modal-close-btn" onclick="closeModal('mulaiModal')">✕</button>
    <h3>Welcome to the Portal</h3>
    <p>Account authentication is required to identify your workspace privileges. Please proceed to log into your workspace dashboard.</p>
    <button class="btn-prime" onclick="window.location.href='login.php'">Log In to System</button>
  </div>
</div>

<script>
  const features = {
    video: {
      title: 'Interactive Courses & Media',
      desc: 'Seamless integration of digital curriculum modules paired alongside high-resolution video streams designed for optimal conceptual understanding.'
    },
    latihan: {
      title: 'Self-Evaluation & Practice',
      desc: 'An array of periodic assessment tools engineered to accurately benchmark exam readiness with instant grading reports.'
    },
    kehadiran: {
      title: 'Centralized Attendance Systems',
      desc: 'Transparently track session logs, check chronological attendance performance, and update profiles in real-time.'
    },
    pembayaran: {
      title: 'Financial Administration Systems',
      desc: 'Effortlessly audit regular tuition balances, secure billing invoices, and download transactional statements with verified tracking.'
    }
  };

  function openFeature(key) {
    const f = features[key];
    document.getElementById('modalTitle').textContent = f.title;
    document.getElementById('modalDesc').textContent = f.desc;
    openModal('featureModal');
  }

  function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
  }

  const cards = document.querySelectorAll('.feature-card');
  cards.forEach(card => {
    card.addEventListener('mousemove', e => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      card.style.setProperty('--x', `${x}px`);
      card.style.setProperty('--y', `${y}px`);
    });
  });

  document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) {
      if (e.target === this) closeModal(this.id);
    });
  });
</script>
</body>
</html>