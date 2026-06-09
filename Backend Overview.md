# ITI Attendance & Grading Platform: Backend Overview (M1 & M2)

This covers the backend built so far. **M1** is the structural core: cohort management, course setup, lab groups with student rosters, and instructor engagements. **M2** is the billing engine: turning the delivered-session schedule into amounts owed to instructors and track admins, and forwarding a consolidated rollup to central accounting. The billing section is at the end.

---

## The Four User Roles

Everyone has exactly one role. The role determines what you can see and do.

**Branch Manager (BM):** runs the whole branch. Full control: creates cohorts, assigns Track Admins, manages everything across all tracks.

**Track Admin (TA):** manages a specific cohort. Sets up courses, lab groups, and engagements, and places students into lab groups, but only for cohorts they have been explicitly assigned to. Cannot see or touch other cohorts.

**Instructor:** delivers sessions. Holds engagements that define when and what they teach, and can see the lab groups they run together with the students in each one.

**Student:** enrolled in a cohort and placed in a lab group. Can see the group they belong to.

---

## The Structural Hierarchy

Everything lives inside this hierarchy:

```
Branch
  └── Track  (e.g. Open Source, Python, .NET)
        └── Cohort  (e.g. Intake 45)  ← only ONE active per track at a time
              ├── Course  (e.g. Laravel Fundamentals)
              │     └── Course Components  (e.g. Lab = 40%, Exam = 60%)
              └── Lab Group  (~15 students, one assigned instructor)
```

### Branch
The physical ITI location. Has a name and an assigned manager. Tracks belong to it.

### Track
A specialization offered at the branch. A track can have many cohorts over time, but only one can be **active** at any given moment. Opening a second active cohort for the same track is blocked at both the database level and the API level.

### Cohort
A batch of students going through a track together. Created by the BM. Has a lifecycle status: `planned`, `active`, or `completed`. When the BM creates a cohort they assign one or more Track Admins to run it. Those Track Admins can only see and manage their assigned cohorts, not anyone else's.

### Course
A subject taught within a cohort (e.g. "Laravel Fundamentals"). Always scored out of 100. Belongs to exactly one cohort.

### Course Component
Defines how a course's 100 points are split. Each component has:
- A **type**: `lab`, `quiz`, `exam`, or `project`
- A **weight**: how many of the course's 100 points this component is worth (e.g. 40). The weights across a course's components must add up to 100.
- A **raw max**: the maximum raw score achievable before weighting is applied (e.g. 70 out of 70 scales to 40% of the total)

Example: a course might have a lab component (weight 40, raw max 70) and an exam component (weight 60, raw max 100). If a student scores 70/70 on the lab and 80/100 on the exam, their total is 40 + 48 = 88.

If you update a course and send a new component list, the old components are **fully replaced**. It is a redefinition, not an addition.

