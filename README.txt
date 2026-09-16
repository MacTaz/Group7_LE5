=================================================================
 SPORTS FACILITY BOOKING SYSTEM
 Laboratory Exercise 4 - Secure Web System:
 Sessions, Cookies, Validation, and Access Restrictions
=================================================================

System Name: Sports Facility Booking System
Members: Agatha Fei R. Pelayo (Agathahaha)

-----------------------------------------------------------------
ADMIN ACCOUNT
-----------------------------------------------------------------
Username: DOOM1
Password: Boss_ofSports

-----------------------------------------------------------------
REGULAR USER ACCOUNT
-----------------------------------------------------------------
Username: Agathahaha
Password: LOVEsports_11

NOTE: These are dummy accounts for classroom testing only, as
instructed in the activity sheet. Passwords are stored as
bcrypt hashes in xml/users.xml, never in plain text.

-----------------------------------------------------------------
SYSTEM PURPOSE
-----------------------------------------------------------------
The system allows registered users to browse available sports
facilities (basketball court, badminton court, swimming pool,
tennis court, function hall) and submit a booking request for a
chosen date and number of hours. Administrators can view every
booking made in the system and remove records as needed.

-----------------------------------------------------------------
USER ROLES
-----------------------------------------------------------------
Administrator : Login, view admin dashboard, view/delete all
                booking records, view system-wide statistics.
Regular User  : Login, view own dashboard, book a facility
                (main activity), view own booking history.
Guest         : Not logged in. May only view the login page.
                Cannot view any dashboard, booking form, or
                admin function.

-----------------------------------------------------------------
IMPLEMENTED SECURITY FEATURES
-----------------------------------------------------------------
1. Login Validation
   - login.php checks that username and password are not empty,
     that the account exists, and that the password is correct
     (via password_verify against a bcrypt hash).
   - On any failure, a single generic message is shown:
     "Invalid username or password." The system never reveals
     which field was wrong.

2. PHP Session
   - On successful login, $_SESSION['username'], $_SESSION['role'],
     $_SESSION['fullname'], and $_SESSION['last_activity'] are
     created. session_regenerate_id() is called on login to guard
     against session fixation.

3. Restricted Pages (Session-based)
   - includes/session_check.php is required at the top of every
     protected page (dashboard.php, user/*, admin/*). A guest who
     manually types a protected URL is redirected to login.php
     with the message "Please login first." This is a real
     server-side PHP check, not a hidden link.

4. Role-Based Access Control
   - includes/role_check.php provides requireRole(). Every file
     under /admin calls requireRole('admin'). A logged-in Regular
     User who manually types /admin/dashboard.php or
     /admin/manage.php is redirected to unauthorized.php
     (ACCESS DENIED), even though the admin menu link is also
     hidden from them in includes/navbar.php. Hiding the button
     AND protecting the PHP file are both implemented.

5. Cookies
   - A single "Remember my username" checkbox on the login form
     stores ONLY the username in a cookie (remember_username),
     valid for 30 days. The password, an auth token, and all
     other sensitive data are never stored in a cookie.

6. Form Validation (Book a Facility form - user/transaction.php)
   - Required fields: Full Name, Email, Contact Number, Facility,
     Date, Hours.
   - Email validation: must be a valid email address.
   - Number validation: number of hours must be between 1 and 10.
   - Length validation: name must be at least 3 characters.
   - Date validation: booking date cannot be in the past.
   - All validation is enforced in PHP on the server, not only
     via the HTML5 input types.

7. Logout
   - logout.php clears $_SESSION, deletes the session cookie, and
     calls session_destroy(). After logout, pressing Back and
     reloading a protected page redirects to login.php because
     session_check.php finds no active session.

8. Session Timeout
   - SESSION_TIMEOUT is set to 15 minutes in includes/security.php.
     session_check.php compares the current time against
     $_SESSION['last_activity'] on every protected page load. If
     the user has been idle beyond the limit, the session is
     destroyed and the user is redirected to login.php with:
     "Your session has expired. Please login again."

9. Unauthorized Access Page
   - unauthorized.php displays "ACCESS DENIED - You do not have
     permission to access this page." with a link back to the
     correct dashboard for the user's role.

10. Basic Web Security Practices
    - Passwords hashed with password_hash()/password_verify()
      (bcrypt), never stored or compared in plain text.
    - All user-facing output is escaped with htmlspecialchars()
      to reduce XSS risk.
    - Session cookie set with httponly and samesite=Lax.
    - session_regenerate_id(true) on login to prevent session
      fixation.
    - XML used as the system's data source (xml/users.xml,
      xml/facilities.xml, xml/bookings.xml) as required by
      Section 18 of the activity sheet.

-----------------------------------------------------------------
PAGE ACCESS MATRIX
-----------------------------------------------------------------
Page                      Guest   User   Admin
Login                       Y      Y      Y
User Dashboard              N      Y      Y
Book a Facility             N      Y      Y (can also book)
Admin Dashboard             N      N      Y
Manage Bookings             N      N      Y

-----------------------------------------------------------------
HOW TO RUN
-----------------------------------------------------------------
1. Requires PHP 8.x with the built-in SimpleXML/DOM extensions
   (bundled by default with most PHP installs).
2. From the project folder, run:
       php -S localhost:8000
3. Open http://localhost:8000/ in a browser. Guests are
   automatically routed to login.php.
4. Make sure the xml/ folder is writable by the web server, since
   bookings.xml is updated when a booking is submitted or deleted.

-----------------------------------------------------------------
FOLDER STRUCTURE
-----------------------------------------------------------------
GROUP01_SecureWebSystem/
├── index.php               (routes guest -> login, user -> dashboard)
├── login.php                (Security Feature 1, 2, 5)
├── logout.php                (Security Feature 7)
├── dashboard.php             (role router)
├── unauthorized.php          (Security Feature 9)
├── admin/
│   ├── dashboard.php          (admin-only, Security Feature 3 & 4)
│   └── manage.php             (admin-only, view/delete bookings)
├── user/
│   ├── dashboard.php          (user-only)
│   └── transaction.php        (booking form, Security Feature 6)
├── includes/
│   ├── security.php           (session config, timeout, validators, XML helpers)
│   ├── session_check.php      (Security Feature 3 & 8)
│   ├── role_check.php         (Security Feature 4)
│   └── navbar.php              (role-aware navigation)
├── css/
│   └── style.css
├── xml/
│   ├── users.xml               (accounts + roles, hashed passwords)
│   ├── facilities.xml           (topic-specific data)
│   └── bookings.xml              (transactions, updated at runtime)
└── README.txt
