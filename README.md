## Student Record Management System (SRMS)

The Student Record Management System (SRMS) is a role-based web application developed using PHP and MySQL.  
It is designed to manage student academic records, attendance, and grades in a secure and structured way.  
The system provides separate access for administrators, staff members, and students.

---

## Login Credentials

### Admin Account
- Email: admin@portal.com  
- Password: Admin@123  

### Staff Account
- Staff accounts can only be created by the administrator.

### Student Account
- Student accounts can only be created by the administrator, and student IDs are generated automatically.

---

## Features Implemented

### Admin Module
- Create and manage staff and student accounts  
- Assign faculty and semester when creating students  
- Manage subjects for all faculties and semesters  
- Bulk generate grade records for entire cohorts  
- View cohort overview (students, subjects, missing grades)

### Staff Module
- View student list with faculty and semester details  
- Update student attendance percentage  
- Create and edit grades for each subject  
- Add marks, grade letters, and remarks for students  

### Student Module
- View personal profile information  
- View attendance details  
- View detailed subject-wise grades and results  

---

## UI and Design
- Modern responsive dashboard layout  
- Improved dropdown styling and form design  
- Clean glass-based interface with professional colors  
- Semantic HTML structure for accessibility and SEO friendliness  

---

## System Highlights
- Secure authentication using password hashing  
- CSRF protection on all forms  
- Role-based access control for admin, staff, and students  
- Automatic database and table creation through code  

---

## Known Issues
- Subjects must exist for a faculty and semester before grades can be assigned  
- phpMyAdmin may require refresh to display newly created databases  

---

This project demonstrates a complete academic record management workflow using PHP, MySQL, and secure role-based system design.
