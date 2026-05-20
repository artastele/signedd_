/**
 * Activity Player JavaScript
 * Handles interactive activities for learners
 * Part of: SPED LMS — Process 7
 */

class ActivityPlayer {
    constructor(activityType, activityData, activityId, basePath) {
        this.activityType = activityType;
        this.activityData = activityData;
        this.activityId = activityId;
        this.basePath = basePath;
        this.seconds = 0;
        this.timerInterval = null;
        this.selectedLeft = null;
        this.selectedRight = null;
        this.matchingAnswers = {};
    }

    /**
     * Initialize the activity player
     */
    init() {
        this.startTimer();
        this.render();
        this.attachSubmitHandler();
    }

    /**
     * Start the timer
     */
    startTimer() {
        const timerElement = document.getElementById('timer');
        this.timerInterval = setInterval(() => {
            this.seconds++;
            const mins = Math.floor(this.seconds / 60);
            const secs = this.seconds % 60;
            timerElement.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }, 1000);
    }

    /**
     * Render the activity based on type
     */
    render() {
        const container = document.getElementById('activityContainer');
        
        switch(this.activityType) {
            case 'Multiple Choice':
                this.renderMultipleChoice(container);
                break;
            case 'True/False':
                this.renderTrueFalse(container);
                break;
            case 'Fill in the Blanks':
                this.renderFillBlanks(container);
                break;
            case 'Matching':
                this.renderMatching(container);
                break;
            case 'Drag & Drop Sorting':
                this.renderDragDropSorting(container);
                break;
            case 'Sequencing':
                this.renderSequencing(container);
                break;
            case 'Image Labeling':
                this.renderImageLabeling(container);
                break;
            case 'Flashcards':
                this.renderFlashcards(container);
                break;
            default:
                container.innerHTML = '<p>Activity type not supported yet.</p>';
        }
    }

