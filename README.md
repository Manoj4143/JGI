# 📅 JGI - JAIN INSTITUTE OF TECHNOLOGY — Automated Timetable Generator & Scheduling System

An enterprise-grade, automated campus timetable generator custom-tailored for **JGI - JAIN INSTITUTE OF TECHNOLOGY (JIT)**. Built with **PHP**, **MySQL**, and a modern **responsive web interface**, it features a constraint-satisfaction heuristic engine that automatically produces clash-free, contiguous academic timetables while balancing room allocations, faculty availability, lab batch splitting, and student cohort loads.

---

## 🚀 Key Innovations & Highlights

- **🤖 Automated Clash-Free Scheduling Engine:** Eliminates room double-booking, professor overlap, and student cohort conflicts using multi-constraint heuristics.
- **📱 Fully Responsive Mobile-First Design:** Complete mobile layout with a slide-in off-canvas navigation drawer, sticky bottom navigation bar, and adaptive mobile card feeds.
- **🔄 Smart Faculty Timing Adjustment Portal:** Allows teachers to request class slot changes or mark unavailability with crystal-clear separation between **Actual Current Timing** and the **Timing You Need**.
- **🛡️ Administrative Verification Console:** Enables Dean/HOD administrators to review, approve, or reject timing requests in real-time, automatically synchronizing approvals with the timetable constraint engine.
- **🌓 Midnight Command Center & Kinetic Light Themes:** Instant theme toggle (☀️ Light / 🌙 Dark) with persistent `localStorage` preference and accessible contrast.
- **🔐 Multi-Tier Role-Based Access Control (RBAC):** Dedicated permissions and tailored navigation for **Administrators**, **Faculty Members**, and **Counselors**.

---

## 📱 Mobile-First User Experience

The application is completely optimized for all device viewports (smartphones, tablets, laptops, and 4K displays):

| Feature | Desktop (>= 769px) | Mobile (<= 768px) |
| :--- | :--- | :--- |
| **Sidebar Navigation** | Sleek collapsible sidebar (72px collapsed &rarr; 250px on hover). | Off-canvas drawer sliding from the left with a backdrop blur overlay and `✕` close button. |
| **Quick Navigation** | Full topbar with global search pill (⌘F) and user chip. | **Mobile Bottom Navigation Bar** with direct touch access to Home, Schedule, Classes, Requests, and Menu. |
| **Timing Request Form** | 2-column side-by-side comparison cards (*Actual* vs *Desired*). | Vertically stacked 1-column layout with large touch targets (min 44px height). |
| **Requests Roster** | Full-width sturdy data table with high contrast. | **Adaptive Mobile Card Feed** detailing ID, Subject, From &rarr; To route, reason, and touch buttons. |
| **Timetables & Grids** | Structured campus grid matrix. | Smooth touch momentum horizontal scrolling (`-webkit-overflow-scrolling: touch`). |

---

## 🔄 Faculty Timing Request Workflow

### The Problem It Solves
Faculty members often need to move lectures due to workshops, research conferences, or medical reasons. Previously, forms had only a single *"Time Slot"* field, causing ambiguity over whether teachers should enter their *current scheduled time* or their *desired new time*.

### The Simple, Two-Step Workflow
1. **Quick-Fill from Scheduled Lectures:** A dropdown list at the top displays the teacher's assigned classes (e.g., `[Tuesday 11:15 AM] BIST703`). Selecting one automatically populates the Subject, Current Day, and Current Period.
2. **Step 1 — 📍 Actual Current Timing (From):** The day and period where the class is *currently timetabled* (or the slot where the faculty is unavailable).
3. **Step 2 — 🎯 Timing You Need (To / Preferred Slot):** The replacement day and period where the faculty wants the lecture *shifted to*.
   - *Note:* If **Faculty Unavailability / Leave** is selected, Step 2 is automatically disabled because the engine blocks the slot without needing a target replacement.
4. **Admin Approval & Auto-Sync:** When an Administrator approves an unavailability request, it automatically updates the `faculty_timing` database table (`is_available = 0`), preventing the generator from booking that period.

---

## 👥 Role-Based Access Control (RBAC)

The application supports three distinct roles with context-aware navigation:

### 1. 👨‍💼 Administrator
- Full access to timetable generation (`generate_tt.php`, `mastertt.php`).
- Manage academic departments, teachers, subjects, rooms, and student cohorts.
- Review and approve/reject faculty timing change requests (`timing_requests_admin.php`).
- View and manually configure the faculty unavailability matrix (`faculty_timing.php`).
- Manage user credentials and assign roles (`userlist.php`).

### 2. 👨‍🏫 Faculty Member
- View assigned weekly teaching schedule (`search_t_result.php`).
- Submit class slot reallocation and leave requests (`request_timing.php`).
- Track real-time status (Pending, Approved, Rejected) with administrative notes.
- View verified academic credentials and assigned curriculum (`my_profile.php`).

### 3. 🎓 Counselor / Student Representative
- Read-only access to master timetables and cohort schedules (`mastertt.php`, `search_course.php`).
- Room allocation and teacher directory lookup (`search_room.php`, `search_teacher.php`).

---

## 🗄️ Database Architecture

Key database tables powering the application:

