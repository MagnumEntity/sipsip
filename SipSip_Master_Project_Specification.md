# SipSip --- Master Project Specification

## Implementation Specification for Antigravity

**Project:** SipSip --- Water Intake Tracking System\
**Document purpose:** Single authoritative implementation specification
combining the approved Systems Analysis and Design (SAD) requirements
and System Design Document (SDD).\
**Implementation status:** Ready for development\
**Primary instruction:** Implement the system described here faithfully.
Do not add unrelated features or change established behavior unless
explicitly approved.

------------------------------------------------------------------------

# 1. Project Overview

SipSip is a simple web-based water intake tracking system. It allows
registered users to record water consumption, set a personal daily
water-intake goal, monitor progress, review historical records, view
daily/weekly/monthly statistics, maintain streaks, and receive
context-aware reminders and hydration tips.

The system is intended for general users who want a convenient way to
monitor their water consumption. It is a water-intake tracker only and
is not a medical application.

The system must provide separate, secure data for each registered user.

------------------------------------------------------------------------

# 2. Technology Stack --- MUST USE

Use the following stack:

-   **Frontend:** HTML5, CSS3, vanilla JavaScript
-   **Backend:** PHP
-   **Database:** MySQL
-   **Local server/development environment:** XAMPP (Apache + MySQL +
    PHP)
-   **Code editor:** Visual Studio Code
-   **Version control:** Git + GitHub
-   **Development assistance:** Antigravity

Do not introduce a frontend framework, backend framework, or unrelated
database unless explicitly requested.

Do not use CakePHP or FuelPHP. This is a plain PHP application.

------------------------------------------------------------------------

# 3. Project Scope

## 3.1 Included

The system includes only:

1.  User registration
2.  Login/logout
3.  Session-based authentication
4.  Username and password management
5.  Account deletion
6.  Water intake recording
7.  Quick-add water buttons
8.  Custom water amount
9.  Water-entry editing
10. Water-entry deletion
11. Daily water-intake goal
12. Goal modification
13. Goal extension when exceeded
14. Dynamic water bottle progress display
15. Daily reset
16. Water-intake history
17. Daily/weekly/monthly statistics
18. Goal achievement tracking
19. Streak tracking
20. Context-aware dashboard notifications
21. Hydration tips
22. Settings
23. Responsive interface
24. User data separation
25. Database persistence

## 3.2 Explicitly Excluded

Do NOT implement:

-   Step tracking
-   Calories or food tracking
-   Exercise/workout tracking
-   Body-weight tracking
-   Body measurements
-   Sleep tracking
-   Medical conditions
-   Medical diagnosis
-   Medical advice
-   Other health metrics
-   Unrelated social features
-   Public user profiles
-   Chat/messaging
-   Admin dashboard
-   Multiple user roles
-   Payment systems
-   Unrelated gamification
-   Browser push notifications unless explicitly requested later

The current system has **one normal user type**. There is no user-facing
admin role or admin UI.

------------------------------------------------------------------------

# 4. Primary Users and Roles

## 4.1 Regular User

A regular user can:

-   Register
-   Log in
-   Log out
-   Configure initial settings
-   Record water
-   Edit water entries
-   Delete water entries
-   View daily progress
-   Change daily goal
-   Extend daily goal
-   View history
-   View statistics
-   View streak
-   Manage username/password
-   Change quick-add buttons
-   Change reset time
-   Show/hide notifications
-   Show/hide tips
-   Delete their account

## 4.2 Administrator

There is one administrator/developer account conceptually associated
with the developer, but:

-   Do not create an admin dashboard.
-   Do not create admin-specific UI.
-   Do not create administrator use cases in the current implementation.
-   Do not expose other users' records.

------------------------------------------------------------------------

# 5. Core User Flow

The intended high-level flow is:

1.  User opens SipSip.
2.  User registers or logs in.
3.  A newly registered user completes Initial Settings.
4.  User reaches Dashboard.
5.  User records water using quick-add or custom amount.
6.  System saves the entry with a timestamp.
7.  System recalculates total intake and progress.
8.  Bottle fill updates immediately.
9.  Notifications/tips update based on current progress.
10. User may edit/delete entries; all related totals recalculate
    immediately.
11. User can review History and Statistics.
12. At the configured reset time, a new tracking day begins while
    previous records remain permanently stored.
13. User can manage settings at any time.

------------------------------------------------------------------------

# 6. Initial Settings

After successful registration, the user must configure:

1.  Left quick-add amount
2.  Right quick-add amount
3.  Daily water goal
4.  Daily reset time

Defaults:

-   Quick-add button 1: **250 mL**
-   Quick-add button 2: **500 mL**
-   Daily goal: **2000 mL**
-   Reset time: **12:00 AM / midnight**
-   Notifications: **enabled**
-   Tips: **enabled**