    /**
     * Render Multiple Choice questions
     */
    renderMultipleChoice(container) {
        let html = '';
        this.activityData.questions.forEach((q, index) => {
            html += `
                <div class="question-item">
                    <div class="question-number">Question ${index + 1}</div>
                    <div class="question-text">${this.escapeHtml(q.question)}</div>
                    <div class="options-list">
                        ${q.options.map((opt, optIndex) => `
                            <label class="option-item">
                                <input type="radio" name="q${index}" value="${optIndex}">
                                <span>${this.escapeHtml(opt)}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
        
        // Attach option selection handlers
        container.querySelectorAll('.option-item input').forEach(radio => {
            radio.addEventListener('change', (e) => this.selectOption(e.target));
        });
    }

    /**
     * Render True/False questions
     */
    renderTrueFalse(container) {
        let html = '';
        this.activityData.questions.forEach((q, index) => {
            html += `
                <div class="question-item">
                    <div class="question-number">Question ${index + 1}</div>
                    <div class="question-text">${this.escapeHtml(q.question)}</div>
                    <div class="options-list">
                        <label class="option-item">
                            <input type="radio" name="q${index}" value="true">
                            <span>✓ True</span>
                        </label>
                        <label class="option-item">
                            <input type="radio" name="q${index}" value="false">
                            <span>✗ False</span>
                        </label>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
        
        // Attach option selection handlers
        container.querySelectorAll('.option-item input').forEach(radio => {
            radio.addEventListener('change', (e) => this.selectOption(e.target));
        });
    }

    /**
     * Render Fill in the Blanks questions
     */
    renderFillBlanks(container) {
        let html = '';
        this.activityData.questions.forEach((q, index) => {
            html += `
                <div class="question-item">
                    <div class="question-number">Question ${index + 1}</div>
                    <div class="question-text">${this.escapeHtml(q.question)}</div>
                    <input type="text" class="fill-blank-input" data-index="${index}" 
                           placeholder="Type your answer here...">
                </div>
            `;
        });
        container.innerHTML = html;
    }

    /**
     * Render Matching activity
     */
    renderMatching(container) {
        const leftItems = this.activityData.pairs.map(p => p.left);
        const rightItems = [...this.activityData.pairs.map(p => p.right)].sort(() => Math.random() - 0.5);
        
        let html = `
            <div class="matching-container">
                <div class="matching-column">
                    <h5 style="text-align: center; margin-bottom: 15px;">Column A</h5>
                    ${leftItems.map((item, i) => `
                        <div class="matching-item" data-left="${i}">
                            ${this.escapeHtml(item)}
                        </div>
                    `).join('')}
                </div>
                <div class="matching-column">
                    <h5 style="text-align: center; margin-bottom: 15px;">Column B</h5>
                    ${rightItems.map((item, i) => `
                        <div class="matching-item" data-right="${this.escapeHtml(item)}">
                            ${this.escapeHtml(item)}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        container.innerHTML = html;
        
        // Attach matching handlers
        container.querySelectorAll('[data-left]').forEach(el => {
            el.addEventListener('click', () => this.selectMatchingItem(el, 'left'));
        });
        container.querySelectorAll('[data-right]').forEach(el => {
            el.addEventListener('click', () => this.selectMatchingItem(el, 'right'));
        });
    }

    /**
     * Render Drag & Drop Sorting activity
     */
    renderDragDropSorting(container) {
        const items = [...this.activityData.items].sort(() => Math.random() - 0.5);
        
        let html = `
            <div class="question-text" style="text-align: center; margin-bottom: 20px;">
                Drag and drop to sort these items in the correct order:
            </div>
            <div class="sortable-list" id="sortableList">
                ${items.map(item => `
                    <div class="sortable-item" data-item="${this.escapeHtml(item)}">
                        <i class="bi bi-grip-vertical" style="margin-right: 10px;"></i>
                        ${this.escapeHtml(item)}
                    </div>
                `).join('')}
            </div>
        `;
        container.innerHTML = html;
        
        // Initialize Sortable
        if (typeof Sortable !== 'undefined') {
            new Sortable(document.getElementById('sortableList'), {
                animation: 150,
                ghostClass: 'dragging'
            });
        }
    }

    /**
     * Render Sequencing activity
     */
    renderSequencing(container) {
        const steps = [...this.activityData.steps].sort(() => Math.random() - 0.5);
        
        let html = `
            <div class="question-text" style="text-align: center; margin-bottom: 20px;">
                Arrange these steps in the correct sequence:
            </div>
            <div class="sortable-list" id="sortableList">
                ${steps.map(step => `
                    <div class="sortable-item" data-step="${this.escapeHtml(step)}">
                        <i class="bi bi-grip-vertical" style="margin-right: 10px;"></i>
                        ${this.escapeHtml(step)}
                    </div>
                `).join('')}
            </div>
        `;
        container.innerHTML = html;
        
        // Initialize Sortable
        if (typeof Sortable !== 'undefined') {
            new Sortable(document.getElementById('sortableList'), {
                animation: 150,
                ghostClass: 'dragging'
            });
        }
    }

    /**
     * Render Image Labeling activity
     */
    renderImageLabeling(container) {
        container.innerHTML = '<p>Image Labeling activity coming soon!</p>';
    }

    /**
     * Render Flashcards activity
     */
    renderFlashcards(container) {
        container.innerHTML = '<p>Flashcards activity coming soon!</p>';
    }

    /**
     * Select option handler
     */
    selectOption(radio) {
        const parent = radio.closest('.options-list');
        parent.querySelectorAll('.option-item').forEach(item => {
            item.classList.remove('selected');
        });
        radio.closest('.option-item').classList.add('selected');
    }

    /**
     * Select matching item handler
     */
    selectMatchingItem(element, side) {
        if (side === 'left') {
            document.querySelectorAll('[data-left]').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            this.selectedLeft = element.dataset.left;
        } else {
            document.querySelectorAll('[data-right]').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            this.selectedRight = element.dataset.right;
        }
        
        if (this.selectedLeft !== null && this.selectedRight !== null) {
            this.matchingAnswers[this.selectedLeft] = this.selectedRight;
            element.style.opacity = '0.5';
            document.querySelector(`[data-left="${this.selectedLeft}"]`).style.opacity = '0.5';
            this.selectedLeft = null;
            this.selectedRight = null;
        }
    }

    /**
     * Collect answers based on activity type
     */
    collectAnswers() {
        const answers = {};
        
        switch(this.activityType) {
            case 'Multiple Choice':
            case 'True/False':
                this.activityData.questions.forEach((q, index) => {
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
                return this.matchingAnswers;
                
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

    /**
     * Attach submit handler
     */
    attachSubmitHandler() {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', () => this.submitAnswers());
        }
    }

    /**
     * Submit answers
     */
    submitAnswers() {
        if (!confirm('Are you sure you want to submit your answers?')) {
            return;
        }
        
        const answers = this.collectAnswers();
        
        fetch(this.basePath + '/learning/submit-activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `activity_id=${this.activityId}&answers=${encodeURIComponent(JSON.stringify(answers))}&time_spent=${this.seconds}`
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(this.timerInterval);
            
            if (data.success) {
                // Confetti animation
                if (typeof confetti !== 'undefined') {
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 }
                    });
                }
                
                // Show result
                const percentage = data.percentage;
                let message = `🎉 ${data.message}\n\n`;
                message += `Score: ${data.score}/${data.total_points} (${percentage}%)\n`;
                if (data.stars_earned > 0) {
                    message += `Stars Earned: ${'⭐'.repeat(data.stars_earned)}`;
                }
                
                alert(message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Cleanup
     */
    destroy() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ActivityPlayer;
}
