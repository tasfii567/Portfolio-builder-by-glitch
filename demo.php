<?php include 'includes/header.php'; ?>

<section class="demo-wrap">

    <div class="demo-head">
        <span class="demo-eyebrow">Templates</span>
        <h1 class="demo-h1">Portfolio <em>Templates</em></h1>
        <p class="demo-sub">Choose your favorite portfolio design and launch in minutes</p>
    </div>

    <?php
    $templates = [
        ['img' => '01', 'title' => 'Creative Portfolio',    'desc' => 'Modern Creative Agency Design',      'tag' => 'Popular'],
        ['img' => '02', 'title' => 'Developer Portfolio',   'desc' => 'Clean Tech & Code Showcase',          'tag' => 'Dev'],
        ['img' => '01', 'title' => 'Photography Portfolio', 'desc' => 'Elegant Visual Portfolio',            'tag' => 'Visual'],
        ['img' => '02', 'title' => 'Designer Portfolio',    'desc' => 'Bold UI/UX Portfolio Design',         'tag' => 'Design'],
        ['img' => '01', 'title' => 'Freelancer Portfolio',  'desc' => 'Professional Services Showcase',      'tag' => 'Freelance'],
        ['img' => '02', 'title' => 'Student Portfolio',     'desc' => 'Academic & Project Highlight',        'tag' => 'Student'],
        ['img' => '01', 'title' => 'Artist Portfolio',      'desc' => 'Gallery Style Creative Layout',       'tag' => 'Art'],
        ['img' => '02', 'title' => 'Business Portfolio',    'desc' => 'Corporate Professional Design',       'tag' => 'Business'],
    ];
    ?>

    <div class="demo-grid">
        <?php foreach($templates as $t): ?>
        <div class="demo-card">

            <div class="demo-img-wrap">
                <img src="assets/images/portfolio 0<?= $t['img'] ?>.png" alt="<?= $t['title'] ?>">
                <div class="demo-overlay">
                    <a href="login.php" class="demo-preview-btn">
                        <i class="bi bi-eye me-2"></i>See Template
                    </a>
                </div>
                <span class="demo-tag"><?= $t['tag'] ?></span>
            </div>

            <div class="demo-card-body">
                <h3 class="demo-card-title"><?= $t['title'] ?></h3>
                <p class="demo-card-desc"><?= $t['desc'] ?></p>
                <a href="login.php" class="demo-use-btn">Use this template →</a>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

</section>

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

.demo-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    transition: transform .25s, box-shadow .25s;
}

.demo-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(43,41,38,.08);
}

.demo-img-wrap {
    position: relative;
    overflow: hidden;
}

.demo-img-wrap img {
    width: 100%;
    height: 210px;
    object-fit: cover;
    display: block;
    transition: transform .4s;
}

.demo-card:hover .demo-img-wrap img {
    transform: scale(1.06);
}

.demo-overlay {
    position: absolute;
    inset: 0;
    background: rgba(43,41,38,.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .3s;
}

.demo-card:hover .demo-overlay { opacity: 1; }

.demo-preview-btn {
    background: #fff;
    color: var(--text-dark);
    text-decoration: none;
    padding: 11px 22px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    transition: background .2s;
}

.demo-preview-btn:hover {
    background: var(--bg);
    color: var(--text-dark);
}

.demo-tag {
    position: absolute;
    top: 12px;
    left: 12px;
    background: var(--brand-dark);
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 6px;
}

.demo-card-body {
    padding: 18px 20px 20px;
}

.demo-card-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-dark);
    letter-spacing: -.02em;
    margin-bottom: 4px;
}

.demo-card-desc {
    font-size: 13px;
    color: var(--text-mid);
    line-height: 1.55;
    margin-bottom: 14px;
}

.demo-use-btn {
    font-size: 13px;
    font-weight: 600;
    color: var(--accent);
    text-decoration: none;
    letter-spacing: -.01em;
    transition: color .2s;
}

.demo-use-btn:hover { color: var(--brand-dark); }

@media (max-width: 1100px) {
    .demo-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 780px) {
    .demo-grid { grid-template-columns: repeat(2, 1fr); }
    .demo-h1 { font-size: 34px; }
}

@media (max-width: 500px) {
    .demo-grid { grid-template-columns: 1fr; }
    .demo-wrap { padding: 56px 5% 72px; }
}
</style>

<?php include 'includes/footer.php'; ?>