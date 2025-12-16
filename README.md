# <img src="assets/img/header-logo.png" alt="UIU Health Care Logo" width="3%"> UIU Health Care Management System

![Status](https://img.shields.io/badge/Status-Live-success)
![Version](https://img.shields.io/badge/Version-1.0-blue)
![License](https://img.shields.io/badge/License-Proprietary-red)

> **A comprehensive digital health platform for United International University.**

---

## 📖 Table of Contents
- [Project Overview](#-project-overview)
- [System Actors](#-system-actors)
- [Key Features](#-key-features)
- [Technical Architecture](#-technical-architecture)
- [Database Structure](#-database-structure)
- [Deployment & Configuration](#-deployment--configuration)
- [Current Status](#-current-status)

---

## 🏥 Project Overview

The **UIU Health Care** project is a web-based platform designed to digitize and streamline medical services at the **United International University (UIU) Medical Center**. It bridges the gap between the university's medical staff and beneficiaries, ensuring efficient healthcare delivery.

---

## 👥 System Actors

The system serves two primary user groups:

| Actor | Role | Description |
|-------|------|-------------|
| **Beneficiaries** | 🎓 Students / 🏫 Faculty | Access health services, book appointments, and view records. |
| **Administrator** | 👨‍⚕️ Doctor | Manage appointments, issue prescriptions, and oversee the system. |

---

## 🚀 Key Features

### 🌐 Public Landing Page
- **Services Overview**: Detailed information on medical services.
- **Emergency Contact**: Quick access to hotline numbers.
- **Health Tips**: Publicly accessible health blogs.
- **About Us**: Mission and vision of the medical center.

### 🎓 Student Module
- ✅ **Secure Login/Signup**: Authenticated access via Student ID/Email.
- 📊 **Dashboard**: Personalized health activity overview.
- 📅 **Appointment Booking**: Schedule visits with **Dr. Shamima Akter**.
- 📄 **Digital Prescriptions**: Download official prescriptions.
- 💊 **Medicine & Test Search**: Database of **20k+ medicines** with estimated costs.
- 🤖 **Diagnose Bot**: AI-powered symptom checker & first aid guide.
- 🏥 **Nearby Hospitals**: Locator for medical facilities near campus.
- 📝 **Health Blogs**: Read and interact with doctor-published articles.
- 👤 **Profile Management**: Update personal & medical details.

### 👨‍⚕️ Doctor Module
- 🖥️ **Doctor Dashboard**: Daily schedule & pending requests view.
- 📅 **Appointment Management**: Confirm, Reschedule, or Cancel appointments.
- ✍️ **Digital Prescription System**: Create & issue prescriptions digitally.
- 📂 **Patient History**: Access student medical records.
- 📢 **Blog Management**: Publish health tips for the community.
- ⚙️ **Profile Management**: Update professional details.

---

## 🏗️ Technical Architecture

### Frontend
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)

- **Styling**: Custom `main.css` with a clean, medical-themed UI (Green/White palette).

### Backend
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)

- **Logic**: Native PHP for authentication & business logic.
- **Config**: Centralized `config.php`.

### Database
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)

- **System**: MySQL / MariaDB.
- **Hosting**: Compatible with shared hosting (InfinityFree, XAMPP).

---

## 🗄️ Database Structure

The system uses a relational database with the following key tables:

- `students`: User registration data.
- `doctors`: Doctor profiles & credentials.
- `appointments`: Appointment tracking & status.
- `prescriptions`: Digital prescriptions linked to appointments.
- `medicines`: Extensive medicine price dataset.
- `tests`: Medical tests & costs.
- `medical_diagnoses`: Knowledge base for AI bot.
- `blogs`: Health articles.
- `blog_reactions`: User engagement data.

---

## ⚙️ Deployment & Configuration

- **Hosting**: Deployed on **InfinityFree**.
- **Security**:
  - 🔒 `.htaccess` configured for **CORS**, **XSS protection**, and **HTTPS**.
  - 🔑 **Bcrypt** password hashing.
  - 🛡️ **Prepared statements** for SQL injection prevention.
- **Configuration**: Managed via `config.php`.

---

## 🟢 Current Status

| Metric | Status |
|--------|--------|
| **Version** | Live / Deployment Ready |
| **Domain** | [uiu-healthcare.infinityfreeapp.com](https://uiu-healthcare.infinityfreeapp.com/) |
| **Responsiveness** | 📱 In Progress (Desktop-first) |

---

<div align="center">
  <sub>Designed & Developed for United International University</sub><br>
  <sub>© 2025 UIU Health Care</sub>
</div>
