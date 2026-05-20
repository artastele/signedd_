<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-06
// Part of: SPED LMS — Returning Student Lookup

$pageTitle = 'Find Returning Student - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-4">
                <i class="bi bi-search text-success"></i> Find Returning Student
            </h1>

            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Search for Previous Enrollment</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Search by LRN or student name to find previous enrollment data. 
                        This will auto-fill most fields in the enrollment form.
                    </p>

                    <!-- School Year Filter -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="school_year_filter" class="form-label">
                                <strong>School Year (Optional)</strong>
                            </label>
                            <select class="form-select" id="school_year_filter" name="school_year_filter">
                                <option value="">All School Years</option>
                                <?php
                                // Generate school years (current and past 5 years)
                                $currentYear = date('Y');
                                for ($i = 0; $i <= 5; $i++) {
                                    $startYear = $currentYear - $i;
                                    $endYear = $startYear + 1;
                                    $sy = $startYear . '-' . $endYear;
                                    $selected = ($i === 0) ? 'selected' : '';
                                    echo "<option value=\"$sy\" $selected>$sy</option>";
                                }
                                ?>
                            </select>
                            <div class="form-text">
                                Filter by school year to find specific enrollment
                            </div>
                        </div>
                    </div>

                    <!-- Search Tabs -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="lrn-tab" data-bs-toggle="tab" 
                                    data-bs-target="#lrn-search" type="button" role="tab">
                                <i class="bi bi-hash"></i> Search by LRN
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="name-tab" data-bs-toggle="tab" 
                                    data-bs-target="#name-search" type="button" role="tab">
                                <i class="bi bi-person"></i> Search by Name
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- LRN Search -->
                        <div class="tab-pane fade show active" id="lrn-search" role="tabpanel">
                            <form id="lrnSearchForm">
                                <div class="mb-3">
                                    <label for="search_lrn" class="form-label">
                                        <strong>Learner Reference Number (LRN)</strong>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" 
                                           id="search_lrn" name="search_lrn" 
                                           placeholder="Enter 12-digit LRN" 
                                           maxlength="12" pattern="\d{12}">
                                    <div class="form-text">
                                        Enter the 12-digit LRN from previous enrollment
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="bi bi-search"></i> Search by LRN
                                </button>
                            </form>
                        </div>

                        <!-- Name Search -->
                        <div class="tab-pane fade" id="name-search" role="tabpanel">
                            <form id="nameSearchForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="search_last_name" class="form-label">
                                            <strong>Last Name <span class="text-danger">*</span></strong>
                                        </label>
                                        <input type="text" class="form-control" 
                                               id="search_last_name" name="search_last_name" 
                                               placeholder="Dela Cruz" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="search_first_name" class="form-label">
                                            <strong>First Name <span class="text-danger">*</span></strong>
                                        </label>
                                        <input type="text" class="form-control" 
                                               id="search_first_name" name="search_first_name" 
                                               placeholder="Juan" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="search_middle_name" class="form-label">
                                            Middle Name
                                        </label>
                                        <input type="text" class="form-control" 
                                               id="search_middle_name" name="search_middle_name" 
                                               placeholder="Santos">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="search_suffix" class="form-label">
                                            Suffix
                                        </label>
                                        <select class="form-select" id="search_suffix" name="search_suffix">
                                            <option value="">None</option>
                                            <option value="Jr.">Jr.</option>
                                            <option value="Sr.">Sr.</option>
                                            <option value="II">II</option>
                                            <option value="III">III</option>
                                            <option value="IV">IV</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="bi bi-search"></i> Search by Name
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div id="searchResults" class="mt-4" style="display: none;">
                        <hr>
                        <h6 class="text-success">
                            <i class="bi bi-check-circle"></i> Search Results
                        </h6>
                        <div id="resultsContainer"></div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" class="text-center mt-4" style="display: none;">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Searching...</span>
                        </div>
                        <p class="mt-2 text-muted">Searching for student...</p>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Can't find the student? You may need to enroll as a <strong>New Student</strong> instead.
                    <a href="<?php echo $basePath; ?>/enrollment" class="ms-2">Go Back</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const basePath = '<?php echo $basePath; ?>';

// LRN Search
document.getElementById('lrnSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const lrn = document.getElementById('search_lrn').value.trim();
    
    if (lrn.length !== 12 || !/^\d{12}$/.test(lrn)) {
        alert('Please enter a valid 12-digit LRN');
        return;
    }
    
    searchStudent('lrn', { lrn: lrn });
});

// Name Search
document.getElementById('nameSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const lastName = document.getElementById('search_last_name').value.trim();
    const firstName = document.getElementById('search_first_name').value.trim();
    const middleName = document.getElementById('search_middle_name').value.trim();
    const suffix = document.getElementById('search_suffix').value;
    
    if (!lastName || !firstName) {
        alert('Last name and first name are required');
        return;
    }
    
    searchStudent('name', {
        last_name: lastName,
        first_name: firstName,
        middle_name: middleName,
        suffix: suffix
    });
});

// Search Student Function
function searchStudent(searchType, params) {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const searchResults = document.getElementById('searchResults');
    const resultsContainer = document.getElementById('resultsContainer');
    
    // Show loading
    loadingIndicator.style.display = 'block';
    searchResults.style.display = 'none';
    resultsContainer.innerHTML = '';
    
    // Add school year filter
    const schoolYear = document.getElementById('school_year_filter').value;
    if (schoolYear) {
        params.school_year = schoolYear;
    }
    
    // Build query string
    const queryParams = new URLSearchParams(params);
    queryParams.append('search_type', searchType);
    
    // Make AJAX request
    fetch(basePath + '/enrollment/search-student?' + queryParams.toString())
        .then(response => response.json())
        .then(data => {
            loadingIndicator.style.display = 'none';
            
            if (data.success && data.students && data.students.length > 0) {
                // Show results
                searchResults.style.display = 'block';
                displayResults(data.students);
            } else {
                searchResults.style.display = 'block';
                resultsContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        No matching student found. Please check your search criteria or try a different school year.
                    </div>
                `;
            }
        })
        .catch(error => {
            loadingIndicator.style.display = 'none';
            searchResults.style.display = 'block';
            resultsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> 
                    Error searching for student. Please try again.
                </div>
            `;
            console.error('Search error:', error);
        });
}

// Display Results
function displayResults(students) {
    const resultsContainer = document.getElementById('resultsContainer');
    
    let html = '<div class="list-group">';
    
    students.forEach(student => {
        const fullName = [
            student.first_name,
            student.middle_name,
            student.last_name,
            student.extension_name
        ].filter(Boolean).join(' ');
        
        const enrollmentDate = new Date(student.submitted_at || student.created_at);
        const formattedDate = enrollmentDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        
        html += `
            <div class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${fullName}</h6>
                        <p class="mb-1">
                            <strong>LRN:</strong> ${student.lrn || 'Not assigned'} | 
                            <strong>Birth Date:</strong> ${student.birth_date} | 
                            <strong>Grade:</strong> ${student.grade_level_to_enroll}
                        </p>
                        <small class="text-muted">
                            Last enrolled: ${formattedDate}
                        </small>
                    </div>
                    <div>
                        <button class="btn btn-success" onclick="selectStudent(${student.id})">
                            <i class="bi bi-check-circle"></i> Select
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    resultsContainer.innerHTML = html;
}

// Select Student
function selectStudent(enrollmentId) {
    if (confirm('Use this student\'s previous enrollment data to auto-fill the form?')) {
        window.location.href = basePath + '/enrollment/create?type=returning&previous_id=' + enrollmentId;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
