# Job Matching AI

A dependency-free PHP app that:

- extracts skills from portfolio or resume text
- ranks skills against a target role
- creates current LinkedIn and Indeed job-search links using the best skill query
- scores pasted job descriptions against the portfolio

## Run

```powershell
php -S 127.0.0.1:8000 -t job-match-ai
```

Then open:

```text
http://127.0.0.1:8000/job-match.php
```

## Notes

LinkedIn and Indeed do not provide simple public APIs for unrestricted live job ingestion. This app opens live board searches instead of scraping those sites, which keeps it practical and avoids brittle blocked requests. If you later add a paid job-search API, the matching logic in `job-match.php` can be reused to rank returned postings.
