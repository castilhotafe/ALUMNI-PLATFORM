<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersAndProfilesSeeder extends Seeder
{
    public const USER_COUNT = 60;

    public function run(): void
    {
        $adminRole = Role::findByName('Admin');
        $partnerRole = Role::findByName('Partner');
        $alumniRole = Role::findByName('Alumni');
        $lecturerRole = Role::findByName('Lecturer');
        $currentRole = Role::findByName('Current Student');
        $generalRole = Role::findByName('General User');

        $seedUsers = [
            ['name' => 'Alicia Admin', 'email' => 'admin@nmtafe-alumni.test', 'role' => $adminRole->name],
            [
                'name' => 'Patrick Partner',
                'email' => 'partner@nmtafe-alumni.test',
                'role' => $partnerRole->name,
                'attrs' => ['isPartner' => true, 'isVerifiedPartner' => true],
            ],
            [
                'name' => 'Alex Alumni',
                'email' => 'test@example.com',
                'role' => $alumniRole->name,
                'attrs' => ['isAlumni' => true, 'isVerifiedAlumni' => true],
            ],
        ];

        foreach ($seedUsers as $seedUser) {
            $this->createOrUpdateUser(
                $seedUser['name'],
                $seedUser['email'],
                $seedUser['role'],
                $seedUser['attrs'] ?? []
            );
        }

        $dynamicRoles = [
            $alumniRole->name,
            $lecturerRole->name,
            $currentRole->name,
            $partnerRole->name,
            $generalRole->name,
        ];

        $seenNames = [];

        for ($i = 1; $i <= self::USER_COUNT - count($seedUsers); $i++) {
            $role = $dynamicRoles[($i - 1) % count($dynamicRoles)];
            $name = $this->generateUniqueName($seenNames);
            $emailSlug = (string) Str::of($name)
                ->ascii()
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '.')
                ->trim('.');

            $this->createOrUpdateUser(
                $name,
                $emailSlug . '.' . str_pad((string) $i, 3, '0', STR_PAD_LEFT) . '@example.com',
                $role,
                $this->flagsForRole($role)
            );
        }

        $skills = [
            'Laravel',
            'PHP',
            'Vue',
            'React',
            'Python',
            'Cybersecurity',
            'Cloud',
            'Data Engineering',
            'UI/UX',
            'DevOps',
            'Networking',
            'AI',
        ];

        User::query()->orderBy('id')->get()->each(function (User $user, int $index) use ($skills): void {
            Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => 'Community profile for ' . $user->name . '. Focused on practical learning, collaboration, and mentorship.',
                    'details' => collect($skills)->shuffle()->take(4)->values()->all(),
                    'resume_link' => 'https://example.com/resumes/' . $user->id,
                    'picture_url' => 'https://i.pravatar.cc/200?img=' . (($index % 70) + 1),
                ]
            );
        });
    }

    private function createOrUpdateUser(string $name, string $email, string $roleName, array $attrs = []): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            array_merge([
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ], $attrs)
        );

        $user->syncRoles([$roleName]);

        return $user;
    }

    private function flagsForRole(string $roleName): array
    {
        return match ($roleName) {
            'Alumni' => ['isAlumni' => true, 'isVerifiedAlumni' => true],
            'Lecturer' => ['isLecturer' => true, 'isVerifiedLecturer' => true],
            'Current Student' => ['isCurrent' => true, 'isVerifiedCurrent' => true],
            'Partner' => ['isPartner' => true, 'isVerifiedPartner' => true],
            default => [],
        };
    }

    private function generateUniqueName(array &$seenNames): string
    {
        do {
            $name = User::factory()->make()->name;
        } while (in_array($name, $seenNames, true));

        $seenNames[] = $name;

        return $name;
    }
}
