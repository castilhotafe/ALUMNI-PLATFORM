@extends('layouts.platform')

@section('content')
    <div class="nmtafe-card bg-white rounded-4 p-4 p-lg-5" data-x-data="adminUserDashboard({
        users: @js($usersPayload),
        storeUrl: '{{ route('admin.users.store') }}',
        updateUrl: '{{ route('admin.users.update', ['user' => '__USER__']) }}',
        deleteUrl: '{{ route('admin.users.destroy', ['user' => '__USER__']) }}',
        oldForm: @js($oldForm),
    })">
        <div class="section-intro rounded-4 p-4 p-lg-5 mb-4 border bg-white">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-4">
                <div>
                    <div class="nmtafe-kicker text-danger fw-semibold mb-2">Administration</div>
                    <h2 class="display-6 fw-bold mb-3 text-dark">Manage users, roles, and access</h2>
                    <p class="lead text-secondary mb-0">Search accounts, review status, and manage new users.</p>
                </div>

                <button class="btn btn-danger btn-lg" type="button" data-x-on:click="openCreate()">Create user</button>
            </div>

            @foreach ($statsSections as $section)
                <div class="mt-4">
                    <div class="small text-uppercase text-secondary fw-semibold mb-2">{{ $section['title'] }}</div>
                    <div class="row g-3 metric-grid-five">
                        @foreach ($section['items'] as $stat)
                            <div class="col-12 col-sm-6 col-lg-4 metric-grid-five__item">
                                <div class="metric-box metric-box--compact rounded-4 p-3 h-100 border bg-light">
                                    <div class="small text-uppercase text-secondary fw-semibold mb-2">{{ $stat['label'] }}</div>
                                    <div class="h3 fw-bold mb-1 text-danger">{{ $stat['value'] }}</div>
                                    <div class="small text-secondary">{{ $stat['note'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex flex-wrap gap-3 align-items-start justify-content-between mb-4">
            <div>
                <div class="nmtafe-kicker text-danger fw-semibold mb-2">User administration</div>
                <h3 class="h5 mb-0">Search and manage users</h3>
            </div>
            <span class="badge text-bg-danger align-self-start">Admin</span>
        </div>

        <div class="row g-3 align-items-end mb-3">
            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold small" for="admin-user-search">Search users</label>
                <input id="admin-user-search" type="search" class="form-control"
                    placeholder="Search by name, email, or role" data-x-model="searchQuery">
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label fw-semibold small" for="admin-role-filter">Role</label>
                <select id="admin-role-filter" class="form-select" data-x-model="roleFilter">
                    <option value="all">All roles</option>
                    <option value="current">Current Student</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="partner">Partner</option>
                    <option value="alumni">Alumni</option>
                    <option value="general">General User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label fw-semibold small" for="admin-status-filter">Status</label>
                <select id="admin-status-filter" class="form-select" data-x-model="statusFilter">
                    <option value="all">All statuses</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="small text-secondary">
                Showing <span class="fw-semibold" data-x-text="filteredUsers.length"></span> of {{ $users->count() }} users
            </div>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-x-on:click="resetFilters()">Clear
                filters</button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Verification</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template data-x-for="user in filteredUsers" data-x-bind:key="user.id">
                        <tr>
                            <td>
                                <div class="fw-semibold" data-x-text="user.name"></div>
                                <div class="small text-secondary" data-x-text="user.email"></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="fw-semibold" data-x-text="user.roleLabel"></div>
                                    <template
                                        data-x-if="(user.roleKey === 'current' && user.isVerifiedCurrent) || (user.roleKey === 'alumni' && user.isVerifiedAlumni) || (user.roleKey === 'lecturer' && user.isVerifiedLecturer) || (user.roleKey === 'partner' && user.isVerifiedPartner)">
                                        <span class="badge text-bg-success">Verified</span>
                                    </template>
                                </div>
                                <div class="small text-secondary" data-x-show="user.selfDeclaredRole"
                                    data-x-text="'Declared: ' + user.selfDeclaredRole"></div>
                            </td>
                            <td>
                                <span class="badge"
                                    data-x-bind:class="user.status === 'active' ? 'text-bg-success' : 'text-bg-secondary'"
                                    data-x-text="user.status === 'active' ? 'Active' : 'Pending'"></span>
                                <div class="small text-secondary mt-1"
                                    data-x-text="user.emailVerified ? 'Email verified' : 'Email unverified'"></div>
                            </td>
                            <td>
                                <div class="small text-secondary"
                                    data-x-text="user.studentId ? 'Student ID: ' + user.studentId : 'No student ID'"></div>
                                <div class="small text-secondary"
                                    data-x-text="user.passwordConfirmed ? 'Password confirmed' : 'Password pending'"></div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <form method="POST" data-x-bind:action="userDeleteUrl(user.id)"
                                        data-x-on:submit.prevent="confirmDelete($event, user)">
                                        <button class="btn btn-outline-primary btn-sm" type="button"
                                            data-x-on:click="openEdit(user)">Edit</button>
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr data-x-show="filteredUsers.length === 0">
                        <td colspan="5" class="text-secondary">No users match your filters.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <template data-x-teleport="body">
            <div data-x-show="isOpen" style="display: none;" data-x-transition.opacity.duration.200ms>
                <div class="modal-backdrop fade show"></div>

                <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
                    <div class="modal-dialog modal-dialog-scrollable modal-lg" data-x-on:click.outside="closeModal()">
                        <div class="modal-content">
                            <form method="POST" data-x-bind:action="formAction" data-x-on:submit="submitForm($event)">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <template data-x-if="formMode === 'edit'">
                                    <input type="hidden" name="_method" value="PATCH">
                                </template>
                                <input type="hidden" name="form_mode" data-x-model="formMode">
                                <input type="hidden" name="user_id" data-x-model="form.id">

                                <div class="modal-header">
                                    <div>
                                        <div class="nmtafe-kicker text-danger fw-semibold"
                                            data-x-text="formMode === 'create' ? 'Create user' : 'Edit user'"></div>
                                        <h4 class="h5 mb-0"
                                            data-x-text="formMode === 'create' ? 'Add a new account' : 'Update account details'">
                                        </h4>
                                    </div>
                                    <button type="button" class="btn-close" aria-label="Close"
                                        data-x-on:click="closeModal()"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label" for="admin-user-name">Full name</label>
                                            <input id="admin-user-name" type="text" name="name"
                                                class="form-control" data-x-model="form.name"
                                                data-x-bind:class="{ 'is-invalid': formErrors.name }" required>
                                            <div class="invalid-feedback" data-x-text="formErrors.name"
                                                data-x-show="formErrors.name"></div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label" for="admin-user-email">Email</label>
                                            <input id="admin-user-email" type="email" name="email"
                                                class="form-control" data-x-model="form.email"
                                                data-x-bind:class="{ 'is-invalid': formErrors.email }" required>
                                            <div class="invalid-feedback" data-x-text="formErrors.email"
                                                data-x-show="formErrors.email"></div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label" for="admin-user-role">Role</label>
                                            <template data-x-if="!form.roleLocked">
                                                <select id="admin-user-role" name="role_selection" class="form-select"
                                                    data-x-model="form.roleSelection"
                                                    data-x-bind:class="{ 'is-invalid': formErrors.roleSelection }" required>
                                                    <option value="current">Current Student</option>
                                                    <option value="lecturer">Lecturer</option>
                                                    <option value="partner">Partner</option>
                                                    <option value="alumni">Alumni</option>
                                                    <option value="general">General User</option>
                                                </select>
                                            </template>
                                            <template data-x-if="form.roleLocked">
                                                <div class="border rounded-3 p-3 bg-light">
                                                    <div class="fw-semibold text-danger mb-1">Admin</div>
                                                    <div class="small text-secondary mb-0">Admin roles are locked and
                                                        cannot be changed here.</div>
                                                    <input type="hidden" name="role_selection" value="admin">
                                                </div>
                                            </template>
                                            <div class="invalid-feedback d-block" data-x-text="formErrors.roleSelection"
                                                data-x-show="formErrors.roleSelection"></div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label" for="admin-user-status">Status</label>
                                            <select id="admin-user-status" name="status" class="form-select"
                                                data-x-model="form.status" data-x-bind:class="{ 'is-invalid': formErrors.status }"
                                                required>
                                                <option value="active">Active</option>
                                                <option value="pending">Pending</option>
                                            </select>
                                            <div class="invalid-feedback" data-x-text="formErrors.status"
                                                data-x-show="formErrors.status"></div>
                                            <div class="form-text">This controls the account state. Email verification can be toggled separately below.</div>
                                        </div>
                                        <div class="col-12 col-lg-6" data-x-show="showStudentId">
                                            <label class="form-label" for="admin-user-student">Student ID</label>
                                            <input id="admin-user-student" type="text" name="student_id"
                                                class="form-control" data-x-model="form.studentId"
                                                data-x-bind:class="{ 'is-invalid': formErrors.studentId }" data-x-bind:required="showStudentId"
                                                data-x-bind:disabled="!showStudentId" placeholder="8 digit student ID">
                                            <div class="invalid-feedback" data-x-text="formErrors.studentId"
                                                data-x-show="formErrors.studentId"></div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label" for="admin-user-password">Password</label>
                                            <input id="admin-user-password" type="password" name="password"
                                                class="form-control" data-x-model="form.password"
                                                data-x-bind:class="{ 'is-invalid': formErrors.password }"
                                                data-x-bind:required="formMode === 'create'" autocomplete="new-password">
                                            <div class="invalid-feedback" data-x-text="formErrors.password"
                                                data-x-show="formErrors.password"></div>
                                            <div class="form-text" data-x-show="formMode === 'edit'">Leave blank to keep the
                                                current password.</div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label" for="admin-user-password-confirmation">Confirm
                                                password</label>
                                            <input id="admin-user-password-confirmation" type="password"
                                                name="password_confirmation" class="form-control"
                                                data-x-model="form.passwordConfirmation"
                                                data-x-bind:class="{ 'is-invalid': formErrors.passwordConfirmation }"
                                                data-x-bind:required="formMode === 'create'" autocomplete="new-password">
                                            <div class="invalid-feedback" data-x-text="formErrors.passwordConfirmation"
                                                data-x-show="formErrors.passwordConfirmation"></div>
                                        </div>
                                        <div class="col-12">
                                            <div class="border rounded-3 p-3">
                                                <div class="fw-semibold mb-2">Verification flags</div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                role="switch" id="verify-email"
                                                                name="emailVerified" value="1"
                                                                data-x-model="form.emailVerified">
                                                            <label class="form-check-label" for="verify-email">Email verified</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                role="switch" id="verify-current"
                                                                name="isVerifiedCurrent" value="1"
                                                                data-x-model="form.isVerifiedCurrent">
                                                            <label class="form-check-label" for="verify-current">Current
                                                                Student</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                role="switch" id="verify-alumni" name="isVerifiedAlumni"
                                                                value="1" data-x-model="form.isVerifiedAlumni">
                                                            <label class="form-check-label"
                                                                for="verify-alumni">Alumni</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                role="switch" id="verify-lecturer"
                                                                name="isVerifiedLecturer" value="1"
                                                                data-x-model="form.isVerifiedLecturer">
                                                            <label class="form-check-label"
                                                                for="verify-lecturer">Lecturer</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                role="switch" id="verify-partner"
                                                                name="isVerifiedPartner" value="1"
                                                                data-x-model="form.isVerifiedPartner">
                                                            <label class="form-check-label"
                                                                for="verify-partner">Partner</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-x-on:click="closeModal()">Cancel</button>
                                    <button type="submit" class="btn btn-danger"
                                        data-x-text="formMode === 'create' ? 'Create user' : 'Save changes'"></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <script>
            window.adminUserDashboard = (config) => ({
                users: (config.users || []).map((user) => ({
                    ...user,
                    search: `${user.name} ${user.email} ${user.roleLabel}`.toLowerCase(),
                })),
                searchQuery: '',
                roleFilter: 'all',
                statusFilter: 'all',
                formMode: 'create',
                isOpen: false,
                form: {},
                formErrors: {},
                init() {
                    this.form = this.defaultForm();

                    if (config.oldForm && (config.oldForm.name || config.oldForm.email)) {
                        this.formMode = config.oldForm.mode === 'edit' ? 'edit' : 'create';
                        this.form = {
                            ...this.defaultForm(),
                            id: config.oldForm.id ? Number(config.oldForm.id) : null,
                            name: config.oldForm.name || '',
                            email: config.oldForm.email || '',
                            roleSelection: config.oldForm.roleSelection || 'current',
                            status: config.oldForm.status || 'active',
                            studentId: config.oldForm.studentId || '',
                            emailVerified: Boolean(config.oldForm.emailVerified),
                            isVerifiedCurrent: Boolean(config.oldForm.isVerifiedCurrent),
                            isVerifiedAlumni: Boolean(config.oldForm.isVerifiedAlumni),
                            isVerifiedLecturer: Boolean(config.oldForm.isVerifiedLecturer),
                            isVerifiedPartner: Boolean(config.oldForm.isVerifiedPartner),
                            roleLocked: config.oldForm.roleSelection === 'admin',
                        };
                        this.openModal();
                    }
                },
                defaultForm() {
                    return {
                        id: null,
                        name: '',
                        email: '',
                        roleSelection: 'current',
                        status: 'active',
                        studentId: '',
                        password: '',
                        passwordConfirmation: '',
                        emailVerified: true,
                        isVerifiedCurrent: false,
                        isVerifiedAlumni: false,
                        isVerifiedLecturer: false,
                        isVerifiedPartner: false,
                        roleLocked: false,
                    };
                },
                get filteredUsers() {
                    const query = this.searchQuery.trim().toLowerCase();

                    return this.users.filter((user) => {
                        const matchesQuery = !query || user.search.includes(query);
                        const matchesRole = this.roleFilter === 'all' || user.roleKey === this.roleFilter;
                        const matchesStatus = this.statusFilter === 'all' || user.status === this
                            .statusFilter;

                        return matchesQuery && matchesRole && matchesStatus;
                    });
                },
                get showStudentId() {
                    return ['current', 'alumni'].includes(this.form.roleSelection);
                },
                get formAction() {
                    if (this.formMode === 'edit' && this.form.id) {
                        return this.userUpdateUrl(this.form.id);
                    }

                    return config.storeUrl;
                },
                resetFilters() {
                    this.searchQuery = '';
                    this.roleFilter = 'all';
                    this.statusFilter = 'all';
                },
                openModal() {
                    this.isOpen = true;
                    document.body.classList.add('modal-open');
                },
                closeModal() {
                    this.isOpen = false;
                    setTimeout(() => {
                        document.body.classList.remove('modal-open');
                    }, 200);
                },
                openCreate() {
                    this.formMode = 'create';
                    this.form = this.defaultForm();
                    this.formErrors = {};
                    this.openModal();
                },
                openEdit(user) {
                    this.formMode = 'edit';
                    this.form = {
                        ...this.defaultForm(),
                        id: user.id,
                        name: user.name,
                        email: user.email,
                        roleSelection: user.roleKey || 'general',
                        status: user.status || 'pending',
                        studentId: user.studentId ? String(user.studentId) : '',
                        emailVerified: Boolean(user.emailVerified),
                        isVerifiedCurrent: Boolean(user.isVerifiedCurrent),
                        isVerifiedAlumni: Boolean(user.isVerifiedAlumni),
                        isVerifiedLecturer: Boolean(user.isVerifiedLecturer),
                        isVerifiedPartner: Boolean(user.isVerifiedPartner),
                        roleLocked: Boolean(user.isAdmin),
                    };
                    this.formErrors = {};
                    this.openModal();
                },
                userUpdateUrl(id) {
                    return config.updateUrl.replace('__USER__', id);
                },
                userDeleteUrl(id) {
                    return config.deleteUrl.replace('__USER__', id);
                },
                confirmDelete(event, user) {
                    if (!window.confirm(`Delete ${user.name}?`)) {
                        return;
                    }

                    event.target.submit();
                },
                submitForm(event) {
                    if (!this.validateForm()) {
                        event.preventDefault();
                    }
                },
                validateForm() {
                    const errors = {};

                    if (!this.form.name.trim()) {
                        errors.name = 'Name is required.';
                    }

                    if (!this.form.email.trim()) {
                        errors.email = 'Email is required.';
                    } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(this.form.email)) {
                        errors.email = 'Use a valid email address.';
                    }

                    if (!this.form.roleSelection) {
                        errors.roleSelection = 'Select a role.';
                    }

                    if (!this.form.status) {
                        errors.status = 'Select a status.';
                    }

                    if (this.showStudentId) {
                        if (!this.form.studentId) {
                            errors.studentId = 'Student ID is required.';
                        } else if (!/^\d{8}$/.test(this.form.studentId)) {
                            errors.studentId = 'Student ID must be 8 digits.';
                        }
                    }

                    if (this.formMode === 'create' || this.form.password) {
                        if (!this.form.password) {
                            errors.password = 'Password is required.';
                        } else if (this.form.password.length < 8) {
                            errors.password = 'Password must be at least 8 characters.';
                        }

                        if (this.form.password !== this.form.passwordConfirmation) {
                            errors.passwordConfirmation = 'Passwords do not match.';
                        }
                    }

                    this.formErrors = errors;
                    return Object.keys(errors).length === 0;
                },
            });
        </script>
    </div>
@endsection