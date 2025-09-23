# Laravel Multi-User Authentication System

## Project Overview
Laravel application with MySQL, Bootstrap, admin login/registration, role-based authentication system with admin, PM, and external customer user types, separate dashboards, and middleware for permission control.

## User Types
- Admin (internal): Full system access, can create PM users
- PM (internal): Project management access
- External Customer: Limited customer portal access

## Features
- Separate login/registration for each user type
- Role-based middleware for permission control
- Dedicated dashboards for each user type
- Bootstrap for responsive UI
- MySQL database integration

## Development Guidelines
- Use Laravel best practices for authentication
- Implement proper middleware for role-based access
- Create separate controllers for different user types
- Use Bootstrap for consistent UI styling
- Follow Laravel naming conventions
