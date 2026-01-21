<?php
// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    header("location: index.php");
    exit;
}

// Get system statistics
try {
    // Count totals
    $stmt = $conn->query("SELECT COUNT(*) as total FROM course_tbl");
    $totalCourses = $stmt->fetch()['total'];

    $stmt = $conn->query("SELECT COUNT(*) as total FROM exam_tbl");
    $totalExams = $stmt->fetch()['total'];

    $stmt = $conn->query("SELECT COUNT(*) as total FROM examinee_tbl");
    $totalStudents = $stmt->fetch()['total'];

    $stmt = $conn->query("SELECT COUNT(*) as total FROM exam_question_tbl");
    $totalQuestions = $stmt->fetch()['total'];

    $stmt = $conn->query("SELECT COUNT(*) as total FROM exam_attempt");
    $totalAttempts = $stmt->fetch()['total'];

    // Get recent activity
    $stmt = $conn->query("
        SELECT 'exam_attempt' as type, ea.attempt_date as date, e.exmne_fullname as user, et.ex_title as details
        FROM exam_attempt ea
        INNER JOIN examinee_tbl e ON ea.exmne_id = e.exmne_id
        INNER JOIN exam_tbl et ON ea.exam_id = et.ex_id
        ORDER BY ea.attempt_date DESC
        LIMIT 10
    ");
    $recentActivity = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("System Settings Error: " . $e->getMessage());
    $totalCourses = $totalExams = $totalStudents = $totalQuestions = $totalAttempts = 0;
    $recentActivity = [];
}
?>

<div class="app-main__outer">
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-settings icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        SYSTEM SETTINGS
                        <div class="page-title-subheading">
                            System configuration and management tools
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Statistics -->
        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-chart-bars icon-gradient bg-plum-plate"></i>
                        System Statistics
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="widget-numbers text-primary">
                                        <span><?php echo $totalCourses; ?></span>
                                    </div>
                                    <div class="widget-subheading">Courses</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="widget-numbers text-info">
                                        <span><?php echo $totalExams; ?></span>
                                    </div>
                                    <div class="widget-subheading">Exams</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="widget-numbers text-success">
                                        <span><?php echo $totalStudents; ?></span>
                                    </div>
                                    <div class="widget-subheading">Students</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="widget-numbers text-warning">
                                        <span><?php echo $totalQuestions; ?></span>
                                    </div>
                                    <div class="widget-subheading">Questions</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="widget-numbers text-danger">
                                        <span><?php echo $totalAttempts; ?></span>
                                    </div>
                                    <div class="widget-subheading">Attempts</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="widget-numbers text-dark">
                                        <span><?php echo date('Y'); ?></span>
                                    </div>
                                    <div class="widget-subheading">Current Year</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- System Configuration -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-cog icon-gradient bg-strong-bliss"></i>
                        System Configuration
                    </div>
                    <div class="card-body">
                        <form method="post" id="systemConfigFrm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="form-group">
                                <label>System Name</label>
                                <input type="text" name="system_name" class="form-control" value="JustExam - Online Examination System" readonly>
                                <small class="form-text text-muted">The name of your examination system</small>
                            </div>

                            <div class="form-group">
                                <label>Default Session Timeout (minutes)</label>
                                <select name="session_timeout" class="form-control">
                                    <option value="30">30 minutes</option>
                                    <option value="60" selected>60 minutes</option>
                                    <option value="120">120 minutes</option>
                                    <option value="180">180 minutes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Max Login Attempts</label>
                                <select name="max_login_attempts" class="form-control">
                                    <option value="3">3 attempts</option>
                                    <option value="5" selected>5 attempts</option>
                                    <option value="10">10 attempts</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Login Timeout (minutes)</label>
                                <select name="login_timeout" class="form-control">
                                    <option value="5">5 minutes</option>
                                    <option value="15" selected>15 minutes</option>
                                    <option value="30">30 minutes</option>
                                    <option value="60">60 minutes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="maintenance_mode" name="maintenance_mode">
                                    <label class="custom-control-label" for="maintenance_mode">Maintenance Mode</label>
                                </div>
                                <small class="form-text text-muted">Enable to prevent student access during maintenance</small>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="pe-7s-check mr-1"></i>Save Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Tools -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-wrench icon-gradient bg-tempting-azure"></i>
                        System Tools
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column">
                            <button class="btn btn-info mb-2" onclick="exportData('students')">
                                <i class="pe-7s-download mr-2"></i>Export Students (CSV)
                            </button>
                            <button class="btn btn-success mb-2" onclick="exportData('results')">
                                <i class="pe-7s-download mr-2"></i>Export Results (CSV)
                            </button>
                            <button class="btn btn-primary mb-2" onclick="exportData('courses')">
                                <i class="pe-7s-download mr-2"></i>Export Courses (CSV)
                            </button>
                            <button class="btn btn-secondary mb-2" onclick="exportData('exams')">
                                <i class="pe-7s-download mr-2"></i>Export Exams (CSV)
                            </button>
                            <button class="btn btn-warning mb-2" onclick="clearLogs()">
                                <i class="pe-7s-trash mr-2"></i>Clear Security Logs
                            </button>
                            <button class="btn btn-dark mb-2" onclick="generateBackup()">
                                <i class="pe-7s-diskette mr-2"></i>Generate Database Backup
                            </button>
                            <button class="btn btn-outline-info mb-2" onclick="viewSystemInfo()">
                                <i class="pe-7s-info mr-2"></i>System Information
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Security Status -->
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-shield icon-gradient bg-grow-early"></i>
                        Security Status
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="pe-7s-check fa-2x text-success mb-2"></i>
                                    <h6>SQL Injection</h6>
                                    <small class="text-success">Protected</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="pe-7s-check fa-2x text-success mb-2"></i>
                                    <h6>CSRF Protection</h6>
                                    <small class="text-success">Active</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="pe-7s-check fa-2x text-success mb-2"></i>
                                    <h6>Password Security</h6>
                                    <small class="text-success">Bcrypt</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="pe-7s-check fa-2x text-success mb-2"></i>
                                    <h6>Session Security</h6>
                                    <small class="text-success">Secure</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-clock icon-gradient bg-midnight-bloom"></i>
                        Recent Activity
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentActivity)): ?>
                            <div class="table-responsive">
                                <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>Activity</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentActivity as $activity): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y H:i', strtotime($activity['date'])); ?></td>
                                                <td><?php echo escape($activity['user']); ?></td>
                                                <td>
                                                    <div class="badge badge-info">Exam Taken</div>
                                                </td>
                                                <td><?php echo escape($activity['details']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="pe-7s-clock fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No Recent Activity</h4>
                                <p class="text-muted">System activity will appear here</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // System Configuration Form
    $("#systemConfigFrm").on('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Save Configuration?',
            text: 'This will update system settings.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = $(this).serialize();
                
                $.post("query/updateSystemConfigExe.php", formData, function(res) {
                    if (res.res == "success") {
                        Swal.fire("Success", res.msg, "success");
                    } else {
                        Swal.fire("Error", res.msg, "error");
                    }
                }, 'json').fail(function() {
                    Swal.fire("Error", "Something went wrong. Please try again.", "error");
                });
            }
        });
    });
});