After saving Initial Settings, send the user to the Dashboard.

Initial settings must be associated with the new user's account.

------------------------------------------------------------------------

# 7. Authentication and Account Management

## 7.1 Registration

Registration requires:

-   Username
-   Password

Process:

1.  User enters username/password.
2.  System validates input.
3.  System checks whether username already exists.
4.  If available, create account.
5.  Password must be securely hashed.
6.  Create default user settings.
7.  Send user to Initial Settings.

Do not store plaintext passwords.

## 7.2 Login

Login requires:

-   Username
-   Password

If credentials are valid:

-   Create a PHP session.
-   Maintain the authenticated state.
-   Send user to Dashboard.

If credentials are invalid, display the generic message:

**Invalid username or password**

Do not reveal whether the username or password was the incorrect part.

## 7.3 Logout

Destroy/invalidate the authenticated session and return the user to the
login page.

## 7.4 Change Username

Authenticated users may change their username.

The new username must satisfy normal username validation and must not
already belong to another account.

## 7.5 Change Password

Authenticated users may change their password.

The new password must be securely hashed before storage.

## 7.6 Delete Account

Allow authenticated users to delete their account.

Account deletion must also remove that user's associated:

-   Water intake records
-   User settings

Use database referential integrity/cascade deletion where appropriate.

Require confirmation before permanent deletion.

------------------------------------------------------------------------

# 8. Dashboard

The Dashboard is the primary SipSip screen.

## 8.1 Layout

The Dashboard should contain:

1.  Navigation/top bar
2.  Context-aware notifications at the top
3.  Progress text above the bottle
4.  Left quick-add button
5.  Dynamic water bottle in the center
6.  Right quick-add button
7.  Custom amount field below the bottle/buttons
8.  Hydration tips below the custom field
9.  Navigation to:
    -   Dashboard
    -   History
    -   Statistics
    -   Settings/Profile

Example progress text:

`500 mL / 2000 mL`

## 8.2 Dynamic Bottle

The bottle must visually show the user's current progress.

Bottle requirements:

-   Bottle graphic should be dark/gray so blue water fill is clearly
    visible.
-   Water fill is proportional to daily intake versus current daily
    goal.
-   Fill must visually cap at 100%.
-   Progress text can still show amounts over the goal.

Formula:

`progress percentage = current daily intake / current daily goal × 100`

Examples:

-   1000 / 2000 = 50%
-   1000 / 3000 = 33.3%
-   1000 / 1500 = 66.7%

If the goal changes, the actual intake does not change. Only the
percentage/bottle fill changes.

If goal increases, the same intake appears less full.

If goal decreases, the same intake appears more full.

------------------------------------------------------------------------

# 9. Water Intake Recording

## 9.1 Quick-Add Buttons

Default:

-   Button 1: 250 mL
-   Button 2: 500 mL

Users can change both values in Settings.

Clicking a quick-add button:

1.  Adds that amount to the current tracking day.
2.  Records the exact amount.
3.  Records a timestamp.
4.  Saves to database.
5.  Recalculates total.
6.  Recalculates progress.
7.  Updates bottle.
8.  Updates notifications/tips.
9.  Checks whether the goal has been reached/exceeded.

## 9.2 Custom Amount

The custom amount field accepts numbers only.

If invalid:

-   Do not save.
-   Do not change total.
-   Show a red validation error below the field.

Current requirement: there is no specified minimum or maximum custom
water-entry amount.

If custom amount is **greater than 500 mL**, require a confirmation
prompt before recording it.

Important:

-   Exactly **500 mL does NOT require confirmation**.
-   501 mL or higher DOES require confirmation.
-   The 500 mL threshold is for **water-entry confirmation** and must
    not be confused with the separate goal-extension threshold.

After confirmation, save the amount normally.

------------------------------------------------------------------------

# 10. Water Entry Editing

Users can edit existing entries.

When an entry is edited:

1.  Validate the new amount.
2.  Update the record.
3.  Recalculate current daily total.
4.  Recalculate progress.
5.  Update bottle.
6.  Update goal status.
7.  Update notifications/tips.
8.  Update history/statistics where applicable.

If the new amount is greater than 500 mL, use the same confirmation rule
as a custom new entry.

------------------------------------------------------------------------

# 11. Water Entry Deletion

Users can delete individual entries.

Deletion should require confirmation.

After deletion:

1.  Remove the entry.
2.  Recalculate current daily total.
3.  Recalculate progress.
4.  Update bottle.
5.  Update goal achievement status.
6.  Update notifications/tips.
7.  Update history/statistics.

If all entries for the current day are deleted:

