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
- **develop**: Used for integrating features before creating a release.
    All new work must be merged here through Pull Requests.

### Working Branches
All work must be done in feature/ branches.

**Naming convention:**

- feature/<short-description>
- fix/<short-description>
- hotfix/<short-description>
- release/<version>

### Rules
- Merging into `develop` or `main` requires a Pull Request
- Minimum two approval.
- Force-push is **not allowed** on shared branches.
- Direct commits to `main` or `develop` are strictly prohibited.


## Conventional Commits

### Types
Changes relevant to the project, API or UI:

- feat Commits that add, adjust or remove a feature to/of/from the API or UI
- fix Commits that fix an API or UI bug of a preceded feat commit
- refactor Commits that rewrite or restructure code without altering API or UI behavior
- perf Commits are special type of refactor commits that specifically improve performance
- style Commits that address code style (e.g., white-space, formatting, missing semi-colons) and do not affect application behavior
- test Commits that add missing tests or correct existing ones
- docs Commits that exclusively affect documentation
- build Commits that affect build-related components such as build tools, dependencies, project version, ...

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
- Optionally reference issue identifiers (e.g., Closes #123, Fixes JIRA-456)
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

## Code Review (Short Version)
All code changes must be reviewed before merging.

**Reviewers should check for:**
- Code clarity and readability
- Test coverage
- Security concerns
- Consistency with project standards

A PR requires at least two approval before merging.

## Push Policies (Short Version)
- Direct pushes to main or develop are not allowed.
- Work must be done in feature branches.
- Always run tests and linting before pushing.
- Never push secrets, credentials, or sensitive data.
- Force-push is prohibited on shared branches.

## Issue Creation (Short Version)

**Issues must include:**
- A clear title
- A concise description of the problem or request
- Expected vs actual behavior
- Relevant logs, screenshots, or context(If it is necesary)
- Use labels to categorize issues (bug, enhancement, docs).

## General Coding & Security Standards (Short Version)

All contributors must use the project’s defined runtime and framework versions to ensure consistency and compatibility:

- Laravel **13.24.0**
- PHP **8.5.2**
- Follow project naming conventions and folder structure.
- Write clean, readable, and maintainable code.
- Avoid unnecessary complexity.
- Validate inputs and handle errors properly.
- Do not expose sensitive information in logs or code.
- Use environment variables for configuration.
- Follow best practices for security and performance.
