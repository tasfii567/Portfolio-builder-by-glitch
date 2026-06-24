<?php include 'includes/header.php'; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif !important;
        background: #f7f3ea;
        color: #2b2926;
        overflow-x: hidden;
    }

    .navbar {
        background: rgba(247, 243, 234, 0.9) !important;
        backdrop-filter: blur(20px);
        border-bottom: 1px solid #e8e1d3 !important;
        padding: 18px 0 !important;
    }

    .navbar-brand {
        font-size: 16px !important;
        font-weight: 700;
        color: #2b2926 !important;
        letter-spacing: -.02em;
    }

    .navbar-brand span {
        color: #6b8c5a;
    }

    .nav-link {
        font-size: 14px !important;
        color: #8a8270 !important;
        font-weight: 400 !important;
    }

    .nav-link:hover {
        color: #2b2926 !important;
    }

    .btn-nav {
        background: #36402c !important;
        color: #fff !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 9px 20px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        transition: all .2s !important;
    }

    .btn-nav:hover {
        background: #46532f !important;
        transform: translateY(-1px);
    }

    .hero-section {
        padding: 100px 0 80px;
        background: #f7f3ea;
    }

    .hero-eyebrow {
        font-size: 12px;
        font-weight: 500;
        color: #6b8c5a;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 20px;
        display: block;
    }

    .hero h1 {
        font-size: 54px;
        font-weight: 800;
        line-height: 1.08;
        letter-spacing: -.04em;
        color: #2b2926;
        margin-bottom: 20px;
    }

    .hero h1 em {
        font-style: normal;
        color: #6b8c5a;
    }

    .hero-sub {
        font-size: 16px;
        color: #8a8270;
        line-height: 1.65;
        margin-bottom: 36px;
        font-weight: 400;
    }

    .btn-primary-main {
        background: #36402c;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 13px 28px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: all .2s;
        letter-spacing: -.01em;
    }

    .btn-primary-main:hover {
        background: #46532f;
        color: #fff;
        transform: translateY(-1px);
    }

    .btn-ghost-main {
        background: transparent;
        color: #8a8270;
        border: 1px solid #ddd5c0;
        border-radius: 10px;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 400;
        text-decoration: none;
        display: inline-block;
        transition: all .2s;
        margin-left: 10px;
    }

    .btn-ghost-main:hover {
        border-color: #36402c;
        color: #2b2926;
    }

    .hero-meta {
        display: flex;
        gap: 36px;
        margin-top: 48px;
        padding-top: 36px;
        border-top: 1px solid #e8e1d3;
    }

    .meta-num {
        font-size: 24px;
        font-weight: 700;
        color: #2b2926;
        letter-spacing: -.03em;
    }

    .meta-label {
        font-size: 12px;
        color: #a39c89;
        margin-top: 3px;
    }

    .hero-visual {
        position: relative;
        height: 500px;
    }

    .v-card {
        position: absolute;
        background: #fff;
        border: 1px solid #e8e1d3;
        border-radius: 16px;
        padding: 16px;
    }

    .vc-main {
        width: 260px;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        animation: vfloatM 6s ease-in-out infinite;
    }

    .vc-a {
        width: 180px;
        top: 80px;
        left: -10px;
        animation: vfloatS 6s ease-in-out infinite;
    }

    .vc-b {
        width: 160px;
        top: 80px;
        right: -10px;
        animation: vfloatS 5s ease-in-out infinite;
        animation-delay: -1.5s;
    }

    .vc-c {
        width: 200px;
        bottom: 100px;
        left: 0;
        animation: vfloatS 7s ease-in-out infinite;
        animation-delay: -3s;
    }

    .vc-d {
        width: 170px;
        bottom: 120px;
        right: 0;
        animation: vfloatS 5.5s ease-in-out infinite;
        animation-delay: -4.5s;
    }

    @keyframes vfloatM {

        0%,
        100% {
            transform: translateX(-50%) translateY(0);
        }

        50% {
            transform: translateX(-50%) translateY(-14px);
        }
    }

    @keyframes vfloatS {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .avatar-ring {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #6b8c5a;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .chip {
        display: inline-block;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 8px;
        border-radius: 6px;
        background: #ede7d6;
        color: #5c5544;
    }

    .chip-p {
        background: #e8efe0;
        color: #4f6b43;
    }

    .chip-g {
        background: #ece4cf;
        color: #6b5a35;
    }

    .chip-a {
        background: #f2e6cb;
        color: #8a6a35;
    }

    .dot-on {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #6f9a52;
    }

    .sparkline {
        height: 32px;
        display: flex;
        align-items: flex-end;
        gap: 3px;
        margin-top: 10px;
    }

    .sparkline span {
        display: block;
        border-radius: 3px 3px 0 0;
        background: #ddd2b8;
        width: 8px;
    }

    .sparkline span.hi {
        background: #6b8c5a;
    }

    .partners-strip {
        padding: 0 0 60px;
    }

    .logos-row {
        display: flex;
        align-items: center;
        gap: 40px;
        flex-wrap: wrap;
    }

    .logos-label {
        font-size: 12px;
        color: #a39c89;
        font-weight: 400;
    }

    .logo-item {
        font-size: 13px;
        font-weight: 600;
        color: #c7bfa8;
        letter-spacing: .02em;
    }

    .section-label {
        font-size: 11px;
        font-weight: 600;
        color: #6b8c5a;
        letter-spacing: .1em;
        text-transform: uppercase;
        margin-bottom: 14px;
        display: block;
    }

    .section-h {
        font-size: 38px;
        font-weight: 800;
        letter-spacing: -.03em;
        color: #2b2926;
        line-height: 1.1;
        margin-bottom: 48px;
    }

    .feat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1px;
        background: #e8e1d3;
        border-radius: 16px;
        overflow: hidden;
    }

    .feat-item {
        background: #fff;
        padding: 28px;
        transition: background .2s;
    }

    .feat-item:hover {
        background: #f7f3ea;
    }

    .feat-num {
        font-size: 11px;
        font-weight: 600;
        color: #c7bfa8;
        letter-spacing: .06em;
        margin-bottom: 20px;
    }

    .feat-icon {
        width: 36px;
        height: 36px;
        background: #ede7d6;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b8c5a;
        font-size: 18px;
        margin-bottom: 14px;
    }

    .feat-title {
        font-size: 15px;
        font-weight: 600;
        color: #2b2926;
        margin-bottom: 6px;
        letter-spacing: -.01em;
    }

    .feat-desc {
        font-size: 13px;
        color: #a39c89;
        line-height: 1.55;
        margin: 0;
    }

    .tpl-wrap {
        overflow: hidden;
        margin-top: 48px;
    }

    .tpl-track {
        display: flex;
        gap: 16px;
        width: max-content;
        animation: tscroll 25s linear infinite;
    }

    .tpl-track:hover {
        animation-play-state: paused;
    }

    @keyframes tscroll {
        from {
            transform: translateX(0);
        }

        to {
            transform: translateX(-50%)
        }
    }

    .tpl-card {
        width: 200px;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e8e1d3;
        background: #fff;
        flex-shrink: 0;
    }

    .tpl-thumb {
        height: 130px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        color: rgba(255, 255, 255, .85);
    }

    .tpl-info {
        padding: 12px 14px;
    }

    .tpl-name {
        font-size: 13px;
        font-weight: 600;
        color: #2b2926;
    }

    .tpl-role {
        font-size: 11px;
        color: #a39c89;
        margin-top: 2px;
    }

    .testimonial-wrap {
        background: #36402c;
        padding: 100px 0;
    }

    .test-quote {
        font-size: 26px;
        font-weight: 500;
        color: #fff;
        line-height: 1.55;
        letter-spacing: -.02em;
        margin-bottom: 40px;
    }

    .test-av {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #8aa66b;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        margin: 0 auto 12px;
    }

    .test-name {
        font-size: 14px;
        font-weight: 500;
        color: #ece5d4;
    }

    .test-role {
        font-size: 12px;
        color: #9aa68c;
        margin-top: 3px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: #e8e1d3;
        border-radius: 16px;
        overflow: hidden;
    }

    .stat-box {
        background: #fff;
        padding: 32px 28px;
    }

    .stat-big {
        font-size: 40px;
        font-weight: 800;
        color: #2b2926;
        letter-spacing: -.04em;
    }

    .stat-big span {
        color: #6b8c5a;
    }

    .stat-lbl {
        font-size: 13px;
        color: #a39c89;
        margin-top: 6px;
    }

    .cta-inner {
        background: #36402c;
        border-radius: 24px;
        padding: 64px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
        flex-wrap: wrap;
    }

    .cta-h {
        font-size: 36px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.03em;
        line-height: 1.15;
        max-width: 360px;
        margin: 0;
    }

    .cta-h span {
        color: #a8c99a;
    }

    .btn-white {
        background: #fff;
        color: #2b2926;
        border: none;
        border-radius: 10px;
        padding: 13px 28px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all .2s;
    }

    .btn-white:hover {
        background: #ede7d6;
        color: #2b2926;
    }

    .btn-ghost-w {
        background: transparent;
        color: rgba(255, 255, 255, .5);
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 10px;
        padding: 12px 24px;
        font-size: 14px;
        text-decoration: none;
        display: inline-block;
        transition: all .2s;
        margin-left: 10px;
    }

    .btn-ghost-w:hover {
        border-color: rgba(255, 255, 255, .4);
        color: #fff;
    }

    .fade-up {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity .7s cubic-bezier(.16, 1, .3, 1), transform .7s cubic-bezier(.16, 1, .3, 1);
    }

    .fade-up.in {
        opacity: 1;
        transform: none;
    }

    @media(max-width:768px) {
        .hero h1 {
            font-size: 36px;
        }

        .feat-grid {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .cta-inner {
            flex-direction: column;
            text-align: center;
            padding: 40px 24px;
        }

        .hero-visual {
            height: 300px;
        }
    }
</style>

<!-- HERO -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5 hero">
            <div class="col-lg-6">
                <span class="hero-eyebrow">Portfolio Platform</span>
                <h1>Your work deserves to be <em>seen.</em></h1>
                <p class="hero-sub">Build a beautiful portfolio in minutes. No code, no clutter — just your work, perfectly presented.</p>
                <div>
                    <a href="reg.php" class="btn-primary-main">Start for free</a>
                    <a href="demo.php" class="btn-ghost-main">Browse templates</a>
                </div>
                <div class="hero-meta">
                    <div>
                        <div class="meta-num">500+</div>
                        <div class="meta-label">Portfolios live</div>
                    </div>
                    <div>
                        <div class="meta-num">5+</div>
                        <div class="meta-label">Templates</div>
                    </div>
                    <div>
                        <div class="meta-num">Free</div>
                        <div class="meta-label">To get started</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="v-card vc-main">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                            <div class="avatar-ring">AJ</div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#2b2926;">Alex Johnson</div>
                                <div style="font-size:11px;color:#a39c89;">Full Stack Developer</div>
                            </div>
                            <div class="dot-on" style="margin-left:auto;"></div>
                        </div>
                        <div style="display:flex;gap:5px;margin-bottom:12px;">
                            <span class="chip chip-p">React</span>
                            <span class="chip chip-g">Node.js</span>
                            <span class="chip chip-a">Python</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:#a39c89;padding-top:10px;border-top:1px solid #e8e1d3;">
                            <span>3 projects</span><span>5 skills</span><span style="color:#6f9a52;">&#9679; Published</span>
                        </div>
                    </div>
                    <div class="v-card vc-a">
                        <div style="font-size:11px;color:#a39c89;margin-bottom:8px;">Profile views</div>
                        <div style="font-size:22px;font-weight:700;color:#2b2926;letter-spacing:-.02em;">1,248</div>
                        <div style="font-size:11px;color:#6f9a52;margin-top:3px;">&#8593; 24% this week</div>
                        <div class="sparkline">
                            <span style="height:40%"></span><span style="height:55%"></span><span style="height:45%"></span><span style="height:70%"></span><span style="height:60%"></span><span class="hi" style="height:80%"></span><span class="hi" style="height:100%"></span>
                        </div>
                    </div>
                    <div class="v-card vc-b">
                        <div style="font-size:11px;color:#a39c89;margin-bottom:10px;">Template</div>
                        <div style="width:100%;height:48px;background:#36402c;border-radius:8px;"></div>
                        <div style="font-size:11px;font-weight:500;color:#2b2926;margin-top:8px;">Developer Pro</div>
                    </div>
                    <div class="v-card vc-c">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:28px;height:28px;background:#e3ecd5;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#4f6b43;font-size:14px;">&#10003;</div>
                            <div>
                                <div style="font-size:12px;font-weight:600;color:#2b2926;">Portfolio published!</div>
                                <div style="font-size:11px;color:#a39c89;">portfoliobuilder.io/alex</div>
                            </div>
                        </div>
                    </div>
                    <div class="v-card vc-d">
                        <div style="font-size:11px;color:#a39c89;margin-bottom:8px;">Recruiter views</div>
                        <div style="display:flex;align-items:center;">
                            <div style="width:24px;height:24px;border-radius:50%;background:#6b8c5a;border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:9px;color:#fff;font-weight:700;">G</div>
                            <div style="width:24px;height:24px;border-radius:50%;background:#c08a4a;border:2px solid #fff;margin-left:-6px;display:flex;align-items:center;justify-content:center;font-size:9px;color:#fff;font-weight:700;">A</div>
                            <div style="width:24px;height:24px;border-radius:50%;background:#8aa66b;border:2px solid #fff;margin-left:-6px;display:flex;align-items:center;justify-content:center;font-size:9px;color:#fff;font-weight:700;">M</div>
                            <span style="font-size:11px;color:#a39c89;margin-left:6px;">+5 more</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PARTNERS -->
<div class="container partners-strip">
    <div class="logos-row">
        <span class="logos-label">Trusted by graduates from</span>
        <span class="logo-item">BUET</span>
        <span class="logo-item">DU</span>
        <span class="logo-item">NSU</span>
        <span class="logo-item">BRAC</span>
        <span class="logo-item">IUT</span>
        <span class="logo-item">MIT</span>
    </div>
</div>

<!-- FEATURES -->
<div class="container py-5 fade-up">
    <span class="section-label">Features</span>
    <h2 class="section-h">Built for people who care<br>about how their work looks.</h2>
    <div class="feat-grid">
        <div class="feat-item">
            <div class="feat-num">01</div>
            <div class="feat-icon"><i class="bi bi-layout-text-window-reverse"></i></div>
            <div class="feat-title">5+ templates</div>
            <p class="feat-desc">Professionally designed layouts for every field and style.</p>
        </div>
        <div class="feat-item">
            <div class="feat-num">02</div>
            <div class="feat-icon"><i class="bi bi-pencil-square"></i></div>
            <div class="feat-title">Easy editor</div>
            <p class="feat-desc">Add projects, skills and experience in a clean dashboard.</p>
        </div>
        <div class="feat-item">
            <div class="feat-num">03</div>
            <div class="feat-icon"><i class="bi bi-file-earmark-pdf"></i></div>
            <div class="feat-title">PDF resume</div>
            <p class="feat-desc">Export a polished resume from your profile in one click.</p>
        </div>
        <div class="feat-item">
            <div class="feat-num">04</div>
            <div class="feat-icon"><i class="bi bi-link-45deg"></i></div>
            <div class="feat-title">Shareable link</div>
            <p class="feat-desc">Your own public URL to send to recruiters and clients.</p>
        </div>
        <div class="feat-item">
            <div class="feat-num">05</div>
            <div class="feat-icon"><i class="bi bi-phone"></i></div>
            <div class="feat-title">Mobile perfect</div>
            <p class="feat-desc">Looks flawless on every screen — phone, tablet, desktop.</p>
        </div>
        <div class="feat-item">
            <div class="feat-num">06</div>
            <div class="feat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="feat-title">Analytics</div>
            <p class="feat-desc">See who visits your portfolio and track your reach.</p>
        </div>
    </div>
</div>

<!-- TEMPLATES SCROLL -->
<div class="container py-5 fade-up">
    <span class="section-label">Templates</span>
    <h2 class="section-h">A template for every profession.</h2>
    <div class="tpl-wrap">
        <div class="tpl-track">
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#36402c;">Developer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Code Portfolio</div>
                    <div class="tpl-role">For developers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#5c4a36;">Designer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Creative Studio</div>
                    <div class="tpl-role">For designers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#2f4a3a;">Analyst</div>
                <div class="tpl-info">
                    <div class="tpl-name">Data Showcase</div>
                    <div class="tpl-role">For analysts</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#2e2820;">Freelancer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Pro Services</div>
                    <div class="tpl-role">For freelancers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#495c3c;">Student</div>
                <div class="tpl-info">
                    <div class="tpl-name">Campus Ready</div>
                    <div class="tpl-role">For students</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#5a4023;">Photographer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Visual Gallery</div>
                    <div class="tpl-role">For photographers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#36402c;">Marketer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Brand Story</div>
                    <div class="tpl-role">For marketers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#5c4a36;">Writer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Word Craft</div>
                    <div class="tpl-role">For writers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#36402c;">Developer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Code Portfolio</div>
                    <div class="tpl-role">For developers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#5c4a36;">Designer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Creative Studio</div>
                    <div class="tpl-role">For designers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#2f4a3a;">Analyst</div>
                <div class="tpl-info">
                    <div class="tpl-name">Data Showcase</div>
                    <div class="tpl-role">For analysts</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#2e2820;">Freelancer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Pro Services</div>
                    <div class="tpl-role">For freelancers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#495c3c;">Student</div>
                <div class="tpl-info">
                    <div class="tpl-name">Campus Ready</div>
                    <div class="tpl-role">For students</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#5a4023;">Photographer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Visual Gallery</div>
                    <div class="tpl-role">For photographers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#36402c;">Marketer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Brand Story</div>
                    <div class="tpl-role">For marketers</div>
                </div>
            </div>
            <div class="tpl-card">
                <div class="tpl-thumb" style="background:#5c4a36;">Writer</div>
                <div class="tpl-info">
                    <div class="tpl-name">Word Craft</div>
                    <div class="tpl-role">For writers</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TESTIMONIAL -->
<div class="testimonial-wrap fade-up">
    <div class="container text-center">
        <div style="max-width:680px;margin:0 auto;">
            <p class="test-quote">"I had a portfolio live in 20 minutes. My recruiter said it was the most professional one they'd seen."</p>
            <div class="test-av">NS</div>
            <div class="test-name">Nafisa Sharmin</div>
            <div class="test-role">Diligite Developer · CTG</div>
        </div>
    </div>
</div>

<!-- STATS -->
<div class="container py-5 fade-up">
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-big">500<span>+</span></div>
            <div class="stat-lbl">Portfolios published</div>
        </div>
        <div class="stat-box">
            <div class="stat-big">20<span>+</span></div>
            <div class="stat-lbl">Premium templates</div>
        </div>
        <div class="stat-box">
            <div class="stat-big">98<span>%</span></div>
            <div class="stat-lbl">User satisfaction</div>
        </div>
        <div class="stat-box">
            <div class="stat-big">0<span>৳</span></div>
            <div class="stat-lbl">Cost to start</div>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="container py-5 fade-up">
    <div class="cta-inner">
        <h2 class="cta-h">Ready to build something <span>impressive?</span></h2>
        <div>
            <a href="reg.php" class="btn-white">Get started free</a>
            <a href="demo.php" class="btn-ghost-w">See templates</a>
        </div>
    </div>
</div>

<script>
    const io = new IntersectionObserver(entries => {
        entries.forEach((e, i) => {
            if (e.isIntersecting) setTimeout(() => e.target.classList.add('in'), i * 80);
        });
    }, {
        threshold: 0.1
    });
    document.querySelectorAll('.fade-up').forEach(el => io.observe(el));
</script>

<?php include 'includes/footer.php'; ?>