-   Total becomes 0 mL.
-   Bottle becomes empty.
-   Progress becomes 0%.

------------------------------------------------------------------------

# 12. Daily Goal

Default daily goal:

**2000 mL**

Users can change the goal in Settings.

When the goal changes:

-   Save the new goal.
-   Keep actual water intake unchanged.
-   Recalculate progress immediately.
-   Update bottle immediately.
-   Update goal status immediately.

Example:

Current intake = 1000 mL.

If goal changes:

-   2000 → 50%
-   3000 → 33.3%
-   1500 → 66.7%

------------------------------------------------------------------------

# 13. Goal Reached

When current daily intake reaches or exceeds the current daily goal:

-   Show a congratulatory message.
-   Trigger a celebration animation.
-   Visually show the bottle as full/capped at 100%.
-   Continue allowing the user to record more water.

The user must NOT be blocked from recording additional water.

------------------------------------------------------------------------

# 14. Goal Extension

If a water entry causes the user to exceed the current daily goal:

1.  Show an over-goal notification.
2.  Ask whether the user wants to extend the goal.
3.  Provide:
    -   +250 mL
    -   +500 mL
    -   +1000 mL
    -   Custom amount
4.  Require confirmation before applying the extension.
5.  If the user confirms, increase the daily goal.
6.  Recalculate progress and bottle immediately.
7.  If the user declines, keep the current goal and continue allowing
    water entries.

Custom goal extension amounts greater than 1000 mL are allowed.

Important distinction:

-   **Water-entry confirmation:** required when a custom water amount is
    \>500 mL.
-   **Goal-extension custom amount:** \>1000 mL is allowed/handled as a
    custom extension amount.

Do not conflate these two rules.

------------------------------------------------------------------------

# 15. Daily Reset

A new tracking day starts at the user's configured reset time.

Default:

**12:00 AM / midnight**

The reset time is configurable in Settings.

At the configured reset time:

-   Current day's dashboard progress resets to zero.
-   Bottle becomes empty.
-   New tracking period begins.

Previous tracking days are NOT deleted.

All previous records remain permanently stored for:

-   History
-   Statistics
-   Goal achievement
-   Streak calculations

The implementation should determine a record's "tracking day" according
to the configured reset time.

Do not introduce a separate timezone feature unless explicitly
requested.

------------------------------------------------------------------------

# 16. History

History shows previous water-intake tracking periods/days.

For each relevant day, show:

-   Total water intake
-   Daily goal for that day
-   Whether the goal was reached

Historical records must remain available even after daily reset.

Users should be able to access previous records and, where appropriate,
edit/delete individual water entries.

If there are no records, show a clear empty-state message.

------------------------------------------------------------------------

# 17. Streak System

A streak represents consecutive tracking days where:

`daily intake >= that day's daily goal`

Rules:

-   Reaching the goal counts toward the streak.
-   Exceeding the goal does not harm the streak.
-   Missing the goal resets the current streak to 0.
-   A user's goal may differ across days; compare each day against that
    day's recorded goal.
-   Current streak should be displayed prominently in History or another
    suitable area.

Example:

`You're currently on a 5-day streak!`

A longest-streak feature is optional and is not required for the current
scope.

------------------------------------------------------------------------

# 18. Statistics

Statistics must provide separate:

-   Day
-   Week
-   Month

views.

## 18.1 Daily

Show relevant daily values such as:

-   Total intake
-   Daily goal
-   Goal achievement
-   Difference between intake and goal
-   Other approved daily calculations

## 18.2 Weekly

Show:

-   Weekly total
-   Weekly average
-   Goal achievement information
-   Recorded differences
-   Graph comparing the current week with past weeks

## 18.3 Monthly

Show:

-   Monthly total
-   Monthly average
-   Goal achievement information
-   Recorded differences
-   Graph comparing the current month with past months

Avoid medical or health-improvement claims.

Statistics must be calculated from stored water-intake records and the
applicable recorded goals/settings.

------------------------------------------------------------------------

# 19. Notifications and Tips

These are **in-app dashboard notifications/tips**, not browser push
notifications.

They are context-aware and per-user.

Examples:

## Far Behind

Display a reminder encouraging the user to drink/record water.

## 500 mL Away

Example:

`You're 500 mL away from your goal!`

## 100 mL Away

Example:

`You're almost there! 100 mL away!`

## Goal Reached

Display:

-   Congratulations
-   Celebration animation

## Over Goal

Display:

-   Over-goal message
-   Goal extension prompt

Tips should vary/contextually respond to progress rather than being a
single permanently displayed message.

Users can enable/disable:

-   Notifications
-   Tips

Defaults:

-   Notifications enabled
-   Tips enabled

Notifications/tips should be dismissible by clicking/tapping the
notification rectangle.

