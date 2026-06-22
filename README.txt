==================================================================
 PORTFOLIO BUILDER (by glitch) — Setup & Guide
==================================================================

THE FLOW
--------
index.php  ->  Register  ->  Login  ->  Dashboard
   Step 1  edit-profile.php   name, photo, email, GitHub, education,
                              experience, skills  -> Save
   Step 2  templates.php      pick 1 of 5 portfolio templates -> Apply
   Step 3  create-portfolio.php  add projects (images + links)
   Step 4  create-resume.php   review CV  ->  Download CV (PDF)
           job-match.php       see your best-fit role of 5

5 PORTFOLIO TEMPLATES (public page = view-portfolio.php)
  Midnight · Aurora · Sunset · Minimal · Terminal
  (template files live in the /templates folder)


SETUP (XAMPP)
-------------
1. Copy this whole folder into:  C:\xampp\htdocs\
   So the path becomes:  C:\xampp\htdocs\Portfolio-builder-by-glitch\

2. In XAMPP Control Panel, START  Apache  and  MySQL.

3. Create the database:
   a. Open  http://localhost/phpmyadmin
   b. (Optional) Click "New", type  portfolio_builder , Create.
   c. Click  portfolio_builder  in the left list to SELECT it.
   d. Click the  Import  tab  ->  Choose File  ->  database.sql  ->  Go.
      (Selecting the DB first is important so tables land in the right place.)

4. Open the app:
   http://localhost/Portfolio-builder-by-glitch/index.php

5. Register an account, then follow Steps 1–4 from the sidebar.


NOTES
-----
- The CV "Download CV (PDF)" button uses an online library (html2pdf via
  CDN), so you need an internet connection the first time it loads.
  No Composer / no extra install needed. The Print button is a fallback.

- Uploaded files are saved in:
    uploads/avatars/    profile photos
    uploads/projects/   project images
  Both folders are already created.

- db.php uses XAMPP defaults (user "root", no password). If you set a
  MySQL password, change $DB_PASS in db.php.

- Shared link looks like:
    http://localhost/Portfolio-builder-by-glitch/view-portfolio.php?u=YOUR-SLUG
  Anyone can open it — no login needed.

- Helper folders:
    inc/        shared PHP (sidebar, profile schema, template list)
    assets/     shared stylesheet (app.css)
    templates/  the 5 portfolio designs
==================================================================
