<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-13
// Part of: SPED LMS — IEP Implementation Workspace

$pageTitle = 'IEP Workspace — ' . htmlspecialchars($iep['student_name'] ?? 'Student') . ' — SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div class="container-fluid py-3">

    <?php
    $iepLinkedLessonPlanIds = $iepLinkedLessonPlanIds ?? [];
    $iepId = (int) ($iep['id'] ?? 0);
    $navActive = 'workspace';
    $showWorkspaceLink = true;
    require __DIR__ . '/../iep/partials/iep_p5_p6_nav_bar.php';
    ?>

    <!-- ============================================================
         PAGE HEADER
         ============================================================ -->
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation"
           class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;flex-shrink:0;">
            <i class="ti ti-arrow-left me-1"></i>Back
        </a>
        <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/progress-tracker"
           class="btn btn-sm btn-outline-secondary flex-shrink-0">
            <i class="ti ti-bar-chart-line me-1"></i>Progress tracker
        </a>
        <div class="flex-grow-1 min-width-0">
            <h4 class="mb-0 fw-bold" style="color:#1e4072;">
                IEP Implementation &mdash; <?php echo htmlspecialchars($iep['student_name']); ?>
            </h4>
        </div>
        <span class="badge" style="background:#1e4072;font-size:0.78rem;padding:6px 12px;flex-shrink:0;">
            <i class="ti ti-calendar me-1"></i><?php echo htmlspecialchars($iep['school_year']); ?>
        </span>
    </div>


    <!-- ====================================================
         SECTION 1 — LESSON PLANS
         ==================================================== -->
    <div class="card mb-4" id="sectionLessonPlans">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background:#1e4072;color:#fff;border-radius:6px 6px 0 0;">
            <span class="fw-semibold">
                <i class="ti ti-book me-2"></i>Lesson Plans
            </span>
        </div>
        <div class="card-body p-3">

            <?php if (empty($lessonPlans)): ?>
                <p class="text-muted small mb-0">
                    No lesson plans yet. Add them from the <a href="<?php echo htmlspecialchars($basePath); ?>/iep/form/<?php echo (int)$iepId; ?>">IEP form</a> (IEP steps) or publish plans you create elsewhere for this learner.
                </p>
            <?php else: ?>
                <!-- Lesson plan cards -->
                <div class="row g-3" id="lessonPlansList">
                <?php foreach ($lessonPlans as $lp): ?>
                    <?php
                    $lpId     = (int)$lp['id'];
                    $lpStatus = $lp['status'] ?? 'draft';
                    $lpDomain = $lp['pdsp_domain'] ?? '';
                    $lpDoc    = $lp['document_path'] ?? '';

                    $domainLabels = [
                        'perceptuo_cognitive'    => 'Perceptuo-Cognitive',
                        'psychosocial'           => 'Psychosocial',
                        'socio_emotional'        => 'Socio-Emotional',
                        'psychomotor'            => 'Psychomotor',
                        'daily_living_skills'    => 'Daily Living Skills',
                        'communication_language' => 'Communication & Language',
                    ];
                    $domainLabel = $domainLabels[$lpDomain] ?? ucwords(str_replace('_', ' ', $lpDomain));

                    $lpMaterialCount = 0;
                    $lpActivityCount = 0;
                    foreach ($materials as $m) {
                        if ((int)$m['lesson_plan_id'] === $lpId) $lpMaterialCount++;
                    }
                    foreach ($activities as $a) {
                        if ((int)$a['lesson_plan_id'] === $lpId) $lpActivityCount++;
                    }
                    $iepLpLocked = !empty($iepLinkedLessonPlanIds) && in_array($lpId, $iepLinkedLessonPlanIds, true);
                    ?>
                    <div class="col-lg-6" id="lp-<?php echo $lpId; ?>">
                        <div class="card h-100" style="border-left:4px solid <?php echo $lpStatus === 'published' ? '#3b6d11' : '#5a6670'; ?>;">
                            <div class="card-body pb-2">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2 flex-wrap">
                                    <h6 class="fw-bold mb-0 flex-grow-1" style="color:#1e4072;font-size:0.95rem;">
                                        <?php echo htmlspecialchars($lp['title']); ?>
                                    </h6>
                                    <div class="d-flex flex-wrap gap-1 align-items-center justify-content-end">
                                        <?php if ($iepLpLocked): ?>
                                            <span class="badge" style="background:#1e4072;color:#fff;font-size:0.65rem;">From IEP</span>
                                        <?php endif; ?>
                                        <?php if ($lpStatus === 'published'): ?>
                                            <span class="badge" style="background:#3b6d11;font-size:0.7rem;">
                                                <i class="ti ti-circle-check me-1"></i>Published
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#5a6670;color:#fff;font-size:0.7rem;">Draft</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="badge" style="background:#1e4072;font-size:0.7rem;">
                                        <?php echo htmlspecialchars($domainLabel); ?>
                                    </span>
                                    <?php if ($lpDoc): ?>
                                        <span class="badge" style="background:#3b6d11;font-size:0.7rem;">
                                            <i class="ti ti-circle-check me-1"></i>Doc uploaded
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-3 mb-3 small text-muted">
                                    <span><i class="ti ti-files me-1"></i><?php echo $lpMaterialCount; ?> material<?php echo $lpMaterialCount !== 1 ? 's' : ''; ?></span>
                                    <span><i class="ti ti-activity me-1"></i><?php echo $lpActivityCount; ?> activit<?php echo $lpActivityCount !== 1 ? 'ies' : 'y'; ?></span>
                                </div>

                                <div class="d-flex flex-wrap gap-1">
                                    <?php if ($iepLpLocked): ?>
                                        <p class="small text-muted w-100 mb-1" style="border-left:3px solid #1e4072;padding-left:8px;">
                                            Linked from the IEP step table. Deleting removes the plan here; unlink on the IEP form if the step should stay without this plan.
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!$lpDoc): ?>
                                        <button class="btn btn-sm"
                                                style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="openUploadDocModal(<?php echo $lpId; ?>, '<?php echo htmlspecialchars(addslashes($lp['title'])); ?>')">
                                            <i class="ti ti-upload me-1"></i>Upload Doc
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($lpStatus === 'draft'): ?>
                                        <button class="btn btn-sm"
                                                style="background:#3b6d11;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="confirmPublish(<?php echo $lpId; ?>, '<?php echo htmlspecialchars(addslashes($lp['title'])); ?>')">
                                            <i class="ti ti-send me-1"></i>Publish
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm"
                                            style="background:#a01422;color:#fff;border:none;font-size:0.75rem;"
                                            onclick="confirmDeleteLessonPlan(<?php echo $lpId; ?>, '<?php echo htmlspecialchars(addslashes($lp['title'])); ?>')">
                                        <i class="ti ti-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div><!-- /Section 1 -->


    <!-- ====================================================
         SECTION 2 — LEARNING MATERIALS
         ==================================================== -->
    <div class="card mb-4" id="sectionMaterials">
        <div class="card-header" style="background:#1e4072;color:#fff;border-radius:6px 6px 0 0;">
            <span class="fw-semibold"><i class="ti ti-files me-2"></i>Learning Materials</span>
        </div>
        <div class="card-body p-3">

            <!-- Material type selector grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="material-type-card" id="matTypeFile" onclick="openMaterialModal('file')"
                         tabindex="0" role="button" aria-label="Upload File">
                        <div class="material-type-icon">📁</div>
                        <div class="material-type-title">Upload File</div>
                        <div class="material-type-desc">Upload a document, image, or video</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="material-type-card" id="matTypeLink" onclick="openMaterialModal('link')"
                         tabindex="0" role="button" aria-label="External Link">
                        <div class="material-type-icon">🔗</div>
                        <div class="material-type-title">External Link</div>
                        <div class="material-type-desc">Add a URL to an external resource</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="material-type-card" id="matTypeEmbed" onclick="openMaterialModal('embed')"
                         tabindex="0" role="button" aria-label="Embed Content">
                        <div class="material-type-icon">▶</div>
                        <div class="material-type-title">Embed</div>
                        <div class="material-type-desc">Embed YouTube or Google Drive content</div>
                    </div>
                </div>
            </div>

            <!-- Materials list -->
            <?php if (empty($materials)): ?>
                <div class="text-center py-4" id="materialsEmptyState">
                    <i class="ti ti-files-off" style="font-size:2.5rem;color:#ccc;"></i>
                    <p class="text-muted small mt-2 mb-0">No materials added yet.</p>
                </div>
            <?php endif; ?>

            <div id="materialsList" <?php echo empty($materials) ? 'style="display:none;"' : ''; ?>>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.85rem;">
                        <thead style="background:#1e4072;color:#fff;">
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Lesson Plan</th>
                                <th style="min-width:200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="materialsTableBody">
                        <?php foreach ($materials as $mat): ?>
                            <?php
                            $matType = $mat['material_type'] ?? 'file';
                            $matIcon = $matType === 'file' ? '📁' : ($matType === 'link' ? '🔗' : '▶');
                            $matBg   = $matType === 'file' ? '#1e4072' : ($matType === 'link' ? '#6c757d' : '#a01422');
                            ?>
                            <tr id="matRow_<?php echo (int)$mat['id']; ?>">
                                <td class="text-center"><?php echo $matIcon; ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($mat['title']); ?></td>
                                <td>
                                    <span class="badge" style="background:<?php echo $matBg; ?>;font-size:0.7rem;">
                                        <?php echo ucfirst(htmlspecialchars($matType)); ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?php echo htmlspecialchars($mat['lesson_plan_title'] ?? '—'); ?></td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <button class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="viewMaterial(<?php echo htmlspecialchars(json_encode($mat), ENT_QUOTES); ?>)">
                                            <i class="ti ti-eye me-1"></i>View
                                        </button>
                                        <button class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="openEditMaterial(<?php echo htmlspecialchars(json_encode($mat), ENT_QUOTES); ?>)">
                                            <i class="ti ti-pencil me-1"></i>Edit
                                        </button>
                                        <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="confirmDeleteMaterial(<?php echo (int)$mat['id']; ?>, '<?php echo htmlspecialchars(addslashes($mat['title'])); ?>')">
                                            <i class="ti ti-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div><!-- /Section 2 -->


    <!-- ====================================================
         SECTION 3 — ACTIVITIES
         ==================================================== -->
    <div class="card mb-4" id="sectionActivities">
        <div class="card-header" style="background:#1e4072;color:#fff;border-radius:6px 6px 0 0;">
            <span class="fw-semibold"><i class="ti ti-activity me-2"></i>Activities</span>
        </div>
        <div class="card-body p-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0" style="color:#1e4072;">Select an activity type to build:</h6>
                <button class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;" onclick="openImportActivityModal()">
                    <i class="ti ti-file-import me-1"></i>Import from CSV
                </button>
            </div>

            <!-- Activity type grid -->
            <div class="row g-2 mb-3" id="activityTypeGrid">
                <?php
                $activityTypes = [
                    ['key' => 'multiple_choice', 'icon' => 'ti-list-check',      'label' => 'Multiple Choice',   'desc' => 'Pick the correct answer'],
                    ['key' => 'true_false',      'icon' => 'ti-check',            'label' => 'True / False',      'desc' => 'True or false statement'],
                    ['key' => 'fill_in_blanks',  'icon' => 'ti-forms',            'label' => 'Fill in the Blanks','desc' => 'Complete the sentence'],
                    ['key' => 'matching',        'icon' => 'ti-arrows-exchange',  'label' => 'Matching',          'desc' => 'Match items together'],
                    ['key' => 'drag_drop_sort',  'icon' => 'ti-drag-drop',        'label' => 'Drag & Drop Sort',  'desc' => 'Arrange in correct order'],
                    ['key' => 'image_label',     'icon' => 'ti-photo',            'label' => 'Image Label',       'desc' => 'Label parts of an image'],
                    ['key' => 'flashcards',      'icon' => 'ti-cards',            'label' => 'Flashcards',        'desc' => 'Flip and learn'],
                    ['key' => 'sequencing',      'icon' => 'ti-sort-ascending',   'label' => 'Sequencing',        'desc' => 'Put events in order'],
                ];
                foreach ($activityTypes as $at):
                ?>
                <div class="col-6 col-md-3">
                    <div class="activity-type-card" id="actCard_<?php echo $at['key']; ?>"
                         data-type="<?php echo $at['key']; ?>"
                         onclick="selectActivityType('<?php echo $at['key']; ?>')"
                         tabindex="0" role="button">
                        <i class="ti <?php echo $at['icon']; ?> activity-type-icon"></i>
                        <div class="activity-type-label"><?php echo $at['label']; ?></div>
                        <div class="activity-type-desc"><?php echo $at['desc']; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Activity Builder (slides in below grid) -->
            <div id="activityBuilder" style="display:none;overflow:hidden;">
                <div class="activity-builder-inner p-3 border rounded mb-3"
                     style="border-color:#1e4072 !important;background:#f8f9fa;">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color:#1e4072;">
                            <i class="ti ti-pencil me-1"></i>
                            <span id="builderTypeLabel">Activity Builder</span>
                        </h6>
                        <button class="btn btn-sm btn-outline-secondary" onclick="closeActivityBuilder()">
                            <i class="ti ti-x me-1"></i>Cancel
                        </button>
                    </div>

                    <!-- Common fields -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Lesson Plan <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="builderLessonPlan" required>
                                <option value="">— Select lesson plan —</option>
                                <?php foreach ($lessonPlans as $lp): ?>
                                    <option value="<?php echo (int)$lp['id']; ?>">
                                        <?php echo htmlspecialchars($lp['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Activity Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="builderTitle" placeholder="Enter title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Instructions</label>
                            <textarea class="form-control form-control-sm" id="builderInstructions" rows="2"
                                      placeholder="Instructions for the learner..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Due Date <span class="text-muted">(optional)</span></label>
                            <input type="date" class="form-control form-control-sm" id="builderDueDate">
                        </div>
                        <div class="col-md-4" id="builderMaxScoreWrap">
                            <label class="form-label small fw-semibold">Max Score</label>
                            <input type="number" class="form-control form-control-sm" id="builderMaxScore"
                                   min="0" value="10" placeholder="10">
                        </div>
                    </div>

                    <!-- Type-specific builder area -->
                    <div id="builderTypeArea"></div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;"
                                onclick="saveActivity()">
                            <i class="ti ti-device-floppy me-1"></i>Save Activity
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="closeActivityBuilder()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activities list -->
            <?php if (empty($activities)): ?>
                <div class="text-center py-4" id="activitiesEmptyState">
                    <i class="ti ti-mood-empty" style="font-size:2.5rem;color:#ccc;"></i>
                    <p class="text-muted small mt-2 mb-0">No activities added yet.</p>
                </div>
            <?php endif; ?>

            <div id="activitiesList" <?php echo empty($activities) ? 'style="display:none;"' : ''; ?>>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.85rem;">
                        <thead style="background:#1e4072;color:#fff;">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Lesson Plan</th>
                                <th>Due Date</th>
                                <th>Max Score</th>
                                <th style="min-width:200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="activitiesTableBody">
                        <?php foreach ($activities as $act): ?>
                            <?php
                            $actType = $act['activity_type'] ?? '';
                            $actTypeColors = [
                                'multiple_choice' => '#1e4072',
                                'true_false'      => '#3b6d11',
                                'fill_in_blanks'  => '#a01422',
                                'matching'        => '#6c757d',
                                'drag_drop_sort'  => '#e67e22',
                                'image_label'     => '#8e44ad',
                                'flashcards'      => '#2980b9',
                                'sequencing'      => '#16a085',
                            ];
                            $actColor = $actTypeColors[$actType] ?? '#6c757d';
                            $actTypeLabel = ucwords(str_replace('_', ' ', $actType));
                            ?>
                            <tr id="actRow_<?php echo (int)$act['id']; ?>">
                                <td class="fw-semibold"><?php echo htmlspecialchars($act['title']); ?></td>
                                <td>
                                    <span class="badge" style="background:<?php echo $actColor; ?>;font-size:0.7rem;">
                                        <?php echo htmlspecialchars($actTypeLabel); ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?php echo htmlspecialchars($act['lesson_plan_title'] ?? '—'); ?></td>
                                <td class="text-muted"><?php echo $act['due_date'] ? htmlspecialchars($act['due_date']) : '—'; ?></td>
                                <td><?php echo (int)($act['max_score'] ?? 0); ?></td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <button class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="viewActivity(<?php echo htmlspecialchars(json_encode($act), ENT_QUOTES); ?>)">
                                            <i class="ti ti-eye me-1"></i>View
                                        </button>
                                        <button class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="openEditActivity(<?php echo htmlspecialchars(json_encode($act), ENT_QUOTES); ?>)">
                                            <i class="ti ti-pencil me-1"></i>Edit
                                        </button>
                                        <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="confirmDeleteActivity(<?php echo (int)$act['id']; ?>, '<?php echo htmlspecialchars(addslashes($act['title'])); ?>')">
                                            <i class="ti ti-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div><!-- /Section 3 -->


    <!-- ====================================================
         SECTION 4 — PUBLISH & SUBMISSIONS
         ==================================================== -->
    <div class="card mb-4" id="sectionPublish">
        <div class="card-header" style="background:#1e4072;color:#fff;border-radius:6px 6px 0 0;">
            <span class="fw-semibold"><i class="ti ti-send me-2"></i>Publish &amp; Submissions</span>
        </div>
        <div class="card-body p-3">

            <?php
            $draftPlans = array_filter($lessonPlans, fn($lp) => ($lp['status'] ?? '') === 'draft');
            ?>
            <?php if (!empty($draftPlans)): ?>
                <h6 class="fw-semibold mb-3" style="color:#1e4072;">Draft Lesson Plans</h6>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <?php foreach ($draftPlans as $dp): ?>
                        <button class="btn btn-sm"
                                style="background:#3b6d11;color:#fff;border:none;"
                                onclick="confirmPublish(<?php echo (int)$dp['id']; ?>, '<?php echo htmlspecialchars(addslashes($dp['title'])); ?>')">
                            <i class="ti ti-send me-1"></i>Publish: <?php echo htmlspecialchars($dp['title']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h6 class="fw-semibold mb-3" style="color:#1e4072;">Submissions</h6>
            <?php 
            $hasSubmissions = false;
            foreach ($submissionsByLp as $lpId => $subs) {
                if (!empty($subs)) $hasSubmissions = true;
            }
            ?>
            <?php if (!$hasSubmissions): ?>
                <div class="text-center py-4">
                    <i class="ti ti-inbox" style="font-size:2.5rem;color:#ccc;"></i>
                    <p class="text-muted small mt-2 mb-0">No submissions yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.85rem;">
                        <thead style="background:#1e4072;color:#fff;">
                            <tr>
                                <th>Learner</th>
                                <th>Activity</th>
                                <th>Submitted At</th>
                                <th>Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissionsByLp as $lpId => $subs): ?>
                                <?php foreach ($subs as $sub): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sub['student_name'] ?? 'Learner'); ?></td>
                                        <td><?php echo htmlspecialchars($sub['activity_title']); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($sub['submitted_at'])); ?></td>
                                        <td>
                                            <?php
                                            $dm = (int)($sub['display_max_score'] ?? $sub['activity_max_score'] ?? 0);
                                            $gmx = (int)($sub['graded_max_score'] ?? 0);
                                            ?>
                                            <?php if ($sub['graded_score'] !== null && $sub['graded_score'] !== ''): ?>
                                                <span class="badge bg-success"><?php echo (int)$sub['graded_score']; ?> / <?php echo $gmx > 0 ? $gmx : $dm; ?></span>
                                            <?php elseif ($sub['auto_score'] !== null && $sub['auto_score'] !== ''): ?>
                                                <span class="badge" style="background:#1e4072;"><?php echo (int)$sub['auto_score']; ?> / <?php echo $dm > 0 ? $dm : (int)($sub['activity_max_score'] ?? 0); ?> <span class="opacity-75">(auto)</span></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">— / <?php echo $dm > 0 ? $dm : (int)($sub['activity_max_score'] ?? 0); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep/implementation/submission/<?php echo $sub['activity_id']; ?>?student_id=<?php echo $sub['student_id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;">
                                                <i class="ti ti-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div><!-- /Section 4 -->

