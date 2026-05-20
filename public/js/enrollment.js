// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-02
// Part of: SPED LMS — Enrollment JavaScript Utilities

// ============================================
// AUTO-SAVE FUNCTIONALITY
// ============================================

let autoSaveInterval = null;
let sessionKeepaliveInterval = null;
let formChanged = false;
let sessionExpiryTime = null;
let sessionWarningShown = false;

function initAutoSave() {
    // Mark form as changed when any input changes
    document.querySelectorAll('input, select, textarea').forEach(element => {
        element.addEventListener('change', () => {
            formChanged = true;
        });
    });

    // Auto-save every 30 seconds
    autoSaveInterval = setInterval(() => {
        if (formChanged) {
            saveDraft();
        }
    }, 30000); // 30 seconds

    // Session keepalive every 5 minutes (ping server to extend session)
    sessionKeepaliveInterval = setInterval(() => {
        keepSessionAlive();
    }, 300000); // 5 minutes

    // Set session expiry time (60 minutes from now)
    sessionExpiryTime = Date.now() + (60 * 60 * 1000); // 60 minutes

    // Check session expiry every minute
    setInterval(() => {
        checkSessionExpiry();
    }, 60000); // 1 minute

    console.log('Auto-save initialized (every 30 seconds)');
    console.log('Session keepalive initialized (every 5 minutes)');
}

function keepSessionAlive() {
    // Ping server to keep session alive
    fetch(getBasePath() + '/enrollment/keepalive', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Extend session expiry time
            sessionExpiryTime = Date.now() + (60 * 60 * 1000);
            sessionWarningShown = false;
            console.log('Session extended at ' + new Date().toLocaleTimeString());
        }
    })
    .catch(error => {
        console.error('Session keepalive failed:', error);
    });
}

function checkSessionExpiry() {
    const timeLeft = sessionExpiryTime - Date.now();
    const minutesLeft = Math.floor(timeLeft / 60000);

    // Show warning when 5 minutes left
    if (minutesLeft <= 5 && minutesLeft > 0 && !sessionWarningShown) {
        sessionWarningShown = true;
        showSessionWarning(minutesLeft);
    }

    // Session expired
    if (timeLeft <= 0) {
        showToast('Your session has expired. Please save your work and log in again.', 'error');
        clearInterval(autoSaveInterval);
        clearInterval(sessionKeepaliveInterval);
    }
}

