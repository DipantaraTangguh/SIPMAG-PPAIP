# SIPMAG - Technical Handoff Document

This document provides a comprehensive technical breakdown of the SIPMAG Figma design, intended for an AI Coding Agent or Frontend Developer to translate into **React + Tailwind CSS v4**.

---

## 1. Design Tokens (Tailwind v4 Mapping)

### 1.1 Colors

The application relies on a curated set of brand colors and functional status colors. In Tailwind v4, these should be mapped in your base CSS (e.g., using `@theme`).

**Brand Colors:**
*   **Primary (`--color-primary`):** `#682828` (Base)
    *   *Hover/Focus:* `#85171A` (Derived from Figma Primitives)
    *   *Active/Dark:* `#5A1012`
*   **Accent (`--color-accent`):** `#943939` (Base)
    *   *Hover/Focus:* `#AC6163`
    *   *Active/Dark:* `#701316`
*   **Background (App Window):** `#F8F9FB`
*   **Surface (Cards/Forms):** `#FFFFFF`

**Status Colors:**
Based on the status review screens ("Ditinjau", "Disetujui", "Ditolak"):
*   **PendingReview (Sedang Ditinjau):**
    *   Background/Surface: `#FFFBEB` (Tailwind `amber-50`)
    *   Icon/Text: `#F59E0B` (Tailwind `amber-500`)
*   **Approved (Disetujui!):**
    *   Background/Surface: `#ECFDF5` (Tailwind `emerald-50` / `e0f2f1`)
    *   Icon/Text: `#10B981` (Tailwind `emerald-500` / `#059669`)
*   **Rejected (Ditolak):**
    *   Background/Surface: `#FEF2F2` (Tailwind `red-50` / `fee2e2`)
    *   Icon/Text: `#EF4444` (Tailwind `red-500`)

### 1.2 Typography

The design uses two primary font families. They map to the Tailwind scale as follows:

*   **Font Families:**
    *   **Heading:** `Inter` (`font-heading`)
    *   **Body/UI:** `Inter` (`font-sans`)

*   **Scale Mapping:**
    *   `text-3xl` (30px) / `font-bold` (700) / `leading-[36px]` → Page Titles (e.g., "Portal Magang")
    *   `text-2xl` (24px) / `font-bold` (700) / `leading-[32px]` → Section Titles / App Logo
    *   `text-xl` (20px) / `font-bold` (700) / `leading-[28px]` → Sub-section Titles (e.g., "Isi Form Magang-01")
    *   `text-base` (16px) / `font-medium` (500) → Standard Body Text, Form Labels
    *   `text-sm` (14px) / `font-normal` (400) → Descriptions, Input Text, Nav Links
    *   `text-xs` (12px) / `font-medium` (500) → Badges, Helper Text, Stepper Labels

---

## 2. Component Specifications

### 2.1 Layout Shell

*   **Sidebar (Aside):**
    *   **Width:** Fixed at `260px` (`w-[260px]`).
    *   **Behavior:** Sticky/Fixed to the left viewport (`fixed h-screen left-0 top-0`).
    *   **Styling:** Background uses Primary dark variant (`bg-primary-dark`). Text is `#FFFFFF`.
    *   **Padding:** Vertical `py-8`, Horizontal padding for items `px-6`. Logo area has `mb-10`.
*   **Topbar / Header Container:**
    *   **Height:** Implicit based on content (`~104px` to `120px` height footprint).
    *   **Behavior:** Sits within the main scrollable area, to the right of the sidebar (`ml-[260px]`).
    *   **Padding:** Top/Bottom `py-8` (`32px`), Left/Right `px-8` (`32px`) or `px-12` (`48px`) depending on the main container density.
*   **Main Content Area:**
    *   Background: `#F8F9FB`.
    *   Margin-left to offset sidebar: `ml-[260px]`. Maximum width constraints applied to inner content (`max-w-5xl`).

### 2.2 Navigation (Menu Items)

General Nav Item Specs: `h-11` (44px), `px-6 py-3`, gap between icon and text `gap-3`. Active items have a light tinted background (`bg-[#F9ECEC]`/`bg-white/10`) with accent text color.

**Menu Items by Role Configuration:**

1.  **Mahasiswa (Student) - Default view mapped in Figma:**
    *   Beranda (Home) 
    *   Portal Magang
    *   Bimbingan & Logbook
    *   Sidang Magang
    *   Profil
2.  **Kaprodi (Head of Program):**
    *   Dashboard
    *   Validasi Syarat Akademik (Form 1)
    *   Validasi Form 2 & Surat Pengantar
    *   Penugasan Dosen Pembimbing
    *   Approval Sidang
3.  **PPAIP (Admin):**
    *   Dashboard Admin
    *   Manajemen Dokumen & SK
    *   Pemantauan Logbook Global
    *   Penjadwalan Sidang
4.  **DPM (Dosen Pembimbing Magang):**
    *   Dashboard Dosen
    *   Daftar Mahasiswa Bimbingan
    *   Review & Approval Logbook
    *   Penilaian Sidang

### 2.3 Forms (Form 1, Pengajuan Pembimbing, Logbook)

*   **Form Card Containers:**
    *   Background: `bg-white`
    *   Border Radius: `rounded-xl` (`12px`)
    *   Shadow/Border: Subtly outlined with `#EDEEF0` (`border border-gray-200`)
    *   Padding: Header gets `p-8` (`32px`), Body is spaced with `gap-6`.
*   **Input Fields Structure:**
    *   Height/Sizing: Standard inputs are `h-12` (`48px`). Textareas range from `140px` to `200px` height.
    *   Border Radius: `rounded-lg` (`8px`).
    *   Padding inside inputs: `px-4 py-3`.
    *   Border Color: `border-gray-300`, active states glow with the `ring-primary/accent`.
*   **Buttons:**
    *   Border Radius: `rounded-lg` (`8px`).
    *   Padding: `px-6 py-2.5` or `px-8 py-3`.
    *   Primary Button: `bg-primary text-white border-none`.
    *   Secondary/Ghost Button: `text-primary bg-transparent hover:bg-gray-50`.

---

## 3. Interaction & State Logic

### 3.1 Conditional Rendering & Access Logic

The UI elements should be guarded dynamically based on the student's `access_status` and data state:

*   **"Ajukan Sidang" Logic:** The button to apply for the final defense (Sidang) should be strictly **disabled/hidden** until a prerequisite number of logbook entries (e.g., minimum 6 entries) hold an `Approved` status by the DPM. 
*   **Step Indicators:** The horizontal progress stepper (Syarat Akademik -> Lamaran -> Pengajuan Pembimbing -> Logbook -> Sidang) dynamically assigns `Active` / `Completed` / `Locked` states based on the approval of the previous phase.

### 3.2 Form 2 Submission Logic (Repeated Inputs)

Based on the explicit design annotations within the Figma file:
*   **Multiple Form 2 Records:** Form 2 is structured technically like Form 1, but with a critical difference: **Students can input and generate Form 2 multiple times.**
*   **Why:** To allow students to generate personalized, signed PDF Letters of Introduction (Surat Pengantar Magang) for multiple different companies during their application phase.
*   **UI Display:** The system must render a *List or Table View* of all generated Form 2s under the student's profile. Admin/Kaprodi will have visibility over this array of submissions to track exactly where a student is applying.
*   **Action:** Each row should have an action to "Unduh PDF" (Download PDF) once the signature is digitally appended.
