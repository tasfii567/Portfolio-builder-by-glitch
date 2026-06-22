<?php

/**
 * DUMMY resume data — single-column ATS-style layout
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * INTEGRATION NOTE (for the Profile module member)
 * ─────────────────────────────────────────────────────────────────────────────
 * When the real profile module is ready, replace this file's contents with a
 * fetch/query that populates the same six variables below.  Both
 * resume_preview.php and export_pdf.php include this file and expect exactly
 * these variables — nothing else needs to change.
 *
 * Variable contract:
 *
 *   $user  (array, required keys: name, email, phone)
 *     'name'      string  — full name                            REQUIRED
 *     'location'  string  — city / country                       optional
 *     'phone'     string  — contact number                       REQUIRED
 *     'email'     string  — contact email                        REQUIRED
 *     'linkedin'  string  — full URL or ''                       optional
 *     'github'    string  — full URL or ''                       optional
 *     'portfolio' string  — full URL or ''                       optional
 *     'summary'   string  — paragraph text or ''                 optional
 *
 *   $education  (array of arrays)
 *     'degree'      string         REQUIRED
 *     'institution' string         REQUIRED
 *     'duration'    string         optional  e.g. "Jan 2022 – Present"
 *     'score'       string         optional  e.g. "CGPA: 3.30"
 *     'notes'       string[]       optional  MUST be an array, even if empty []
 *
 *   $skills  (array of arrays)
 *     'category'  string  — bold label                           optional
 *     'items'     string  — comma-separated skill list           REQUIRED
 *
 *   $projects  (array of arrays)
 *     'title'    string    REQUIRED
 *     'tech'     string    optional
 *     'links'    array     optional — each item: ['label'=>string, 'url'=>string]
 *                          url must be a non-empty string (not null)
 *     'bullets'  string[]  REQUIRED (can be [])
 *
 *   $certifications  (array of arrays)
 *     'title'       string  REQUIRED
 *     'description' string  optional
 *
 *   $volunteering  (array of arrays)
 *     'role'         string    REQUIRED
 *     'organization' string    optional
 *     'year'         string    optional
 *     'bullets'      string[]  optional — MUST be an array if provided
 *
 * Any of the six top-level arrays can be [] — both consumers hide the
 * section heading automatically instead of rendering an empty block.
 * ─────────────────────────────────────────────────────────────────────────────
 */

$user = [
    'name'      => 'Tasfia Islam',
    'location'  => 'Chattogram, Bangladesh',
    'phone'     => '01824777669',
    'email'     => 'ttasfyislam567@gmail.com',
    'linkedin'  => 'https://linkedin.com/in/tasfia-islam',
    'github'    => 'https://github.com/tasfia-islam',
    'portfolio' => 'https://tasfia-islam.example.com',
    'summary'   => 'Computer Science and Engineering undergraduate with hands-on experience in full-stack development, AI/ML systems, and quality assurance. Skilled in building web applications, implementing AI solutions, and ensuring product reliability through comprehensive testing. Adept at analytical thinking, problem-solving, and translating technical concepts into functional solutions.',
];

$education = [
    [
        'degree'      => 'BSc. in Computer Science and Engineering',
        'institution' => 'International Islamic University Chittagong',
        'duration'    => 'January, 2022 - Present',
        'score'       => 'CGPA: 3.30',
        'notes'       => ['Thesis Topic: Green AI Implementation for Tea Disease Classification'],
    ],
    [
        'degree'      => 'Higher Secondary Certificate',
        'institution' => 'Chattogram Cantonment Public College',
        'duration'    => '2020',
        'score'       => 'GPA: 5.00',
        'notes'       => [],   // always an array, even when empty
    ],
];

$skills = [
    ['category' => 'Testing',                 'items' => 'Manual Testing, Test Case Design, Regression Testing, Defect Life Cycle'],
    ['category' => 'Tools',                   'items' => 'Jira, Selenium (Basic), TestRail, Postman'],
    ['category' => 'Language and Database',   'items' => 'SQL (Data validation queries, filtering, joins), Python, HTML, CSS'],
    ['category' => 'Core Competencies',       'items' => 'API Testing Fundamentals, SDLC, AI and Machine Learning, Defect Reporting'],
];

$projects = [
    [
        'title'   => 'SpendWise',
        'tech'    => 'JS, HTML, CSS',
        'links'   => [
            ['label' => 'Github', 'url' => 'https://github.com/tasfia-islam/spendwise'],
            ['label' => 'Live',   'url' => 'https://spendwise.example.com'],
        ],
        'bullets' => [
            'Built a Progressive Web App (PWA) for expense tracking with real-time currency conversion.',
            'Integrated third-party APIs for currency exchange with error handling and validation logic.',
            'Designed and executed 20+ manual test cases in TestRail covering core user flows, input validation, and edge cases for a PWA expense tracker.',
            'Implemented Selenium automation scripts in Python to cover critical UI flows including form validation and transaction management.',
        ],
    ],
    [
        'title'   => 'ProFolio',
        'tech'    => 'JavaScript, Python, HTML, CSS',
        'links'   => [
            ['label' => 'Github', 'url' => 'https://github.com/tasfia-islam/profolio'],
            ['label' => 'Live',   'url' => 'https://profolio.example.com'],
        ],
        'bullets' => [
            'Evaluated a web-based digital platform from a user experience, data accuracy, and usability perspective.',
            'Developed a full-stack portfolio platform with responsive UI and backend API integration.',
            'Used Selenium for basic automation and identified defects in Jira.',
            'Implemented form validation logic and user authentication flows using JavaScript.',
        ],
    ],
];

$certifications = [
    [
        'title'       => 'Fresh Graduate Training Program (FGTP) — IEB',
        'description' => 'Government-led professional engineering training.',
    ],
    [
        'title'       => 'Art of Problem Definition – UNICEF & Generation Unlimited',
        'description' => 'Approaching problems through a 5-step problem solving process via research, critical thinking, and collaboration.',
    ],
    [
        'title'       => 'AI+ Foundation™ — AIcerts',
        'description' => 'Fundamentals of AI, data-driven decision-making, and business applications.',
    ],
];

$volunteering = [
    [
        'role'         => 'Volunteer',
        'organization' => 'Project Chauna Piyaju | Youth\'s Voice',
        'year'         => '2026',
        'bullets'      => ['Distributed food and relief among 1000+ underprivileged kids.'],
    ],
    [
        'role'         => 'Volunteer',
        'organization' => 'Red Crescent Society',
        'year'         => '2018',
        'bullets'      => ['Trained in and provided First Aid to injured students during various tournaments.'],
    ],
];