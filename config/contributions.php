<?php

declare(strict_types=1);

use App\Models\EnrolmentListing;
use App\Models\Group;
use App\Models\JobListing;
use App\Models\Post;
use App\Models\Project;
use App\Models\ResearchPaper;

return [
    'events' => [
        'post_created' => [
            'points' => 10,
            'reason' => 'Shared TAFE learning through a new post',
        ],
        'post_updated' => [
            'points' => 5,
            'reason' => 'Updated a post',
        ],
        'post_deleted' => [
            'points' => -10,
            'reason' => 'Deleted a post',
        ],
        'project_created' => [
            'points' => 15,
            'reason' => 'Created a project showcase',
        ],
        'project_updated' => [
            'points' => 5,
            'reason' => 'Updated a project showcase',
        ],
        'project_deleted' => [
            'points' => -15,
            'reason' => 'Deleted a project showcase',
        ],
        'research_paper_created' => [
            'points' => 15,
            'reason' => 'Created a research paper entry',
        ],
        'research_paper_updated' => [
            'points' => 5,
            'reason' => 'Updated a research paper entry',
        ],
        'research_paper_deleted' => [
            'points' => -15,
            'reason' => 'Deleted a research paper entry',
        ],
        'group_created' => [
            'points' => 15,
            'reason' => 'Started a new group discussion space',
        ],
        'group_updated' => [
            'points' => 5,
            'reason' => 'Updated a group discussion space',
        ],
        'group_deleted' => [
            'points' => -15,
            'reason' => 'Deleted a group discussion space',
        ],
        'job_application_clicked' => [
            'points' => 3,
            'reason' => 'Applied for a career opportunity',
        ],
        'enrolment_link_clicked' => [
            'points' => 3,
            'reason' => 'Opened an enrolment opportunity',
        ],
        'profile_updated' => [
            'points' => 5,
            'reason' => 'Updated profile information',
        ],
    ],

    'badges' => [
        ['label' => 'First-Year', 'min' => 0, 'max' => 50],
        ['label' => 'Apprentice', 'min' => 51, 'max' => 150],
        ['label' => 'Advanced Diplomat', 'min' => 151, 'max' => 299],
        ['label' => 'Industry Professional', 'min' => 300, 'max' => null],
    ],

    'reference_types' => [
        'post' => ['label' => 'Post', 'class' => Post::class],
        'project' => ['label' => 'Project', 'class' => Project::class],
        'research_paper' => ['label' => 'Research paper', 'class' => ResearchPaper::class],
        'group' => ['label' => 'Group', 'class' => Group::class],
        'job_listing' => ['label' => 'Job listing', 'class' => JobListing::class],
        'enrolment_listing' => ['label' => 'Enrolment listing', 'class' => EnrolmentListing::class],
    ],
];
