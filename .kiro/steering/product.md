---
inclusion: always
---

# SPED LMS — Product Overview

## What this system is
A Special Education (SPED) Learning Management System for managing student enrollment, IEP (Individualized Education Plan) creation, implementation, and learning activity tracking. Built for Philippine SPED schools following DepEd standards.

## Target users
- Parents — submit enrollment documents, track child progress
- SPED Teachers — verify enrollment, conduct assessments, implement IEPs
- Guidance Counselors — facilitate IEP meetings, provide insights and signatures
- Principals — sign and approve IEPs
- Master Teachers — conduct class observations and COT
- Admins — manage user roles and system access

## Color scheme (apply to ALL UI — never deviate)
- Primary:  #a01422 (deep crimson) — buttons, active states, alerts, headings
- Secondary: #1e4072 (navy blue) — navbar, sidebar, secondary actions, badges
- Neutral:  #f5f5f5 (light surface), #ffffff (white cards), #2c2c2c (body text)

## DFD Process Map (DO NOT alter process flows, data stores, or actor names without explicit approval)

### Process 1 — Parent Complying Enrollment Requirements
- Actor: Parent
- Inputs: PSA, PWD ID / Medical Record, BEEF (file uploads)
- Output: Enrollment submission → Process 2
- Store: `enrollment_submissions`

### Process 2 — Verifying Enrollment Requirements
- Actor: SPED Teacher
- Inputs: Submitted documents from Process 1
- Output: Verified Student Record, Education History
- Stores: `student_records`, `education_history`

### Process 3 — Conducting Initial Assessment
- Actor: SPED Teacher
- Inputs: Student Record
- Output: Assessment Record (versioned — never overwrite)
- Store: `assessment_records`

### Process 4 — Facilitating IEP Meeting
- Actors: Guidance, Principal
- Inputs: Assessment Record
- Output: Meeting Schedule → Guidance + Principal (PHPMailer notification)
- Store: `iep_meetings`

### Process 5 — Generating IEP
- Actors: Guidance (insight + signature), Principal (signature + remarks)
- Inputs: Meeting details, IEP Draft
- Output: Signed IEP → IEP Repository
- Stores: `iep_documents`, `iep_signatures`
- Rule: Lock document from edits once all signatures are collected

### Process 6 — Implementing IEP
- Inputs: IEP, Learner Educational Assessment Form, Learning Materials
- Output: Individualized Education Plan linked to learner
- Stores: `learner_iep`, `learning_materials`

### Process 7 — Learner Engaging in Learning Activities
- Inputs: IEP, Modules
- Output: Learning Performance log → Process 8 (future)
- Stores: `activity_records`, `module_access_logs`
