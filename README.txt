The Daily Dispatch

A secure, modular digital news platform built for the Web Application Development module (PUSL3190) 



Technology Stack

Backend: PHP 8.2 (Strict Object-Oriented Programming, No Frameworks)
Database:MySQL 8.0 (InnoDB engine for foreign keys and FULLTEXT searching)
Frontend:Vanilla JavaScript (ES6) & Semantic CSS3 (Grid / Flexbox)
Environment:Apache 2.4 via XAMPP 8.2 on Windows 11



Key Features

Dynamic Homepage:Features a breaking news ticker, a hero article section, a two-column responsive grid, and a sidebar for the latest updates.
Asynchronous Features:Includes a 300ms debounced live search bar and a notification system that polls a JSON API every 30 seconds.
Custom Rich-Text Editor:Built from scratch using `contenteditable` and `execCommand()` for administrative content creation.
Live Metrics:The "About" page pulls live system statistics (article, user, and category counts) directly from the database.



Implemented Security Defenses

The architecture incorporates exactly 10 production-ready security layers:

1.  SQL Injection Prevention: Enforced via strict PDO Prepared Statements with `EMULATE_PREPARES = false`.
2.  XSS Shielding: Handled using `htmlspecialchars()` with the `ENT_QUOTES` flag across all views and JSON payloads.
3.  CSRF Mitigation: Validates hidden form synchroniser tokens generated via `random_bytes(32)` using constant-time `hash_equals()`.
4.  Secure File Uploads: Uses the PHP `finfo` class to read magic bytes for true MIME type validation, followed by cryptographic file renaming.
5.  Execution Blocks: An `.htaccess` file inside the uploads directory prevents Apache from executing arbitrary scripts.
6.  Password Hashing: Implemented using `PASSWORD_BCRYPT` with a fixed cost factor of 12.
7.  Session Hardening: Invokes `session_regenerate_id(true)` upon login alongside strict `HttpOnly` cookie configurations.
8.  Double-Wall Validation: Enforces input length verification on both the client side (JavaScript counters) and server side (`mb_strlen()`).
9.  Privilege Escalation Guards: Restricts administrative files using a rigid `Auth::requireAdmin()` entry checkpoint.
10. Information Leakage Suppression: Captures database anomalies inside defensive `try/catch` blocks, routing errors to system logs while showing generic alerts to users.



 Key Directory Structure


/newssite/
  ├── /admin/               # Protected administrative panel pages
  ├── /api/                 # Async JSON service endpoints (Search, Notifications)
  ├── /assets/              # Vanilla CSS and JavaScript assets
  ├── /includes/            # Core OOP models (Database, Auth, Article, User, Notification)
  ├── /uploads/             # Hashed image storage with protective .htaccess
  ├── index.php             # System Homepage
  ├── schema.sql            # Core database initialization script
  └── README.md             # Project documentation