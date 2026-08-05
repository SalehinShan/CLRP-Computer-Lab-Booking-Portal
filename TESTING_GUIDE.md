# CLRP Testing & Deployment Guide (GitHub Pages Ready)

This repository contains the **Computer Laboratory Resource Portal (CLRP)** formatted as **GitHub Pages Ready static HTML/CSS/JS** files inside the `views/` directory while preserving the exact modern dark glassmorphism design system.

---

## 🚀 Live Demo & GitHub Pages Deployment

### How to Enable GitHub Pages:
1. Push your changes to GitHub:
   ```bash
   git add .
   git commit -m "Keep HTML views in views/ subfolder and update index.html links"
   git push origin ui # or git push origin main
   ```
2. On GitHub, navigate to **Settings** -> **Pages**.
3. Under **Build and deployment**:
   - **Source**: `Deploy from a branch`
   - **Branch**: Select `main` (or `ui`) and folder `/ (root)`.
4. Click **Save**. Your site will be live at `https://<your-username>.github.io/CLRP-Computer-Lab-Booking-Portal/`.

---

## 🖥️ Local Static Testing

You can open [index.html](file:///Users/salehinshan/Desktop/CLRP-Computer-Lab-Booking-Portal/index.html) directly in any web browser or use a simple local web server:

### Option A: Open directly in Browser
Double-click [index.html](file:///Users/salehinshan/Desktop/CLRP-Computer-Lab-Booking-Portal/index.html) or drag it into Chrome/Firefox/Safari.

### Option B: Local HTTP Server (Python / Node)
```bash
# Python 3
python3 -m http.server 8000

# Node / npx
npx serve .
```
Navigate to `http://localhost:8000`.

---

## 🔑 Demo Accounts & Portals

The portal provides 1-click quick login & auto-fill demo accounts:

### 1. System Administrator Portal
*   **Direct View Page**: [views/admin/dashboard.html](file:///Users/salehinshan/Desktop/CLRP-Computer-Lab-Booking-Portal/views/admin/dashboard.html)
*   **Login Email**: `admin.sys@northsouth.edu`
*   **Features**: System stats overview, manage lab rooms, workstations inventory, software catalog mapping, user directory, student reservation approvals, technician ticket assignment.

### 2. Lab Technician Workspace
*   **Direct View Page**: [views/technician/dashboard.html](file:///Users/salehinshan/Desktop/CLRP-Computer-Lab-Booking-Portal/views/technician/dashboard.html)
*   **Login Email**: `kamrul.hasan@northsouth.edu`
*   **Features**: Assigned maintenance ticket queue, filter by unassigned/my tickets, ticket status update & resolution modal, historical ticket logs.

### 3. Student Portal
*   **Direct View Page**: [views/student/dashboard.html](file:///Users/salehinshan/Desktop/CLRP-Computer-Lab-Booking-Portal/views/student/dashboard.html)
*   **Login Email**: `abu.shan.241@northsouth.edu`
*   **Features**: Filter lab workstations by room or installed software, PC reservation modal, manage my bookings, file hardware/software issue reports.

---

## 🎨 UI & Design Highlights
*   **Theme**: Dark Mode with Glassmorphism blur effects (`Plus Jakarta Sans` typography, vibrant HSL gradients & glow).
*   **Interactivity**: Client-side tab switching, live filtering, dynamic stat counter updates, modal dialogs, and toast notifications.
