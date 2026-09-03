# Repository Rules

This document defines the repository rules used by the ALUMNI-PLATFORM project.

The purpose of these rules is to protect the project, keep the codebase stable,
and make sure changes are reviewed before they are merged.

---

## 1. Branch Structure

The repository uses the following main branches:

### main`

The `main` branch contains stable and approved code.

-   Direct pushes to `main` are not allowed.
-   Changes must reach `main` through a Pull Request.
-   Pull Requests must be reviewed before merging.
-   `main` should always remain in a working state.

### `dev`

The `dev` branch is used as the main development branch.

-   New development work should normally begin from `dev`.
-   Feature and issue branches should be merged into `dev` through Pull Requests.
-   Once development has been reviewed and tested, approved changes can be merged
    from `dev` into `main`.

---

## 2. Feature and Issue Branches

Developers should not work directly on `main`.

A new branch should be created for each Issue or task.

Examples:

```text
feature/alumni-profile
feature/job-opportunities-board
```

## 3. Pull Request Requirements

All changes intended for the `main` branch must be submitted through a Pull Request.

Pull Requests must meet the following requirements:

-   At least two approvals are required before merging.
-   Review conversations must be resolved before merging.
-   If new commits are pushed after approval, previous approvals may need to be reviewed again.
-   Required automated checks must pass before merging.
-   Pull Requests should clearly describe the changes made.
-   Pull Requests should reference the related Issue when applicable.

Example:

```text
Closes #7


fix/login-validation
docs/repository-rules
```
