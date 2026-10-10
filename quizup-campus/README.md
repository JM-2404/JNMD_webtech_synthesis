# QuizUp Campus

A quiz and revision platform: accounts, categorised quizzes and flashcards, XP, levels, daily streaks, a leaderboard and a shared chat room. Red and black theme with a light/dark switch.

Stack: HTML5, CSS3, vanilla JavaScript, PHP 8 (PDO), MySQL/MariaDB.

## Folder map

```
quizup-campus/
├── index.php            landing page
├── register.php         create account
├── login.php            log in
├── logout.php
├── dashboard.php        streak, level, stats, recent results
├── quizzes.php          category picker
├── quiz.php             quiz player (assets/js/quiz.js)
├── flashcards.php       flashcard decks (assets/js/flashcards.js)
├── chat.php             chat room (assets/js/chat.js)
├── leaderboard.php
├── profile.php          best scores + change password
├── api/                 JSON endpoints used by the JavaScript
├── includes/            bootstrap.php (db, session, helpers), header, footer
├── config/config.php    database + game settings
├── database/            schema.sql and seed.sql
└── assets/              css/style.css, js/app.js (theme + helpers)
```

## 1. Run it locally (AMPPS, Windows)

1. Make sure nothing else uses port 80 (stop XAMPP). Start **Apache** and **MySQL** in AMPPS.
2. Copy the `quizup-campus` folder into `C:\Program Files\Ampps\www\` (so you get `www\quizup-campus`).
3. Open phpMyAdmin (`http://localhost/phpmyadmin`, user `root`, password `mysql`).
4. Create a database called `quizup_campus` (collation `utf8mb4_general_ci`).
5. Select it, open the **Import** tab, import `database/schema.sql`, then import `database/seed.sql` (once only).
6. Check `config/config.php`: host `localhost`, user `root`, password `mysql`.
7. Visit `http://localhost/quizup-campus/` and create an account.

## 2. Put it online (InfinityFree)

1. In the InfinityFree control panel open **MySQL Databases**, create a database and note the host, name, user and password.
2. Open phpMyAdmin from there and import `schema.sql`, then `seed.sql`.
3. Copy `config/config.local.example.php` to `config/config.local.php` and fill in those details. (The file is ignored by Git so your password stays out of GitHub.)
4. Upload the whole folder contents into `htdocs/` using the File Manager or FTP.
5. Visit your site address.

**Already have tables online?** `schema.sql` only uses `CREATE TABLE IF NOT EXISTS`, so it never drops or overwrites anything. But if your old `users`, `quiz_attempts` or `community_messages` tables have different columns, the new pages will fail on them. Run `DESCRIBE users;` (and the other two) in phpMyAdmin and compare with `schema.sql`. Easiest safe option: create a new database for this version and keep the old one untouched.

## 3. Add your own questions and cards

In phpMyAdmin, insert rows into `questions` (`correct` is 0 for A, 1 for B, 2 for C, 3 for D) or `flashcards`. New categories go in `categories` (use a lowercase `slug` with hyphens).

## 4. Settings you can change (config/config.php)

Questions per quiz, seconds per question, XP per correct answer, completion and perfect-score bonuses, hint penalty, and XP needed per level.

## How the game maths works

- XP for a quiz = correct x 10 + 5 for finishing + 20 if perfect - 2 per hint used (never below 0).
- Level = total XP / 100 (rounded down) + 1.
- Streak: studying on consecutive days adds 1, missing a whole day resets it to 1.
- Scores are re-checked on the server from the answers submitted, so the stored score is not taken from the browser.

## Where the lesson concepts appear

| Concept | Where to point in your demo |
|---|---|
| Conditions | `quiz.js` chooseAnswer / finishQuiz (result titles), `touch_streak()` in `includes/bootstrap.php` |
| Functions | every named function in `quiz.js`, `flashcards.js`, `bootstrap.php` |
| Arrays | `questions`, `answers`, option lists, shuffle and `reduce` in `quiz.js` |
| DOM | `renderQuestion()` builds option buttons with `createElement` |
| Event handling | click and keydown listeners at the bottom of `quiz.js` |
| Calculations | percentage, average time, timer fraction, XP and level formulas |

## Known limits

- Chat checks for new messages every 5 seconds (simple and works on free hosting). It is not instant like WebSockets.
- Quiz answers are sent to the browser so feedback is instant; the server still recalculates the score.
- There is no password reset email yet.
