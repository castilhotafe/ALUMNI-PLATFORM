<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b10f19">

    <title>{{ config('app.name', 'NMTAFE Alumni') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])

</head>

<body class="alumni-home">
    <nav class="alumni-nav py-3 w-100">
        <div class="container-fluid px-3 px-lg-5 d-flex align-items-center justify-content-between gap-3">
            <a class="d-inline-flex align-items-center gap-3 text-decoration-none text-dark" href="{{ url('/') }}">
                <img src="{{ asset('branding/nmtafe-logo-white.svg') }}" alt="North Metropolitan TAFE"
                    class="alumni-logo bg-dark rounded-3 p-2">
                <div class="d-none d-sm-block">
                    <div class="fw-bold text-uppercase small text-danger">Alumni App</div>
                    <div class="text-secondary small">North Metropolitan TAFE</div>
                </div>
            </a>

            <div class="d-flex align-items-center gap-2 gap-sm-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-dark rounded-pill px-4 fw-semibold">Go to
                        dashboard</a>
                @endauth

                @guest
                    {{-- Stub routes for login and register until views are done --}}
                    <a href="" class="btn btn-outline-dark rounded-pill px-4 fw-semibold">Log in</a>
                    <a href="" class="btn btn-danger rounded-pill px-4 fw-semibold">Register</a>
                @endguest
            </div>
        </div>
    </nav>

    <main class="container-fluid px-3 px-lg-5 py-4 py-lg-5">
        <section class="hero-panel rounded-5 p-4 p-lg-5 mb-4 mb-lg-5">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-12 col-lg-7">
                    <div class="hero-badge mb-4">Alumni network</div>
                    <h1 class="hero-title fw-bold mb-4">Keep your NMTAFE story moving forward.</h1>
                    <p class="hero-copy mb-4 mb-lg-5">
                        The Alumni app helps graduates stay visible, reconnect with peers, and discover what comes
                        next.
                        Build your profile, share projects, follow research, and find opportunities from the same
                        trusted TAFE community.
                    </p>

                    <div class="d-flex flex-wrap gap-3 mb-4 mb-lg-5">
                        @guest
                            <a href="" class="btn btn-danger btn-lg rounded-pill px-4 fw-semibold">Create your
                                account</a>
                            <a href="" class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-semibold">Already have
                                an
                                account?</a>
                        @endguest

                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="btn btn-danger btn-lg rounded-pill px-4 fw-semibold">Open your dashboard</a>
                        @endauth
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="soft-pill"><strong class="me-2">Profiles</strong> alumni details, skills, and
                            study history</span>
                        <span class="soft-pill"><strong class="me-2">Network</strong> messages, groups, and
                            conversations</span>
                        <span class="soft-pill"><strong class="me-2">Opportunities</strong> jobs, enrolments, and
                            discovery</span>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="stats-card rounded-5 p-4 p-lg-5 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <div class="text-uppercase small opacity-75 fw-semibold">What you can do</div>
                                <div class="h4 mb-0 text-white">Built for alumni momentum</div>
                            </div>
                            <span class="badge rounded-pill text-bg-light text-dark px-3 py-2">TAFE feel</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="rounded-4 bg-white bg-opacity-10 p-3 h-100">
                                    <div class="stat-value">01</div>
                                    <div class="small opacity-75">Stay connected to classmates and the wider NMTAFE
                                        community.</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="rounded-4 bg-white bg-opacity-10 p-3 h-100">
                                    <div class="stat-value">02</div>
                                    <div class="small opacity-75">Showcase projects, research, and achievements with
                                        confidence.</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-4 bg-white bg-opacity-10 p-3 p-lg-4 mb-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-chip bg-white bg-opacity-25 text-white flex-shrink-0"><i
                                        class="fa-solid fa-comments"></i></div>
                                <div>
                                    <div class="fw-semibold text-white">Keep conversations going</div>
                                    <div class="small opacity-75">Message alumni, join groups, and keep professional
                                        relationships active.</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-4 bg-white bg-opacity-10 p-3 p-lg-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-chip bg-white bg-opacity-25 text-white flex-shrink-0"><i
                                        class="fa-solid fa-briefcase"></i></div>
                                <div>
                                    <div class="fw-semibold text-white">Find next steps</div>
                                    <div class="small opacity-75">Browse jobs, return-to-study options, and
                                        opportunities shared by the community.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-4 mb-lg-5">
            <div class="row align-items-end g-3 mb-3 mb-lg-4">
                <div class="col-12 col-lg-8">
                    <div class="section-kicker mb-2">Why it matters</div>
                    <h2 class="display-6 fw-bold mb-0">A clear, TAFE-inspired space for alumni to return to.</h2>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <p class="mb-0 text-secondary">Everything on the landing page leads directly into the Alumni
                        app, with no extra friction.</p>
                </div>
            </div>

            <div class="feature-grid row g-3 g-lg-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="fa-solid fa-user-graduate"></i></div>
                            <h3 class="h5 fw-bold">Show your journey</h3>
                            <p class="mb-0 text-secondary">Build a profile that reflects your study history, work
                                experience, and alumni identity.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="fa-solid fa-network-wired"></i></div>
                            <h3 class="h5 fw-bold">Reconnect quickly</h3>
                            <p class="mb-0 text-secondary">Message alumni, follow conversations, and keep your
                                NMTAFE network warm.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="fa-solid fa-compass"></i></div>
                            <h3 class="h5 fw-bold">Explore opportunities</h3>
                            <p class="mb-0 text-secondary">Discover jobs, projects, research, and study pathways
                                without leaving the community.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="fa-solid fa-shield-halved"></i></div>
                            <h3 class="h5 fw-bold">Stay within the brand</h3>
                            <p class="mb-0 text-secondary">The app keeps the North Metropolitan TAFE look and feel
                                front and centre.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-band rounded-5 p-4 p-lg-5 mb-4 mb-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-8">
                    <div class="soft-pill mb-3">North Metropolitan TAFE alumni experience</div>
                    <h2 class="h1 fw-bold text-white mb-3">Create your account or sign in and start using the
                        network.</h2>
                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.82);">
                        Whether you are returning after graduation or logging in to keep your profile current, the
                        Alumni app is the place to reconnect, discover, and contribute.
                    </p>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-3 justify-content-lg-end">
                        @guest
                            <a href="" class="btn btn-light btn-lg rounded-pill px-4 fw-semibold">Log
                                in</a>
                            <a href=""
                                class="btn btn-outline-light btn-lg rounded-pill px-4 fw-semibold">Register</a>
                        @endguest

                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="btn btn-light btn-lg rounded-pill px-4 fw-semibold">Go to dashboard</a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        <footer class="alumni-footer d-flex flex-column flex-lg-row justify-content-between gap-2 pb-4 pb-lg-5">
            <div class="fw-semibold">North Metropolitan TAFE Alumni App</div>
            <div>Designed to keep the community connected, informed, and ready for what comes next.</div>
        </footer>
    </main>
    </div>

</body>

</html>
