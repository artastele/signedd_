<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Interactive Activity Player

$pageTitle = htmlspecialchars($activity['activity_name']) . ' - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Parse activity data
$activityData = json_decode($activity['activity_data'], true);
?>

<link rel="stylesheet" href="<?php echo $basePath; ?>/css/learner.css">
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<body data-logged-in="true" class="learner-page">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-content">
    <!-- Activity Header -->
    <div class="activity-header">
        <a href="<?php echo $basePath; ?>/learning/modules" class="btn-cartoon btn-back">
            <i class="bi bi-arrow-left"></i> Exit
        </a>
        <div class="activity-title-bar">
            <h2><?php echo htmlspecialchars($activity['activity_name']); ?></h2>
            <div class="activity-meta">
                <span class="activity-type-badge"><?php echo htmlspecialchars($activity['activity_type']); ?></span>
                <span class="activity-points">🏆 <?php echo $activity['total_points']; ?> points</span>
            </div>
        </div>
        <div class="timer-display">
            <i class="bi bi-clock"></i>
            <span id="timer">00:00</span>
        </div>
    </div>

    <!-- Best Score Display -->
    <?php if ($bestAttempt): ?>
        <div class="best-score-banner">
            <i class="bi bi-trophy-fill"></i>
            Your Best Score: <?php echo $bestAttempt['score']; ?>/<?php echo $bestAttempt['total_points']; ?>
            (<?php echo round(($bestAttempt['score'] / $bestAttempt['total_points']) * 100); ?>%)
        </div>
    <?php endif; ?>

    <!-- Activity Player Card -->
    <div class="card cartoon-card activity-player-card">
        <div class="card-body">
            <div id="activityContainer">
                <!-- Activity content will be rendered here based on type -->
            </div>

            <div class="activity-actions">
                <button id="submitBtn" class="btn-cartoon btn-submit-activity">
                    Submit Answers! 🚀
                </button>
            </div>
        </div>
    </div>

    <!-- Previous Attempts -->
    <?php if (!empty($attempts)): ?>
        <div class="card cartoon-card mt-4">
            <div class="card-body">
                <h4>📊 Your Previous Attempts</h4>
                <div class="attempts-list">
                    <?php foreach (array_slice($attempts, 0, 5) as $attempt): ?>
                        <div class="attempt-item">
                            <div class="attempt-score">
                                <?php 
                                $percentage = ($attempt['score'] / $attempt['total_points']) * 100;
                                $emoji = $percentage >= 90 ? '🌟' : ($percentage >= 70 ? '⭐' : '📝');
                                ?>
                                <?php echo $emoji; ?> <?php echo $attempt['score']; ?>/<?php echo $attempt['total_points']; ?>
                                (<?php echo round($percentage); ?>%)
                            </div>
                            <div class="attempt-date">
                                <?php echo date('M j, Y g:i A', strtotime($attempt['attempted_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.activity-title-bar {
    flex: 1;
    text-align: center;
}

.activity-title-bar h2 {
    margin: 0;
    color: #333;
    font-weight: bold;
}

.activity-meta {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 10px;
}

.activity-type-badge {
    background: var(--kid-purple);
    color: #fff;
    padding: 5px 15px;
    border-radius: 15px;
    font-size: 0.9rem;
    font-weight: bold;
}

.activity-points {
    background: var(--kid-yellow);
    color: #333;
    padding: 5px 15px;
    border-radius: 15px;
    font-size: 0.9rem;
    font-weight: bold;
}

.best-score-banner {
    background: linear-gradient(135deg, var(--kid-yellow) 0%, var(--kid-orange) 100%);
    color: #fff;
    padding: 15px;
    border-radius: 20px;
    text-align: center;
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 20px;
    border: 3px solid #333;
    box-shadow: 4px 4px 0 rgba(0,0,0,0.1);
}

.best-score-banner i {
    font-size: 1.5rem;
    margin-right: 10px;
}

.activity-player-card {
    max-width: 900px;
    margin: 0 auto;
}

.question-item {
    background: #f9f9f9;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
    border: 3px solid var(--kid-blue);
}

.question-number {
    background: var(--kid-blue);
    color: #fff;
    padding: 5px 15px;
    border-radius: 15px;
    display: inline-block;
    margin-bottom: 15px;
    font-weight: bold;
}

.question-text {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
}

.option-item {
    background: #fff;
    border: 3px solid #333;
    border-radius: 15px;
    padding: 15px 20px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.option-item:hover {
    transform: translateX(10px);
    border-color: var(--kid-blue);
}

.option-item.selected {
    background: var(--kid-blue);
    color: #fff;
    border-color: var(--kid-blue);
}

.option-item input[type="radio"],
.option-item input[type="checkbox"] {
    margin-right: 15px;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.fill-blank-input {
    border: 3px solid #333;
    border-radius: 10px;
    padding: 10px 15px;
    font-size: 1.1rem;
    font-family: 'Comic Sans MS', cursive;
    width: 100%;
    max-width: 300px;
}

.fill-blank-input:focus {
    border-color: var(--kid-blue);
    outline: none;
}

.sortable-list {
    min-height: 100px;
}

.sortable-item {
    background: #fff;
    border: 3px solid #333;
    border-radius: 15px;
    padding: 15px 20px;
    margin-bottom: 10px;
    cursor: move;
    transition: all 0.3s ease;
}

.sortable-item:hover {
    transform: scale(1.02);
    box-shadow: 4px 4px 0 rgba(0,0,0,0.1);
}

.sortable-item.dragging {
    opacity: 0.5;
}

.matching-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.matching-column {
    background: #fff;
    border-radius: 15px;
    padding: 20px;
    border: 3px solid var(--kid-purple);
}

.matching-item {
    background: #f9f9f9;
    border: 3px solid #333;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.matching-item:hover {
    transform: scale(1.05);
}

.matching-item.selected {
    background: var(--kid-yellow);
    border-color: var(--kid-orange);
}

.activity-actions {
    text-align: center;
    margin-top: 30px;
}

.btn-submit-activity {
    background: var(--kid-green);
    color: #fff;
    font-size: 1.3rem;
    padding: 15px 50px;
}

.attempts-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.attempt-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9f9f9;
    border-radius: 10px;
    padding: 15px;
    border: 2px solid #e0e0e0;
}

.attempt-score {
    font-weight: bold;
    font-size: 1.1rem;
}

.attempt-date {
    color: #666;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .activity-header {
        flex-direction: column;
    }
    
    .matching-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Activity data from PHP
const activityTypeRaw = <?php echo json_encode($activity['activity_type']); ?>;
const activityData = <?php echo json_encode($activityData); ?>;
const activityId = <?php echo $activity['id']; ?>;
const basePath = <?php echo json_encode($basePath); ?>;

// Convert snake_case to Title Case for switch statement
const activityTypeMap = {
    'multiple_choice': 'Multiple Choice',
    'true_false': 'True/False',
    'fill_blanks': 'Fill in the Blanks',
    'matching': 'Matching',
    'drag_drop_sort': 'Drag & Drop Sorting',
    'sequencing': 'Sequencing',
    'image_labeling': 'Image Labeling',
    'flashcards': 'Flashcards'
};
const activityType = activityTypeMap[activityTypeRaw] || activityTypeRaw;

// Timer
let seconds = 0;
const timerElement = document.getElementById('timer');
const timerInterval = setInterval(() => {
    seconds++;
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    timerElement.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}, 1000);

// Render activity based on type
const container = document.getElementById('activityContainer');

switch(activityType) {
    case 'Multiple Choice':
        renderMultipleChoice();
        break;
    case 'True/False':
        renderTrueFalse();
        break;
    case 'Fill in the Blanks':
        renderFillBlanks();
        break;
    case 'Matching':
        renderMatching();
        break;
    case 'Drag & Drop Sorting':
        renderDragDropSorting();
        break;
    case 'Sequencing':
        renderSequencing();
        break;
    default:
        container.innerHTML = '<p>Activity type not supported yet.</p>';
}

function renderMultipleChoice() {
    let html = '';
    activityData.questions.forEach((q, index) => {
        html += `
            <div class="question-item">
                <div class="question-number">Question ${index + 1}</div>
                <div class="question-text">${q.question}</div>
                <div class="options-list">
                    ${q.options.map((opt, optIndex) => `
                        <label class="option-item">
                            <input type="radio" name="q${index}" value="${optIndex}" 
                                   onchange="selectOption(this)">
                            <span>${opt}</span>
                        </label>
                    `).join('')}
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function renderTrueFalse() {
    let html = '';
    activityData.questions.forEach((q, index) => {
        html += `
            <div class="question-item">
                <div class="question-number">Question ${index + 1}</div>
                <div class="question-text">${q.question}</div>
                <div class="options-list">
                    <label class="option-item">
                        <input type="radio" name="q${index}" value="true" 
                               onchange="selectOption(this)">
                        <span>✓ True</span>
                    </label>
                    <label class="option-item">
                        <input type="radio" name="q${index}" value="false" 
                               onchange="selectOption(this)">
                        <span>✗ False</span>
                    </label>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function renderFillBlanks() {
    let html = '';
    activityData.questions.forEach((q, index) => {
        html += `
            <div class="question-item">
                <div class="question-number">Question ${index + 1}</div>
                <div class="question-text">${q.question}</div>
                <input type="text" class="fill-blank-input" data-index="${index}" 
                       placeholder="Type your answer here...">
            </div>
        `;
    });
    container.innerHTML = html;
}

function renderMatching() {
    const leftItems = activityData.pairs.map((p, i) => p.left);
    const rightItems = [...activityData.pairs.map((p, i) => p.right)].sort(() => Math.random() - 0.5);
    
    let html = `
        <div class="matching-container">
            <div class="matching-column">
                <h5 style="text-align: center; margin-bottom: 15px;">Column A</h5>
                ${leftItems.map((item, i) => `
                    <div class="matching-item" data-left="${i}" onclick="selectMatchingItem(this, 'left')">
                        ${item}
                    </div>
                `).join('')}
            </div>
            <div class="matching-column">
                <h5 style="text-align: center; margin-bottom: 15px;">Column B</h5>
                ${rightItems.map((item, i) => `
                    <div class="matching-item" data-right="${item}" onclick="selectMatchingItem(this, 'right')">
                        ${item}
                    </div>
                `).join('')}
            </div>
        </div>
        <div id="matchingAnswers" style="display: none;"></div>
    `;
    container.innerHTML = html;
}

function renderDragDropSorting() {
    const items = [...activityData.items].sort(() => Math.random() - 0.5);
    
    let html = `
        <div class="question-text" style="text-align: center; margin-bottom: 20px;">
            Drag and drop to sort these items in the correct order:
        </div>
        <div class="sortable-list" id="sortableList">
            ${items.map((item, i) => `
                <div class="sortable-item" data-item="${item}">
                    <i class="bi bi-grip-vertical" style="margin-right: 10px;"></i>
                    ${item}
                </div>
            `).join('')}
        </div>
    `;
    container.innerHTML = html;
    
    // Initialize Sortable
    new Sortable(document.getElementById('sortableList'), {
        animation: 150,
        ghostClass: 'dragging'
    });
}

function renderSequencing() {
    const steps = [...activityData.steps].sort(() => Math.random() - 0.5);
    
    let html = `
        <div class="question-text" style="text-align: center; margin-bottom: 20px;">
            Arrange these steps in the correct sequence:
        </div>
        <div class="sortable-list" id="sortableList">
            ${steps.map((step, i) => `
                <div class="sortable-item" data-step="${step}">
                    <i class="bi bi-grip-vertical" style="margin-right: 10px;"></i>
                    ${step}
                </div>
            `).join('')}
        </div>
    `;
    container.innerHTML = html;
    
    // Initialize Sortable
    new Sortable(document.getElementById('sortableList'), {
        animation: 150,
        ghostClass: 'dragging'
    });
}

// Helper functions
function selectOption(radio) {
    const parent = radio.closest('.options-list');
    parent.querySelectorAll('.option-item').forEach(item => {
        item.classList.remove('selected');
    });
    radio.closest('.option-item').classList.add('selected');
}

let selectedLeft = null;
let selectedRight = null;
const matchingAnswers = {};

function selectMatchingItem(element, side) {
    if (side === 'left') {
        document.querySelectorAll('[data-left]').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
        selectedLeft = element.dataset.left;
    } else {
        document.querySelectorAll('[data-right]').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
        selectedRight = element.dataset.right;
    }
    
    if (selectedLeft !== null && selectedRight !== null) {
        matchingAnswers[selectedLeft] = selectedRight;
        element.style.opacity = '0.5';
        document.querySelector(`[data-left="${selectedLeft}"]`).style.opacity = '0.5';
        selectedLeft = null;
        selectedRight = null;
    }
}

// Submit answers
document.getElementById('submitBtn').addEventListener('click', function() {
    if (!confirm('Are you sure you want to submit your answers?')) {
        return;
    }
    
    const answers = collectAnswers();
    
    fetch(basePath + '/learning/activity/submit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `activity_id=${activityId}&answers=${encodeURIComponent(JSON.stringify(answers))}&time_spent=${seconds}`
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(timerInterval);
        
        if (data.success) {
            // Confetti animation
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
            
            // Show result
            const percentage = data.percentage;
            let message = `🎉 ${data.message}\n\n`;
            message += `Score: ${data.score}/${data.total_points} (${percentage}%)\n`;
            if (data.stars_earned > 0) {
                message += `Stars Earned: ${'⭐'.repeat(data.stars_earned)}`;
            }
            
            alert(message);
            
            // Reload to show updated attempts
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

function collectAnswers() {
    const answers = {};
    
    switch(activityType) {
        case 'Multiple Choice':
        case 'True/False':
            activityData.questions.forEach((q, index) => {
                const selected = document.querySelector(`input[name="q${index}"]:checked`);
                answers[index] = selected ? selected.value : null;
            });
            break;
            
        case 'Fill in the Blanks':
            document.querySelectorAll('.fill-blank-input').forEach(input => {
                answers[input.dataset.index] = input.value.trim();
            });
            break;
            
        case 'Matching':
            return matchingAnswers;
            
        case 'Drag & Drop Sorting':
        case 'Sequencing':
            const items = [];
            document.querySelectorAll('.sortable-item').forEach(item => {
                items.push(item.dataset.item || item.dataset.step);
            });
            return { order: items };
    }
    
    return answers;
}

// Cleanup
window.addEventListener('beforeunload', () => {
    clearInterval(timerInterval);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
