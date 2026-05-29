# Test Protocol: Trip Planner

Project: Trip Planner: Final Certification Box 2025-2026
Tested by: Léo Degrugillier
Date: 29/05/2026
Environment: Apache2, PHP 8.1, MySQL 8.0, Ubuntu 22.04

## 1. Authentication

### TEST-01: Successful registration

Action: fill in the registration form with a valid username, email, and password.
Input: username `testuser`, email `test@test.com`, password `Test1234`.
Expected result: account created, redirect to login page.
Result: Pass

### TEST-02: Registration with an already used email

Action: try to register with an email already in the database.
Input: email `test@test.com` (already used).
Expected result: error message "Email already used", no redirect.
Result: Pass

### TEST-03: Registration with an already used username

Action: try to register with a username already in the database.
Input: username `testuser` (already used).
Expected result: error message shown, no account created.
Result: Pass

### TEST-04: Successful login

Action: log in with valid credentials.
Input: email `test@test.com`, password `Test1234`.
Expected result: session created, redirect to dashboard.
Result: Pass

### TEST-05: Login with wrong password

Action: log in with correct email but wrong password.
Input: email `test@test.com`, password `wrongpassword`.
Expected result: error message "Invalid credentials", no session created.
Result: Pass

### TEST-06: Login with non-existing email

Action: log in with an email not in the database.
Input: email `nobody@test.com`, password `Test1234`.
Expected result: error message "Invalid credentials".
Result: Pass

### TEST-07: Logout

Action: click on "Disconnect" from the dashboard.
Expected result: session destroyed, redirect to home page, protected pages not accessible.
Result: Pass

### TEST-08: Access protected page without login

Action: go directly to `trips/user_home.php` without an active session.
Expected result: redirect to login page.
Result: Pass

## 2. Trip Management

### TEST-09: Create a trip

Action: logged in, fill the creation form with a name and at least two places.
Input: name `Japan Tour`, places Tokyo + Osaka, public visibility.
Expected result: trip saved in database, route calculated and displayed.
Result: Pass

### TEST-10: Add a place using Nominatim API

Action: search for "Tokyo" in the place search field.
Expected result: suggestions shown, selecting one automatically fills latitude and longitude.
Result: Pass

### TEST-11: Duplicate place in the same trip

Action: add the same place twice in one trip.
Expected result: error message "Place already in this trip", second addition blocked.
Result: Pass

### TEST-12: Delete a trip

Action: click "Delete" from "My Trips" page and confirm the popup.
Expected result: trip removed from database, no longer shown in list.
Result: Pass

### TEST-13: Cancel deletion

Action: click "Delete" then cancel the confirmation.
Expected result: trip is not deleted.
Result: Pass

### TEST-14: Cannot delete another user's trip

Action: manually send a POST request with another user's trip ID.
Expected result: system checks `user_id = $_SESSION['id']`, trip is not deleted.
Result: Pass

### TEST-15: Minimum two cities required for calculation

Action: try to generate a route with less than two places.
Expected result: user blocked, route not calculated.
Result: Pass

## 3. Visibility and Sharing

### TEST-16: Public trip visible without login

Action: log out, go to `trips/public.php`.
Expected result: public trips are listed and accessible.
Result: Pass

### TEST-17: Restricted page not accessible without login

Action: log out, go directly to `trips/half_public.php`.
Expected result: redirect to login page.
Result: Pass

### TEST-18: Restricted trip visible for allowed user

Action: User A creates a restricted trip and grants access to User B. Log in as User B.
Expected result: trip appears in restricted page for User B.
Result: Pass

### TEST-19: Restricted trip hidden for unauthorized user

Action: log in as User C without granted access.
Expected result: trip does not appear in restricted page.
Result: Pass

### TEST-20: Private trip accessible only by owner

Action: User A creates a private trip. User B opens shared link directly.
Expected result: "Access denied" message or redirect to login.
Result: Pass

### TEST-21: Grant access to all users

Action: click "Grant access to all users" and confirm popup.
Expected result: all users added to `trip_access`, trip visible to all logged-in users.
Result: Pass

## 4. Algorithm

### TEST-22: Route generation after trip creation

Action: create a trip with 5 places.
Expected result: route shown in calculated order, total distance displayed in kilometers.
Result: Pass

### TEST-23: Route returns to starting point

Action: view any generated trip.
Expected result: last step shows return to first place.
Result: Pass

### TEST-24: 2-opt optimization improves nearest neighbor result

Action: run Python script directly on a dataset of 18 Japanese cities.
Expected result: distance with 2-opt is lower than nearest neighbor result.
Result: Pass: about 15% improvement observed.

## 5. Security

### TEST-25: SQL injection attempt

Action: enter `' OR '1'='1` in email field of login form.
Expected result: login fails, no data exposed, prepared PDO queries block injection.
Result: Pass

### TEST-26: Password not stored in plain text

Action: create an account, check `users` table in phpMyAdmin.
Expected result: `password_hash` column contains bcrypt hash, not plain password.
Result: Pass