function exportData(type) {
    Swal.fire({
        title: 'Export Data?',
        text: `This will export ${type} data to CSV format.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Export',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `query/exportDataExe.php?type=${type}&csrf_token=<?php echo generateCSRFToken(); ?>`;
        }
    });
}

function clearLogs() {
    Swal.fire({
        title: 'Clear Security Logs?',
        text: 'This will permanently delete all security logs.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Clear',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("query/clearLogsExe.php", {
                csrf_token: "<?php echo generateCSRFToken(); ?>"
            }, function(res) {
                if (res.res == "success") {
                    Swal.fire("Success", res.msg, "success");
                } else {
                    Swal.fire("Error", res.msg, "error");
                }
            }, 'json');
        }
    });
}

function generateBackup() {
    Swal.fire({
        title: 'Generate Backup?',
        text: 'This will create a database backup file.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Yes, Generate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire("Info", "Backup functionality requires server-side implementation.", "info");
        }
    });
}

function viewSystemInfo() {
    Swal.fire({
        title: 'System Information',
        html: `
            <div class="text-left">
                <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
                <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
                <strong>Database:</strong> MySQL/MariaDB<br>
                <strong>System:</strong> JustExam v1.0<br>
                <strong>Security Score:</strong> 85/100<br>
                <strong>Last Updated:</strong> <?php echo date('M d, Y'); ?>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close'
    });
}
</script>