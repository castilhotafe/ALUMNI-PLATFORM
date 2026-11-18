<?php

namespace Database\Seeders;

use App\Models\EnrolmentListing;
use App\Models\EnrolmentListingProfile;
use App\Models\JobListing;
use App\Models\JobListingProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListingsSeeder extends Seeder
{
    public const LISTING_COUNT = 60;

    public function run(): void
    {
        $userIds = User::query()->orderBy('id')->pluck('id')->values();

        if ($userIds->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= self::LISTING_COUNT; $i++) {
            $creatorId = $userIds[($i + 3) % $userIds->count()];
            $jobTitle = $this->jobTitleFor($i);
            $courseTitle = $this->courseTitleFor($i);

            $job = JobListing::create([
                'creator_user_id' => $creatorId,
                'title' => $jobTitle,
                'company' => $this->valueFrom($i, [
                    'Aurora Tech',
                    'Coastal Data Systems',
                    'Northbridge Health Network',
                    'Pilbara Cloud Co',
                    'Horizon Logistics',
                    'Brightpath Education',
                ]),
                'description' => 'This role supports practical delivery, team collaboration, and guided growth through industry-backed mentorship.',
                'apply_link' => 'https://example.com/jobs/'.$i,
            ]);

            JobListingProfile::create([
                'job_listing_id' => $job->id,
                'bio' => 'Additional detail for '.$jobTitle.'.',
                'details' => ['Flexible hours', 'Hybrid work', 'Mentoring', 'Growth plan'],
                'picture_url' => 'https://picsum.photos/seed/job-'.$i.'/640/360',
            ]);

            $enrolment = EnrolmentListing::create([
                'creator_user_id' => $creatorId,
                'course_title' => $courseTitle,
                'tafe_provider' => 'North Metropolitan TAFE',
                'description' => 'Industry-aligned training pathway designed for practical capability, portfolio outcomes, and employability.',
                'enrol_link' => 'https://example.com/courses/'.$i,
            ]);

            EnrolmentListingProfile::create([
                'enrolment_listing_id' => $enrolment->id,
                'bio' => 'Additional detail for '.$courseTitle.'.',
                'details' => ['Online option', 'Workshop labs', 'Industry projects', 'Career support'],
                'picture_url' => 'https://picsum.photos/seed/enrolment-'.$i.'/640/360',
            ]);
        }
    }

    private function jobTitleFor(int $index): string
    {
        $roles = [
            'Junior Web Developer',
            'IT Support Analyst',
            'Data Operations Assistant',
            'Cyber Security Associate',
            'Cloud Platform Trainee',
            'QA Automation Assistant',
        ];

        $domains = [
            'Alumni Services',
            'Digital Delivery Team',
            'Student Systems',
            'Community Partnerships',
            'Innovation Lab',
        ];

        return $this->valueFrom($index, $roles)
            .' - '
            .$this->valueFrom((int) ceil($index / count($roles)), $domains);
    }

    private function courseTitleFor(int $index): string
    {
        $streams = [
            'Full Stack Web Development',
            'Cyber Security Operations',
            'Cloud Infrastructure Foundations',
            'Data Analytics and Reporting',
            'UX Design and Prototyping',
            'Network Administration Essentials',
        ];

        $formats = [
            'Evening Intake',
            'Weekend Intensive',
            'Semester Program',
            'Industry Pathway',
            'Blended Delivery',
        ];

        return $this->valueFrom($index, $streams)
            .' - '
            .$this->valueFrom((int) ceil($index / count($streams)), $formats);
    }

    private function valueFrom(int $index, array $values): string
    {
        return $values[($index - 1) % count($values)];
    }
}
