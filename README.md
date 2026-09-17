# Michos, Rick, & Morty

A full-stack PHP/MySQL web application for browsing, importing, saving, and managing Rick and Morty character data.

**Live Demo:** https://mrc82-it202-450-m2026-prod.onrender.com/project/  
**Video Demo:** https://youtu.be/_pj2t8f0Zg0

## Overview

This project was built as a full-stack web application using PHP and MySQL. Users can create accounts, browse and filter character records, save characters to their profiles, and view public profile information.
Administrators have additional tools for importing characters from the Rick and Morty API, creating records manually, editing and deleting characters, and managing user-character relationships.

## Features

- User registration and login
- Password hashing and authenticated sessions
- Role-based access for users and administrators
- Character browsing, searching, filtering, and sorting
- Rick and Morty REST API integration
- Manual character creation
- Character CRUD operations
- Saved-character functionality
- Public user profiles
- User-to-character database relationships
- Admin relationship management and assignment
- CSRF protection for protected actions
- Production deployment on Render

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, Bootstrap
- **API:** Rick and Morty REST API
- **Version Control:** Git, GitHub
- **Deployment:** Render
- **Development:** Visual Studio Code

## Application Structure

The browser provides the user interface while PHP handles application logic, authentication, authorization, validation, and database operations.
MySQL stores users, roles, character records, and relationships between users and characters.
The application also communicates with the Rick and Morty API to search for external character data and import selected records into the local database.

## Security

The project includes:

- Hashed passwords
- Session-based authentication
- Role-based authorization
- Input validation
- Parameterized database queries
- Output escaping
- CSRF protection for state-changing actions

## Project Highlights

One of the main design features is the relationship between users and characters. Rather than duplicating character records for each user, the application stores user-character associations in a separate relationship table.
This supports saved characters, administrator assignment, relationship removal, and reports for associated and unassociated characters.

## Author

**Michos Christopher Colobong**

- GitHub: https://github.com/mikopher
- LinkedIn: https://linkedin.com/in/michos-colobong
