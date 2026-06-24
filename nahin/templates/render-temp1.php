<!doctype html>
<html lang="en" x-data="app()" :class="{'dark':dark}" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($name) ?> — Portfolio</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans:ital,wght@0,400;0,700;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    fontFamily: {
                        display: ["PT Sans", "sans-serif"],
                        body: ["DM Sans", "sans-serif"]
                    },
                    colors: {
                        accent: "#FF6B2B",
                        "accent-light": "#FF8F5C"
                    }
                }
            }
        };
    </script>

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box
        }

        html,
        body {
            font-family: "DM Sans", sans-serif
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: "PT Sans", sans-serif
        }

        body {
            transition: background-color .3s, color .3s
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            opacity: .35;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.05'/%3E%3C/svg%3E")
        }

        ::-webkit-scrollbar {
            width: 5px
        }

        ::-webkit-scrollbar-track {
            background: transparent
        }

        ::-webkit-scrollbar-thumb {
            background: #ff6b2b;
            border-radius: 99px
        }

        .reveal {
            opacity: 0;
            transform: translateY(26px);
            transition: opacity .6s cubic-bezier(.4, 0, .2, 1), transform .6s cubic-bezier(.4, 0, .2, 1)
        }

        .reveal.in {
            opacity: 1;
            transform: none
        }

        .d1 {
            transition-delay: .08s
        }

        .d2 {
            transition-delay: .16s
        }

        .d3 {
            transition-delay: .24s
        }

        .d4 {
            transition-delay: .32s
        }

        .nl {
            position: relative
        }

        .nl::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1.5px;
            background: currentColor;
            transition: width .22s cubic-bezier(.4, 0, .2, 1)
        }

        .nl:hover::after,
        .nl.on::after {
            width: 100%
        }

        .nl.on {
            font-weight: 500
        }

        .shimmer {
            position: relative;
            overflow: hidden
        }

        .shimmer::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: rgba(255, 255, 255, .18);
            transform: skewX(-20deg);
            transition: left .4s cubic-bezier(.4, 0, .2, 1)
        }

        .shimmer:hover::after {
            left: 160%
        }

        .pf {
            overflow: hidden;
            background: #d4d4d8
        }

        .pf img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block
        }

        .card-h {
            transition: transform .28s cubic-bezier(.4, 0, .2, 1), border-color .18s
        }

        .card-h:hover {
            transform: translateY(-4px)
        }

        .stag {
            transition: border-color .18s
        }

        [x-cloak] {
            display: none !important
        }

        /* Back-to-dashboard bar */
        .edit-bar {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            gap: 10px
        }

        .edit-bar a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: .18s
        }

        .edit-bar .btn-edit {
            background: #36402c;
            color: #fff
        }

        .edit-bar .btn-edit:hover {
            background: #46532f
        }

        .edit-bar .btn-dash {
            background: #fff;
            color: #36402c;
            border: 1.5px solid #e8e1d3
        }

        .edit-bar .btn-dash:hover {
            background: #f7f3ea
        }
    </style>
</head>