### Lab Group
A smaller working cluster inside a cohort, typically around 15 students, run by one assigned instructor. The Track Admin places students into a group by passing their IDs when creating or updating the group (this replaces the group's roster, and only students already enrolled in that cohort can be added). The group's instructor can see their groups and rosters; each student can see the group they are in.

---

## Engagements: Teaching Assignments

An **engagement** is a formal record that says: *"This instructor delivers this type of session, for this cohort, between these two dates, for this many hours per session."*

- **Types**: `lab`, `lecture`, `business`
- One instructor can hold **multiple engagements** at the same time or at different times (e.g. a lab engagement running June to September and a lecture engagement running July to October)
- Each engagement belongs to one instructor and one cohort
- The assigned person must be someone who teaches: an instructor or a track admin (the two roles can both hold teaching seats)

### Access Window
A read-only derived value available on any user: the span from their **earliest engagement start date** to their **latest engagement end date**. This tells you the total period that person is active on the platform. Any logged-in user can query it.

---

## Authorization Rules

### Cohorts
| Action | Who |
|---|---|
| Create / update / delete a cohort | Branch Manager only |
| View cohorts | BM sees all; Track Admin sees only their assigned cohorts |
| Manage courses and lab groups inside a cohort | BM or the assigned Track Admin |

### Lab Groups
| Action | Who |
|---|---|
| Create / update / delete a group, place students | BM or the assigned Track Admin |
| See the groups you teach and their rosters | The group's instructor |
| See the group you are placed in | The student in it |

### Engagements
| Action | Who |
|---|---|
| Create / update / delete | BM or Track Admin |
| View engagements | BM sees all; Track Admin sees only those in cohorts they run |
| Read access window for any user | Any logged-in user |

---

## API Endpoints

All endpoints require the header `Accept: application/json` and are prefixed with `/api/v1/`. All except the public health check require a Bearer token from login.

### Cohorts & Structure
```
GET    /cohorts                        list cohorts (scoped by role)
POST   /cohorts                        BM only: create a cohort (status optional) and assign TAs
GET    /cohorts/{cohort}               full detail including courses and lab groups
PATCH  /cohorts/{cohort}               BM only: update name or status
DELETE /cohorts/{cohort}               BM only

GET    /cohorts/{cohort}/courses       list courses in a cohort
POST   /cohorts/{cohort}/courses       create a course (components in the same request, weights total 100)
GET    /courses/{course}               course detail with components
PATCH  /courses/{course}               update course (sending components replaces the full set)
DELETE /courses/{course}

GET    /cohorts/{cohort}/lab-groups    list lab groups in a cohort, each with its roster
POST   /cohorts/{cohort}/lab-groups    create a lab group (optional student_ids fills the roster)
GET    /lab-groups/{labGroup}          lab group detail with roster
PATCH  /lab-groups/{labGroup}          update (student_ids replaces the roster)
DELETE /lab-groups/{labGroup}

GET    /my/lab-groups                  instructor: the groups they teach, each with its roster
GET    /my/lab-group                   student: the group they are placed in
```

### Engagements
```
GET    /engagements                    list engagements (scoped to the TA's cohorts; BM sees all)
POST   /engagements                    create an engagement
GET    /engagements/{engagement}       detail
PATCH  /engagements/{engagement}       update
DELETE /engagements/{engagement}

GET    /users/{user}/access-window     earliest start to latest end across all engagements
```

---

## Key Business Rules

1. **One active cohort per track at a time.** Enforced in the database (a partial unique index) and in the API on both creation and activation, returning a clear validation error rather than a server error. A track can still hold `planned` or `completed` cohorts alongside its active one.

2. **Track Admins are scoped.** A Track Admin cannot see, modify, or manage a cohort they haven't been assigned to by the BM. This is checked on every endpoint: listing, viewing, and any write operation. The same scoping applies to that cohort's engagements.

3. **Courses are out of 100.** Every course is fixed at 100 points, and a course's component weights must total 100. A component's raw score is normalized onto its weight at grading time.

4. **Course components are a full set.** When you update a course and include components, the old ones are deleted and replaced entirely. If you don't include components in an update request, they are left untouched.

5. **Lab group membership.** A student is placed in one lab group per cohort through their enrollment. The Track Admin sets a group's roster; the group's instructor sees their groups and rosters; each student sees their own group.

6. **Only teachers can be booked.** An engagement's assigned person must be an instructor or a track admin. A student or the branch manager cannot be booked to teach.

7. **Multiple engagements per instructor are allowed.** There is no limit. The access window is derived, not stored, and is recalculated on request from the live engagement records.

8. **Seeded demo data is additive.** Running the seeder repeatedly never duplicates data; every insert is a "create if not exists" operation. The seed also creates lab groups and enrollments so the my-group endpoints return data out of the box.

---
---

# M2: Billing Engine

The billing engine turns the delivered-session schedule into amounts owed, splits them between external and internal staff, and forwards a consolidated rollup to central accounting.

## Compensation Lives on the Person

Pay is decoupled from role: a person's role says what they can do, their compensation says how they are paid. Three fields sit on each user:

- **`compensation_type`**: `external` or `internal` (null for people who are not paid through billing, like students).
- **`hourly_rate`**: paid per delivered hour. Applies to both external and internal staff.
- **`monthly_salary`**: a fixed salary, paid only to internal staff.

Two payment shapes follow from this:

- **External instructor:** paid purely by the hour. `pay = delivered_hours × hourly_rate`.
- **Internal track admin:** paid a fixed salary plus an hourly component for any sessions they deliver. `pay = monthly_salary + (delivered_hours × hourly_rate)`. The salary is a person-level cost: it is counted **once**, even when the admin teaches across several cohorts.

> Source-of-truth note: the ERD places `compensation_type` on `users` and `hourly_rate` on `billing_records`, but names no rate or salary input column. Since the formulas need both, `hourly_rate` and `monthly_salary` were added to `users` as the supporting inputs, the same way the ERD diagram omits `password` and timestamps. The money fields (`compensation_type`, `hourly_rate`, `monthly_salary`) are hidden from API serialization so they never leak through other endpoints.

## Where Delivered Hours Come From

Hours are never typed in by hand. Each engagement produces sessions, and a session records whether it was delivered and for how many hours. The engine sums `delivered_hours` over delivered sessions (`is_delivered = true`), grouped per instructor per cohort. Undelivered sessions never count.

## Billing Records

A **billing record** is one person's hourly line for one cohort: their delivered hours, the rate applied, and the resulting hourly amount. There is one record per (person, cohort). The fixed salary is **not** stored on any per-cohort record (it is not a cohort cost); it is added once per person when the rollup is assembled.

A record carries a `status`:

- **`pending`**: computed, not yet sent to accounting.
- **`forwarded`**: handed to central accounting. A forwarded line is frozen.

## The Rollup

The rollup is the consolidated, branch-wide view the Branch Manager reads and forwards. It splits totals into **external** and **internal**:

- **external**: number of lines, total delivered hours, total amount (purely hourly).
- **internal**: number of lines, total delivered hours, hourly amount, salary amount (each internal person's salary counted once), and the combined total.
- **grand_total_amount**: external total plus internal total.

Billing follows the schedule, so a line is created for each person who actually delivered hours. An internal person with delivered hours has their fixed salary added once here; a purely-administrative admin who delivered nothing has no line.

## Authorization

Billing is a branch-level finance operation, so **every billing endpoint is Branch Manager only**.

## API Endpoints

```
POST   /billing/run        recompute billing lines from the delivered-session schedule.
                           Optional cohort_id rebuilds just that cohort. Refreshes
                           pending lines; leaves forwarded lines untouched.
GET    /billing/rollup     the consolidated internal/external split plus every line.
POST   /billing/forward    hand all pending lines to accounting (stamp them forwarded)
                           and return the payload that was sent.
```

## Key Billing Rules

1. **Hours come from the schedule.** Only delivered sessions count; undelivered ones are ignored.
2. **External is hourly, internal is salary plus hourly.** A person's fixed salary is counted once across all their cohorts, never per cohort.
3. **Billing follows the schedule.** A line is created for each person who delivered hours; someone who delivered nothing has no line.
4. **Forwarded lines are frozen.** Re-running `run` refreshes pending lines from the latest schedule but never reopens a line already sent to accounting, so the same money is never forwarded twice.
5. **Forwarding is all-or-nothing on pending.** `forward` sends the whole pending set at once, so each fixed salary reaches accounting exactly once.

## Operational Notes

- **Re-run after changing compensation.** The split and rates reflect the last `run`. If a person's `compensation_type`, `hourly_rate`, or `monthly_salary` changes, re-run before reading the rollup so the lines match.
- **Un-delivering a session.** If a previously delivered session is later marked undelivered, the engine does not retroactively zero a pending line until the next `run`; re-running rebuilds the active lines.
