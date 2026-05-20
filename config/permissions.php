<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 2
// Last modified: 2026-05-01
// Part of: SPED LMS — Role-Based Access Control Permissions

return [
    'user' => [
        'dashboard.general',
        'account.settings',
        'role.select',
    ],

    'parent' => [
        'parent.dashboard',
        'dashboard.parent',
        'enrollment.submit',
        'enrollment.view',
        'enrollment.track',
        'iep.meeting',
        'iep.view',
        'iep.sign',
        'progress.view',
        'notifications.iep',
        'account.settings',
    ],

    'sped_teacher' => [
        'dashboard.teacher',
        'enrollment.verify',
        'student.records',
        'student.view',
        'assessment.manage',
        'assessment.conduct',
        'assessment.view',
        'iep.meeting',
        'iep.implement',
        'iep.view',
        'iep.create',
        'iep.sign',
        'learning.materials',
        'activity.logs',
        'activity.record',
        'account.settings',
    ],

    'guidance' => [
        'dashboard.guidance',
        'iep.meeting',
        'iep.schedule',
        'iep.sign',
        'iep.insights',
        'iep.view',
        'student.records',
        'student.view',
        'assessment.view',
        'account.settings',
    ],

    'principal' => [
        'dashboard.principal',
        'iep.meeting',
        'iep.sign',
        'iep.remarks',
        'iep.approve',
        'iep.view',
        'student.records',
        'student.view',
        'assessment.view',
        'reports.view',
        'staff.approve',
        'account.settings',
    ],

    'master_teacher' => [
        'dashboard.master',
        'observation.conduct',
        'cot.submit',
        'cot.view',
        'teacher.evaluate',
        'student.records',
        'student.view',
        'account.settings',
    ],

    'learner' => [
        'dashboard.learner',
        'learning.access',
        'learning.modules',
        'learning.activities',
        'learning.assignments',
        'learning.progress',
        'account.settings',
    ],

    'admin' => [
        '*', // Full access to all permissions
    ],
];