- **`sched`**: The master generated schedule entries (`day_id`, `time_s_id`, `sub_id`, `teacher_id`, `room_id`, `group_name`).
- **`timing_requests`**: Faculty adjustment requests (`request_id`, `teacher_id`, `request_type`, `day_id`, `slot_id`, `pref_day_id`, `pref_slot_id`, `sub_id`, `reason`, `status`, `admin_remark`, `created_at`).
- **`faculty_timing`**: Faculty unavailability matrix (`teacher_id`, `day_id`, `slot_id`, `is_available`, `notes`) used directly by the generator constraint solver.
- **`profile`**: Academic staff profiles (`teacher_id`, `teacher_name`, `acad_rank`, `designation`, `dept_id`, `email`).
- **`subjects`**: Courses & curriculum (`sub_id`, `sub_code`, `sub_name`, `subject_type`, `instructor`, `credits`).
- **`room`**: Classrooms and specialized laboratories (`room_id`, `room_name`, `room_type`).
- **`dept`**: Academic departments (`dept_id`, `department`, `dept_person`, `title`).
- **`user`**: Authentication and roles (`user_id`, `username`, `userpass`, `email`, `role`, `teacher_id`).

---

## 🛠️ Installation & Setup Guide

### 1. Prerequisites
- **Web Server:** Apache or Nginx (or PHP built-in server)
- **PHP Version:** PHP 7.4 to 8.3+ (requires `mysqli` extension enabled)
- **Database:** MySQL 5.7+ or MariaDB 10.4+

### 2. Clone / Copy Repository
Place the project folder in your web server root:
```bash
# Example for XAMPP on Windows:
C:\xampp\htdocs\Automatic-Time-Table-Generator-master
```

### 3. Database Setup
1. Open **phpMyAdmin** (`http://127.0.0.1/phpmyadmin`) or MySQL CLI.
2. Create a database named `scheduling`:
   ```sql
   CREATE DATABASE scheduling CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import the initial SQL schema located in `sql/scheduling.sql`:
   ```bash
   mysql -u root -p scheduling < sql/scheduling.sql
   ```
4. Verify database credentials in `includes/dbconnection.php`:
   ```php
   $conn = mysqli_connect("127.0.0.1", "root", "", "scheduling");
   ```

### 4. Run Schema Migrations
Execute the automated migration scripts in your browser or CLI to ensure all modern columns, Saturday schedules, and timing request tables are synchronized:
```bash
php includes/db_migration_v2.php
php includes/db_migration_v3.php
```

### 5. Start the Application
You can run using Apache in XAMPP or start the PHP built-in web server:
```bash
cd d:\Automatic-Time-Table-Generator-master
php -S 127.0.0.1:8000
```
Open **`http://127.0.0.1:8000/Admin/index.php`** in your browser.

---

## 🔑 Default Login Credentials

| Role | Username / Email | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin` | Full System Control & Generation |
| **Faculty Member** | `ashan@campus.edu` | `a` | Faculty Schedule & Timing Requests |
| **Counselor** | `counselor` | `counselor123` | Master Timetable & Search |

---

## 📂 Project Structure

```
Automatic-Time-Table-Generator-master/
├── Admin/                          # Application pages & controllers
│   ├── admin.php                   # Administrator Dashboard
│   ├── request_timing.php          # Faculty Timing Adjustment Portal (Mobile-Ready)
│   ├── timing_requests_admin.php   # Admin Timing Verification Console (Mobile-Ready)
│   ├── faculty_timing.php          # Faculty Unavailability Matrix
│   ├── mastertt.php                # Master Campus Timetable
│   ├── generate_tt.php             # Timetable Generation Controller
│   ├── search_t_result.php         # Individual Faculty Teaching Schedule
│   ├── search_course.php           # Student Cohort Timetable Lookup
│   ├── search_room.php             # Room Allocation Matrix
│   ├── my_profile.php              # Faculty Profile & Credentials
│   └── userlist.php                # User Administration & RBAC
├── includes/                       # Shared modules, layout & styles
│   ├── shedulo_shell.php           # Shell: Topbar, Sidebar, Mobile Drawer, Bottom Nav
│   ├── style_prac2.css             # Main stylesheet & responsive media queries
│   ├── dbconnection.php            # MySQL Database connection
│   ├── session.php                 # Session authentication & security check
│   ├── db_migration_v2.php         # Saturday slots & time definitions
│   └── db_migration_v3.php         # RBAC, faculty_details & timing_requests schema
├── sql/                            # Database dumps & SQL scripts
│   └── scheduling.sql              # Base database dump
└── README.md                       # Comprehensive documentation
```

---

## 💡 Troubleshooting & FAQs

- **Why did I see `Unknown column 'sub_id'`?**
  Run `php includes/db_migration_v3.php`. This adds `sub_id`, `pref_day_id`, `pref_slot_id`, and `admin_remark` to `timing_requests`.
- **Why did the admin roster look empty?**
  In earlier versions, a reference to `d.dept_name` was used instead of `d.department`. This has been resolved in `Admin/timing_requests_admin.php`.
- **How do I access on a phone / mobile device?**
  Connect your phone to the same local Wi-Fi network and open `http://<your-computer-ip>:8000/Admin/request_timing.php`. The interface automatically scales and enables the slide-out drawer and bottom navigation bar.

---

## 📜 License & Credits

Developed for academic institutions and universities to automate complex scheduling. Built with clean PHP, Vanilla CSS, and modern responsive design best practices.