Dismissal should hide the displayed notification/tip without altering
water records.

------------------------------------------------------------------------

# 20. Settings

Settings must include:

## Water Settings

-   Daily goal
-   Quick-add button 1
-   Quick-add button 2
-   Reset time

## Notification Settings

-   Show/hide notifications
-   Show/hide tips

## Account Settings

-   Change username
-   Change password
-   Delete account

## Session

-   Logout

Changes must save to the authenticated user's settings only.

------------------------------------------------------------------------

# 21. Database Design

Database name:

**sipsip**

Three primary tables:

1.  `users`
2.  `water_intake_records`
3.  `user_settings`

------------------------------------------------------------------------

# 22. Users Table

Suggested schema:

  Column          Type           Constraints
  --------------- -------------- -----------------------------
  user_id         INT            Primary Key, auto-increment
  username        VARCHAR(50)    NOT NULL, UNIQUE
  password_hash   VARCHAR(255)   NOT NULL

Passwords must be stored using secure password hashing such as PHP's
`password_hash()`.

Never store plaintext passwords.

------------------------------------------------------------------------

# 23. Water Intake Records Table

Suggested schema:

  Column        Type       Constraints
  ------------- ---------- -----------------------------
  entry_id      INT        Primary Key, auto-increment
  user_id       INT        Foreign Key → users.user_id
  amount_ml     INT        NOT NULL
  recorded_at   DATETIME   NOT NULL

Every water record belongs to exactly one user.

Every record stores:

-   amount in mL
-   timestamp

Use a foreign key to enforce user ownership.

------------------------------------------------------------------------

# 24. User Settings Table

Suggested schema:

  Column                  Type      Constraints
  ----------------------- --------- -------------------------------------------
  user_id                 INT       Primary Key + Foreign Key → users.user_id
  daily_goal_ml           INT       NOT NULL
  button_1_ml             INT       NOT NULL
  button_2_ml             INT       NOT NULL
  reset_time              TIME      NOT NULL
  notifications_enabled   BOOLEAN   NOT NULL
  tips_enabled            BOOLEAN   NOT NULL

Defaults:

-   `daily_goal_ml = 2000`
-   `button_1_ml = 250`
-   `button_2_ml = 500`
-   `reset_time = 00:00:00`
-   `notifications_enabled = true`
-   `tips_enabled = true`

One settings row per user.

------------------------------------------------------------------------

# 25. Database Relationships

## Users → Water Intake Records

One-to-many:

`One user → many water entries`

## Users → User Settings

One-to-one:

`One user → one settings row`

Account deletion should cascade to the user's:

-   Water intake records
-   User settings

Do not allow one user to retrieve another user's records.

------------------------------------------------------------------------

# 26. Data Integrity

The system must maintain:

-   Unique user IDs
-   Unique usernames
-   Valid foreign keys
-   Valid positive water amounts
-   Valid timestamps
-   Exactly one settings row per user
-   Consistent CRUD behavior
-   Correct recalculation after edits/deletions
-   Correct user separation

Use parameterized/prepared SQL statements.

Prevent SQL injection.

------------------------------------------------------------------------

# 27. Core Calculations

## Daily Total

Sum all water entries belonging to the user's current tracking day.

Conceptually:

`daily_total = SUM(amount_ml) for current tracking day and authenticated user`

## Progress

`progress_percent = daily_total / daily_goal_ml × 100`

## Bottle Fill

`bottle_fill = min(progress_percent, 100)`

The visual bottle must never fill beyond 100%.

## Remaining Amount

`remaining = daily_goal_ml - daily_total`

If remaining is positive, use it for appropriate reminder messages.

If remaining is zero, goal is reached.

If remaining is negative, the user is over goal.

## Streak

For each tracking day:

`goal_reached = daily_total >= that day's goal`

Current streak is the number of consecutive most-recent tracking days
that reached their respective goals.

------------------------------------------------------------------------

# 28. Page Requirements

## 28.1 Login Page

Must contain:

-   SipSip branding/title
-   Username field
-   Password field
-   Log In button
-   Register link
-   Error message area

Invalid login:

`Invalid username or password`

## 28.2 Registration Page

Must contain:

-   Username field
-   Password field
-   Register button
-   Link back to login
-   Validation/error messages

## 28.3 Initial Settings Page

Must contain:

-   Hydration-themed visual/iconography
-   Quick-add button settings
-   Daily goal
-   Reset time
-   Save/Start button

Defaults must be clearly usable without additional configuration.

## 28.4 Dashboard

Must contain:

-   Navigation
-   Notifications
-   Progress text
-   Dynamic bottle
-   Quick-add buttons
-   Custom amount
-   Tips

## 28.5 History

Must contain:

