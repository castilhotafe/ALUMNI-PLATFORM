## Goal

The goal of this document is to establish a shared set of standards for contributing to this project, ensuring consistency, quality, and maintainability across all changes, discussions, and contributions.

## Scope

This document covers:

- Branching strategy
- Commit message conventions
- Pull request requirements
- Code review rules
- Push policies
- Issue creation guidelines
- General coding and security standards

All contributors must follow these rules to ensure a consistent and reliable development process.

## Branching Strategy

This project follows a structured branching model to ensure clean collaboration, and safe integration of new changes.

### Main Branches

- **main**: Contains production-ready code.
  No direct commits or pushes are allowed.
- **dev**: Used for integrating features before creating a release.
  All new work must be merged here through Pull Requests.

### Working Branches

All work must be done in feature/ branches.

**Naming convention:**

- feature/<short-description>
- fix/<short-description>
- hotfix/<short-description>
- release/<version>

### Rules

- Merging into `main` or `dev` requires a Pull Request
- Minimum two approval.
- Force-push is **not allowed** on shared branches.
- Direct commits to `main` or `dev` are strictly prohibited.

## Conventional Commits

### Types

Changes relevant to the project:

- feat: that add, adjust or remove a feature
- fix: that fix an bug of a preceded feat commit
- refactor: that rewrite or restructure code without altering behavior
- perf: are special type of refactor commits that specifically improve performance
- style: that address code style and do not affect application behavior
- test: that add missing tests or correct existing ones
- docs: that exclusively affect documentation
- build: that affect build-related components such as build tools, dependencies, project version, ...

**Breaking Changes Indicator**
A commit that introduce breaking changes must be indicated by an ! before the :
e.g. **feat(api)!: remove status endpoint**
Breaking changes should be described in the commit footer section.

### Body

The body should include the motivation for the change and contrast this with previous behavior.
The body is an optional part
Use the imperative, present tense: "change" not "changed" nor "changes"

### Footer

The footer should contain issue references and informations about Breaking Changes

- The footer is an optional part, except if the commit introduce breaking changes
- Optionally reference issue identifiers (e.g., Closes 123)
- Breaking Changes must start with the word BREAKING CHANGE:
- For a single line description just add a space after BREAKING CHANGE:
- For a multi line description add two new lines after BREAKING CHANGE:

### Versioning

If your next release contains commit with...

- Breaking Changes incremented the major version
- API relevant changes (feat or fix) incremented the minor version
- Else increment the patch version

### Examples

feat: add email notifications on new direct messages
feat(shopping cart): add the amazing button
feat!: remove ticket list endpoint

BREAKING CHANGE: ticket endpoints no longer supports list all entities.
fix(shopping-cart): prevent order an empty shopping cart
fix(api): fix wrong calculation of request body checksum
fix: add missing parameter to service call

The error occurred due to <reasons>.
perf: decrease memory footprint for determine unique visitors by using HyperLogLog
build: update dependencies
build(release): bump version to 1.0.0
refactor: implement fibonacci number calculation as recursion
style: remove empty line

## Pull Request Requirements

### Pr Title

- Follow Conventional Commits
- Be clear and concise

### PR Description

Every PR must include a complete description containing the following sections:

**Summary**
A short explanation of what the PR changes and why.

**Motivation / Context**
Describe the reason behind the change and the problem it solves.

**Changes Included**
List the main changes introduced:

- New features
- Bug fixes
- Refactors
- UI or API adjustments
- Dependency updates
- Configuration changes

### Merge Rules

- Merges must be performed only through Pull Requests
- Direct merges into main or develop are strictly prohibited
- Force-push is not allowed on shared branches
- Squash & Merge is recommended for PRs with many small commits
- Merge Commit is acceptable when preserving detailed history is desired

### PR Size

To ensure efficient reviews:

- PRs must be small and focused
- Avoid mixing unrelated changes in a single PR
- Large PRs should be split into smaller, logical units

