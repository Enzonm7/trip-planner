# Technical Documentation — Trip Planner

Project: Trip Planner Final Certification Box 2025-2026
Authors: Enzonm7 Enzo NZENGUE MAYILA, Léo Degrugillier, Rémi Kalkan
Repository: https://github.com/Enzonm7/trip-planner
Date: 29/05/2026

## 1. System Architecture

### 1.1 Overview

The application follows a classic three-tier architecture. The presentation layer is made of HTML pages rendered with PHP. The business logic layer uses PHP for web logic and Python for the optimization algorithm. The data layer is handled by a MySQL database through phpMyAdmin.

Communication between PHP and Python is done using `proc_open()`: data is sent in JSON through stdin, and the optimized route is returned in JSON format.

### 1.2 Folder Structure

```
epreuve-finale/
├── index.php                          # Home page
├── readme.md                          # User documentation
├── ARCHITECTURE.md                    # This file
│
├── auth/                              # Authentication module
│   ├── login.php                      # Login page
│   ├── logout.php                     # User logout
│   └── register.php                   # User registration
│
├── config/                            # Application configuration
│   └── db.php                         # Database connection settings
│
├── algo/                              # Python algorithms
│   ├── clustering.py                  # Clustering algorithm (K-means)
│   ├── hotel.py                       # Hotel management (medoid, suggestion)
│   ├── plan_trip.py                   # Route planning (TSP, 2-opt)
│   └── __pycache__/                   # Python cache
│
├── docs/                              # Documentation
│   └── bddBox.sql                     # Database schema
│
├── travel_page/                       # Main trip management module
│   ├── trip.php                       # Main trip page
│   ├── show.php                       # Detailed trip view
│   ├── styles.css                     # Stylesheet
│   │
│   ├── Model/                         # Data models
│   │   └── cityModel.php              # City model
│   │
│   ├── strategy/                      # Strategy pattern for algorithm execution
│   │   ├── pythonStrategy.php         # Python execution via proc_open
│   │   └── nominatimStrategy.php      # Placeholder (see trip.php for JS usage)
│   │
│   └── test_algo/                     # Algorithm tests
│       └── main.py                    # Test script
│
└── trips/                             # User trip management
    ├── user_home.php                  # User dashboard
    ├── view_trip.php                  # Trip view page
    ├── edit_trip.php                  # Edit trip
    ├── public.php                     # Public trips
    ├── private.php                    # Private trips
    ├── half_public.php                # Shared (semi-public) trips
    └── grant_access.php               # Grant access to trips
```

### 1.3 Git Branch Strategy

The `main` branch is shared by all developers and contains stable code after merges. The `enzo` branch is dedicated to Python algorithm development. The `Leo` branch handles authentication, database, editing, and viewing trips. The `remi` branch focuses on trip management and sharing.

Merges into `main` are performed regularly after major tested steps.

## 2. Technology Choices

### 2.1 Stack

PHP 8.x is used for backend and web pages. It was chosen for team familiarity, native HTML integration, and wide support.

Python 3.x handles the optimization algorithm. Its simple syntax and built-in math capabilities make it well suited for algorithmic work.

MySQL is used as the database. Its relational model fits the project entities well, and phpMyAdmin simplifies management.

HTML and CSS are used for the frontend without unnecessary frameworks.

Apache2 is used on Linux (Ubuntu) and Laragon on Windows. Both provide native PHP support.

### 2.2 External API

The Nominatim API from OpenStreetMap is used to retrieve geographic coordinates (latitude, longitude) from place names. It is called on the client side using JavaScript in `trip.php` and `edit_trip.php`.

URL: https://nominatim.openstreetmap.org/search

Nominatim was chosen because it is free, does not require an API key, and aligns with open-source principles. Google Places API was considered but Nominatim is simpler to integrate. Note that `nominatimStrategy.php` is only a placeholder; actual usage is handled on the client side.

### 2.3 Alternatives Considered

For the backend, Python Flask could have been used, but it would duplicate server logic alongside the algorithm. For the database, PostgreSQL is more powerful but unnecessarily complex for this project scale.

## 3. Database Schema

### 3.1 Tables

```sql
users           -- User accounts
trips           -- Trips created by users
places          -- Geographic locations (reusable across trips)
trip_places     -- Junction table: links trips and places with visit order
trip_access     -- Grants restricted access to specific users
```

### 3.2 Entity Relationships

```
users (1) ──── (N) trips
trips (N) ──── (N) places   [via trip_places]
trips (N) ──── (N) users    [via trip_access, for restricted visibility]
```

### 3.3 Visibility Logic

The `public` value makes a trip accessible to everyone via a shared link without login. The `restricted` value limits access to authenticated users explicitly granted via `trip_access`. The `private` value restricts access to the owner only.

## 4. Algorithm Description

### 4.1 Problem Definition

The goal is to visit all selected places exactly once and return to the starting point while minimizing total travel distance.

### 4.2 Distance Formula

According to project specifications, the orthodromic distance between two points is:

```
D(Va, Vb) = R × arccos(sin(lata) × sin(latb) + cos(lata) × cos(latb) × cos(longb - longa))
```

R equals 6378.197 km (Earth radius). Coordinates are in radians. A clamping safeguard (`max(-1.0, min(1.0, val))`) prevents `acos` errors for nearly identical points.

### 4.3 Algorithm: Nearest Neighbor + 2-opt

Phase 1 is a greedy initialization using the nearest neighbor approach. Starting from the first location, the algorithm always moves to the closest unvisited location until all are visited, then returns to the start. Complexity is O(n²), producing a complete but suboptimal route.

Phase 2 is a local optimization using 2-opt. The algorithm tests all possible segment inversions. If reversing the segment between indices `i` and `j` reduces total distance, the change is kept. This process repeats until no improvement is possible. Complexity is O(n²) per iteration and typically converges in a few passes.

On a dataset of 18 Japanese cities, nearest neighbor alone produces about 2300 km. After 2-opt optimization, the result drops to about 1950 km, an improvement of roughly 15%.

### 4.4 Hotel Clustering Extension (Day 2 Modification)

Following exam updates, the algorithm was extended with a hotel clustering mode.

* K-means groups locations into `k` clusters (k chosen by the user)
* The medoid (most central point) of each cluster is selected as a hotel to minimize travel distance
* A TSP (nearest neighbor + 2-opt) is applied only to hotels
* The total score combines hotel-to-hotel travel plus daily excursions within each cluster

## 5. Security

Passwords are hashed using `password_hash($password, PASSWORD_DEFAULT)` (bcrypt) and verified with `password_verify()`. No plain-text passwords are stored.

All database queries use prepared PDO statements, preventing SQL injection. Authentication is session-based: `$_SESSION['id']` is checked on every protected page. Share tokens are generated using `bin2hex(random_bytes(32))`, ensuring cryptographic randomness.

## 6. Installation and Setup

### 6.1 Requirements

* PHP 8.x
* Python 3.x
* MySQL 8.x
* Apache2 (Linux) or Laragon (Windows)

### 6.2 Steps

```bash
# 1. Clone repository
git clone https://github.com/Enzonm7/trip-planner
cd trip-planner

# 2. Import database
# Open phpMyAdmin -> create database "Box" -> import bdd.sql

# 3. Configure database connection
# Edit config/db.php with credentials

# 4. Start server
# Apache2 (Linux): place project in /var/www/html/
# Laragon (Windows): place project in laragon/www/

# 5. Access application
# http://localhost/trip-planner/
```

### 6.3 Python Dependencies

```bash
pip install -r requirements.txt
# Only standard library used (math, json, sys) — no external packages required
```

