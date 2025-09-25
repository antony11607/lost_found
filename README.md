**Lost & Found Web Application**

A simple web application that allows users to report and search lost/found items.
Built using PHP, MySQL, HTML, CSS, and JavaScript.

**Features**

Report lost or found items with title, description, category, and image upload.

Store all reports in a MySQL database.

Search functionality to quickly find items by name.

View details of each item on a separate page.

Image preview before uploading.

**Project Structure**
lost_found_app/
├── index.php          # Homepage with search & item list
├── report.php         # Form to report a new item
├── item_detail.php    # Item details page
├── db.php             # Database connection
├── script.js          # Form validation & search
├── style.css          # Stylesheet
├── uploads/           # Folder for uploaded images
└── README.md          # Documentation

**Tech Stack**

Frontend: HTML, CSS, JavaScript

Backend: PHP

Database: MySQL (phpMyAdmin)

Server: XAMPP / WAMP (Localhost)

**Installation**

Clone this repository:

git clone [https://github.com/yourusername/lost_found_app.git]
(https://github.com/antony11607/lost_found.git)


Move the folder into your XAMPP htdocs/ (or WAMP www/) directory.

Create a MySQL database (e.g., lost_found_db) using phpMyAdmin.

Import the provided SQL file (lost_found_db.sql) into the database.

Update your database credentials inside db.php.

Start Apache & MySQL from XAMPP/WAMP.

Open the project in your browser:

http://localhost/lost_found_app/


Author
**Antony Xavier J M**
GitHub: antony11607