## Code Review

All code changes must be reviewed before merging.

**Reviewers should check for:**

- Code clarity and readability
- Test coverage
- Security concerns
- Consistency with project standards

A PR requires at least two approval before merging.

## Push Policies

- Direct pushes to main or dev are not allowed.
- Work must be done in feature branches.
- Always run tests and linting before pushing.
- Never push secrets, credentials, or sensitive data.
- Force-push is prohibited on shared branches.

## Issue Creation

## **Issues must include:**

name: Custom issue template
about: The purpose of this template is to streamline the process of creating an issue so the team follows a standard
title: ''
labels: ''
assignees: ''

---

---

title: "[Feature] Short Description"
labels: ""
assignees: ""

---

### Goal

Describe the objective of this issue.

Answer:

- What are we trying to achieve?
- Why is this work needed?

Example:
Set up the team's GitHub Project so development can be planned, tracked and managed consistently.

---

### User Story

Describe the value from the user's perspective.
Format:

As a <user>,
I want <goal>,
so that <benefit>.

Example:

As a team member,
I want a configured GitHub Project,
so that I can easily track my work and collaborate with the team.

---

### Scope

Describe what will be delivered as part of this issue.

List only the work included in this issue.

Example:

- Configure workflow statuses.
- Create project labels.
- Configure Board and Table views.

---

### Acceptance Criteria

Define the measurable conditions that must be satisfied before this issue can be closed.

Example:

- [ ] Workflow statuses have been configured.
- [ ] Project labels have been created.
- [ ] Team members can access the project.

---

### Notes (Optional)

Add any useful references, links or additional context.

Examples:

- Assessment documentation
- Client requirements
- Design mockups
- API documentation

## General Coding & Security Standards

- Follow project naming conventions and folder structure.
- Write clean, readable, and maintainable code.
- Avoid unnecessary complexity.
- Validate inputs and handle errors properly.
- Do not expose sensitive information in logs or code.
- Use environment variables for configuration.
- Follow best practices for security and performance.

## Initial Development Setup

Follow these steps to clone the repository and set up your local development environment.

### Prerequisites

Ensure your system meets the required environment versions for this project:

- **PHP**: `^8.5.2`
- **Laravel**: `^13.24.0`
- **Composer**: For managing PHP dependencies.
- **Node.js & npm**: For managing frontend assets

### 1. Clone the Repository

Clone the project from GitHub and move into the directory:

```bash
git clone https://github.com/castilhotafe/ALUMNI-PLATFORM.git
cd ALUMNI-PLATFORM
```

### 2. Install PHP Dependencies

If you have not already installed the required Laravel framework (`v13.24.0`) dependencies using Composer:

```bash
composer install
```

### 3. Environment Configuration

The project ignores the `.env` file for security. Create your local environment file by copying the example template:

```bash
cp .env.example .env
```

_Make sure to open `.env` and update the Mailpit credentials to match your local system configuration. The database configuration does not need to be updated as we are using the automatically generated database.sqlite for development._

### 4. Generate Application Key

Generate the unique application encryption key:

```bash
php artisan key:generate
```

### 5. Install Frontend Dependencies

Since Node and npm are not bundled with the repository, install your frontend packages and compile the assets locally:

```bash
npm install
npm run dev
```

### 6. Database Migrations

Set up your local database schema by running the migration files:

```bash
php artisan migrate:fresh
```

### 7. Mailpit Setup (Local Email Testing)

This application uses **Mailpit** to intercept and test outgoing emails locally.

1. Download and install Mailpit on your system (via Homebrew, Docker, or direct binary).
2. Start the Mailpit service on your machine.
3. Update your local `.env` file with the following configurations:
    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=127.0.0.1
    MAIL_PORT=1025
    ```
4. Access the Mailpit web dashboard at `http://localhost:8025` to view sent emails.