-   Current streak
-   Historical daily records
-   Total intake
-   Goal
-   Goal status
-   Edit/delete controls where applicable
-   Empty state

## 28.6 Statistics

Must contain:

-   Day tab/view
-   Week tab/view
-   Month tab/view
-   Relevant totals
-   Averages
-   Goal achievement
-   Graphs/comparisons

## 28.7 Settings

Must contain:

-   Username management
-   Password management
-   Account deletion
-   Daily goal
-   Quick-add values
-   Reset time
-   Notification visibility
-   Tip visibility
-   Logout

------------------------------------------------------------------------

# 29. UI Design Principles

SipSip should be:

-   Simple
-   Clean
-   Easy to understand
-   Hydration-themed
-   Responsive
-   Readable
-   Consistent

Use clear visual feedback for:

-   Success
-   Errors
-   Confirmation
-   Goal reached
-   Over-goal state
-   Empty states

The water bottle should be the main visual element on the Dashboard.

Use readable labels and accessible controls.

Avoid unnecessary visual complexity.

------------------------------------------------------------------------

# 30. Responsive Design

The web application should work on:

-   Desktop
-   Mobile-sized screens

Use responsive HTML/CSS.

Do not require a separate native mobile application.

------------------------------------------------------------------------

# 31. Browser Compatibility

Target modern versions of:

-   Google Chrome
-   Mozilla Firefox
-   Safari
-   Brave

Internet access is required for deployed use.

The deployed system will use its configured domain.

------------------------------------------------------------------------

# 32. Accessibility

Include reasonable accessibility practices:

-   Semantic HTML
-   Labels for form controls
-   Keyboard-accessible controls
-   Readable contrast
-   Clear error messages
-   Clear confirmation dialogs
-   Responsive layouts
-   Do not communicate important information through color alone

------------------------------------------------------------------------

# 33. Validation

Validation must occur on both:

-   Client side where useful for user experience
-   Server side for security and correctness

Never rely only on JavaScript validation.

## Username

Validate according to the application's defined username rules and
uniqueness requirement.

## Password

Validate according to the application's password requirements.

Store only secure hashes.

## Water Amount

Must be numeric and valid.

Invalid values must not update the database.

## Goal

Must be a valid positive amount.

## Quick-Add Amounts

Must be valid positive numeric amounts.

## Reset Time

Must be a valid time value.

------------------------------------------------------------------------

# 34. Confirmation Rules

Require confirmation for:

1.  Custom water entry \>500 mL
2.  Editing an entry to a value \>500 mL
3.  Deleting a water entry
4.  Extending the daily goal
5.  Deleting an account

Exactly 500 mL does not trigger the \>500 mL water-entry confirmation.

------------------------------------------------------------------------

# 35. Error Handling

Errors must be understandable and must not expose sensitive
implementation details.

Examples:

-   Invalid username or password
-   Username already exists
-   Invalid water amount
-   Invalid goal
-   Invalid settings
-   Database operation failed
-   Unauthorized access
-   Record not found

Do not expose:

-   SQL queries
-   Database credentials
-   Stack traces to normal users
-   Internal filesystem details
-   Password hashes

------------------------------------------------------------------------

# 36. Authentication and Authorization Security

All pages/actions containing user data must verify authentication.

A user must only be able to access records where:

`record.user_id == authenticated_user_id`

Do not trust a user-supplied user ID.

Always derive the authenticated user identity from the server-side
session.

Protect actions such as:

-   Add water
-   Edit water
-   Delete water
-   Change goal
-   Change settings
-   Delete account

against unauthorized access.

------------------------------------------------------------------------

# 37. Password Security

Use PHP password hashing:

-   `password_hash()`
-   `password_verify()`

Do not encrypt passwords as a substitute for hashing.

Do not store plaintext passwords.

Do not place passwords or secrets in Git.

------------------------------------------------------------------------

# 38. SQL Security

Use prepared/parameterized queries.

Never construct SQL queries by directly concatenating raw user input.

Validate and sanitize user input as appropriate.

------------------------------------------------------------------------

# 39. Session Security

Use PHP sessions.

On successful login:

-   Establish the authenticated session.

On logout:

-   Destroy/invalidate the session.

Protected pages must redirect unauthenticated users to login.

------------------------------------------------------------------------

# 40. Suggested Project Structure

The following structure is the preferred starting organization:

``` text
sipsip/
│
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── history.php
├── statistics.php
├── settings.php
│
├── config/
│   └── database.php
│
├── includes/
│   ├── auth.php
│   ├── header.php
│   ├── footer.php
│   └── functions.php
│
├── actions/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── add_water.php
│   ├── edit_water.php
│   ├── delete_water.php
│   ├── update_goal.php
│   ├── update_settings.php
│   └── delete_account.php
│
├── css/
│   └── style.css
│
├── js/
│   └── script.js
│
└── assets/
    └── images/
```

