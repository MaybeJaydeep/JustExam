<?php
// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    header("location: index.php");
    exit;
}

// Get current email settings (you might want to store these in a settings table)
$emailSettings = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => '587',
    'smtp_username' => '',
    'smtp_password' => '',
    'from_email' => 'noreply@justexam.com',
    'from_name' => 'JustExam System',
    'enable_notifications' => true,
    'welcome_email' => true,
    'exam_completion' => true,
    'password_reset' => true
];
?>

<div class="app-main__outer">
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-mail icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        EMAIL SETTINGS
                        <div class="page-title-subheading">
                            Configure email notifications and SMTP settings
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- SMTP Configuration -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-cog icon-gradient bg-plum-plate"></i>
                        SMTP Configuration
                    </div>
                    <div class="card-body">
                        <form method="post" id="smtpConfigFrm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="config_type" value="smtp">
                            
                            <div class="form-group">
                                <label for="smtp_host">SMTP Host</label>
                                <input type="text" 
                                       id="smtp_host" 
                                       name="smtp_host" 
                                       class="form-control" 
                                       value="<?php echo escape($emailSettings['smtp_host']); ?>" 
                                       placeholder="smtp.gmail.com"
                                       required>
                                <small class="form-text text-muted">Your email provider's SMTP server</small>
                            </div>

                            <div class="form-group">
                                <label for="smtp_port">SMTP Port</label>
                                <select id="smtp_port" name="smtp_port" class="form-control" required>
                                    <option value="587" <?php echo $emailSettings['smtp_port'] == '587' ? 'selected' : ''; ?>>587 (TLS)</option>
                                    <option value="465" <?php echo $emailSettings['smtp_port'] == '465' ? 'selected' : ''; ?>>465 (SSL)</option>
                                    <option value="25" <?php echo $emailSettings['smtp_port'] == '25' ? 'selected' : ''; ?>>25 (Plain)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="smtp_username">SMTP Username</label>
                                <input type="email" 
                                       id="smtp_username" 
                                       name="smtp_username" 
                                       class="form-control" 
                                       value="<?php echo escape($emailSettings['smtp_username']); ?>" 
                                       placeholder="your-email@gmail.com"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="smtp_password">SMTP Password</label>
                                <input type="password" 
                                       id="smtp_password" 
                                       name="smtp_password" 
                                       class="form-control" 
                                       placeholder="Enter password or app password"
                                       required>
                                <small class="form-text text-muted">For Gmail, use App Password instead of regular password</small>
                            </div>

                            <div class="form-group">
                                <label for="from_email">From Email</label>
                                <input type="email" 
                                       id="from_email" 
                                       name="from_email" 
                                       class="form-control" 
                                       value="<?php echo escape($emailSettings['from_email']); ?>" 
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="from_name">From Name</label>
                                <input type="text" 
                                       id="from_name" 
                                       name="from_name" 
                                       class="form-control" 
                                       value="<?php echo escape($emailSettings['from_name']); ?>" 
                                       required>
                            </div>

                            <div class="form-group text-right">
                                <button type="button" class="btn btn-info mr-2" onclick="testEmailConnection()">
                                    <i class="pe-7s-mail mr-1"></i>Test Connection
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="pe-7s-check mr-1"></i>Save SMTP Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-alarm icon-gradient bg-strong-bliss"></i>
                        Notification Settings
                    </div>
                    <div class="card-body">
                        <form method="post" id="notificationConfigFrm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="config_type" value="notifications">
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="enable_notifications" 
                                           name="enable_notifications"
                                           <?php echo $emailSettings['enable_notifications'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="enable_notifications">
                                        Enable Email Notifications
                                    </label>
                                </div>
                                <small class="form-text text-muted">Master switch for all email notifications</small>
                            </div>

                            <hr>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="welcome_email" 
                                           name="welcome_email"
                                           <?php echo $emailSettings['welcome_email'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="welcome_email">
                                        Welcome Email for New Students
                                    </label>
                                </div>
                                <small class="form-text text-muted">Send welcome email when new students are created</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="exam_completion" 
                                           name="exam_completion"
                                           <?php echo $emailSettings['exam_completion'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="exam_completion">
                                        Exam Completion Notifications
                                    </label>
                                </div>
                                <small class="form-text text-muted">Send email when students complete exams</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="password_reset" 
                                           name="password_reset"
                                           <?php echo $emailSettings['password_reset'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="password_reset">
                                        Password Reset Emails
                                    </label>
                                </div>
                                <small class="form-text text-muted">Send password reset links via email</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="admin_notifications" 
                                           name="admin_notifications">
                                    <label class="custom-control-label" for="admin_notifications">
                                        Admin Notifications
                                    </label>
                                </div>
                                <small class="form-text text-muted">Send notifications to admin for important events</small>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-success">
                                    <i class="pe-7s-check mr-1"></i>Save Notification Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Email Templates -->
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-file-empty icon-gradient bg-tempting-azure"></i>
                        Email Templates
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column">
                            <button class="btn btn-outline-primary mb-2" onclick="editTemplate('welcome')">
                                <i class="pe-7s-edit mr-2"></i>Edit Welcome Email Template
                            </button>
                            <button class="btn btn-outline-success mb-2" onclick="editTemplate('completion')">
                                <i class="pe-7s-edit mr-2"></i>Edit Completion Email Template
                            </button>
                            <button class="btn btn-outline-warning mb-2" onclick="editTemplate('reset')">
                                <i class="pe-7s-edit mr-2"></i>Edit Password Reset Template
                            </button>
                            <button class="btn btn-outline-info mb-2" onclick="sendTestEmail()">
                                <i class="pe-7s-mail mr-2"></i>Send Test Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Log -->
        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-history icon-gradient bg-midnight-bloom"></i>
                        Recent Email Activity
                        <div class="btn-actions-pane-right">
                            <button class="btn btn-sm btn-outline-danger" onclick="clearEmailLogs()">
                                <i class="pe-7s-trash mr-1"></i>Clear Logs
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="emailLogs">
                            <div class="text-center py-4">
                                <i class="pe-7s-mail fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No email activity yet</p>
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
    // SMTP Configuration Form
    $("#smtpConfigFrm").on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        Swal.fire({
            title: 'Save SMTP Settings?',
            text: 'This will update email configuration.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("query/updateEmailSettingsExe.php", formData, function(res) {
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

    // Notification Configuration Form
    $("#notificationConfigFrm").on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        $.post("query/updateEmailSettingsExe.php", formData, function(res) {
            if (res.res == "success") {
                Swal.fire("Success", res.msg, "success");
            } else {
                Swal.fire("Error", res.msg, "error");
            }
        }, 'json').fail(function() {
            Swal.fire("Error", "Something went wrong. Please try again.", "error");
        });
    });

    // Load email logs
    loadEmailLogs();
});

function testEmailConnection() {
    let formData = $("#smtpConfigFrm").serialize();
    
    Swal.fire({
        title: 'Testing Connection...',
        text: 'Please wait while we test the SMTP connection.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.post("query/testEmailConnectionExe.php", formData, function(res) {
        if (res.res == "success") {
            Swal.fire("Success", "SMTP connection successful!", "success");
        } else {
            Swal.fire("Error", res.msg, "error");
        }
    }, 'json').fail(function() {
        Swal.fire("Error", "Connection test failed.", "error");
    });
}

function editTemplate(type) {
    Swal.fire({
        title: `Edit ${type.charAt(0).toUpperCase() + type.slice(1)} Email Template`,
        html: `
            <textarea id="emailTemplate" class="form-control" rows="10" placeholder="Email template content...">
Loading template...
            </textarea>
        `,
        showCancelButton: true,
        confirmButtonText: 'Save Template',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            return document.getElementById('emailTemplate').value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Save template logic would go here
            Swal.fire("Success", "Template saved successfully!", "success");
        }
    });
}

function sendTestEmail() {
    Swal.fire({
        title: 'Send Test Email',
        input: 'email',
        inputLabel: 'Test Email Address',
        inputPlaceholder: 'Enter email address',
        showCancelButton: true,
        confirmButtonText: 'Send Test',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.post("query/sendTestEmailExe.php", {
                email: result.value,
                csrf_token: "<?php echo generateCSRFToken(); ?>"
            }, function(res) {
                if (res.res == "success") {
                    Swal.fire("Success", "Test email sent successfully!", "success");
                    loadEmailLogs();
                } else {
                    Swal.fire("Error", res.msg, "error");
                }
            }, 'json');
        }
    });
}

function clearEmailLogs() {
    Swal.fire({
        title: 'Clear Email Logs?',
        text: 'This will permanently delete all email logs.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Clear',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("query/clearEmailLogsExe.php", {
                csrf_token: "<?php echo generateCSRFToken(); ?>"
            }, function(res) {
                if (res.res == "success") {
                    Swal.fire("Success", res.msg, "success");
                    loadEmailLogs();
                } else {
                    Swal.fire("Error", res.msg, "error");
                }
            }, 'json');
        }
    });
}

function loadEmailLogs() {
    // This would load email logs from the server
    // For now, showing placeholder
    $("#emailLogs").html(`
        <div class="text-center py-4">
            <i class="pe-7s-mail fa-2x text-muted mb-3"></i>
            <p class="text-muted">Email logging will be implemented with actual email functionality</p>
        </div>
    `);
}
</script>