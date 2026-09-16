# Sports Facility Booking — Laboratory Exercise 4

A minimal PHP system that demonstrates sessions, cookies, validation, and
role-based access control. No database server is needed — "records" are
stored in plain JSON files under `/data`, so it runs on any XAMPP/PHP setup
with zero configuration.

## How to run it

1. Copy the whole `sports-facility-booking` folder into `htdocs` (XAMPP) or
   your PHP server's document root.
2. Make sure the `data/` folder is writable (it is, by default).
3. Visit `http://localhost/sports-facility-booking/` in your browser.
4. Log in with a demo account:
   - **Admin:** `admin` / `admin123`
   - **User:** `juan` / `user123`

## File structure

```
sports-facility-booking/
├── index.php                 Routes guest/user/admin to the right page
├── login.php                 Login form + validation + attempt limiting
├── logout.php                Destroys the session completely
├── access_denied.php         Shown when a role tries a page it can't access
│
├── includes/
│   ├── config.php            session_start() + timeout/lockout constants
│   ├── auth.php               require_login(), require_role(), timeout check
│   ├── functions.php         All validation helpers + JSON read/write
│   └── users_data.php        Hardcoded demo accounts (stand-in for a DB table)
│
├── admin/
│   ├── dashboard.php          Admin-only landing page
│   ├── manage_facilities.php Add/list facilities (validated form)
│   └── manage_bookings.php   View all bookings, approve/reject
│
├── user/
│   ├── dashboard.php          Regular-user landing page
│   ├── book_facility.php     Main booking form (fully validated)
│   └── my_bookings.php       Shows only the logged-in user's own bookings
│
├── data/
│   ├── facilities.json       Seed list of bookable facilities
│   └── bookings.json         Starts empty; fills up as users submit bookings
│
└── assets/
    └── style.css              Shared styling
```

## Where each required feature lives

| Requirement                          | File(s) |
|---------------------------------------|---------|
| Login validation                      | `login.php` |
| PHP session (`$_SESSION['username']`, `['role']`) | `login.php`, used everywhere via `includes/auth.php` |
| Session-based page restriction        | `includes/auth.php` → `require_login()`, called at the top of every protected page |
| User roles (admin / user)             | `includes/users_data.php`, checked in `require_role()` |
| Role-based access                     | `includes/auth.php` → `require_role()`; separate `admin/` and `user/` folders; nav links differ per role |
| Cookies (non-sensitive only)          | `login.php` — "Remember my username" checkbox sets/reads `remember_username` cookie. No password/token is ever stored in a cookie. |
| Form validation                       | `user/book_facility.php` (main form), `admin/manage_facilities.php` |
| Input restrictions (required, email, number range, length, date) | `includes/functions.php` |
| Unauthorized-access handling          | `access_denied.php` |
| Session timeout (15 min)              | `includes/auth.php` → `check_session_timeout()` |
| Logout / session termination          | `logout.php` |
| Max login attempts / lockout          | `login.php` (`MAX_LOGIN_ATTEMPTS`, `LOCKOUT_TIME` in `includes/config.php`) |
| Hiding buttons vs. protecting pages   | `user/dashboard.php` has no admin links, **and** `admin/*.php` still rejects a user who types the URL directly |

## Notes on the validation rules used

- **Required fields** — name, email, contact, facility, date, time slot, participants must all be filled in.
- **Email validation** — `filter_var($email, FILTER_VALIDATE_EMAIL)`.
- **Number validation** — participants must be a number from 1–20 (`validate_number_range`); contact number must be 7–15 digits only (`validate_phone`).
- **Length validation** — full name must be at least 2 characters (`validate_min_length`).
- **Date validation** — booking date cannot be in the past (`validate_date_not_past`). A second helper, `validate_date_not_future`, is included in `functions.php` in case your version of the form ever needs a "date must already have happened" field (e.g. a birthdate) — same idea, opposite direction.
- Every field is also re-checked against a fixed list where relevant (facility name, time slot) so a user can't submit a value that was never in the dropdown.
- All text output uses `htmlspecialchars()` to prevent stored/reflected XSS.
- Passwords are hashed with `password_hash()` / verified with `password_verify()` — never stored or compared as plain text.
- `session_regenerate_id(true)` is called on successful login to prevent session fixation.
- Protected pages send `Cache-Control: no-store` so pressing Back after logout won't show a cached page.

## Extending it

- To require the admin role on a new page, put this at the very top (before any HTML):
  ```php
  require_once '../includes/auth.php';
  require_role('admin', '../access_denied.php', '../login.php');
  ```
- To add a new field to the booking form, add its input in `book_facility.php` and a matching check using the helpers in `includes/functions.php`.
- If your instructor requires MySQL instead of JSON files, only `includes/functions.php` (`read_json`/`write_json`) and `includes/users_data.php` need to change — the rest of the logic stays the same.