<body class="bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased">

    <!-- ═══ NAV ═══ -->
    <header class="fixed inset-x-0 top-0 z-50 transition-all duration-300" :class="sc?'bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md shadow-sm shadow-black/5':''">
        <nav class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between" aria-label="Main navigation">
            <a href="#hero" class="font-display font-bold text-xl tracking-tight relative z-10">
                <span class="text-zinc-900 dark:text-white"><?= h(strtolower(substr($firstName, 0, 3))) ?></span><span class="text-accent"><?= h(strtolower(substr($firstName, 3))) ?: 'io' ?></span>
            </a>
            <ul class="hidden md:flex items-center gap-8 text-sm" role="list">
                <li><a href="#about" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='about'?'on !text-zinc-900 dark:!text-white':''">About</a></li>
                <li><a href="#experience" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='experience'?'on !text-zinc-900 dark:!text-white':''">Experience</a></li>
                <li><a href="#work" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='work'?'on !text-zinc-900 dark:!text-white':''">Projects</a></li>
                <li><a href="#education" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='education'?'on !text-zinc-900 dark:!text-white':''">Education</a></li>
                <?= $extraNavMd ?>
                <li><a href="#contact" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='contact'?'on !text-zinc-900 dark:!text-white':''">Contact</a></li>
            </ul>
            <div class="flex items-center gap-3">
                <button @click="dark=!dark" class="w-9 h-9 flex items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors" :aria-label="dark?'Light mode':'Dark mode'">
                    <svg x-show="!dark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                    <svg x-show="dark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                </button>
                <a href="Dashboard.php" class="hidden md:inline-flex items-center gap-2 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium px-4 py-2 rounded-full hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors">
                    🏠 Dashboard
                </a>
                <?php if ($contactEmail): ?>
                    <a href="#contact" class="hidden md:inline-flex items-center gap-2 shimmer bg-accent text-white text-sm font-medium px-5 py-2 rounded-full hover:bg-accent-light transition-colors">
                        Contact me
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                <?php endif; ?>
                <button @click="mm=!mm" class="md:hidden w-9 h-9 flex items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-800" :aria-expanded="mm" aria-label="Toggle menu">
                    <svg x-show="!mm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </nav>
        <div x-show="mm" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden bg-white dark:bg-zinc-950 border-t border-zinc-100 dark:border-zinc-900">
            <ul class="flex flex-col px-6 py-5 gap-4 text-sm font-medium" role="list">
                <li><a href="#about" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">About</a></li>
                <li><a href="#experience" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">Experience</a></li>
                <li><a href="#work" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">Projects</a></li>
                <li><a href="#education" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">Education</a></li>
                <?= $extraNavMobile ?>
                <li><a href="#contact" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">Contact</a></li>
                <li class="pt-2 border-t border-zinc-100 dark:border-zinc-900">
                    <a href="Dashboard.php" @click="mm=false" class="inline-flex items-center gap-2 text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors font-medium">🏠 Dashboard</a>
                </li>
            </ul>
        </div>
    </header>

    <main>
        <!-- ═══ HERO ═══ -->
        <section id="hero" class="relative min-h-screen flex items-center pt-16 overflow-hidden">
            <div class="absolute top-1/4 right-0 w-96 h-96 bg-accent/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute bottom-1/4 left-0 w-64 h-64 bg-zinc-200/50 dark:bg-zinc-800/30 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="relative z-10 max-w-6xl mx-auto px-6 py-24 w-full">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <?php if ($location): ?>
                            <p class="reveal text-sm font-medium text-accent tracking-widest uppercase mb-4">📍 <?= h($location) ?></p>
                        <?php endif; ?>
                        <h1 class="reveal d1 font-display font-bold text-5xl md:text-6xl lg:text-7xl leading-[1.05] tracking-tight text-zinc-900 dark:text-white mb-4">
                            Hi, I'm <span class="text-accent"><?= h($firstName) ?></span>
                        </h1>
                        <p class="reveal d2 text-lg md:text-xl text-zinc-600 dark:text-zinc-300 font-medium mb-4"><?= h($title) ?></p>
                        <p class="reveal d2 text-base text-zinc-500 dark:text-zinc-400 font-light leading-relaxed max-w-md mb-10"><?= h($bio) ?></p>
                        <div class="reveal d3 flex flex-wrap gap-4">
                            <a href="#work" class="shimmer inline-flex items-center gap-2 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-medium px-7 py-3.5 rounded-full hover:bg-zinc-700 dark:hover:bg-zinc-200 transition-colors text-sm">
                                View my projects
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </a>
                            <a href="#contact" class="inline-flex items-center gap-2 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 font-medium px-7 py-3.5 rounded-full hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors text-sm">Get in touch</a>
                        </div>
                        <div class="reveal d4 flex gap-8 mt-14 pt-8 border-t border-zinc-100 dark:border-zinc-900">
                            <div>
                                <p class="font-display font-bold text-3xl text-zinc-900 dark:text-white"><?= count($projects) ?>+</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Projects</p>
                            </div>
                            <div>
                                <p class="font-display font-bold text-3xl text-zinc-900 dark:text-white"><?= count($skills) ?>+</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Skills</p>
                            </div>
                            <div>
                                <p class="font-display font-bold text-3xl text-zinc-900 dark:text-white"><?= h($expYears) ?></p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Experience</p>
                            </div>
                        </div>
                    </div>
                    <div class="reveal d2 flex justify-center md:justify-end">
                        <div class="relative w-72 h-72 md:w-80 md:h-80 lg:w-96 lg:h-96">
                            <div class="pf w-full h-full rounded-3xl"><?= $avatarHtml ?></div>
                            <div class="absolute -bottom-4 -left-4 bg-accent text-white font-display font-bold text-sm px-4 py-2.5 rounded-2xl shadow-lg">
                                <?= h($title) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ ABOUT ═══ -->
        <section id="about" class="py-24 bg-zinc-50 dark:bg-zinc-900/40">
            <div class="max-w-6xl mx-auto px-6">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="reveal order-2 md:order-1">
                        <div class="pf w-full aspect-square max-w-sm mx-auto rounded-3xl"><?= $avatarHtml ?></div>
                    </div>
                    <div class="order-1 md:order-2">
                        <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">About me</p>
                        <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white leading-tight mb-6">A bit about<br />who I am</h2>
                        <p class="reveal d2 text-zinc-500 dark:text-zinc-400 leading-relaxed mb-4"><?= nl2br(h($bio)) ?></p>
                        <?php if ($location || $phone || $email): ?>
                            <div class="reveal d3 flex flex-col gap-2 mb-6 text-sm text-zinc-500 dark:text-zinc-400">
                                <?php if ($location): ?><span>📍 <?= h($location) ?></span><?php endif; ?>
                                <?php if ($contactPhone): ?><span>📞 <?= h($contactPhone) ?></span><?php endif; ?>
                                <?php if ($contactEmail): ?><span>✉️ <?= h($contactEmail) ?></span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($skills)): ?>
                            <div class="reveal d4">
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-3">Skills &amp; tools</p>
                                <div class="flex flex-wrap gap-2" role="list" aria-label="Skills"><?= $skillTagsHtml ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ EXPERIENCE ═══ -->
        <section id="experience" class="py-24">
            <div class="max-w-6xl mx-auto px-6">
                <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Career</p>
                <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-12">Work Experience</h2>
                <div class="flex flex-col gap-5"><?= $experienceHtml ?></div>
            </div>
        </section>

        <!-- ═══ PROJECTS ═══ -->
        <section id="work" class="py-24 bg-zinc-50 dark:bg-zinc-900/40">
            <div class="max-w-6xl mx-auto px-6">
                <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Portfolio</p>
                <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-12">Selected Projects</h2>
                <div class="grid md:grid-cols-2 gap-6"><?= $projectsHtml ?></div>
            </div>
        </section>

        <!-- ═══ EDUCATION ═══ -->
        <section id="education" class="py-24">
            <div class="max-w-6xl mx-auto px-6">
                <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Background</p>
                <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-12">Education</h2>
                <div class="flex flex-col gap-5"><?= $educationHtml ?></div>
            </div>
        </section>

        <?php if ($showCerts || $showAchievements): ?>
            <!-- ═══ CERTS & ACHIEVEMENTS ═══ -->
            <section id="extras" class="py-24 bg-zinc-50 dark:bg-zinc-900/40">
                <div class="max-w-6xl mx-auto px-6">
                    <?php if ($showCerts): ?>
                        <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Credentials</p>
                        <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-10">Certifications</h2>
                        <div class="grid md:grid-cols-2 gap-5 mb-16"><?= $certsHtml ?></div>
                    <?php endif; ?>
                    <?php if ($showAchievements): ?>
                        <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Recognition</p>
                        <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-10">Achievements</h2>
                        <div class="grid md:grid-cols-2 gap-5"><?= $achievementsHtml ?></div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ═══ CONTACT ═══ -->
        <section id="contact" class="py-24">
            <div class="max-w-6xl mx-auto px-6">
                <div class="bg-zinc-900 dark:bg-zinc-800 rounded-3xl p-10 md:p-16 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-accent/20 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
                    <div class="absolute bottom-0 left-0 w-40 h-40 bg-accent/10 rounded-full blur-2xl pointer-events-none" aria-hidden="true"></div>
                    <div class="relative z-10 grid md:grid-cols-2 gap-12 items-start">
                        <div>
                            <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Get in touch</p>
                            <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-white leading-tight mb-5">Let's work<br />together</h2>
                            <p class="reveal d2 text-zinc-400 leading-relaxed mb-8">I'm open to new opportunities. Feel free to reach out!</p>
                            <div class="reveal d3 flex flex-col gap-4"><?= $socialLinksHtml ?></div>
                        </div>
                        <?php if (!empty($profile['enable_contact_form'])): ?>
                            <div class="reveal d2">
                                <form action="contact-handler.php" method="POST" novalidate>
                                    <input type="hidden" name="portfolio_user_id" value="<?= (int)$userId ?>">
                                    <div class="flex flex-col gap-4">
                                        <div class="grid sm:grid-cols-2 gap-4">
                                            <div>
                                                <label for="fname" class="block text-xs font-medium text-zinc-400 mb-1.5">Name *</label>
                                                <input type="text" id="fname" name="name" placeholder="Jane Smith" required autocomplete="name" class="w-full bg-zinc-800 border border-zinc-700 text-white text-sm rounded-xl px-4 py-3 placeholder-zinc-600 focus:outline-none focus:border-accent transition-colors" />
                                            </div>
                                            <div>
                                                <label for="femail" class="block text-xs font-medium text-zinc-400 mb-1.5">Email *</label>
                                                <input type="email" id="femail" name="email" placeholder="jane@company.com" required autocomplete="email" class="w-full bg-zinc-800 border border-zinc-700 text-white text-sm rounded-xl px-4 py-3 placeholder-zinc-600 focus:outline-none focus:border-accent transition-colors" />
                                            </div>
                                        </div>
                                        <div>
                                            <label for="fsubject" class="block text-xs font-medium text-zinc-400 mb-1.5">Subject</label>
                                            <input type="text" id="fsubject" name="subject" placeholder="Project inquiry" class="w-full bg-zinc-800 border border-zinc-700 text-white text-sm rounded-xl px-4 py-3 placeholder-zinc-600 focus:outline-none focus:border-accent transition-colors" />
                                        </div>
                                        <div>
                                            <label for="fmessage" class="block text-xs font-medium text-zinc-400 mb-1.5">Message *</label>
                                            <textarea id="fmessage" name="message" rows="4" placeholder="Tell me about your project..." required class="w-full bg-zinc-800 border border-zinc-700 text-white text-sm rounded-xl px-4 py-3 placeholder-zinc-600 focus:outline-none focus:border-accent transition-colors resize-none"></textarea>
                                        </div>
                                        <button type="submit" class="shimmer w-full bg-accent text-white font-display font-bold text-sm py-3.5 rounded-xl hover:bg-accent-light transition-colors">Send message →</button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="reveal d2 flex items-center justify-center">
                                <?php if ($contactEmail): ?>
                                    <a href="mailto:<?= h($contactEmail) ?>" class="shimmer inline-flex items-center gap-2 bg-accent text-white font-medium px-8 py-4 rounded-full hover:bg-accent-light transition-colors text-base">
                                        Email me →
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-zinc-100 dark:border-zinc-900">
        <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-zinc-400">© <span id="yr"></span> <?= h($name) ?>. All rights reserved.</p>
            <p class="text-xs text-zinc-500">Built with <span class="text-accent">PortfolioBuilder</span></p>
        </div>
    </footer>

    <!-- ═══ EDIT BAR (only visible when logged in — this page is always authenticated) ═══ -->
    <div class="edit-bar">
        <a href="choose-template.php" class="btn-dash">← Change Template</a>
        <a href="edit-portfolio.php" class="btn-edit">✏️ Edit Portfolio</a>
    </div>

    <script>
        function app() {
            return {
                dark: false,
                mm: false,
                sc: false,
                s: 'hero',
                init() {
                    this.dark = localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    this.$watch('dark', v => localStorage.setItem('theme', v ? 'dark' : 'light'));
                    window.addEventListener('scroll', () => {
                        this.sc = window.scrollY > 20;
                        this.updateSection();
                    }, {
                        passive: true
                    });
                    const io = new IntersectionObserver(entries => {
                        entries.forEach(e => {
                            if (e.isIntersecting) {
                                e.target.classList.add('in');
                                io.unobserve(e.target);
                            }
                        });
                    }, {
                        threshold: .1,
                        rootMargin: '0px 0px -40px 0px'
                    });
                    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
                    document.getElementById('yr').textContent = new Date().getFullYear();
                },
                updateSection() {
                    const atBottom = window.innerHeight + window.scrollY >= document.body.scrollHeight - 60;
                    if (atBottom) {
                        this.s = 'contact';
                        return;
                    }
                    const ids = ['contact', 'extras', 'education', 'work', 'experience', 'about', 'hero'];
                    for (const id of ids) {
                        const el = document.getElementById(id);
                        if (el && window.scrollY >= el.offsetTop - 130) {
                            this.s = id;
                            return;
                        }
                    }
                }
            };
        }
    </script>
</body>

</html>
