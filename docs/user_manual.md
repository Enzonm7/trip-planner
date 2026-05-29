# User Manual — Trip Planner

Version 1.0
29/05/2026

## 1. Introduction

Trip Planner is a web application that automatically creates an optimized route from a list of destinations. You only need to add the places you want to visit, and the application calculates the shortest route using an optimization algorithm.

## 2. Installation

### Requirements

- PHP 8.x
- Python 3.x
- MySQL 8.x
- Apache2 (Linux) or Laragon (Windows)

### On Linux with Apache2

```bash
git clone https://github.com/Enzonm7/trip-planner
sudo cp -r trip-planner /var/www/html/
```

Then import the `bdd.sql` file into phpMyAdmin by creating a database named `Box`, and edit `config/db.php` with your credentials. The application will be available at `http://localhost/trip-planner/`.

### On Windows with Laragon

Clone or download the project and place it in `laragon/www/trip-planner/`. Open phpMyAdmin, create the `Box` database, import `bdd.sql`, then edit `config/db.php`. The application will then be available at `http://localhost/trip-planner/`.

## 3. Getting Started

### Create an Account

From the home page, click on "Account Creation", enter a username, an email address, and a password, then click on "Register". You will be redirected to the login page.

### Login

Click on "Authentication", enter your email and password, then click on "Login". You will then access your dashboard.

### Logout

Click on "Disconnect" from your dashboard.

## 4. Create a Trip

From the dashboard, click on "Create a trip" and give a name to your trip. To add places:

- Type a place name in the search field (for example "Tokyo")
- Select the correct result from the suggestions
- Coordinates are automatically retrieved using the Nominatim API
- Repeat the process for each place (minimum 2)
- Enter the number of hotels wanted for your trip

Click on "Generate Tour" to start the calculation. The algorithm finds the best route and saves it.

## 5. View Your Trips

From the dashboard, click on "View different trips". Three categories are available.

"Public" is open to everyone and contains trips marked as public. "Restricted" is only available for connected users who received access. "My Trips" shows all your trips, whatever their visibility.

Click on "View" to display the visit order and the total distance of a trip.

## 6. Manage Your Trips

From the "My Trips" page, each trip has three actions.

Edit allows you to change the name, places, or number of hotels.

Share (Grant Access) allows you to give access to a restricted trip:

* Click on "Grant access" next to the trip
* Select users in the dropdown list
* Click on "Validate" to confirm

You can also click on "Grant access to all users" to share with everyone. A confirmation will be requested.

Delete permanently removes the trip after confirmation.

## 7. Understand the Result

After generation, the result page shows the optimized visit order as a numbered list, the total distance in kilometers (including return to the starting point), the number of hotels, and the cities concerned.

### Hotel Clustering Mode

When this option is enabled, places are grouped by geographic proximity. The most central place in each group becomes a hotel. You travel from hotel to hotel, with daily trips to nearby places. The total score includes both hotel-to-hotel travel and daily trips.

## 8. Share a Trip

Each trip has a unique link in the form `/trips/view_trip.php?token=...`.

A public trip can be shared with anyone without login. A restricted trip requires the receiver to be connected and to have received access from you. A private trip is only accessible by you, even with the link.

## 9. Error Messages

"Email already used" means that an account already exists with this email address. Use another email or login.

"Invalid credentials" means that the email or password is incorrect. Check your credentials.

"Place already in this trip" means there is a duplicate place. Choose another place.

"Trip not found" means the link is invalid or expired. Check the URL.

"Access denied" means you do not have the required permissions. Ask the trip owner to give you access.

"No places in this trip" means that no place was saved. Edit the trip and add destinations.