This structure may be adjusted if a cleaner implementation is necessary,
but functionality and security requirements must remain unchanged.

------------------------------------------------------------------------

# 41. Backend Organization

Suggested responsibilities:

## `config/database.php`

-   MySQL connection
-   Database configuration
-   No hardcoded production secrets

## `includes/auth.php`

-   Session setup
-   Authentication checks
-   Current-user identification

## `includes/functions.php`

Reusable functions such as:

-   Current tracking day calculation
-   Daily total calculation
-   Progress calculation
-   Goal status
-   Streak calculation
-   Validation helpers
-   Notification/tip logic

## `includes/header.php`

Reusable navigation/header markup.

## `includes/footer.php`

Reusable footer markup/scripts.

## `actions/`

Server-side form/action handlers.

Keep database operations and request processing organized.

------------------------------------------------------------------------

# 42. Git and GitHub Requirements

GitHub repository must remain the source-control repository.

Use descriptive commits.

Example:

``` bash
git add .
git commit -m "Add authentication system"
git push
```

Recommended milestone commits:

1.  Initialize project
2.  Add database connection
3.  Add authentication
4.  Add initial settings
5.  Add dashboard
6.  Add water recording
7.  Add goal/progress system
8.  Add history
9.  Add statistics
10. Add settings
11. Add notifications
12. Add validation/security
13. Complete testing
14. Final cleanup

Do not commit:

-   Passwords
-   API keys
-   Database credentials
-   `.env` files containing secrets
-   Sensitive local configuration

The `.gitignore` should exclude secrets, IDE/local files, logs,
temporary files, and other environment-specific files.

------------------------------------------------------------------------

# 43. Antigravity Implementation Rules

Antigravity must:

1.  Follow this specification as the primary implementation reference.
2.  Preserve the established database design.
3.  Preserve the established formulas.
4.  Preserve user-data separation.
5.  Preserve validation/confirmation rules.
6.  Preserve the defined UI behavior.
7.  Use the specified technology stack.
8.  Avoid adding unrelated features.
9.  Avoid introducing frameworks unless explicitly approved.
10. Keep frontend and backend validation.
11. Test changes after implementation.
12. Keep code organized and maintainable.
13. Use Git appropriately.
14. Avoid committing secrets.
15. Review this specification before making major architectural changes.

If a requirement appears ambiguous or contradictory, do not silently
invent a new behavior. Identify the ambiguity and ask for clarification
before making a major change.

Small implementation details may be chosen by Antigravity when they do
not change the approved behavior.

------------------------------------------------------------------------

# 44. Recommended Implementation Order

Implement incrementally in this order:

## Phase 1 --- Foundation

-   Verify project structure
-   PHP/XAMPP setup
-   MySQL database
-   Database connection
-   Basic shared includes

## Phase 2 --- Authentication

-   Registration
-   Password hashing
-   Login
-   Sessions
-   Logout
-   Protected pages
-   User separation

## Phase 3 --- Initial Settings

-   Default settings
-   Initial setup page
-   Save settings

## Phase 4 --- Dashboard

-   Layout
-   Navigation
-   Progress text
-   Bottle UI
-   Quick-add buttons
-   Custom input

## Phase 5 --- Water Recording

-   Add records

-   Validation

-   500 mL confirmation

-   Timestamps

-   Edit

-   Delete

-   Recalculation

## Phase 6 --- Goal and Progress

-   Daily goal
-   Goal changes
-   Bottle calculations
-   Goal reached state
-   Celebration
-   Goal extension

## Phase 7 --- Daily Reset

-   Configurable reset time
-   Tracking-day calculation
-   Historical preservation

## Phase 8 --- History and Streak

-   Historical records
-   Goal status
-   Current streak
-   Edit/delete

## Phase 9 --- Statistics

-   Day
-   Week
-   Month
-   Totals
-   Averages
-   Goal achievement
-   Graphs/comparisons

## Phase 10 --- Settings

-   Goal
-   Buttons
-   Reset time
-   Notifications
-   Tips
-   Username
-   Password
-   Delete account
-   Logout

## Phase 11 --- Notifications/Tips

-   Context-aware logic
-   Dismissal
-   Visibility settings
-   Goal/over-goal messages

## Phase 12 --- Security and Refinement

-   Authorization review
-   SQL injection protection
-   Validation review
-   Session review
-   Error handling
-   Responsive design
-   Accessibility
-   Browser testing

## Phase 13 --- Final Testing

Test all requirements and edge cases before finalizing.

------------------------------------------------------------------------

# 45. Testing Requirements

Test at minimum:

## Authentication

