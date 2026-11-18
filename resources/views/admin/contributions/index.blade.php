@php
    $pageTitle = 'Contribution Ledger';
    $sectionKicker = 'Administration';
@endphp

@extends('layouts.platform')

@section('content')
    @php
        $referenceLabels = collect($referenceTypes)->mapWithKeys(fn (array $definition, string $key) => [
            $definition['class'] ?? $key => $definition['label'] ?? ucfirst(str_replace('_', ' ', $key)),
        ]);
    @endphp

    <div class="contribution-ledger-panel bg-white rounded-4 p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 mb-4">
            <div>
                <div class="nmtafe-kicker text-danger fw-semibold mb-2">Administration</div>
                <h2 class="display-6 fw-bold mb-3 text-dark">Contribution ledger</h2>
                <p class="lead text-secondary mb-0">Track every point movement and issue manual awards or penalties for the TAFE community.</p>
            </div>

            <div class="text-lg-end contribution-ledger-summary">
                <div class="small text-uppercase text-secondary fw-semibold mb-1">Net ledger points</div>
                <div class="display-5 fw-bold text-danger mb-1 contribution-ledger-summary__value">{{ number_format($summary['net']) }}</div>
                <div class="small text-secondary">Across {{ number_format($summary['entries']) }} contribution entries</div>
            </div>
        </div>

        <div class="row g-3 mb-4 metric-grid-five">
            <div class="col-12 col-sm-6 col-lg-4 metric-grid-five__item">
                <div class="metric-box metric-box--compact rounded-4 p-3 h-100 border bg-light">
                    <div class="small text-uppercase text-secondary fw-semibold mb-2">Awards</div>
                    <div class="h3 fw-bold mb-1 text-danger">{{ number_format($summary['awards']) }}</div>
                    <div class="small text-secondary">Positive points granted</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 metric-grid-five__item">
                <div class="metric-box metric-box--compact rounded-4 p-3 h-100 border bg-light">
                    <div class="small text-uppercase text-secondary fw-semibold mb-2">Penalties</div>
                    <div class="h3 fw-bold mb-1 text-dark">{{ number_format(abs($summary['penalties'])) }}</div>
                    <div class="small text-secondary">Negative points applied</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 metric-grid-five__item">
                <div class="metric-box metric-box--compact rounded-4 p-3 h-100 border bg-light">
                    <div class="small text-uppercase text-secondary fw-semibold mb-2">Members affected</div>
                    <div class="h3 fw-bold mb-1 text-danger">{{ number_format($contributions->getCollection()->pluck('user_id')->unique()->count()) }}</div>
                    <div class="small text-secondary">Users in this page of the ledger</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 metric-grid-five__item">
                <div class="metric-box metric-box--compact rounded-4 p-3 h-100 border bg-light">
                    <div class="small text-uppercase text-secondary fw-semibold mb-2">Ledger rows</div>
                    <div class="h3 fw-bold mb-1 text-danger">{{ number_format($contributions->total()) }}</div>
                    <div class="small text-secondary">Paginated results</div>
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-xl-4">
                <div class="border rounded-4 p-4 bg-light h-100">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <div class="small text-uppercase text-secondary fw-semibold mb-1">Manual ledger entry</div>
                            <h3 class="h5 mb-0">Grant or penalise points</h3>
                        </div>
                        <span class="badge text-bg-danger">Admin only</span>
                    </div>

                    <form method="POST" action="{{ route('admin.contributions.store') }}" class="vstack gap-3">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div>
                            <x-searchable-select
                                id="contribution-user"
                                name="user_id"
                                label="User"
                                :options="$users->map(fn ($user) => ['value' => $user->id, 'label' => $user->name . ' (' . $user->email . ')'])->values()"
                                :selected="old('user_id')"
                                placeholder="Search a user..."
                                helper-text="Type to narrow the list when the ledger gets large."
                            />
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label for="contribution-amount" class="form-label fw-semibold">Amount</label>
                                <input id="contribution-amount" name="amount" type="number" min="1" step="1" class="form-control" value="{{ old('amount', 10) }}" required>
                            </div>
                            <div class="col-6">
                                <label for="contribution-direction" class="form-label fw-semibold">Action</label>
                                <select id="contribution-direction" name="direction" class="form-select">
                                    <option value="grant" @selected(old('direction', 'grant') === 'grant')>Grant</option>
                                    <option value="penalize" @selected(old('direction') === 'penalize')>Penalise</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="contribution-reason" class="form-label fw-semibold">Reason</label>
                            <input id="contribution-reason" name="reason" type="text" class="form-control" maxlength="255" value="{{ old('reason') }}" placeholder="Workshop facilitation, peer mentoring, industry placement support" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="contribution-reference-type" class="form-label fw-semibold">Reference type</label>
                                <select id="contribution-reference-type" name="reference_type" class="form-select">
                                    <option value="">Manual entry</option>
                                    @foreach ($referenceTypes as $referenceKey => $referenceDefinition)
                                        <option value="{{ $referenceKey }}" @selected(old('reference_type') === $referenceKey)>
                                            {{ $referenceDefinition['label'] ?? ucfirst(str_replace('_', ' ', $referenceKey)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="contribution-reference-id" class="form-label fw-semibold">Reference ID</label>
                                <input id="contribution-reference-id" name="reference_id" type="number" min="1" step="1" class="form-control" value="{{ old('reference_id') }}" placeholder="Optional model record ID">
                                <div class="form-text">Leave blank for a manual admin adjustment.</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-danger btn-lg w-100">Save ledger entry</button>
                    </form>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="border rounded-4 p-0 bg-white h-100 overflow-hidden">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 p-4 border-bottom bg-light">
                        <div>
                            <div class="small text-uppercase text-secondary fw-semibold mb-1">Ledger entries</div>
                            <h3 class="h5 mb-0">Latest contribution movements</h3>
                        </div>
                        <div class="small text-secondary">Showing {{ $contributions->count() }} of {{ $contributions->total() }} entries</div>
                    </div>

                    <div class="table-responsive contribution-ledger-table">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Points</th>
                                    <th>Reason</th>
                                    <th>Reference</th>
                                    <th>Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contributions as $contribution)
                                    @php
                                        $referenceSummary = $contribution->reference_type && $contribution->reference_id
                                            ? trim(($referenceLabels->get($contribution->reference_type) ?? 'Reference') . ' #' . $contribution->reference_id)
                                            : 'Manual entry';
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $contribution->created_at?->format('d M Y') }}</div>
                                            <div class="small text-secondary">{{ $contribution->created_at?->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $contribution->user?->name }}</div>
                                            <div class="small text-secondary">{{ $contribution->user?->email }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $contribution->amount >= 0 ? 'text-bg-success' : 'text-bg-danger' }} contribution-amount {{ $contribution->amount >= 0 ? 'contribution-amount--positive' : 'contribution-amount--negative' }}">
                                                {{ $contribution->amount >= 0 ? '+' : '' }}{{ number_format($contribution->amount) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $contribution->reason }}</div>
                                        </td>
                                        <td>
                                            <div class="small text-secondary">{{ $referenceSummary }}</div>
                                        </td>
                                        <td>
                                            <div class="small text-secondary">{{ $contribution->admin?->name ?? 'System' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-secondary p-4">No contributions have been recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <x-compact-pagination
                        route-name="admin.contributions.index"
                        page-name="page"
                        :current-page="$contributions->currentPage()"
                        :total-pages="$contributions->lastPage()"
                        :query-params="request()->query()"
                    />
                </div>
            </div>
        </div>
    </div>
@endsection