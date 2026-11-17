<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContributionController extends Controller
{
    public function index(): View
    {
        $contributions = Contribution::with(['user', 'admin', 'reference'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.contributions.index', [
            'pageTitle' => 'Contribution Ledger',
            'sectionKicker' => 'Administration',
            'contributions' => $contributions,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'summary' => [
                'net' => (int) Contribution::sum('amount'),
                'awards' => (int) Contribution::where('amount', '>', 0)->sum('amount'),
                'penalties' => (int) Contribution::where('amount', '<', 0)->sum('amount'),
                'entries' => Contribution::count(),
            ],
            'referenceTypes' => config('contributions.reference_types', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var array<string, array{label?: string, class?: class-string}> $referenceTypes */
        $referenceTypes = config('contributions.reference_types') ?? [];

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'amount' => ['required', 'integer', 'min:1'],
            'direction' => ['required', 'string', Rule::in(['grant', 'penalize'])],
            'reason' => ['required', 'string', 'max:255'],
            'reference_type' => [
                'nullable',
                'string',
                Rule::in(array_keys($referenceTypes)),
                'required_with:reference_id',
            ],
            'reference_id' => ['nullable', 'integer', 'min:1', 'required_with:reference_type'],
        ]);

        $targetUser = User::findOrFail((int) $validated['user_id']);
        $amount = (int) $validated['amount'];

        if ($validated['direction'] === 'penalize') {
            $amount = -abs($amount);
        }

        $reference = null;

        if (! empty($validated['reference_type']) && ! empty($validated['reference_id'])) {
            $referenceClass = $referenceTypes[$validated['reference_type']]['class'] ?? null;

            if (is_string($referenceClass) && class_exists($referenceClass)) {
                $reference = $referenceClass::query()->findOrFail((int) $validated['reference_id']);
            }
        }

        if ($amount > 0) {
            $targetUser->addContribution($amount, $validated['reason'], $reference, $request->user());
        } else {
            $targetUser->deductContribution(abs($amount), $validated['reason'], $reference, $request->user());
        }

        return redirect()
            ->route('admin.contributions.index')
            ->with('status', 'Contribution ledger updated for ' . $targetUser->name . '.');
    }
}