<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunityContentSeeder extends Seeder
{
    public const POST_COUNT = 120;
    public const DIRECT_CONVERSATION_COUNT = 60;

    public function run(): void
    {
        $users = User::query()->orderBy('id')->get()->values();

        if ($users->count() < 2) {
            return;
        }

        for ($i = 1; $i <= self::POST_COUNT; $i++) {
            $author = $users[($i - 1) % $users->count()];
            $title = $this->postTitleFor($i);

            Post::create([
                'user_id' => $author->id,
                'title' => $title,
                'body' => 'Sharing this week\'s progress, opportunities, and collaboration highlights from our alumni and student network.',
                'visibility' => $i % 4 === 0 ? 'private' : 'public',
            ]);
        }
    }

    private function postTitleFor(int $index): string
    {
        $topics = [
            'Weekly Industry Roundup',
            'Project Milestone Highlights',
            'Mentorship Session Recap',
            'Community Opportunity Board',
            'Alumni Spotlight Story',
            'Skills Workshop Notes',
        ];

        $formats = [
            'Community Brief',
            'Update',
            'Recap',
            'Insights',
            'Digest',
        ];

        return $topics[($index - 1) % count($topics)]
            . ' - '
            . $formats[((int) ceil($index / count($topics)) - 1) % count($formats)];
    }
}
