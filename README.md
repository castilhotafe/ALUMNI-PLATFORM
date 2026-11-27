<div align="center">
  <h1>Alumni Platform</h1>

  <p><strong>Connect with people, share your work, and find what’s next.</strong></p>
  <p>A community platform for North Metropolitan TAFE students, alumni, and industry partners.</p>

  <p>
    <a href="https://www.php.net/"><img alt="PHP 8.5" src="https://img.shields.io/badge/PHP-8.5-555C91?style=flat-square&amp;logo=php&amp;logoColor=white"></a>
    <a href="https://laravel.com/docs/13.x"><img alt="Laravel 13" src="https://img.shields.io/badge/Laravel-13-C7252E?style=flat-square&amp;logo=laravel&amp;logoColor=white"></a>
    <a href="https://laravel.com/docs/13.x/sanctum"><img alt="Laravel Sanctum 4" src="https://img.shields.io/badge/Sanctum-4-8F1723?style=flat-square&amp;logo=laravel&amp;logoColor=white"></a>
    <a href="https://getbootstrap.com/docs/5.3/getting-started/introduction/"><img alt="Bootstrap 5" src="https://img.shields.io/badge/Bootstrap-5-6531A9?style=flat-square&amp;logo=bootstrap&amp;logoColor=white"></a>
    <a href="https://alpinejs.dev/"><img alt="Alpine.js 3" src="https://img.shields.io/badge/Alpine.js-3-255D66?style=flat-square&amp;logo=alpinedotjs&amp;logoColor=white"></a>
    <a href="https://vite.dev/"><img alt="Vite 8" src="https://img.shields.io/badge/Vite-8-5445B8?style=flat-square&amp;logo=vite&amp;logoColor=white"></a>
  </p>

  <p>
    <strong><a href="https://alumni.screencraft.net.au/">Open the live platform</a></strong>
  </p>

  <p>
    <a href="#demo">Demo</a> ·
    <a href="#features">Features</a> ·
    <a href="#tech-stack">Tech stack</a> ·
    <a href="#getting-started">Getting started</a> ·
    <a href="#contributing">Contributing</a>
  </p>
</div>

## Demo

https://github.com/user-attachments/assets/cfc853b7-1159-460f-8aa0-279529b9ab78

The video follows a student finding collaborators, an alumnus showcasing a project, and an industry partner sharing an opportunity. It uses fictional profiles and listings.

[![Brag demo production](https://img.shields.io/badge/Brag-Demo%20production-24292F?style=flat-square&logo=github&logoColor=white)](https://github.com/latent-spaces/brag)
[![HyperFrames video rendering](https://img.shields.io/badge/HyperFrames-Video%20rendering-C7252E?style=flat-square)](https://github.com/heygen-com/hyperframes)

The video was made with Brag, rendered locally with HyperFrames, and mastered with FFmpeg. [Brag](https://github.com/latent-spaces/brag) · [HyperFrames](https://github.com/heygen-com/hyperframes) · [FFmpeg](https://ffmpeg.org/)

## Features

| Feature | What it gives the community |
| --- | --- |
| **Developer Directory** | Find people by name or skill and explore their profiles. |
| **Networking Hub** | Discover groups and see their members, projects, and research. |
| **Projects and Research Papers** | Showcase projects, credit collaborators, and share publications. |
| **Me** | Update your profile and manage your posts, projects, and papers. |
| **Discover Hub** | Explore jobs and learning opportunities. Eligible industry partners and lecturers can publish listings, along with administrators. |
| **Contribution points** | Publishing content such as projects and research can earn points. Administrators review those entries in the contribution ledger. |

## Tech stack

| Layer | Tools |
| --- | --- |
| Application | [PHP](https://www.php.net/) · [Laravel 13](https://laravel.com/docs/13.x) · [Blade](https://laravel.com/docs/13.x/blade) |
| Authentication and permissions | [Laravel Sanctum](https://laravel.com/docs/13.x/sanctum) for API tokens · [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) for roles and permissions |
| Frontend | [Bootstrap 5](https://getbootstrap.com/docs/5.3/getting-started/introduction/) · [Alpine.js](https://alpinejs.dev/) · [Sass](https://sass-lang.com/) · [Vite](https://vite.dev/) |
| Local data | [SQLite](https://www.sqlite.org/) · Laravel migrations and seeders |
| Tests and automation | [Pest](https://pestphp.com/) · [Laravel Pint](https://laravel.com/docs/13.x/pint) · [GitHub Actions](.github/workflows/ci.yml) |

The web app uses verified accounts and enforces visibility for private projects and research. Contribution events record points in the ledger. For the implementation, see the [web routes](routes/web.php), [API routes](routes/api.php), and [contribution rules](config/contributions.php).

## Getting started

Use **PHP 8.5** with PDO SQLite, **Composer**, and **Node.js 22.12 or later** with npm. These steps are for a fresh local clone using SQLite.

```bash
git clone https://github.com/castilhotafe/ALUMNI-PLATFORM.git
cd ALUMNI-PLATFORM

touch database/database.sqlite
composer setup
php artisan db:seed
composer dev
```

Open [localhost:8000](http://localhost:8000). The setup script installs dependencies, creates the local configuration and application key, runs migrations, and builds assets. The seed command adds sample community content. The development command starts Laravel, the queue listener, and Vite.

- To initialise roles and permissions without the sample community content, use `php artisan db:seed --class=RolePermissionSeeder` in place of `php artisan db:seed`.
- For local verification and password reset emails, start [Mailpit](https://mailpit.axllent.org/) with SMTP on `127.0.0.1:1025`. Open the mailbox at [localhost:8025](http://localhost:8025).

## Testing

```bash
php artisan test
XDEBUG_MODE=coverage composer test
```

The first command runs the test suite. The second also runs coverage and the Pint style check and requires Xdebug with coverage enabled. The tests use in-memory SQLite. The [CI workflow](.github/workflows/ci.yml) builds frontend assets and runs the Composer checks on pushes and pull requests.

## Repository map

| Path | Where to look |
| --- | --- |
| [`app/`](app/) | Models, controllers, policies, and event listeners |
| [`resources/`](resources/) | Blade views, Sass, and frontend behaviour |
| [`routes/`](routes/) | Web and API entry points |
| [`database/`](database/) | Schema migrations and seeders |
| [`tests/`](tests/) | Feature and unit tests |
| [`docs/`](docs/) | API notes and demo assets |

## Contributing

The team uses GitHub Projects to organise issues. Open a focused pull request from a feature branch into `dev`. The project’s review process requires two approvals before merge. [Contribution guide](Contributing.md) · [Issues](https://github.com/castilhotafe/ALUMNI-PLATFORM/issues) · [Pull requests](https://github.com/castilhotafe/ALUMNI-PLATFORM/pulls)

## License

This repository is licensed under the [MIT License](LICENSE).
