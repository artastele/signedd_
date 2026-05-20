# SPED LMS — Special Education Learning Management System

A comprehensive Learning Management System for managing student enrollment, IEP (Individualized Education Plan) creation, implementation, and learning activity tracking. Built for Philippine SPED schools following DepEd standards.

## SignED
## Overview
- **SignED is an inclusive digital learning platform designed to enhance the quality of education for pupils with hearing impairments by providing Filipino Sign Language (FSL)-integrated materials, interactive video lessons, and specialized teacher training resources. The system addresses key accessibility gaps by delivering engaging, curriculum-aligned content that supports diverse learning needs while empowering teachers with tools and competencies for inclusive instruction. Through its integrated approach, SignED fosters a more accessible and effective learning environment that promotes academic growth and skill development among learners. Aligned with Sustainable Development Goal 4: Quality Education, the platform contributes to equitable access to quality education by reducing barriers, supporting inclusive practices, and ensuring that no learner is left behind.**

## Problem Statement
- **Limited inclusion of learners with special educational needs (SENs) in distance learning programs during the implementation of inclusive education policies.**
- **Insufficient implementation of inclusive content policies for hearing-impaired students in the Department of Education (DepEd).**
- **Limited integration of Filipino Sign Language (FSL) in teacher education programs nationwide.**

## Objectives
- **Increased inclusion of learners with special educational needs in distance learning programs to at least 85% participation.**
- **Improved implementation of inclusive content policies for hearing-impaired students to at least 80% compliance across DepEd schools.**
- **Increased integration of Filipino Sign Language (FSL) in teacher education programs nationwide to at least 75% program adoption.**

## Target Users / Personas
- **SPED Teachers**
- **Hearing-Impaired Learners**

## Features

- **Process 1:** Parent Enrollment Submission
- **Process 2:** SPED Teacher Document Verification
- **Process 3:** Initial Assessment Conduct
- **Process 4:** IEP Meeting Facilitation
- **Process 5:** IEP Generation and Signing
- **Process 6:** IEP Implementation
- **Process 7:** Learning Activity Tracking

## Technology Stack

- **Backend:** PHP (OOP/MVC)
- **Database:** MySQL 8+
- **Frontend:** Bootstrap 5 (Custom Theme)
- **Email:** PHPMailer

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer (for PHPMailer dependency management)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd sped-lms
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and update database credentials and email settings.

3. **Install dependencies**
   ```bash
   composer install
   ```
   This will install PHPMailer and other required packages.

4. **Set up database**
   - Create a MySQL database named `sped_lms`
   - The schema will be automatically applied on first run

5. **Configure web server**
   - Point document root to `/public` directory
   - Enable mod_rewrite (Apache) or configure URL rewriting (Nginx)

6. **Set permissions**
   ```bash
   chmod -R 755 public/uploads
   chmod -R 755 logs
   ```

7. **Access the application**
   - Navigate to `http://localhost/sped-lms` (or your configured URL)
   - Default admin credentials:
     - Email: `admin@spedlms.local`
     - Password: `password` (change immediately after first login)

## Project Structure

```
/app
  /Controllers     — HTTP request handlers
  /Models          — Database interaction layer
  /Views           — HTML templates
  /Middleware      — RBAC and session management
/config
  db.php           — Database connection
  schema.sql       — Database schema (single source of truth)
  permissions.php  — Role-permission mapping
/public
  /css             — Custom Bootstrap theme
  /js              — JavaScript files
  /uploads         — User-uploaded files
/routes
  web.php          — Route definitions
/logs              — Application logs
```

## User Roles

- **Admin** — Full system access
- **Parent** — Submit enrollment, track child progress
- **SPED Teacher** — Verify enrollment, conduct assessments, implement IEPs
- **Guidance** — Facilitate IEP meetings, provide insights and signatures
- **Principal** — Sign and approve IEPs
- **Master Teacher** — Conduct class observations (future)

## Security Features

- Password hashing (bcrypt)
- Role-based access control (RBAC)
- Session timeout (15 minutes)
- Login attempt logging
- Admin activity logging
- Secure file uploads

## Development Workflow

See `CHANGELOG.md` for feature development history and approval tracking.

## License

Proprietary — SPED LMS Project

## Support

For issues or questions, contact the development team.