-   Successful registration
-   Duplicate username
-   Invalid login
-   Successful login
-   Logout
-   Protected-page access
-   Password change
-   Username change
-   Account deletion

## Water

-   250 mL quick-add
-   500 mL quick-add
-   Custom amount
-   Invalid amount
-   Exactly 500 mL
-   More than 500 mL
-   Edit
-   Delete
-   Delete all current-day records

## Goal

-   Default 2000 mL
-   Change goal
-   Goal reached exactly
-   Goal exceeded
-   Goal extension
-   Declining extension
-   Custom extension

## Bottle

Test:

-   0%
-   25%
-   33.3%
-   50%
-   66.7%
-   100%
-   Over 100% visually capped

## Daily Reset

-   Default midnight
-   Custom reset time
-   New tracking day
-   Previous-day history preservation

## Streak

-   First successful day
-   Consecutive successful days
-   Missed goal
-   Over-goal day
-   Different daily goals

## Statistics

-   Daily totals
-   Weekly totals
-   Monthly totals
-   Averages
-   Goal achievement
-   Graph comparisons

## User Separation

Create at least two test users and verify:

-   User A cannot see User B's entries.
-   User B cannot see User A's entries.
-   User settings remain separate.

## Security

-   SQL injection attempts
-   Unauthorized action requests
-   Direct access to protected pages
-   Password storage
-   Session behavior
-   Account deletion cascade

------------------------------------------------------------------------

# 46. System Architecture

The system follows a simple client-server architecture:

``` text
User Browser
     │
     ▼
PHP Web Application
     │
     ├── Authentication
     ├── Water Intake Management
     ├── Goal & Progress
     ├── History
     ├── Statistics
     ├── Notifications/Tips
     └── Settings
     │
     ▼
MySQL Database
```

The browser provides the interface.

PHP handles server-side logic, authentication, validation, database
operations, and business rules.

MySQL stores persistent user and water-intake data.

------------------------------------------------------------------------

# 47. Data Flow Summary

## Account Management

User → Registration/Login → PHP Authentication → Users table → Session →
Dashboard

## Water Recording

User → Quick Add/Custom Amount → Validation → PHP → Water Intake Records
→ Recalculate → Dashboard

## Goal

User → Settings/Goal Extension → Validation/Confirmation → User Settings
→ Recalculate Progress

## History

Water Intake Records → Tracking-Day Calculation → Historical Records →
History UI

## Statistics

Water Intake Records + Goals → Calculations → Daily/Weekly/Monthly
Statistics → Statistics UI

## Notifications

Current Intake + Current Goal + Settings → Notification Logic →
Dashboard Notification

------------------------------------------------------------------------

# 48. Functional Requirements Traceability

The implementation must satisfy these core functional requirements:

  ID      Requirement
  ------- -------------------------------------
  FR-01   Register account
  FR-02   Log in
  FR-03   Log out
  FR-04   Configure initial settings
  FR-05   Record water with quick-add buttons
  FR-06   Record custom water amount
  FR-07   Validate water input
  FR-08   Edit water entries
  FR-09   Delete water entries
  FR-10   Set/change daily goal
  FR-11   Display progress
  FR-12   Display dynamic bottle
  FR-13   Extend goal
  FR-14   Perform configurable daily reset
  FR-15   Preserve history
  FR-16   Calculate streak
  FR-17   Display daily statistics
  FR-18   Display weekly statistics
  FR-19   Display monthly statistics
  FR-20   Display notifications
  FR-21   Display tips
  FR-22   Manage username
  FR-23   Manage password
  FR-24   Delete account
  FR-25   Configure quick-add buttons
  FR-26   Configure reset time
  FR-27   Show/hide notifications
  FR-28   Show/hide tips
  FR-29   Keep user records separated
  FR-30   Securely store passwords

------------------------------------------------------------------------

# 49. Non-Functional Requirements

## Performance

Pages and normal interactions should respond quickly under normal
local/deployment conditions.

## Security

-   Password hashing
-   Sessions
-   Authorization
-   Prepared statements
-   Server-side validation
-   User-data isolation
-   No secrets in Git

## Usability

-   Simple interface
-   Clear navigation
-   Clear feedback
-   Easy water entry
-   Understandable progress

## Compatibility

Support modern Chrome, Firefox, Safari, and Brave.

## Accessibility

Use semantic HTML, labels, readable controls, keyboard accessibility,
and clear feedback.

## Data Integrity

Ensure that create/read/update/delete operations keep totals, goals,
history, and statistics consistent.

------------------------------------------------------------------------

# 50. Important Edge Cases

The implementation must handle:

