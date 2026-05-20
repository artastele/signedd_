<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-05
// Part of: SPED LMS — Activity Builder

$pageTitle = 'Create Activity - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-puzzle text-primary"></i> Create Activity</h1>
        <a href="<?php echo $basePath; ?>/iep/implementation" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="activityForm">
                        <!-- Activity Name -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Activity Name *</label>
                            <input type="text" id="activityName" class="form-control" required>
                        </div>

                        <!-- Activity Type -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Activity Type *</label>
                            <select id="activityType" class="form-select" required>
                                <option value="">-- Choose Type --</option>
                                <option value="multiple_choice">Multiple Choice Quiz</option>
                                <option value="true_false">True/False Quiz</option>
                                <option value="fill_blanks">Fill in the Blanks</option>
                                <option value="matching">Matching Activity</option>
                                <option value="drag_drop_sort">Drag & Drop Sorting</option>
                            </select>
                        </div>

                        <!-- Instructions -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Instructions</label>
                            <textarea id="instructions" class="form-control" rows="3"></textarea>
                        </div>

                        <!-- Dynamic Activity Builder -->
                        <div id="activityBuilder" style="display: none;">
                            <!-- Content will be dynamically generated -->
                        </div>

                        <!-- Assign To -->
                        <div class="mb-4" id="assignSection" style="display: none;">
                            <label class="form-label fw-bold">Assign To *</label>
                            <select id="assignTo" class="form-select" multiple size="5" required>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>"
                                            <?php if (isset($iep) && $iep['id'] == $student['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($student['student_name']); ?> 
                                        (LRN: <?php echo htmlspecialchars($student['lrn']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple students</small>
                        </div>

                        <!-- Assignment Options -->
                        <div class="mb-4" id="assignmentOptions" style="display: none;">
                            <div class="form-check mb-2">
                                <input type="checkbox" id="isAssignment" class="form-check-input">
                                <label class="form-check-label" for="isAssignment">
                                    This is a graded assignment
                                </label>
                            </div>
                            <div id="assignmentFields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Due Date</label>
                                        <input type="date" id="dueDate" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Points Display -->
                        <div class="alert alert-info" id="pointsDisplay" style="display: none;">
                            <strong>Total Points:</strong> <span id="totalPoints">0</span>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Create Activity
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let activityData = { questions: [] };
let totalPoints = 0;

// Activity type change
document.getElementById('activityType').addEventListener('change', function() {
    const type = this.value;
    const builder = document.getElementById('activityBuilder');
    const assignSection = document.getElementById('assignSection');
    const assignmentOptions = document.getElementById('assignmentOptions');
    const pointsDisplay = document.getElementById('pointsDisplay');
    
    if (type) {
        builder.style.display = 'block';
        assignSection.style.display = 'block';
        assignmentOptions.style.display = 'block';
        pointsDisplay.style.display = 'block';
        buildActivityForm(type);
    } else {
        builder.style.display = 'none';
        assignSection.style.display = 'none';
        assignmentOptions.style.display = 'none';
        pointsDisplay.style.display = 'none';
    }
});

// Assignment checkbox
document.getElementById('isAssignment').addEventListener('change', function() {
    document.getElementById('assignmentFields').style.display = this.checked ? 'block' : 'none';
});

// Build activity form based on type
function buildActivityForm(type) {
    const builder = document.getElementById('activityBuilder');
    activityData = { questions: [] };
    totalPoints = 0;
    
    switch(type) {
        case 'multiple_choice':
            builder.innerHTML = buildMultipleChoiceForm();
            break;
        case 'true_false':
            builder.innerHTML = buildTrueFalseForm();
            break;
        case 'fill_blanks':
            builder.innerHTML = buildFillBlanksForm();
            break;
        case 'matching':
            builder.innerHTML = buildMatchingForm();
            break;
        case 'drag_drop_sort':
            builder.innerHTML = buildDragDropForm();
            break;
    }
}

// Multiple Choice Form
function buildMultipleChoiceForm() {
    return `
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Multiple Choice Questions</h6>
            </div>
            <div class="card-body">
                <div id="mcQuestions"></div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMCQuestion()">
                    <i class="bi bi-plus"></i> Add Question
                </button>
            </div>
        </div>
    `;
}

function addMCQuestion() {
    const container = document.getElementById('mcQuestions');
    const index = activityData.questions.length;
    
    const questionHtml = `
        <div class="card mb-3" id="mcq-${index}">
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label fw-bold">Question ${index + 1}</label>
                    <input type="text" class="form-control" id="mcq-text-${index}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Choices:</label>
                    <input type="text" class="form-control mb-1" placeholder="Choice A" id="mcq-choice-${index}-0">
                    <input type="text" class="form-control mb-1" placeholder="Choice B" id="mcq-choice-${index}-1">
                    <input type="text" class="form-control mb-1" placeholder="Choice C" id="mcq-choice-${index}-2">
                    <input type="text" class="form-control mb-1" placeholder="Choice D" id="mcq-choice-${index}-3">
                </div>
                <div class="mb-2">
                    <label class="form-label">Correct Answer:</label>
                    <select class="form-select" id="mcq-correct-${index}">
                        <option value="0">A</option>
                        <option value="1">B</option>
                        <option value="2">C</option>
                        <option value="3">D</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Points:</label>
                    <input type="number" class="form-control" value="10" min="1" id="mcq-points-${index}">
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeMCQuestion(${index})">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', questionHtml);
    activityData.questions.push({});
}

function removeMCQuestion(index) {
    document.getElementById(`mcq-${index}`).remove();
}

// True/False Form
function buildTrueFalseForm() {
    return `
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">True/False Questions</h6>
            </div>
            <div class="card-body">
                <div id="tfQuestions"></div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTFQuestion()">
                    <i class="bi bi-plus"></i> Add Question
                </button>
            </div>
        </div>
    `;
}

function addTFQuestion() {
    const container = document.getElementById('tfQuestions');
    const index = activityData.questions.length;
    
    const questionHtml = `
        <div class="card mb-3" id="tfq-${index}">
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label fw-bold">Statement ${index + 1}</label>
                    <input type="text" class="form-control" id="tfq-text-${index}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Correct Answer:</label>
                    <select class="form-select" id="tfq-correct-${index}">
                        <option value="true">True</option>
                        <option value="false">False</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Points:</label>
                    <input type="number" class="form-control" value="5" min="1" id="tfq-points-${index}">
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeTFQuestion(${index})">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', questionHtml);
    activityData.questions.push({});
}

function removeTFQuestion(index) {
    document.getElementById(`tfq-${index}`).remove();
}

// Fill in Blanks, Matching, Drag & Drop forms (simplified versions)
function buildFillBlanksForm() {
    return `<div class="alert alert-info">Fill in the Blanks builder - Coming soon!</div>`;
}

function buildMatchingForm() {
    return `<div class="alert alert-info">Matching Activity builder - Coming soon!</div>`;
}

function buildDragDropForm() {
    return `<div class="alert alert-info">Drag & Drop builder - Coming soon!</div>`;
}

// Form submission
document.getElementById('activityForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const activityType = document.getElementById('activityType').value;
    const activityName = document.getElementById('activityName').value;
    const instructions = document.getElementById('instructions').value;
    const assignTo = Array.from(document.getElementById('assignTo').selectedOptions).map(opt => opt.value);
    const isAssignment = document.getElementById('isAssignment').checked;
    const dueDate = document.getElementById('dueDate').value;
    
    // Collect activity data based on type
    let activityData = {};
    let totalPoints = 0;
    
    if (activityType === 'multiple_choice') {
        activityData.questions = [];
        const questions = document.querySelectorAll('[id^="mcq-text-"]');
        questions.forEach((q, i) => {
            const text = q.value;
            const choices = [
                document.getElementById(`mcq-choice-${i}-0`).value,
                document.getElementById(`mcq-choice-${i}-1`).value,
                document.getElementById(`mcq-choice-${i}-2`).value,
                document.getElementById(`mcq-choice-${i}-3`).value
            ];
            const correct = parseInt(document.getElementById(`mcq-correct-${i}`).value);
            const points = parseInt(document.getElementById(`mcq-points-${i}`).value);
            
            activityData.questions.push({ question: text, choices, correct_answer: correct, points });
            totalPoints += points;
        });
    } else if (activityType === 'true_false') {
        activityData.questions = [];
        const questions = document.querySelectorAll('[id^="tfq-text-"]');
        questions.forEach((q, i) => {
            const text = q.value;
            const correct = document.getElementById(`tfq-correct-${i}`).value === 'true';
            const points = parseInt(document.getElementById(`tfq-points-${i}`).value);
            
            activityData.questions.push({ question: text, correct_answer: correct, points });
            totalPoints += points;
        });
    }
    
    const formData = new FormData();
    formData.append('material_name', activityName);
    formData.append('activity_type', activityType);
    formData.append('instructions', instructions);
    formData.append('activity_data', JSON.stringify(activityData));
    formData.append('total_points', totalPoints);
    formData.append('is_assignment', isAssignment ? '1' : '0');
    if (dueDate) formData.append('due_date', dueDate);
    
    assignTo.forEach(id => formData.append('learner_iep_ids[]', id));
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating...';
    
    try {
        const response = await fetch('<?php echo $basePath; ?>/iep/implementation/save-activity', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            window.location.href = '<?php echo $basePath; ?>/iep/implementation';
        } else {
            alert('Error: ' + result.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Create Activity';
        }
    } catch (error) {
        alert('Failed: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Create Activity';
    }
});

// Initialize with one question if type is selected
setTimeout(() => {
    const type = document.getElementById('activityType').value;
    if (type === 'multiple_choice') addMCQuestion();
    if (type === 'true_false') addTFQuestion();
}, 100);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
