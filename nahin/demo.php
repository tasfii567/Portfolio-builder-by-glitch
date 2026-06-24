<?php include 'includes/header.php'; ?>

<section class="demo-wrap">

    <div class="demo-head">
        <span class="demo-eyebrow">Templates</span>
        <h1 class="demo-h1">Portfolio <em>Templates</em></h1>
        <p class="demo-sub">Choose your favorite portfolio design and launch in minutes</p>
    </div>

    <?php
    require_once 'config/db.php';

    $stmt = $pdo->query("
        SELECT name, html_file
        FROM templates
        WHERE status = 'active'
        ORDER BY id ASC
    ");
    $templateRows = $stmt->fetchAll();
    $templates = array_map(function ($template, $index) {
        return [
            'img' => str_pad((string) (($index % 2) + 1), 2, '0', STR_PAD_LEFT),
            'title' => $template['name'],
            'desc' => 'Portfolio template',
            'tag' => 'Template',
            'file' => $template['html_file'],
        ];
    }, $templateRows, array_keys($templateRows));
    ?>

    <div class="demo-grid">
        <?php foreach ($templates as $t): ?>
            <div class="demo-card">

                <div class="demo-img-wrap">
                    <div class="demo-iframe-wrap">
                        <iframe
                            src="<?= htmlspecialchars($t['file']) ?>"
                            class="demo-iframe"
                            scrolling="no"
                            tabindex="-1"
                            loading="lazy"
                            title="<?= htmlspecialchars($t['title']) ?> preview">
                        </iframe>
                        <div class="demo-iframe-block"></div>
                    </div>
                    <!-- Thumbnail image overlay -->
                    <div class="demo-thumb-overlay">

                    </div>
                    <div class="demo-overlay">
                        <button class="demo-preview-btn" onclick="openPreview('<?= htmlspecialchars($t['file']) ?>', '<?= htmlspecialchars($t['title']) ?>')">
                            <i class="bi bi-eye"></i> See Template
                        </button>
                    </div>
                    <span class="demo-tag"><?= htmlspecialchars($t['tag']) ?></span>
                </div>

                <div class="demo-card-body">
                    <h3 class="demo-card-title"><?= htmlspecialchars($t['title']) ?></h3>
                    <p class="demo-card-desc"><?= htmlspecialchars($t['desc']) ?></p>
                    <button class="demo-use-btn" onclick="openPreview('<?= htmlspecialchars($t['file']) ?>', '<?= htmlspecialchars($t['title']) ?>')">
                        Use this template →
                    </button>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</section>

<!-- ═══ TEMPLATE PREVIEW MODAL ═══ -->
<div id="templateModal" class="tpl-modal-bg" onclick="handleModalBgClick(event)">
    <div class="tpl-modal">

        <div class="tpl-modal-header">
            <div class="tpl-modal-meta">
                <span class="tpl-modal-title" id="modalTitle">Preview</span>
            </div>
            <div class="tpl-modal-actions">
                <a id="modalOpenLink" href="#" target="_blank" class="tpl-action-btn" title="Open in new tab">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <button class="tpl-action-btn" onclick="closePreview()" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <div class="tpl-modal-body">
            <div class="tpl-loading" id="modalLoading">
                <div class="tpl-spinner"></div>
                <span>Loading preview…</span>
            </div>
            <iframe id="templateFrame" title="Template preview" onload="hideLoading()"></iframe>
        </div>

        <div class="tpl-modal-footer">
            <span class="tpl-footer-note">This is a live preview of the template</span>
            <a id="modalUseBtn" href="login.php" class="tpl-use-btn-modal">Use this template →</a>
        </div>

    </div>
</div>

<style>
    .demo-wrap {
        background: var(--bg);
        padding: 80px 5% 100px;
    }

    .demo-head {
        text-align: center;
        margin-bottom: 56px;
    }

    .demo-eyebrow {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 14px;
    }

    .demo-h1 {
        font-size: 48px;
        font-weight: 800;
        letter-spacing: -.04em;
        color: var(--text-dark);
        line-height: 1.08;
        margin-bottom: 14px;
    }

    .demo-h1 em {
        font-style: normal;
        color: var(--accent);
    }

    .demo-sub {
        font-size: 16px;
        color: var(--text-mid);
        max-width: 420px;
        margin: 0 auto;
        line-height: 1.65;
    }

    .demo-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 28px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* ── Card ── */
    .demo-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        transition: transform .25s, box-shadow .25s;
    }

    .demo-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(43, 41, 38, .10);
    }

    /* ── Image / iframe area ── */
    .demo-img-wrap {
        position: relative;
        overflow: hidden;
        height: 210px;
    }

    .demo-iframe-wrap {
        width: 100%;
        height: 210px;
        overflow: hidden;
        position: relative;
        background: #f5f4f2;
    }

    .demo-iframe {
        width: 1280px;
        height: 900px;
        border: none;
        display: block;
        transform: scale(0.233);
        transform-origin: top left;
        pointer-events: none;
        transition: transform .4s;
    }

    .demo-iframe-block {
        position: absolute;
        inset: 0;
        z-index: 1;
    }

    .demo-card:hover .demo-iframe {
        transform: scale(0.233) translateY(-12px);
    }

    /* ── Thumbnail overlay — সরে যায় hover এ ── */
    .demo-thumb-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        transition: opacity .35s ease, transform .35s ease;
    }

    .demo-thumb-overlay img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .demo-card:hover .demo-thumb-overlay {
        opacity: 0;
        transform: scale(1.04);
    }

    /* ── See Template overlay button ── */
    .demo-overlay {
        position: absolute;
        inset: 0;
        background: rgba(30, 26, 22, .45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        z-index: 3;
        transition: opacity .3s;
    }

    .demo-card:hover .demo-overlay {
        opacity: 1;
    }

    .demo-preview-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #fff;
        color: var(--text-dark, #1a1a1a);
        border: none;
        cursor: pointer;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        transition: background .2s, transform .15s;
    }

    .demo-preview-btn:hover {
        background: #f5f4f2;
        transform: scale(1.04);
    }

    /* ── Tag badge ── */
    .demo-tag {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 4;
        background: var(--brand-dark, #1e3a1e);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 6px;
    }

    /* ── Card body ── */
    .demo-card-body {
        padding: 18px 20px 20px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .demo-card-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: -.02em;
        margin: 0;
    }

    .demo-card-desc {
        font-size: 13px;
        color: var(--text-mid);
        line-height: 1.55;
        margin: 0 0 10px;
    }

    /* ── "Use this template" — proper button ── */
    .demo-use-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: fit-content;
        background: none;
        border: 1.5px solid var(--accent, #3a6e3a);
        color: var(--accent, #3a6e3a);
        font-size: 13px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        letter-spacing: -.01em;
        transition: background .2s, color .2s, transform .15s;
        text-decoration: none;
    }

    .demo-use-btn:hover {
        background: var(--accent, #3a6e3a);
        color: #fff;
        transform: translateY(-1px);
    }

    /* ── Responsive ── */
    @media (max-width: 1100px) {
        .demo-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 780px) {
        .demo-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .demo-h1 {
            font-size: 34px;
        }
    }

    @media (max-width: 500px) {
        .demo-grid {
            grid-template-columns: 1fr;
        }

        .demo-wrap {
            padding: 56px 5% 72px;
        }
    }

    /* ═══ Modal ═══ */
    .tpl-modal-bg {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, .6);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .tpl-modal-bg.active {
        display: flex;
        animation: tplFadeIn .2s ease;
    }

    @keyframes tplFadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .tpl-modal {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 1100px;
        height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 32px 80px rgba(0, 0, 0, .25);
        animation: tplSlideUp .25s cubic-bezier(.4, 0, .2, 1);
    }

    @keyframes tplSlideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .tpl-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        border-bottom: 1px solid #f0eeec;
        gap: 12px;
        flex-shrink: 0;
    }

    .tpl-modal-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex: 1;
    }

    .tpl-modal-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-dark, #1a1a1a);
        white-space: nowrap;
    }

    .tpl-modal-url {
        font-size: 12px;
        font-family: monospace;
        color: #888;
        background: #f5f4f2;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #e8e6e3;
        white-space: nowrap;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .tpl-modal-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .tpl-action-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e8e6e3;
        border-radius: 8px;
        background: none;
        color: #666;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s, color .15s;
    }

    .tpl-action-btn:hover {
        background: #f5f4f2;
        color: #1a1a1a;
    }

    .tpl-modal-body {
        flex: 1;
        position: relative;
        overflow: hidden;
    }

    .tpl-loading {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: #faf9f7;
        font-size: 13px;
        color: #888;
        z-index: 2;
    }

    .tpl-spinner {
        width: 28px;
        height: 28px;
        border: 2.5px solid #e8e6e3;
        border-top-color: var(--accent, #3a6e3a);
        border-radius: 50%;
        animation: tplSpin .7s linear infinite;
    }

    @keyframes tplSpin {
        to {
            transform: rotate(360deg);
        }
    }

    #templateFrame {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }

    .tpl-modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        border-top: 1px solid #f0eeec;
        flex-shrink: 0;
    }

    .tpl-footer-note {
        font-size: 12px;
        color: #aaa;
    }

    .tpl-use-btn-modal {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--accent, #3a6e3a);
        color: #fff;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 20px;
        border-radius: 10px;
        transition: opacity .2s;
    }

    .tpl-use-btn-modal:hover {
        opacity: .88;
        color: #fff;
    }

    @media (max-width: 600px) {
        .tpl-modal {
            height: 95vh;
            border-radius: 12px;
        }

        .tpl-modal-url {
            display: none;
        }

        .tpl-footer-note {
            display: none;
        }
    }
</style>

<script>
    function openPreview(file, title) {
        const modal = document.getElementById('templateModal');
        const frame = document.getElementById('templateFrame');
        const loader = document.getElementById('modalLoading');
        const mTitle = document.getElementById('modalTitle');
        const mLink = document.getElementById('modalOpenLink');

        mTitle.textContent = title;
        mLink.href = file;

        loader.style.display = 'flex';
        frame.src = '';
        frame.src = file;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closePreview() {
        const modal = document.getElementById('templateModal');
        const frame = document.getElementById('templateFrame');
        modal.classList.remove('active');
        frame.src = '';
        document.body.style.overflow = '';
    }

    function hideLoading() {
        document.getElementById('modalLoading').style.display = 'none';
    }

    function handleModalBgClick(e) {
        if (e.target === document.getElementById('templateModal')) closePreview();
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closePreview();
    });
</script>

<?php include 'includes/footer.php'; ?>