function showSessionWarning(minutesLeft) {
    const warningDiv = document.createElement('div');
    warningDiv.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    warningDiv.style.zIndex = '9999';
    warningDiv.style.maxWidth = '500px';
    warningDiv.innerHTML = `
        <h5 class="alert-heading"><i class="bi bi-clock-history"></i> Session Expiring Soon</h5>
        <p>Your session will expire in <strong>${minutesLeft} minute(s)</strong>.</p>
        <button type="button" class="btn btn-warning btn-sm" onclick="keepSessionAlive(); this.closest('.alert').remove();">
            <i class="bi bi-arrow-clockwise"></i> Extend Session
        </button>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(warningDiv);
}

function saveDraft() {
    const formData = new FormData(document.getElementById('enrollmentForm'));
    
    fetch(getBasePath() + '/enrollment/save-draft', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            formChanged = false;
            showToast('Draft saved automatically', 'success');
            console.log('Draft saved at ' + new Date().toLocaleTimeString());
        }
    })
    .catch(error => {
        console.error('Auto-save failed:', error);
    });
}

function manualSave() {
    saveDraft();
    showToast('Saving draft...', 'info');
}

// ============================================
// SIGNATURE PAD INTEGRATION
// ============================================

let signaturePad = null;

function initSignaturePad(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    // Initialize signature pad
    signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 1,
        maxWidth: 2.5
    });

    // Resize canvas to fit container
    resizeCanvas(canvas);
    window.addEventListener('resize', () => resizeCanvas(canvas));

    console.log('Signature pad initialized');
}

function resizeCanvas(canvas) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    
    if (signaturePad) {
        signaturePad.clear();
    }
}

function clearSignature() {
    if (signaturePad) {
        signaturePad.clear();
    }
}

function getSignatureData() {
    if (signaturePad && !signaturePad.isEmpty()) {
        return signaturePad.toDataURL('image/png');
    }
    return null;
}

function setSignatureData(dataURL) {
    if (signaturePad && dataURL) {
        signaturePad.fromDataURL(dataURL);
    }
}

// ============================================
// LOCATION DROPDOWNS (Dynamic Loading)
// ============================================

function initLocationDropdowns() {
    // Load provinces on page load
    loadProvinces('current_province', 'current_city', 'current_barangay');
    loadProvinces('permanent_province', 'permanent_city', 'permanent_barangay');

    // Province change handlers
    document.getElementById('current_province')?.addEventListener('change', function() {
        loadCities(this.value, 'current_city', 'current_barangay');
    });

    document.getElementById('permanent_province')?.addEventListener('change', function() {
        loadCities(this.value, 'permanent_city', 'permanent_barangay');
    });

    // City change handlers
    document.getElementById('current_city')?.addEventListener('change', function() {
        const province = document.getElementById('current_province').value;
        loadBarangays(province, this.value, 'current_barangay');
    });

    document.getElementById('permanent_city')?.addEventListener('change', function() {
        const province = document.getElementById('permanent_province').value;
        loadBarangays(province, this.value, 'permanent_barangay');
    });

    // "Same as current address" checkbox
    document.getElementById('same_as_current_address')?.addEventListener('change', function() {
        if (this.checked) {
            copyCurrentToPermanent();
        }
    });

    console.log('Location dropdowns initialized');
}

function loadProvinces(provinceSelectId, citySelectId, barangaySelectId) {
    fetch(getBasePath() + '/api-provinces.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById(provinceSelectId);
                if (select) {
                    select.innerHTML = '<option value="">-- Select Province --</option>';
                    data.provinces.forEach(province => {
                        select.innerHTML += `<option value="${province}">${province}</option>`;
                    });
                }
            }
        })
        .catch(error => console.error('Failed to load provinces:', error));
}

function loadCities(province, citySelectId, barangaySelectId) {
    if (!province) return;

    fetch(getBasePath() + '/api-cities.php?province=' + encodeURIComponent(province))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById(citySelectId);
                if (select) {
                    select.innerHTML = '<option value="">-- Select City/Municipality --</option>';
                    data.cities.forEach(city => {
                        select.innerHTML += `<option value="${city}">${city}</option>`;
                    });
                }
                
                // Clear barangay dropdown
                const barangaySelect = document.getElementById(barangaySelectId);
                if (barangaySelect) {
                    barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                }
            }
        })
        .catch(error => console.error('Failed to load cities:', error));
}

function loadBarangays(province, city, barangaySelectId) {
    if (!province || !city) return;

    fetch(getBasePath() + '/api-barangays.php?province=' + encodeURIComponent(province) + '&city=' + encodeURIComponent(city))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById(barangaySelectId);
                if (select) {
                    select.innerHTML = '<option value="">-- Select Barangay --</option>';
                    data.barangays.forEach(barangay => {
                        select.innerHTML += `<option value="${barangay}">${barangay}</option>`;
                    });
                }
            }
        })
        .catch(error => console.error('Failed to load barangays:', error));
}

function copyCurrentToPermanent() {
    document.getElementById('permanent_house_no').value = document.getElementById('current_house_no').value;
    document.getElementById('permanent_province').value = document.getElementById('current_province').value;
    
    // Trigger change to load cities
    const event = new Event('change');
    document.getElementById('permanent_province').dispatchEvent(event);
    
    // Wait for cities to load, then set city
    setTimeout(() => {
        document.getElementById('permanent_city').value = document.getElementById('current_city').value;
        document.getElementById('permanent_city').dispatchEvent(event);
        
        // Wait for barangays to load, then set barangay
        setTimeout(() => {
            document.getElementById('permanent_barangay').value = document.getElementById('current_barangay').value;
            document.getElementById('permanent_zip_code').value = document.getElementById('current_zip_code').value;
        }, 500);
    }, 500);
}

// ============================================
// FORM VALIDATION
// ============================================

function validateEnrollmentForm() {
    let isValid = true;
    const errors = [];

    // Required fields
    const requiredFields = [
        { id: 'last_name', label: 'Last Name' },
        { id: 'first_name', label: 'First Name' },
        { id: 'birth_date', label: 'Birth Date' },
        { id: 'sex', label: 'Sex' },
        { id: 'grade_level_to_enroll', label: 'Grade Level' }
    ];

    requiredFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (element && !element.value.trim()) {
            isValid = false;
            errors.push(field.label + ' is required');
            element.classList.add('is-invalid');
        } else if (element) {
            element.classList.remove('is-invalid');
        }
    });

    // Signature validation
    if (signaturePad && signaturePad.isEmpty()) {
        isValid = false;
        errors.push('Parent/Guardian signature is required');
    }

    // Show errors
    if (!isValid) {
        showToast(errors.join('<br>'), 'error');
    }

    return isValid;
}

// ============================================
// MULTI-STEP FORM NAVIGATION
// ============================================

let currentStep = 1;
const totalSteps = 7;

function showStep(step) {
    // Hide all steps
    document.querySelectorAll('.form-step').forEach(el => {
        el.style.display = 'none';
    });

    // Show current step
    const stepElement = document.getElementById('step-' + step);
    if (stepElement) {
        stepElement.style.display = 'block';
    }

    // Update progress bar
    updateProgressBar(step);

    // Update buttons
    document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'inline-block';
    document.getElementById('nextBtn').style.display = step === totalSteps ? 'none' : 'inline-block';
    document.getElementById('submitBtn').style.display = step === totalSteps ? 'inline-block' : 'none';

    currentStep = step;
}

function nextStep() {
    if (currentStep < totalSteps) {
        showStep(currentStep + 1);
    }
}

function prevStep() {
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

function updateProgressBar(step) {
    const progress = (step / totalSteps) * 100;
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.textContent = 'Step ' + step + ' of ' + totalSteps;
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function calculateAge() {
    const birthDateInput = document.getElementById('birth_date');
    const ageInput = document.getElementById('age');
    
    if (!birthDateInput || !ageInput) return;
    
    const birthDate = new Date(birthDateInput.value);
    if (!birthDateInput.value || isNaN(birthDate.getTime())) {
        ageInput.value = '';
        return;
    }
    
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    // Adjust age if birthday hasn't occurred this year
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    ageInput.value = age >= 0 ? age : '';
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, type === 'error' ? 0 : 3000);
}

function getBasePath() {
    // Get base path from script tag or detect from current URL
    const scripts = document.getElementsByTagName('script');
    for (let script of scripts) {
        if (script.src.includes('enrollment.js')) {
            const url = new URL(script.src);
            const path = url.pathname;
            const basePath = path.substring(0, path.lastIndexOf('/js/'));
            return basePath || '';
        }
    }
    return '';
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Enrollment utilities loaded');
    
    // Initialize components if elements exist
    if (document.getElementById('enrollmentForm')) {
        initAutoSave();
        initLocationDropdowns();
    }
    
    if (document.getElementById('signaturePad')) {
        initSignaturePad('signaturePad');
    }
    
    if (document.querySelector('.form-step')) {
        showStep(1);
    }
    
    // Calculate age on page load if birth date exists
    if (document.getElementById('birth_date') && document.getElementById('birth_date').value) {
        calculateAge();
    }
});
