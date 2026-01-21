<?php
// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    header("location: index.php");
    exit;
}

// Get available courses for import
try {
    $stmt = $conn->query("SELECT * FROM course_tbl ORDER BY cou_name ASC");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Bulk Import Error: " . $e->getMessage());
    $courses = [];
}
?>

<div class="app-main__outer">
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-cloud-upload icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        BULK IMPORT
                        <div class="page-title-subheading">
                            Import students and questions from CSV files
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Student Import -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-users icon-gradient bg-plum-plate"></i>
                        Import Students
                        <div class="btn-actions-pane-right">
                            <button class="btn btn-sm btn-info" onclick="downloadTemplate('students')">
                                <i class="pe-7s-download mr-1"></i>Download Template
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" id="importStudentsFrm" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="import_type" value="students">
                            
                            <div class="form-group">
                                <label for="student_csv_file">CSV File</label>
                                <input type="file" 
                                       id="student_csv_file" 
                                       name="csv_file" 
                                       class="form-control-file" 
                                       accept=".csv" 
                                       required>
                                <small class="form-text text-muted">
                                    Upload a CSV file with student information. Maximum file size: 2MB
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="default_course">Default Course</label>
                                <select id="default_course" name="default_course" class="form-control" required>
                                    <option value="">Select Default Course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo escape($course['cou_id']); ?>">
                                            <?php echo escape($course['cou_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">
                                    This course will be assigned to students if not specified in CSV
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="default_password">Default Password</label>
                                <input type="text" 
                                       id="default_password" 
                                       name="default_password" 
                                       class="form-control" 
                                       value="student123" 
                                       required>
                                <small class="form-text text-muted">
                                    Default password for imported students (they can change it later)
                                </small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="send_welcome_email" name="send_welcome_email">
                                    <label class="custom-control-label" for="send_welcome_email">
                                        Send welcome email to imported students
                                    </label>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="pe-7s-cloud-upload mr-1"></i>Import Students
                                </button>
                            </div>
                        </form>

                        <div class="alert alert-info mt-3">
                            <h6><i class="pe-7s-info mr-2"></i>CSV Format Requirements:</h6>
                            <ul class="mb-0">
                                <li><strong>Required columns:</strong> fullname, email, gender, birthdate, year_level</li>
                                <li><strong>Optional columns:</strong> course_id, password, status</li>
                                <li><strong>Gender values:</strong> male, female, other</li>
                                <li><strong>Date format:</strong> YYYY-MM-DD (e.g., 1995-06-15)</li>
                                <li><strong>Status values:</strong> active, inactive, suspended (default: active)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Import -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-license icon-gradient bg-strong-bliss"></i>
                        Import Questions
                        <div class="btn-actions-pane-right">
                            <button class="btn btn-sm btn-info" onclick="downloadTemplate('questions')">
                                <i class="pe-7s-download mr-1"></i>Download Template
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" id="importQuestionsFrm" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="import_type" value="questions">
                            
                            <div class="form-group">
                                <label for="question_csv_file">CSV File</label>
                                <input type="file" 
                                       id="question_csv_file" 
                                       name="csv_file" 
                                       class="form-control-file" 
                                       accept=".csv" 
                                       required>
                                <small class="form-text text-muted">
                                    Upload a CSV file with questions. Maximum file size: 5MB
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="target_exam">Target Exam</label>
                                <select id="target_exam" name="target_exam" class="form-control" required>
                                    <option value="">Select Exam</option>
                                    <?php
                                    try {
                                        $stmt = $conn->query("
                                            SELECT et.ex_id, et.ex_title, c.cou_name 
                                            FROM exam_tbl et 
                                            LEFT JOIN course_tbl c ON et.cou_id = c.cou_id 
                                            ORDER BY c.cou_name, et.ex_title
                                        ");
                                        while ($exam = $stmt->fetch()): ?>
                                            <option value="<?php echo escape($exam['ex_id']); ?>">
                                                <?php echo escape($exam['cou_name'] . ' - ' . $exam['ex_title']); ?>
                                            </option>
                                        <?php endwhile;
                                    } catch (PDOException $e) {
                                        echo '<option value="">Error loading exams</option>';
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">
                                    Questions will be added to this exam
                                </small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="replace_questions" name="replace_questions">
                                    <label class="custom-control-label" for="replace_questions">
                                        Replace existing questions in exam
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    If checked, all existing questions will be deleted before import
                                </small>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-success">
                                    <i class="pe-7s-cloud-upload mr-1"></i>Import Questions
                                </button>
                            </div>
                        </form>

                        <div class="alert alert-info mt-3">
                            <h6><i class="pe-7s-info mr-2"></i>CSV Format Requirements:</h6>
                            <ul class="mb-0">
                                <li><strong>Required columns:</strong> question, choice_a, choice_b, choice_c, choice_d, correct_answer</li>
                                <li><strong>Correct answer:</strong> Must match exactly one of the choices</li>
                                <li><strong>Question length:</strong> Maximum 1000 characters</li>
                                <li><strong>Choice length:</strong> Maximum 500 characters each</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import History -->
        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-history icon-gradient bg-tempting-azure"></i>
                        Recent Import History
                    </div>
                    <div class="card-body">
                        <div id="importHistory">
                            <div class="text-center py-4">
                                <i class="pe-7s-clock fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No import history available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Import Students Form
    $("#importStudentsFrm").on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let fileInput = $("#student_csv_file")[0];
        
        if (!fileInput.files[0]) {
            Swal.fire("Error", "Please select a CSV file", "error");
            return;
        }
        
        // Check file size (2MB limit)
        if (fileInput.files[0].size > 2 * 1024 * 1024) {
            Swal.fire("Error", "File size must be less than 2MB", "error");
            return;
        }
        
        Swal.fire({
            title: 'Import Students?',
            text: 'This will import students from the CSV file.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Import',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Importing...',
                    text: 'Please wait while we process your file.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: "query/bulkImportExe.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.res == "success") {
                            Swal.fire("Success", res.msg, "success").then(() => {
                                $("#importStudentsFrm")[0].reset();
                                loadImportHistory();
                            });
                        } else {
                            Swal.fire("Error", res.msg, "error");
                        }
                    },
                    error: function() {
                        Swal.fire("Error", "Something went wrong. Please try again.", "error");
                    }
                });
            }
        });
    });

    // Import Questions Form
    $("#importQuestionsFrm").on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let fileInput = $("#question_csv_file")[0];
        
        if (!fileInput.files[0]) {
            Swal.fire("Error", "Please select a CSV file", "error");
            return;
        }
        
        // Check file size (5MB limit)
        if (fileInput.files[0].size > 5 * 1024 * 1024) {
            Swal.fire("Error", "File size must be less than 5MB", "error");
            return;
        }
        
        Swal.fire({
            title: 'Import Questions?',
            text: 'This will import questions from the CSV file.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Import',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Importing...',
                    text: 'Please wait while we process your file.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: "query/bulkImportExe.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.res == "success") {
                            Swal.fire("Success", res.msg, "success").then(() => {
                                $("#importQuestionsFrm")[0].reset();
                                loadImportHistory();
                            });
                        } else {
                            Swal.fire("Error", res.msg, "error");
                        }
                    },
                    error: function() {
                        Swal.fire("Error", "Something went wrong. Please try again.", "error");
                    }
                });
            }
        });
    });

    // Load import history on page load
    loadImportHistory();
});

function downloadTemplate(type) {
    window.location.href = `query/downloadTemplateExe.php?type=${type}&csrf_token=<?php echo generateCSRFToken(); ?>`;
}

function loadImportHistory() {
    $.get("query/getImportHistoryExe.php", function(res) {
        if (res.res == "success" && res.data.length > 0) {
            let html = '<div class="table-responsive"><table class="table table-striped table-hover">';
            html += '<thead><tr><th>Date</th><th>Type</th><th>File</th><th>Records</th><th>Status</th></tr></thead><tbody>';
            
            res.data.forEach(function(item) {
                html += `<tr>
                    <td>${item.date}</td>
                    <td><span class="badge badge-info">${item.type}</span></td>
                    <td>${item.filename}</td>
                    <td>${item.records}</td>
                    <td><span class="badge badge-${item.status == 'success' ? 'success' : 'danger'}">${item.status}</span></td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            $("#importHistory").html(html);
        }
    }, 'json');
}
</script>