1.  No water entries yet.
2.  All current-day entries deleted.
3.  Exactly reaching the goal.
4.  Going over the goal.
5.  Continuing to record after reaching the goal.
6.  Changing goal after recording water.
7.  Changing goal to a value below current intake.
8.  Changing quick-add values.
9.  Custom amount exactly 500 mL.
10. Custom amount above 500 mL.
11. Invalid custom amount.
12. Goal extension declined.
13. Goal extension accepted.
14. Goal extension custom amount greater than 1000 mL.
15. Daily reset while historical records exist.
16. Consecutive goal-reaching days.
17. Missed goal breaking streak.
18. Two users using the system.
19. User attempting to access another user's record.
20. Account deletion.
21. Empty History.
22. Empty Statistics.
23. Invalid or expired session.
24. Database failure.
25. Duplicate username.

------------------------------------------------------------------------

# 51. Important Behavioral Rules --- Quick Reference

These rules are especially important and must not be changed
accidentally.

### Water

-   Default quick-add: **250 mL + 500 mL**

-   Custom amounts are numeric.

-   500 mL custom water entry requires confirmation.

-   Exactly 500 mL does not.

-   Every entry has a timestamp.

-   Entries can be edited/deleted.

### Goal

-   Default: **2000 mL**
-   Goal can be changed.
-   Changing goal does not change actual intake.
-   Reaching goal triggers celebration.
-   User can continue recording after goal.
-   Exceeding goal offers extension.
-   Extension options: +250, +500, +1000, custom.
-   Extension requires confirmation.
-   Custom extension \>1000 mL is allowed.

### Bottle

-   Fill = intake / goal × 100.
-   Visual fill capped at 100%.
-   Progress text can exceed goal.

### Reset

-   Default: midnight.
-   User can change reset time.
-   Reset starts a new tracking day.
-   Previous days remain stored.

### Streak

-   A day counts when intake \>= that day's goal.
-   Going over goal is fine.
-   Missing goal resets current streak.

### Notifications

-   In-app only.
-   Context-aware.
-   User can disable them.
-   User can dismiss them.

### Users

-   Username + password.
-   Passwords securely hashed.
-   Sessions used after login.
-   Users cannot see each other's data.
-   Account deletion removes associated records/settings.

------------------------------------------------------------------------

# 52. Do Not Implement These Unless Explicitly Approved

Antigravity must not independently add:

-   React
-   Vue
-   Angular
-   Laravel
-   CakePHP
-   FuelPHP
-   Node.js backend
-   Firebase
-   MongoDB
-   External authentication services
-   Admin dashboards
-   Social profiles
-   Friends/followers
-   Chat
-   Leaderboards
-   Calories
-   Food tracking
-   Exercise tracking
-   Steps
-   Sleep
-   Weight
-   Medical features
-   Push notifications
-   Paid subscriptions
-   Unrelated APIs
-   AI-generated health advice
-   Any unrelated feature that expands project scope

If an additional dependency or architecture change appears necessary,
ask for approval first.

------------------------------------------------------------------------

# 53. Implementation Philosophy

Keep SipSip simple.

The goal is a functional school/practice web application, not an
unnecessarily complex commercial platform.

Prioritize:

1.  Correctness
2.  Security
3.  Requirement compliance
4.  Maintainability
5.  Usability
6.  Responsive design

Do not over-engineer the application.

Do not replace established requirements with assumptions.

------------------------------------------------------------------------

# 54. Final Acceptance Criteria

SipSip is considered ready when:

-   Users can register and log in.

-   Passwords are securely hashed.

-   User sessions work.

-   User records are isolated.

-   Initial settings work.

-   Quick-add buttons work.

-   Custom water entry works.

-   500 mL confirmation works.

-   Entries can be edited/deleted.

-   Daily total is correct.

-   Daily goal works.

-   Bottle progress is correct.

-   Goal celebration works.

-   Goal extension works.

-   Daily reset works.

-   History persists.

-   Streak works.

-   Daily/weekly/monthly statistics work.

-   Notifications/tips work and can be disabled.

-   Settings work.

-   Account deletion works.

-   Database integrity is maintained.

-   SQL injection protections are implemented.

-   Protected actions require authentication.

-   Responsive layout works.

-   Major edge cases are tested.

-   Code is committed to Git.

-   No secrets are committed.

------------------------------------------------------------------------

# 55. Final Instruction to Antigravity

**Build SipSip according to this document.**

Treat this specification as the authoritative implementation reference
for the current project scope.

Implement incrementally and keep the code understandable.

Do not silently change requirements.

Do not add unrelated features.

Do not replace the specified technology stack with a framework or
alternative architecture.

Before major architectural changes, stop and request approval.

After each major implementation milestone, verify that the existing
functionality still works and that user data remains isolated.

The finished application must implement the requirements, behavior,
calculations, validation, security, UI structure, and database design
described above.
