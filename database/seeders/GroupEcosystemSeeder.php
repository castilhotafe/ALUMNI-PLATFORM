<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\GroupProfile;
use App\Models\Project;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupEcosystemSeeder extends Seeder
{
    public const GROUP_COUNT = 50;

    public function run(): void
    {
        $userIds = User::query()->orderBy('id')->pluck('id')->values();
        $projectIds = Project::query()->orderBy('id')->pluck('id')->values();
        $paperIds = ResearchPaper::query()->orderBy('id')->pluck('id')->values();

        if ($userIds->isEmpty() || $projectIds->isEmpty() || $paperIds->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= self::GROUP_COUNT; $i++) {
            $creatorId = $userIds[($i + 7) % $userIds->count()];
            $groupName = $this->groupNameFor($i);

            $group = Group::create([
                'name' => $groupName,
                'description' => 'A community space for project sharing, peer support, and practical collaboration.',
                'creator_user_id' => $creatorId,
            ]);

            $memberIds = collect([
                $creatorId,
                $userIds[($i + 9) % $userIds->count()],
                $userIds[($i + 13) % $userIds->count()],
                $userIds[($i + 21) % $userIds->count()],
            ])->unique()->values();

            $group->members()->syncWithoutDetaching([
                $creatorId => ['member_role' => 'owner'],
            ]);

            $memberIds
                ->reject(fn(int $memberId) => $memberId === $creatorId)
                ->each(function (int $memberId) use ($group): void {
                    $group->members()->syncWithoutDetaching([
                        $memberId => ['member_role' => 'member'],
                    ]);
                });

            $group->projects()->syncWithoutDetaching([
                $projectIds[($i - 1) % $projectIds->count()],
                $projectIds[($i + 18) % $projectIds->count()],
            ]);

            $group->researchPapers()->syncWithoutDetaching([
                $paperIds[($i - 1) % $paperIds->count()],
                $paperIds[($i + 23) % $paperIds->count()],
            ]);

            GroupProfile::create([
                'group_id' => $group->id,
                'bio' => 'Profile for ' . $groupName . '.',
                'details' => ['Networking', 'Mentorship', 'Projects', 'Events'],
                'picture_url' => 'https://picsum.photos/seed/group-' . $i . '/640/360',
            ]);
        }
    }

    private function groupNameFor(int $index): string
    {
        $prefixes = [
            'Career Builders',
            'Tech Mentors',
            'Industry Connect',
            'Digital Makers',
            'Future Leaders',
            'Project Exchange',
        ];

        $suffixes = [
            'Circle',
            'Collective',
            'Network',
            'Forum',
            'Guild',
        ];

        return $prefixes[($index - 1) % count($prefixes)]
            . ' '
            . $suffixes[((int) ceil($index / count($prefixes)) - 1) % count($suffixes)];
    }
}