</div><!-- /container-fluid -->
</div><!-- /main-content -->

<?php require __DIR__ . '/../iep/partials/iep_pdsp_reference_drawer.php'; ?>


<!-- ================================================================
     MODAL: Upload Lesson Document
     ================================================================ -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="uploadDocModalLabel">
                    <i class="ti ti-upload me-2"></i>Upload Lesson Document
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Uploading for: <strong id="uploadDocLpTitle"></strong></p>
                <input type="hidden" id="uploadDocLpId">
                <?php
                $fieldName     = 'lesson_doc_upload';
                $acceptedTypes = '.jpg,.jpeg,.png,.pdf';
                $maxSize       = 10;
                $showCamera    = true;
                require __DIR__ . '/../components/upload-zone.php';
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#a01422;color:#fff;border:none;"
                        onclick="submitUploadDoc()">
                    <i class="ti ti-upload me-1"></i>Upload
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ================================================================
     MODAL: Add Material — File Upload
     ================================================================ -->
<div class="modal fade" id="matFileModal" tabindex="-1" aria-labelledby="matFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="matFileModalLabel">
                    <i class="ti ti-upload me-2"></i>Upload File Material
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lesson Plan <span class="text-danger">*</span></label>
                    <select class="form-select" id="matFileLessonPlan" required>
                        <option value="">— Select lesson plan —</option>
                        <?php foreach ($lessonPlans as $lp): ?>
                            <option value="<?php echo (int)$lp['id']; ?>"><?php echo htmlspecialchars($lp['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="matFileTitle" placeholder="Material title" required>
                </div>
                <!-- Simple file input — no upload-zone component to avoid auto-reload side effects -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small">File <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="matFileInput"
                           accept=".jpg,.jpeg,.png,.pdf,.mp4" required>
                    <div class="form-text">Accepted: JPG, PNG, PDF, MP4 · Max 50MB for video, 10MB for others</div>
                    <div id="matFileError" class="text-danger small mt-1" style="display:none;"></div>
                </div>
                <!-- Camera option (mobile only) -->
                <div class="mb-2 d-none" id="matCameraWrap">
                    <label class="form-label fw-semibold small">Or take a photo</label>
                    <input type="file" class="form-control" id="matCameraInput"
                           accept="image/*" capture="environment">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#a01422;color:#fff;border:none;"
                        onclick="submitMaterialFile()">
                    <i class="ti ti-device-floppy me-1"></i>Save Material
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Add Material — External Link
     ================================================================ -->
<div class="modal fade" id="matLinkModal" tabindex="-1" aria-labelledby="matLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="matLinkModalLabel">
                    <i class="ti ti-link me-2"></i>Add External Link
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lesson Plan <span class="text-danger">*</span></label>
                    <select class="form-select" id="matLinkLessonPlan" required>
                        <option value="">— Select lesson plan —</option>
                        <?php foreach ($lessonPlans as $lp): ?>
                            <option value="<?php echo (int)$lp['id']; ?>"><?php echo htmlspecialchars($lp['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="matLinkTitle" placeholder="Material title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">URL <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="matLinkUrl" placeholder="https://..." required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#a01422;color:#fff;border:none;"
                        onclick="submitMaterialLink()">
                    <i class="ti ti-device-floppy me-1"></i>Save Material
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Add Material — Embed
     ================================================================ -->
<div class="modal fade" id="matEmbedModal" tabindex="-1" aria-labelledby="matEmbedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="matEmbedModalLabel">
                    <i class="ti ti-player-play me-2"></i>Embed Content
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lesson Plan <span class="text-danger">*</span></label>
                    <select class="form-select" id="matEmbedLessonPlan" required>
                        <option value="">— Select lesson plan —</option>
                        <?php foreach ($lessonPlans as $lp): ?>
                            <option value="<?php echo (int)$lp['id']; ?>"><?php echo htmlspecialchars($lp['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="matEmbedTitle" placeholder="Material title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">YouTube or Google Drive URL <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="matEmbedUrl"
                           placeholder="https://youtube.com/... or https://drive.google.com/..."
                           oninput="detectEmbedType(this.value)" required>
                    <div class="form-text" id="matEmbedTypeHint"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#a01422;color:#fff;border:none;"
                        onclick="submitMaterialEmbed()">
                    <i class="ti ti-device-floppy me-1"></i>Save Material
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Import Activity CSV
     ================================================================ -->
<div class="modal fade" id="importActivityModal" tabindex="-1" aria-labelledby="importActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#3b6d11;color:#fff;">
                <h5 class="modal-title" id="importActivityModalLabel">
                    <i class="ti ti-file-import me-2"></i>Import Activity CSV
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lesson Plan <span class="text-danger">*</span></label>
                    <select class="form-select" id="importActivityLessonPlan" required>
                        <option value="">— Select lesson plan —</option>
                        <?php foreach ($lessonPlans as $lp): ?>
                            <option value="<?php echo (int)$lp['id']; ?>"><?php echo htmlspecialchars($lp['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">CSV File <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="importActivityFile" accept=".csv" required>
                    <div class="form-text">Upload a simple CSV to automatically generate activities. <br>
                    Format: <strong>title, instructions, type, max_score, question1, q1_option1, q1_option2, q1_correct...</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="#" class="btn btn-sm btn-outline-secondary" onclick="downloadActivityCsvTemplate(event)">
                    <i class="ti ti-download me-1"></i>Sample Template
                </a>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;" onclick="submitImportActivity()">
                        <i class="ti ti-upload me-1"></i>Import
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- ================================================================
     MODAL: View Material
     ================================================================ -->
<div class="modal fade" id="viewMaterialModal" tabindex="-1" aria-labelledby="viewMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="viewMaterialModalLabel"><i class="ti ti-eye me-2"></i><span id="vMatTitle">Material</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewMaterialBody">
                <!-- populated by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Edit Material
     ================================================================ -->
<div class="modal fade" id="editMaterialModal" tabindex="-1" aria-labelledby="editMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#3b6d11;color:#fff;">
                <h5 class="modal-title" id="editMaterialModalLabel"><i class="ti ti-pencil me-2"></i>Edit Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editMatId">
                <input type="hidden" id="editMatType">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editMatTitle" required>
                </div>
                <!-- URL field: shown for link/embed -->
                <div class="mb-3" id="editMatUrlWrap" style="display:none;">
                    <label class="form-label fw-semibold small">URL</label>
                    <input type="url" class="form-control" id="editMatUrl" placeholder="https://...">
                </div>
                <!-- File replacement: shown for file type -->
                <div class="mb-3" id="editMatFileWrap" style="display:none;">
                    <label class="form-label fw-semibold small">Replace File <span class="text-muted">(optional)</span></label>
                    <input type="file" class="form-control" id="editMatFile" accept=".jpg,.jpeg,.png,.pdf,.mp4">
                    <div class="form-text">Leave blank to keep existing file. Max 10MB (50MB for MP4).</div>
                </div>
                <div id="editMatError" class="alert alert-danger" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#3b6d11;color:#fff;border:none;" onclick="submitEditMaterial()">
                    <i class="ti ti-device-floppy me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: View Activity
     ================================================================ -->
<div class="modal fade" id="viewActivityModal" tabindex="-1" aria-labelledby="viewActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="viewActivityModalLabel"><i class="ti ti-eye me-2"></i><span id="vActTitle">Activity</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewActivityBody">
                <!-- populated by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Edit Activity
     ================================================================ -->
<div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#3b6d11;color:#fff;">
                <h5 class="modal-title" id="editActivityModalLabel"><i class="ti ti-pencil me-2"></i>Edit Activity</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editActId">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editActTitle" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Instructions</label>
                    <textarea class="form-control" id="editActInstructions" rows="3" placeholder="Instructions for the learner..."></textarea>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Max Score</label>
                        <input type="number" class="form-control" id="editActMaxScore" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Due Date <span class="text-muted">(optional)</span></label>
                        <input type="date" class="form-control" id="editActDueDate">
                    </div>
                </div>
                <div id="editActError" class="alert alert-danger mt-3" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#3b6d11;color:#fff;border:none;" onclick="submitEditActivity()">
                    <i class="ti ti-device-floppy me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ================================================================
// VIEW / EDIT MATERIALS
// ================================================================
// NOTE: BASE is declared in the main JS block below — used here directly

function viewMaterial(mat) {
    document.getElementById('vMatTitle').textContent = mat.title;
    const body = document.getElementById('viewMaterialBody');

    if (mat.material_type === 'file' && mat.file_path) {
        const url = BASE + '/' + mat.file_path;
        const ext = mat.file_path.split('.').pop().toLowerCase();
        if (['jpg','jpeg','png','gif'].includes(ext)) {
            body.innerHTML = `<img src="${url}" class="img-fluid rounded" alt="${mat.title}">`;
        } else if (ext === 'pdf') {
            body.innerHTML = `<iframe src="${url}" style="width:100%;height:500px;border:none;"></iframe>
                <div class="text-center mt-2"><a href="${url}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="ti ti-external-link me-1"></i>Open in new tab</a></div>`;
        } else if (ext === 'mp4') {
            body.innerHTML = `<video controls class="w-100 rounded"><source src="${url}" type="video/mp4">Your browser does not support video.</video>`;
        } else {
            body.innerHTML = `<div class="text-center py-4"><i class="ti ti-file" style="font-size:3rem;color:#6c757d;"></i><p class="mt-2">Preview not available. <a href="${url}" target="_blank">Download file</a></p></div>`;
        }
    } else if (mat.material_type === 'link' && mat.external_url) {
        body.innerHTML = `<div class="text-center py-4">
            <i class="ti ti-external-link" style="font-size:3rem;color:#1e4072;"></i>
            <p class="mt-2 mb-3">External link: <strong>${mat.external_url}</strong></p>
            <a href="${mat.external_url}" target="_blank" rel="noopener" class="btn" style="background:#1e4072;color:#fff;border:none;">
                <i class="ti ti-external-link me-1"></i>Open Link
            </a></div>`;
    } else if (mat.material_type === 'embed' && mat.external_url) {
        let embedUrl = mat.external_url;
        const ytMatch = embedUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/);
        if (ytMatch) embedUrl = `https://www.youtube.com/embed/${ytMatch[1]}`;
        const gdMatch = embedUrl.match(/\/d\/([^/]+)/);
        if (gdMatch && embedUrl.includes('drive.google.com')) embedUrl = `https://drive.google.com/file/d/${gdMatch[1]}/preview`;
        body.innerHTML = `<div class="ratio ratio-16x9"><iframe src="${embedUrl}" allowfullscreen frameborder="0"></iframe></div>`;
    } else {
        body.innerHTML = '<p class="text-muted text-center py-3">No preview available for this material.</p>';
    }

    new bootstrap.Modal(document.getElementById('viewMaterialModal')).show();
}

function openEditMaterial(mat) {
    document.getElementById('editMatId').value   = mat.id;
    document.getElementById('editMatType').value  = mat.material_type;
    document.getElementById('editMatTitle').value = mat.title;
    document.getElementById('editMatError').style.display = 'none';
    document.getElementById('editMatFile').value  = '';

    const urlWrap  = document.getElementById('editMatUrlWrap');
    const fileWrap = document.getElementById('editMatFileWrap');
    if (mat.material_type === 'file') {
        urlWrap.style.display  = 'none';
        fileWrap.style.display = 'block';
    } else {
        urlWrap.style.display  = 'block';
        fileWrap.style.display = 'none';
        document.getElementById('editMatUrl').value = mat.external_url || '';
    }
    new bootstrap.Modal(document.getElementById('editMaterialModal')).show();
}

function submitEditMaterial() {
    const id    = document.getElementById('editMatId').value;
    const type  = document.getElementById('editMatType').value;
    const title = document.getElementById('editMatTitle').value.trim();
    const errEl = document.getElementById('editMatError');
    errEl.style.display = 'none';
    if (!title) { errEl.textContent = 'Title is required.'; errEl.style.display = 'block'; return; }

    const fileInput = document.getElementById('editMatFile');
    const hasFile   = fileInput.files && fileInput.files.length > 0;

    if (type === 'file' || hasFile) {
        const fd = new FormData();
        fd.append('title', title);
        if (hasFile) fd.append('file', fileInput.files[0]);
        fetch(`${BASE}/iep/implementation/material/${id}/edit`, { method: 'POST', body: fd })
            .then(r => r.json()).then(handleEditMaterialResponse).catch(handleEditError);
    } else {
        const url = document.getElementById('editMatUrl').value.trim();
        fetch(`${BASE}/iep/implementation/material/${id}/edit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, external_url: url })
        }).then(r => r.json()).then(handleEditMaterialResponse).catch(handleEditError);
    }
}

function handleEditMaterialResponse(data) {
    if (!data.success) {
        const errEl = document.getElementById('editMatError');
        errEl.textContent = data.message || 'Save failed.';
        errEl.style.display = 'block';
        return;
    }
    bootstrap.Modal.getInstance(document.getElementById('editMaterialModal')).hide();
    const mat = data.material;
    const row = document.getElementById('matRow_' + mat.id);
    if (row) row.querySelectorAll('td')[1].textContent = mat.title;
}

function handleEditError(err) { alert('Network error: ' + err.message); }

// ================================================================
// VIEW / EDIT ACTIVITIES
// ================================================================
const actTypeLabels = {
    multiple_choice: 'Multiple Choice', true_false: 'True / False',
    fill_in_blanks: 'Fill in the Blanks', matching: 'Matching',
    drag_drop_sort: 'Drag & Drop Sort', image_label: 'Image Label',
    flashcards: 'Flashcards', sequencing: 'Sequencing'
};

function viewActivity(act) {
    document.getElementById('vActTitle').textContent = act.title;
    const body = document.getElementById('viewActivityBody');
    let data = act.activity_data;
    if (typeof data === 'string') { try { data = JSON.parse(data); } catch(e) { data = {}; } }

    let html = `
        <div class="mb-3 d-flex flex-wrap gap-2">
            <span class="badge" style="background:#1e4072;">${actTypeLabels[act.activity_type] || act.activity_type}</span>
            ${act.max_score ? `<span class="badge bg-secondary">Max Score: ${act.max_score}</span>` : ''}
            ${act.due_date  ? `<span class="badge bg-warning text-dark">Due: ${act.due_date}</span>` : ''}
        </div>
        ${act.instructions ? `<div class="alert alert-light small mb-3"><strong>Instructions:</strong> ${act.instructions}</div>` : ''}
        <hr>
        <h6 class="fw-semibold mb-3" style="color:#1e4072;">Activity Content</h6>`;

    switch (act.activity_type) {
        case 'multiple_choice':
            (data.questions || []).forEach((q, qi) => {
                html += `<div class="mb-3"><strong>Q${qi+1}:</strong> ${q.text}<ul class="mt-1">`;
                (q.options || []).forEach(o => {
                    html += `<li style="color:${o.is_correct ? '#3b6d11' : 'inherit'}">${o.is_correct ? '✓ ' : ''}${o.text}</li>`;
                });
                html += '</ul></div>';
            });
            break;
        case 'true_false':
            html += `<p><strong>Statement:</strong> ${data.statement || ''}</p>`;
            html += `<p><strong>Answer:</strong> <span class="badge bg-success">${(data.correct_answer || '').toUpperCase()}</span></p>`;
            break;
        case 'fill_in_blanks':
            (data.sentences || []).forEach((s, i) => {
                html += `<div class="mb-2"><strong>${i+1}.</strong> ${s.text} <span class="badge bg-success ms-1">${(s.answers || []).join(' / ')}</span></div>`;
            });
            break;
        case 'matching':
            html += '<table class="table table-sm table-bordered"><thead><tr><th>Left</th><th>Right</th></tr></thead><tbody>';
            (data.pairs || []).forEach(p => { html += `<tr><td>${p.left}</td><td>${p.right}</td></tr>`; });
            html += '</tbody></table>';
            break;
        case 'drag_drop_sort': case 'sequencing':
            (data.items || []).forEach((item, i) => { html += `<div class="mb-1"><span class="badge bg-secondary me-2">${i+1}</span>${item}</div>`; });
            break;
        case 'flashcards':
            (data.cards || []).forEach(c => {
                html += `<div class="mb-2 p-2 border rounded"><strong>Front:</strong> ${c.front} &nbsp;→&nbsp; <strong>Back:</strong> ${c.back}</div>`;
            });
            break;
        case 'image_label':
            if (data.image_path) html += `<img src="${BASE}/${data.image_path}" class="img-fluid rounded mb-2" alt="activity image">`;
            (data.labels || []).forEach(l => { html += `<div class="small text-muted">Label at (${l.x},${l.y}): <strong>${l.answer}</strong></div>`; });
            break;
        default:
            html += `<pre class="bg-light p-2 rounded small">${JSON.stringify(data, null, 2)}</pre>`;
    }

    body.innerHTML = html;
    new bootstrap.Modal(document.getElementById('viewActivityModal')).show();
}

function openEditActivity(act) {
    document.getElementById('editActId').value           = act.id;
    document.getElementById('editActTitle').value        = act.title;
    document.getElementById('editActInstructions').value = act.instructions || '';
    document.getElementById('editActMaxScore').value     = act.max_score || 0;
    document.getElementById('editActDueDate').value      = act.due_date ? act.due_date.substring(0,10) : '';
    document.getElementById('editActError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('editActivityModal')).show();
}

function submitEditActivity() {
    const id           = document.getElementById('editActId').value;
    const title        = document.getElementById('editActTitle').value.trim();
    const instructions = document.getElementById('editActInstructions').value.trim();
    const maxScore     = parseInt(document.getElementById('editActMaxScore').value) || 0;
    const dueDate      = document.getElementById('editActDueDate').value || null;
    const errEl        = document.getElementById('editActError');
    errEl.style.display = 'none';
    if (!title) { errEl.textContent = 'Title is required.'; errEl.style.display = 'block'; return; }

    fetch(`${BASE}/iep/implementation/activity/${id}/edit`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, instructions, max_score: maxScore, due_date: dueDate })
    })
    .then(r => r.json())
    .then(resp => {
        if (!resp.success) { errEl.textContent = resp.message || 'Save failed.'; errEl.style.display = 'block'; return; }
        bootstrap.Modal.getInstance(document.getElementById('editActivityModal')).hide();
        const act = resp.activity;
        const row = document.getElementById('actRow_' + act.id);
        if (row) {
            const tds = row.querySelectorAll('td');
            tds[0].textContent = act.title;
            tds[3].textContent = act.due_date || '—';
            tds[4].textContent = act.max_score || 0;
        }
    })
    .catch(err => { errEl.textContent = 'Network error: ' + err.message; errEl.style.display = 'block'; });
}
</script>

<!-- ================================================================
     STYLES
     ================================================================ -->
<style>
/* ---- Material type cards ---- */
.material-type-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 20px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    background: #fff;
    user-select: none;
}
.material-type-card:hover,
.material-type-card:focus {
    border-color: #a01422;
    box-shadow: 0 0 0 3px rgba(160,20,34,0.12);
    transform: translateY(-2px);
    outline: none;
}
.material-type-icon { font-size: 2rem; margin-bottom: 8px; }
.material-type-title { font-weight: 700; color: #1e4072; font-size: 0.9rem; margin-bottom: 4px; }
.material-type-desc  { font-size: 0.78rem; color: #6c757d; }

/* ---- Activity type cards ---- */
.activity-type-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 14px 10px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    background: #fff;
    user-select: none;
    height: 100%;
}
.activity-type-card:hover,
.activity-type-card:focus {
    border-color: #a01422;
    box-shadow: 0 0 0 3px rgba(160,20,34,0.12);
    outline: none;
}
.activity-type-card.selected {
    border-color: #a01422;
    background: #fff5f5;
    box-shadow: 0 0 0 3px rgba(160,20,34,0.18);
}
.activity-type-icon { font-size: 1.6rem; color: #1e4072; margin-bottom: 6px; display: block; }
.activity-type-label { font-weight: 700; color: #1e4072; font-size: 0.82rem; margin-bottom: 3px; }
.activity-type-desc  { font-size: 0.72rem; color: #6c757d; }

/* ---- Builder drag handles ---- */
.drag-handle {
    cursor: grab;
    color: #adb5bd;
    padding: 0 6px;
    font-size: 1.1rem;
}
.drag-handle:active { cursor: grabbing; }
.builder-item-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 6px 8px;
}
.builder-item-row.drag-over { border-color: #a01422; background: #fff5f5; }

/* ---- Lesson plan option cards ---- */
.lp-option-card.selected-option {
    border-color: #a01422 !important;
    background: #fff5f5;
}

/* ---- Image label canvas ---- */
#imageLabelCanvas {
    position: relative;
    display: inline-block;
    cursor: crosshair;
}
#imageLabelCanvas img { max-width: 100%; border-radius: 6px; display: block; }
.label-marker {
    position: absolute;
    width: 22px;
    height: 22px;
    background: #a01422;
    border-radius: 50%;
    border: 2px solid #fff;
    transform: translate(-50%, -50%);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .lp-option-card { margin-bottom: 8px; }
    #matCameraWrap { display: block !important; }
}
</style>


<!-- ================================================================
     JAVASCRIPT — Part 1: Config, Helpers, Lesson Plans
     ================================================================ -->
<script>
'use strict';

const BASE   = '<?php echo addslashes($basePath); ?>';
const IEP_ID = <?php echo (int)$iep['id']; ?>;

/* ----------------------------------------------------------------
   Helpers
   ---------------------------------------------------------------- */
function showToast(icon, title, text) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        text: text || '',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

function showLoading(title) {
    Swal.fire({
        title: title || 'Please wait…',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
    });
}

async function postJSON(url, data) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

async function postForm(url, formData) {
    const res = await fetch(url, { method: 'POST', body: formData });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

/* ----------------------------------------------------------------
   Lesson Plan — Upload Document (from card button)
   ---------------------------------------------------------------- */
function openUploadDocModal(lpId, lpTitle) {
    document.getElementById('uploadDocLpId').value = lpId;
    document.getElementById('uploadDocLpTitle').textContent = lpTitle;
    new bootstrap.Modal(document.getElementById('uploadDocModal')).show();
}

async function submitUploadDoc() {
    const lpId = document.getElementById('uploadDocLpId').value;
    const modal = document.getElementById('uploadDocModal');
    const fileInput = modal.querySelector('input[type="file"]');

    if (!fileInput || !fileInput.files.length) {
        Swal.fire({ icon: 'warning', title: 'No file selected', text: 'Please choose a file to upload.', confirmButtonColor: '#a01422' });
        return;
    }

    const fd = new FormData();
    fd.append('document', fileInput.files[0]);
    fd.append('lesson_plan_id', lpId);
    fd.append('iep_id', IEP_ID);

    showLoading('Uploading document…');

    try {
        const data = await postForm(BASE + '/iep/implementation/lesson-plan/upload-doc', fd);
        Swal.close();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('uploadDocModal'))?.hide();
            showToast('success', 'Document uploaded!');
            setTimeout(() => location.reload(), 800);
        } else {
            Swal.fire({ icon: 'error', title: 'Upload failed', text: data.message || 'Could not upload document.', confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Lesson Plan — Publish
   ---------------------------------------------------------------- */
function confirmPublish(lpId, lpTitle) {
    Swal.fire({
        icon: 'question',
        title: 'Publish lesson plan?',
        html: 'This will make <strong>' + lpTitle + '</strong> visible to assigned learners.',
        showCancelButton: true,
        confirmButtonColor: '#3b6d11',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="ti ti-send"></i> Publish',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        showLoading('Publishing…');
        try {
            const data = await postJSON(BASE + '/iep/implementation/lesson-plan/' + lpId + '/publish', { lesson_plan_id: lpId });
            Swal.close();
            if (data.success) {
                const card = document.getElementById('lp-' + lpId);
                if (card) {
                    const badge = card.querySelector('.badge.bg-secondary');
                    if (badge) {
                        badge.style.background = '#3b6d11';
                        badge.className = 'badge';
                        badge.innerHTML = '<i class="ti ti-circle-check me-1"></i>Published';
                    }
                    const publishBtn = card.querySelector('button[onclick*="confirmPublish"]');
                    if (publishBtn) publishBtn.remove();
                    const cardEl = card.querySelector('.card');
                    if (cardEl) cardEl.style.borderLeftColor = '#3b6d11';
                }
                showToast('success', 'Published!', lpTitle + ' is now live.');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
        }
    });
}

/* ----------------------------------------------------------------
   Lesson Plan — Delete
   ---------------------------------------------------------------- */
function confirmDeleteLessonPlan(lpId, lpTitle) {
    Swal.fire({
        icon: 'warning',
        title: 'Delete lesson plan?',
        html: 'This will permanently delete <strong>' + lpTitle + '</strong> and all its materials and activities.',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        showLoading('Deleting…');
        try {
            const data = await postJSON(BASE + '/iep/implementation/lesson-plan/' + lpId + '/delete', { lesson_plan_id: lpId });
            Swal.close();
            if (data.success) {
                const card = document.getElementById('lp-' + lpId);
                if (card) card.remove();
                showToast('success', 'Deleted', lpTitle + ' was removed.');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
        }
    });
}
</script>


<!-- ================================================================
     JAVASCRIPT — Part 2: Materials
     ================================================================ -->
<script>
/* ----------------------------------------------------------------
   Materials — open modal by type
   ---------------------------------------------------------------- */
function openMaterialModal(type) {
    const modalIds = { file: 'matFileModal', link: 'matLinkModal', embed: 'matEmbedModal' };
    const id = modalIds[type];
    if (id) new bootstrap.Modal(document.getElementById(id)).show();
}

/* ----------------------------------------------------------------
   Materials — detect embed type
   ---------------------------------------------------------------- */
function detectEmbedType(url) {
    const hint = document.getElementById('matEmbedTypeHint');
    if (!hint) return;
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        hint.textContent = '✓ Detected: YouTube';
        hint.style.color = '#3b6d11';
    } else if (url.includes('drive.google.com')) {
        hint.textContent = '✓ Detected: Google Drive';
        hint.style.color = '#3b6d11';
    } else if (url.length > 5) {
        hint.textContent = 'Other embed URL';
        hint.style.color = '#6c757d';
    } else {
        hint.textContent = '';
    }
}

/* ----------------------------------------------------------------
   Materials — append row to table
   ---------------------------------------------------------------- */
function appendMaterialRow(mat) {
    const typeIcons  = { file: '📁', link: '🔗', embed: '▶' };
    const typeBg     = { file: '#1e4072', link: '#6c757d', embed: '#a01422' };
    const icon       = typeIcons[mat.material_type] || '📁';
    const bg         = typeBg[mat.material_type]    || '#6c757d';
    const typeLabel  = mat.material_type.charAt(0).toUpperCase() + mat.material_type.slice(1);

    // Find lesson plan title
    const lpSel = document.getElementById('matFileLessonPlan') ||
                  document.getElementById('matLinkLessonPlan') ||
                  document.getElementById('matEmbedLessonPlan');
    let lpTitle = '—';
    if (lpSel) {
        const opt = lpSel.querySelector('option[value="' + mat.lesson_plan_id + '"]');
        if (opt) lpTitle = opt.textContent.trim();
    }

    const tbody = document.getElementById('materialsTableBody');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.id = 'matRow_' + mat.id;
    tr.innerHTML = `
        <td class="text-center">${icon}</td>
        <td>${escHtml(mat.title)}</td>
        <td><span class="badge" style="background:${bg};font-size:0.7rem;">${typeLabel}</span></td>
        <td class="text-muted">${escHtml(lpTitle)}</td>
        <td>
            <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.75rem;"
                    onclick="confirmDeleteMaterial(${mat.id}, '${escAttr(mat.title)}')">
                <i class="ti ti-trash me-1"></i>Delete
            </button>
        </td>`;
    tbody.appendChild(tr);

    // Show table, hide empty state
    document.getElementById('materialsList').style.display = '';
    const empty = document.getElementById('materialsEmptyState');
    if (empty) empty.style.display = 'none';
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}
function escAttr(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

/* ----------------------------------------------------------------
   Materials — Submit File
   ---------------------------------------------------------------- */
async function submitMaterialFile() {
    const lpId     = document.getElementById('matFileLessonPlan').value;
    const title    = document.getElementById('matFileTitle').value.trim();
    const fileInput = document.getElementById('matFileInput');
    const cameraInput = document.getElementById('matCameraInput');
    const errEl    = document.getElementById('matFileError');

    // Hide previous error
    if (errEl) errEl.style.display = 'none';

    if (!lpId)  { Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#a01422' }); return; }
    if (!title) { Swal.fire({ icon: 'warning', title: 'Enter a title', confirmButtonColor: '#a01422' }); return; }

    // Determine which input has a file
    let selectedFile = null;
    if (fileInput && fileInput.files && fileInput.files.length > 0) {
        selectedFile = fileInput.files[0];
    } else if (cameraInput && cameraInput.files && cameraInput.files.length > 0) {
        selectedFile = cameraInput.files[0];
    }

    if (!selectedFile) {
        Swal.fire({ icon: 'warning', title: 'No file selected', text: 'Please choose a file to upload.', confirmButtonColor: '#a01422' });
        return;
    }

    // Validate file size
    const ext = selectedFile.name.split('.').pop().toLowerCase();
    const maxBytes = ext === 'mp4' ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
    if (selectedFile.size > maxBytes) {
        const limit = ext === 'mp4' ? '50MB' : '10MB';
        Swal.fire({ icon: 'warning', title: 'File too large', text: 'Maximum size is ' + limit + ' for this file type.', confirmButtonColor: '#a01422' });
        return;
    }

    // Validate file type
    const allowed = ['jpg', 'jpeg', 'png', 'pdf', 'mp4'];
    if (!allowed.includes(ext)) {
        Swal.fire({ icon: 'warning', title: 'Invalid file type', text: 'Only JPG, PNG, PDF, and MP4 are allowed.', confirmButtonColor: '#a01422' });
        return;
    }

    const fd = new FormData();
    fd.append('lesson_plan_id', lpId);
    fd.append('material_type', 'file');
    fd.append('title', title);
    fd.append('file', selectedFile);

    showLoading('Uploading material…');
    try {
        const data = await postForm(BASE + '/iep/implementation/material/add', fd);
        Swal.close();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('matFileModal'))?.hide();
            appendMaterialRow(data.material);
            showToast('success', 'Material added!');
            // Reset form
            document.getElementById('matFileTitle').value = '';
            document.getElementById('matFileLessonPlan').value = '';
            if (fileInput) fileInput.value = '';
            if (cameraInput) cameraInput.value = '';
        } else {
            Swal.fire({ icon: 'error', title: 'Upload failed', text: data.message || 'Could not upload file.', confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Materials — Submit Link
   ---------------------------------------------------------------- */
async function submitMaterialLink() {
    const lpId  = document.getElementById('matLinkLessonPlan').value;
    const title = document.getElementById('matLinkTitle').value.trim();
    const url   = document.getElementById('matLinkUrl').value.trim();

    if (!lpId)  { Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#a01422' }); return; }
    if (!title) { Swal.fire({ icon: 'warning', title: 'Enter a title', confirmButtonColor: '#a01422' }); return; }
    if (!url)   { Swal.fire({ icon: 'warning', title: 'Enter a URL', confirmButtonColor: '#a01422' }); return; }

    showLoading('Saving material…');
    try {
        const data = await postJSON(BASE + '/iep/implementation/material/add', {
            lesson_plan_id: parseInt(lpId), material_type: 'link', title, external_url: url,
        });
        Swal.close();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('matLinkModal'))?.hide();
            appendMaterialRow(data.material);
            showToast('success', 'Link added!');
            document.getElementById('matLinkTitle').value = '';
            document.getElementById('matLinkUrl').value   = '';
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Materials — Submit Embed
   ---------------------------------------------------------------- */
async function submitMaterialEmbed() {
    const lpId  = document.getElementById('matEmbedLessonPlan').value;
    const title = document.getElementById('matEmbedTitle').value.trim();
    const url   = document.getElementById('matEmbedUrl').value.trim();

    if (!lpId)  { Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#a01422' }); return; }
    if (!title) { Swal.fire({ icon: 'warning', title: 'Enter a title', confirmButtonColor: '#a01422' }); return; }
    if (!url)   { Swal.fire({ icon: 'warning', title: 'Enter a URL', confirmButtonColor: '#a01422' }); return; }

    showLoading('Saving embed…');
    try {
        const data = await postJSON(BASE + '/iep/implementation/material/add', {
            lesson_plan_id: parseInt(lpId), material_type: 'embed', title, external_url: url,
        });
        Swal.close();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('matEmbedModal'))?.hide();
            appendMaterialRow(data.material);
            showToast('success', 'Embed added!');
            document.getElementById('matEmbedTitle').value = '';
            document.getElementById('matEmbedUrl').value   = '';
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Materials — Delete
   ---------------------------------------------------------------- */
function confirmDeleteMaterial(matId, matTitle) {
    Swal.fire({
        icon: 'warning',
        title: 'Delete material?',
        html: 'Remove <strong>' + matTitle + '</strong>?',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        showLoading('Deleting…');
        try {
            const data = await postJSON(BASE + '/iep/implementation/material/' + matId + '/delete', { material_id: matId });
            Swal.close();
            if (data.success) {
                const row = document.getElementById('matRow_' + matId);
                if (row) row.remove();
                showToast('success', 'Deleted', matTitle + ' removed.');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
        }
    });
}
</script>


<!-- ================================================================
     JAVASCRIPT — Part 3: Activity Builder
     ================================================================ -->
<script>
/* ----------------------------------------------------------------
   Activity type selection
   ---------------------------------------------------------------- */
let selectedActivityType = null;

const activityTypeLabels = {
    multiple_choice: 'Multiple Choice',
    true_false:      'True / False',
    fill_in_blanks:  'Fill in the Blanks',
    matching:        'Matching',
    drag_drop_sort:  'Drag & Drop Sort',
    image_label:     'Image Label',
    flashcards:      'Flashcards',
    sequencing:      'Sequencing',
};

function selectActivityType(type) {
    selectedActivityType = type;

    // Update card selection styles
    document.querySelectorAll('.activity-type-card').forEach(c => c.classList.remove('selected'));
    const card = document.getElementById('actCard_' + type);
    if (card) card.classList.add('selected');

    // Update builder label
    document.getElementById('builderTypeLabel').textContent = activityTypeLabels[type] || type;

    // Show/hide max score (hidden for flashcards)
    const maxScoreWrap = document.getElementById('builderMaxScoreWrap');
    if (maxScoreWrap) maxScoreWrap.style.display = type === 'flashcards' ? 'none' : '';

    // Render type-specific builder
    renderBuilderTypeArea(type);

    // Slide builder into view
    const builder = document.getElementById('activityBuilder');
    builder.style.display = 'block';
    builder.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeActivityBuilder() {
    document.getElementById('activityBuilder').style.display = 'none';
    document.querySelectorAll('.activity-type-card').forEach(c => c.classList.remove('selected'));
    selectedActivityType = null;
}

/* ----------------------------------------------------------------
   Builder type-specific areas
   ---------------------------------------------------------------- */
function renderBuilderTypeArea(type) {
    const area = document.getElementById('builderTypeArea');
    area.innerHTML = '';

    switch (type) {
        case 'multiple_choice': area.innerHTML = buildMultipleChoice(); break;
        case 'true_false':      area.innerHTML = buildTrueFalse();      break;
        case 'fill_in_blanks':  area.innerHTML = buildFillInBlanks();   break;
        case 'matching':        area.innerHTML = buildMatching();        break;
        case 'drag_drop_sort':  area.innerHTML = buildDragDropSort();    break;
        case 'image_label':     area.innerHTML = buildImageLabel();      break;
        case 'flashcards':      area.innerHTML = buildFlashcards();      break;
        case 'sequencing':      area.innerHTML = buildSequencing();      break;
    }
    initDragHandles();
}

/* ---- Multiple Choice ---- */
function buildMultipleChoice() {
    return `
    <div id="mcQuestions">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small" style="color:#1e4072;">Questions</span>
            <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                    onclick="addMCQuestion()">
                <i class="ti ti-plus me-1"></i>Add Question
            </button>
        </div>
        <div id="mcQuestionList"></div>
    </div>`;
}

let mcQCount = 0;
function addMCQuestion() {
    mcQCount++;
    const qId = 'mcQ_' + mcQCount;
    const div = document.createElement('div');
    div.className = 'border rounded p-3 mb-3';
    div.id = qId;
    div.style.background = '#fff';
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small">Question ${mcQCount}</span>
            <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.7rem;"
                    onclick="document.getElementById('${qId}').remove()">
                <i class="ti ti-x me-1"></i>Remove
            </button>
        </div>
        <input type="text" class="form-control form-control-sm mb-2 mc-question-text"
               placeholder="Enter question text" required>
        <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-muted">Options (mark correct with radio)</span>
                <button type="button" class="btn btn-sm" style="background:#6c757d;color:#fff;border:none;font-size:0.7rem;"
                        onclick="addMCOption('${qId}_opts')">
                    <i class="ti ti-plus me-1"></i>Add Option
                </button>
            </div>
            <div id="${qId}_opts"></div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="form-label small mb-0">Points:</label>
            <input type="number" class="form-control form-control-sm mc-points" style="width:80px;" min="0" value="1">
        </div>`;
    document.getElementById('mcQuestionList').appendChild(div);
    // Add 2 default options
    addMCOption(qId + '_opts');
    addMCOption(qId + '_opts');
}

let mcOptCount = 0;
function addMCOption(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const opts = container.querySelectorAll('.mc-option-row');
    if (opts.length >= 5) {
        showToast('warning', 'Max 5 options per question'); return;
    }
    mcOptCount++;
    const row = document.createElement('div');
    row.className = 'builder-item-row mc-option-row';
    const radioName = 'mc_correct_' + containerId + '_' + mcOptCount;
    row.innerHTML = `
        <input type="radio" name="${radioName}" class="form-check-input mc-correct-radio" title="Mark as correct">
        <input type="text" class="form-control form-control-sm mc-option-text" placeholder="Option text">
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.mc-option-row').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    container.appendChild(row);
}

/* ---- True / False ---- */
function buildTrueFalse() {
    return `
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label small fw-semibold">Statement <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" id="tfStatement" placeholder="Enter true/false statement">
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Correct Answer</label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tfAnswer" id="tfTrue" value="true" checked>
                    <label class="form-check-label small" for="tfTrue">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tfAnswer" id="tfFalse" value="false">
                    <label class="form-check-label small" for="tfFalse">False</label>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Points</label>
            <input type="number" class="form-control form-control-sm" id="tfPoints" min="0" value="1">
        </div>
    </div>`;
}

/* ---- Fill in the Blanks ---- */
function buildFillInBlanks() {
    return `
    <div class="alert alert-info py-2 small mb-3">
        <i class="ti ti-info-circle me-1"></i>Use <code>___</code> (three underscores) to mark blanks in your sentence.
    </div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label small fw-semibold">Sentence <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" id="fibSentence"
                   placeholder="e.g. The ___ is red." oninput="updateFibPreview()">
            <div id="fibPreview" class="mt-2 p-2 border rounded small" style="background:#f8f9fa;min-height:32px;"></div>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Answers for each blank</label>
            <div id="fibAnswers"></div>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Points</label>
            <input type="number" class="form-control form-control-sm" id="fibPoints" min="0" value="1">
        </div>
    </div>`;
}

function updateFibPreview() {
    const sentence = document.getElementById('fibSentence')?.value || '';
    const preview  = document.getElementById('fibPreview');
    const answersDiv = document.getElementById('fibAnswers');
    if (!preview || !answersDiv) return;

    // Highlight blanks
    const highlighted = sentence.replace(/___/g, '<span style="background:#a01422;color:#fff;padding:0 4px;border-radius:3px;">___</span>');
    preview.innerHTML = highlighted || '<span class="text-muted">Preview will appear here…</span>';

    // Count blanks and render answer inputs
    const blanks = (sentence.match(/___/g) || []).length;
    const existing = answersDiv.querySelectorAll('.fib-answer-input');
    const diff = blanks - existing.length;

    if (diff > 0) {
        for (let i = 0; i < diff; i++) {
            const idx = existing.length + i + 1;
            const inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'form-control form-control-sm fib-answer-input mb-1';
            inp.placeholder = 'Answer for blank ' + idx;
            answersDiv.appendChild(inp);
        }
    } else if (diff < 0) {
        const toRemove = answersDiv.querySelectorAll('.fib-answer-input');
        for (let i = toRemove.length - 1; i >= blanks; i--) {
            toRemove[i].remove();
        }
    }
}

/* ---- Matching ---- */
function buildMatching() {
    return `
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Pairs</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                onclick="addMatchingPair()">
            <i class="ti ti-plus me-1"></i>Add Pair
        </button>
    </div>
    <div id="matchingPairs"></div>
    <div class="mt-2 d-flex align-items-center gap-2">
        <label class="form-label small mb-0">Points per pair:</label>
        <input type="number" class="form-control form-control-sm" id="matchingPoints" style="width:80px;" min="0" value="1">
    </div>`;
}

let matchPairCount = 0;
function addMatchingPair() {
    matchPairCount++;
    const row = document.createElement('div');
    row.className = 'builder-item-row matching-pair';
    row.draggable = true;
    row.innerHTML = `
        <span class="drag-handle"><i class="ti ti-grip-vertical"></i></span>
        <input type="text" class="form-control form-control-sm matching-left" placeholder="Left item">
        <span class="text-muted small px-1">→</span>
        <input type="text" class="form-control form-control-sm matching-right" placeholder="Right item">
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.matching-pair').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    document.getElementById('matchingPairs').appendChild(row);
    initDragHandles();
}

/* ---- Drag & Drop Sort ---- */
function buildDragDropSort() {
    return `
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Items (drag to set correct order)</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                onclick="addDragDropItem()">
            <i class="ti ti-plus me-1"></i>Add Item
        </button>
    </div>
    <div id="dragDropItems"></div>
    <div class="mt-2 d-flex align-items-center gap-2">
        <label class="form-label small mb-0">Points:</label>
        <input type="number" class="form-control form-control-sm" id="dragDropPoints" style="width:80px;" min="0" value="1">
    </div>`;
}

function addDragDropItem() {
    const row = document.createElement('div');
    row.className = 'builder-item-row drag-drop-item';
    row.draggable = true;
    row.innerHTML = `
        <span class="drag-handle"><i class="ti ti-grip-vertical"></i></span>
        <input type="text" class="form-control form-control-sm drag-drop-text" placeholder="Item text">
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.drag-drop-item').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    document.getElementById('dragDropItems').appendChild(row);
    initDragHandles();
}

/* ---- Image Label ---- */
let imageLabelMarkers = [];
function buildImageLabel() {
    return `
    <div class="mb-3">
        <label class="form-label small fw-semibold">Upload Image (JPG/PNG, max 5MB)</label>
        <input type="file" class="form-control form-control-sm" id="imageLabelFile"
               accept=".jpg,.jpeg,.png" onchange="previewImageLabel(this)">
    </div>
    <div id="imageLabelPreviewWrap" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-muted">Click on the image to place a label marker</span>
            <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                    onclick="addImageLabelMarker()">
                <i class="ti ti-map-pin me-1"></i>Add Label
            </button>
        </div>
        <div id="imageLabelCanvas" class="mb-3" onclick="placeMarkerOnClick(event)"></div>
        <div id="imageLabelAnswers"></div>
    </div>
    <div class="mt-2 d-flex align-items-center gap-2">
        <label class="form-label small mb-0">Points per label:</label>
        <input type="number" class="form-control form-control-sm" id="imageLabelPoints" style="width:80px;" min="0" value="1">
    </div>`;
}

function previewImageLabel(input) {
    if (!input.files.length) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire({ icon: 'warning', title: 'File too large', text: 'Max 5MB for image labels.', confirmButtonColor: '#a01422' });
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
        const canvas = document.getElementById('imageLabelCanvas');
        canvas.innerHTML = `<img src="${e.target.result}" id="imageLabelImg" style="max-width:100%;border-radius:6px;display:block;" alt="Label image">`;
        document.getElementById('imageLabelPreviewWrap').style.display = '';
        imageLabelMarkers = [];
        document.getElementById('imageLabelAnswers').innerHTML = '';
    };
    reader.readAsDataURL(file);
}

let markerClickMode = false;
function addImageLabelMarker() {
    markerClickMode = true;
    const canvas = document.getElementById('imageLabelCanvas');
    if (canvas) canvas.style.cursor = 'crosshair';
    showToast('info', 'Click on the image to place a marker');
}

function placeMarkerOnClick(event) {
    if (!markerClickMode) return;
    markerClickMode = false;
    const canvas = document.getElementById('imageLabelCanvas');
    if (!canvas) return;
    canvas.style.cursor = 'default';

    const rect = canvas.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width * 100).toFixed(1);
    const y = ((event.clientY - rect.top)  / rect.height * 100).toFixed(1);
    const idx = imageLabelMarkers.length + 1;

    imageLabelMarkers.push({ x, y, answer: '' });

    // Place marker dot
    const marker = document.createElement('div');
    marker.className = 'label-marker';
    marker.style.left = x + '%';
    marker.style.top  = y + '%';
    marker.textContent = idx;
    canvas.appendChild(marker);

    // Add answer input
    const answersDiv = document.getElementById('imageLabelAnswers');
    const row = document.createElement('div');
    row.className = 'd-flex align-items-center gap-2 mb-2';
    row.innerHTML = `
        <span class="badge" style="background:#a01422;min-width:24px;">${idx}</span>
        <input type="text" class="form-control form-control-sm image-label-answer"
               data-idx="${idx - 1}" placeholder="Answer for label ${idx}"
               oninput="imageLabelMarkers[${idx - 1}].answer = this.value">`;
    answersDiv.appendChild(row);
}

/* ---- Flashcards ---- */
function buildFlashcards() {
    return `
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Cards</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                onclick="addFlashcard()">
            <i class="ti ti-plus me-1"></i>Add Card
        </button>
    </div>
    <div id="flashcardList"></div>`;
}

let flashcardCount = 0;
function addFlashcard() {
    flashcardCount++;
    const row = document.createElement('div');
    row.className = 'builder-item-row flashcard-row';
    row.innerHTML = `
        <div class="flex-grow-1">
            <div class="row g-2">
                <div class="col-6">
                    <input type="text" class="form-control form-control-sm flashcard-front"
                           placeholder="Front (question/term)">
                </div>
                <div class="col-6">
                    <input type="text" class="form-control form-control-sm flashcard-back"
                           placeholder="Back (answer/definition)">
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.flashcard-row').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    document.getElementById('flashcardList').appendChild(row);
}

/* ---- Sequencing ---- */
function buildSequencing() {
    return `
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Steps (drag to set correct order)</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                onclick="addSequencingStep()">
            <i class="ti ti-plus me-1"></i>Add Step
        </button>
    </div>
    <div id="sequencingSteps"></div>
    <div class="mt-2 d-flex align-items-center gap-2">
        <label class="form-label small mb-0">Points:</label>
        <input type="number" class="form-control form-control-sm" id="sequencingPoints" style="width:80px;" min="0" value="1">
    </div>`;
}

function addSequencingStep() {
    const row = document.createElement('div');
    row.className = 'builder-item-row sequencing-step';
    row.draggable = true;
    row.innerHTML = `
        <span class="drag-handle"><i class="ti ti-grip-vertical"></i></span>
        <input type="text" class="form-control form-control-sm sequencing-text" placeholder="Step text">
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.sequencing-step').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    document.getElementById('sequencingSteps').appendChild(row);
    initDragHandles();
}
</script>


<!-- ================================================================
     JAVASCRIPT — Part 4: Drag handles, Save Activity, Delete Activity
     ================================================================ -->
<script>
/* ----------------------------------------------------------------
   HTML5 Drag-and-drop for builder rows
   ---------------------------------------------------------------- */
function initDragHandles() {
    document.querySelectorAll('.builder-item-row[draggable="true"]').forEach(row => {
        row.addEventListener('dragstart', onDragStart);
        row.addEventListener('dragover',  onDragOver);
        row.addEventListener('dragleave', onDragLeave);
        row.addEventListener('drop',      onDrop);
        row.addEventListener('dragend',   onDragEnd);
    });
}

let dragSrc = null;

function onDragStart(e) {
    dragSrc = this;
    e.dataTransfer.effectAllowed = 'move';
    this.style.opacity = '0.5';
}
function onDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    this.classList.add('drag-over');
    return false;
}
function onDragLeave() {
    this.classList.remove('drag-over');
}
function onDrop(e) {
    e.stopPropagation();
    if (dragSrc !== this) {
        const parent = this.parentNode;
        const srcIdx  = Array.from(parent.children).indexOf(dragSrc);
        const destIdx = Array.from(parent.children).indexOf(this);
        if (srcIdx < destIdx) {
            parent.insertBefore(dragSrc, this.nextSibling);
        } else {
            parent.insertBefore(dragSrc, this);
        }
    }
    this.classList.remove('drag-over');
    return false;
}
function onDragEnd() {
    this.style.opacity = '';
    document.querySelectorAll('.builder-item-row').forEach(r => r.classList.remove('drag-over'));
}

/* ----------------------------------------------------------------
   Collect activity data from builder
   ---------------------------------------------------------------- */
function collectActivityData() {
    const type = selectedActivityType;
    let data = {};

    switch (type) {
        case 'multiple_choice': {
            const questions = [];
            document.querySelectorAll('#mcQuestionList > div').forEach(qDiv => {
                const text    = qDiv.querySelector('.mc-question-text')?.value.trim() || '';
                const points  = parseInt(qDiv.querySelector('.mc-points')?.value || '1');
                const options = [];
                qDiv.querySelectorAll('.mc-option-row').forEach(optRow => {
                    options.push({
                        text:      optRow.querySelector('.mc-option-text')?.value.trim() || '',
                        isCorrect: optRow.querySelector('.mc-correct-radio')?.checked || false,
                    });
                });
                questions.push({ text, options, points });
            });
            data = { questions };
            break;
        }
        case 'true_false': {
            data = {
                statement: document.getElementById('tfStatement')?.value.trim() || '',
                answer:    document.querySelector('input[name="tfAnswer"]:checked')?.value || 'true',
                points:    parseInt(document.getElementById('tfPoints')?.value || '1'),
            };
            break;
        }
        case 'fill_in_blanks': {
            const answers = [];
            document.querySelectorAll('.fib-answer-input').forEach(inp => answers.push(inp.value.trim()));
            data = {
                sentence: document.getElementById('fibSentence')?.value.trim() || '',
                answers,
                points: parseInt(document.getElementById('fibPoints')?.value || '1'),
            };
            break;
        }
        case 'matching': {
            const pairs = [];
            document.querySelectorAll('.matching-pair').forEach(row => {
                pairs.push({
                    left:  row.querySelector('.matching-left')?.value.trim()  || '',
                    right: row.querySelector('.matching-right')?.value.trim() || '',
                });
            });
            data = { pairs, points: parseInt(document.getElementById('matchingPoints')?.value || '1') };
            break;
        }
        case 'drag_drop_sort': {
            const items = [];
            document.querySelectorAll('.drag-drop-item').forEach((row, idx) => {
                items.push({ text: row.querySelector('.drag-drop-text')?.value.trim() || '', order: idx + 1 });
            });
            data = { items, points: parseInt(document.getElementById('dragDropPoints')?.value || '1') };
            break;
        }
        case 'image_label': {
            data = {
                markers: imageLabelMarkers,
                points:  parseInt(document.getElementById('imageLabelPoints')?.value || '1'),
            };
            break;
        }
        case 'flashcards': {
            const cards = [];
            document.querySelectorAll('.flashcard-row').forEach(row => {
                cards.push({
                    front: row.querySelector('.flashcard-front')?.value.trim() || '',
                    back:  row.querySelector('.flashcard-back')?.value.trim()  || '',
                });
            });
            data = { cards };
            break;
        }
        case 'sequencing': {
            const steps = [];
            document.querySelectorAll('.sequencing-step').forEach((row, idx) => {
                steps.push({ text: row.querySelector('.sequencing-text')?.value.trim() || '', order: idx + 1 });
            });
            data = { steps, points: parseInt(document.getElementById('sequencingPoints')?.value || '1') };
            break;
        }
    }
    return data;
}

/* ----------------------------------------------------------------
   Save Activity
   ---------------------------------------------------------------- */
async function saveActivity() {
    const lpId         = document.getElementById('builderLessonPlan').value;
    const title        = document.getElementById('builderTitle').value.trim();
    const instructions = document.getElementById('builderInstructions').value.trim();
    const dueDate      = document.getElementById('builderDueDate').value || null;
    const maxScore     = selectedActivityType !== 'flashcards'
                         ? parseInt(document.getElementById('builderMaxScore')?.value || '0')
                         : 0;

    if (!lpId) {
        Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#a01422' }); return;
    }
    if (!title) {
        Swal.fire({ icon: 'warning', title: 'Enter a title', confirmButtonColor: '#a01422' }); return;
    }
    if (!selectedActivityType) {
        Swal.fire({ icon: 'warning', title: 'Select an activity type', confirmButtonColor: '#a01422' }); return;
    }

    const activityData = collectActivityData();

    showLoading('Saving activity…');
    try {
        const data = await postJSON(BASE + '/iep/implementation/activity/create', {
            lesson_plan_id: parseInt(lpId),
            title,
            instructions,
            activity_type: selectedActivityType,
            activity_data: activityData,
            max_score:     maxScore,
            due_date:      dueDate,
        });

        Swal.close();
        if (data.success) {
            // Append to activities table
            appendActivityRow({
                id:               data.activity_id,
                title,
                activity_type:    selectedActivityType,
                lesson_plan_id:   parseInt(lpId),
                due_date:         dueDate,
                max_score:        maxScore,
            });
            closeActivityBuilder();
            showToast('success', 'Activity saved!');
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Append activity row to table
   ---------------------------------------------------------------- */
const actTypeColors = {
    multiple_choice: '#1e4072', true_false: '#3b6d11', fill_in_blanks: '#a01422',
    matching: '#6c757d', drag_drop_sort: '#e67e22', image_label: '#8e44ad',
    flashcards: '#2980b9', sequencing: '#16a085',
};

function appendActivityRow(act) {
    const color     = actTypeColors[act.activity_type] || '#6c757d';
    const typeLabel = activityTypeLabels[act.activity_type] || act.activity_type;

    // Find lesson plan title
    const lpSel = document.getElementById('builderLessonPlan');
    let lpTitle = '—';
    if (lpSel) {
        const opt = lpSel.querySelector('option[value="' + act.lesson_plan_id + '"]');
        if (opt) lpTitle = opt.textContent.trim();
    }

    const tbody = document.getElementById('activitiesTableBody');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.id = 'actRow_' + act.id;
    tr.innerHTML = `
        <td class="fw-semibold">${escHtml(act.title)}</td>
        <td><span class="badge" style="background:${color};font-size:0.7rem;">${escHtml(typeLabel)}</span></td>
        <td class="text-muted">${escHtml(lpTitle)}</td>
        <td class="text-muted">${act.due_date ? escHtml(act.due_date) : '—'}</td>
        <td>${act.max_score || 0}</td>
        <td>
            <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.75rem;"
                    onclick="confirmDeleteActivity(${act.id}, '${escAttr(act.title)}')">
                <i class="ti ti-trash me-1"></i>Delete
            </button>
        </td>`;
    tbody.appendChild(tr);

    document.getElementById('activitiesList').style.display = '';
    const empty = document.getElementById('activitiesEmptyState');
    if (empty) empty.style.display = 'none';
}

/* ----------------------------------------------------------------
   Delete Activity
   ---------------------------------------------------------------- */
function confirmDeleteActivity(actId, actTitle) {
    Swal.fire({
        icon: 'warning',
        title: 'Delete activity?',
        html: 'Remove <strong>' + actTitle + '</strong>?',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        showLoading('Deleting…');
        try {
            const data = await postJSON(BASE + '/iep/implementation/activity/' + actId + '/delete', { activity_id: actId });
            Swal.close();
            if (data.success) {
                const row = document.getElementById('actRow_' + actId);
                if (row) row.remove();
                showToast('success', 'Deleted', actTitle + ' removed.');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
        }
    });
}

/* ----------------------------------------------------------------
   Import Activity CSV
   ---------------------------------------------------------------- */
function openImportActivityModal() {
    new bootstrap.Modal(document.getElementById('importActivityModal')).show();
}

function downloadActivityCsvTemplate(e) {
    e.preventDefault();
    const csvContent = "data:text/csv;charset=utf-8," 
        + "title,instructions,type,max_score,question1,q1_option1,q1_option2,q1_correct,question2,q2_option1,q2_option2,q2_correct\n"
        + "Math Quiz,Answer the following,multiple_choice,10,1+1=?,1,2,2,2+2=?,3,4,2\n"
        + "Science Fact,Is the Earth flat?,true_false,5,The Earth is round,true\n";
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "activity_template.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function submitImportActivity() {
    const lpId = document.getElementById('importActivityLessonPlan').value;
    const fileInput = document.getElementById('importActivityFile');
    
    if (!lpId) {
        Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#3b6d11' }); return;
    }
    if (!fileInput.files.length) {
        Swal.fire({ icon: 'warning', title: 'Select a CSV file', confirmButtonColor: '#3b6d11' }); return;
    }
    
    const formData = new FormData();
    formData.append('lesson_plan_id', lpId);
    formData.append('iep_id', IEP_ID);
    formData.append('csv_file', fileInput.files[0]);

    const btn = document.querySelector('#importActivityModal .btn[onclick="submitImportActivity()"]');
    if (btn) btn.disabled = true;

    showLoading('Importing activities...');

    fetch(BASE + '/iep/implementation/activity/import', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        Swal.close();
        if (btn) btn.disabled = false;
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Imported!',
                text: data.message,
                confirmButtonColor: '#3b6d11'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
        }
    })
    .catch(e => {
        Swal.close();
        if (btn) btn.disabled = false;
        Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect.', confirmButtonColor: '#a01422' });
    });
}

/* ----------------------------------------------------------------
   Init on DOMContentLoaded
   ---------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {
    initDragHandles();

    // Keyboard accessibility for type cards
    document.querySelectorAll('.activity-type-card, .material-type-card').forEach(card => {
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
