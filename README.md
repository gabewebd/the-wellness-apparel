# Submission Components

This submission has the following components:
1. Entire source code for the application
2. SQL database schema, including sample data
3. Instructions for setup and running the application
4. Video Presentation

# The Wellness Apparel - Project Documentation

Project Summary
Since it's a web application, we cannot simply create an executable (.exe) file; it requires a web server environment to host the application.

# How to Use Application

**Option 1: Live Demo (Recommended)**
The application is currently live at: *http://the-wellness-apparel.onlinewebshop.net/*
This is the easiest way to view and evaluate the application, because it is currently hosted and setup with the database and necessary configurations.

**Option 2: Local Setup with Source Code**

**Requirements:**
- Node.js (v16 or higher)
- A local server environment (XAMPP, WAMP or similar), PHP, and MySQL
- A web browswer

**Instructions:**

1. Clone the repository:

   git clone *https://github.com/gabewebd/the-wellness-apparel.git*
   cd wellness-apparel   

2. Install dependencies:

   npm install

   OR

1. Navigate to *https://github.com/gabewebd/the-wellness-apparel* and download as a ZIP repository

2. Extract and drag the folder into your C:\xampp\htdocs\



3. Initialize the database (choose one of the two methods):

	**Method A: Import the provided SQL file**
	- Open XAMPP/WAMP and make sure that the MySQL service is running.
	- In your browser, navigate to *http://localhost/phpmyadmin*
	- Create a new database named *"4611173_wellnessapparel.”*
	- Click on that new database and select the Import tab.
	- After that, import the SQL file from the extracted sql/4611173_wellnessapparel.sql folder.

	**Method B: Create the database manually**
	- Open XAMPP/WAMP and make sure that the MySQL service is running.
	- In your browser, navigate to *http://localhost/phpmyadmin*
	- Create a new database named *"4611173_wellnessapparel.”*
	- Execute the SQL commands from the provided schema file to create the tables.
	- If sample data was provided you will have to import it.

 4. Run the web project:
	Type *localhost/the-wellness-apparel-main/* in your browser address line. Also, you may have to change the directory if the folder was renamed upon download or to whichever you prefer.


# Important Notes
Wellness Apparel is a web app, not a normal desktop application.
- You will not be able to compile this project in an .exe file that can be used independently.
- The application must have a web server environment and database set up to run the app normally and be fully usable.
- For review purposes, it is highly recommended you use the live demo (Option 1) for your evaluation as it provides the truest sense of the intended experience to the user.

# Database Connection
To work properly, the app needs to connect to a database. The online demonstration site is configured with a working database: *http://the-wellness-apparel.onlinewebshop.net/*

# If you have any questions or issues with the setup, please reach out to our members on Canvas:

AGUILUZ, JOSH ANDREI
CAMUS, MARK DAVE
VELASQUEZ, GABRIELLE
YAMAGUCHI, MIKA
