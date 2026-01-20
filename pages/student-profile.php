<?php
// Check if user is logged in
if (!isset($_SESSION['student']['is_logged_in']) || $_SESSION['student']['is_logged_in'] !== true) {
    header("location: index.php");
    exit;
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    header("location: index.php");
    exit;
}

$exmneId = $_SESSION['student']['user_id'];

// Get current student information
try {
    $stmt = $conn->prepare("
        SELECT e.*, c.cou_name 
        FROM examinee_tbl e 
        LEFT JOIN course_tbl c ON e.exmne_course = c.cou_id 
        WHERE e.exmne_id = ?
    ");
    $stmt->execute([$exmneId]);
    $studentInfo = $stmt->fetch();
    
    if (!$studentInfo) {
        die("Student account not found");
    }

    // Get all available courses for course selection
    $stmt = $conn->prepare("SELECT * FROM course_tbl ORDER BY cou_name ASC");
    $stmt->execute();
    $allCourses = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Student Profile Error: " . $e->getMessage());
    die("Error loading profile information");
}
?>

<div class="app-main__outer">
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-user icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        MY PROFILE
                        <div class="page-title-subheading">
                            Manage your account information and settings
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-8">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-user icon-gradient bg-plum-plate"></i>
                        Profile Information
                    </div>
                    <div class="card-body">
                        <form method="post" id="updateStudentProfileFrm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="student_id" value="<?php echo $exmneId; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="student_fullname">Full Name</label>
                                        <input type="text" 
                                               id="student_fullname" 
                                               name="student_fullname" 
                                               class="form-control" 
                                               value="<?php echo escape($studentInfo['exmne_fullname']); ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="student_email">Email Address</label>
                                        <input type="email" 
                                               id="student_email" 
                                               name="student_email" 
                                               class="form-control" 
                                               value="<?php echo escape($studentInfo['exmne_email']); ?>" 
                                               required>
                                        <small class="form-text text-muted">This will be your login email</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="student_gender">Gender</label>
                                        <select id="student_gender" name="student_gender" class="form-control" required>
                                            <option value="male" <?php echo $studentInfo['exmne_gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo $studentInfo['exmne_gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                            <option value="other" <?php echo $studentInfo['exmne_gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="student_birthdate">Birth Date</label>
                                        <input type="date" 
                                               id="student_birthdate" 
                                               name="student_birthdate" 
                                               class="form-control" 
                                               value="<?php echo date('Y-m-d', strtotime($studentInfo['exmne_birthdate'])); ?>" 
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="student_course">Course</label>
                                        <select id="student_course" name="student_course" class="form-control" required>
                                            <option value="<?php echo escape($studentInfo['exmne_course']); ?>">
                                                <?php echo escape($studentInfo['cou_name']); ?> (Current)
                                            </option>
                                            <?php foreach ($allCourses as $course): ?>
                                                <?php if ($course['cou_id'] != $studentInfo['exmne_course']): ?>
                                                    <option value="<?php echo escape($course['cou_id']); ?>">
                                                        <?php echo escape($course['cou_name']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="student_year_level">Year Level</label>
                                        <input type="text" 
                                               id="student_year_level" 
                                               name="student_year_level" 
                                               class="form-control" 
                                               value="<?php echo escape($studentInfo['exmne_year_level']); ?>" 
                                               placeholder="e.g., 1st Year, 2nd Year, etc."
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Account Status</label>
                                <div class="badge badge-<?php echo $studentInfo['exmne_status'] == 'active' ? 'success' : 'warning'; ?> badge-lg">
                                    <?php echo escape(ucfirst($studentInfo['exmne_status'])); ?>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="pe-7s-check mr-1"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Account Summary -->
            <div class="col-md-4">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-chart-bars icon-gradient bg-strong-bliss"></i>
                        Account Summary
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-icon-wrapper avatar-icon-lg">
                                <div class="avatar-icon">
                                    <i class="pe-7s-user"></i>
                                </div>
                            </div>
                            <h5 class="mt-2"><?php echo escape($studentInfo['exmne_fullname']); ?></h5>
                            <p class="text-muted"><?php echo escape($studentInfo['cou_name']); ?></p>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Member Since:</small><br>
                            <strong>
                                <?php echo isset($studentInfo['created_at']) ? date('M d, Y', strtotime($studentInfo['created_at'])) : 'N/A'; ?>
                            </strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Last Updated:</small><br>
                            <strong>
                                <?php echo isset($studentInfo['updated_at']) ? date('M d, Y', strtotime($studentInfo['updated_at'])) : 'N/A'; ?>
                            </strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Student ID:</small><br>
                            <strong><?php echo escape($studentInfo['exmne_id']); ?></strong>
                        </div>

                        <hr>

                        <div class="text-center">
                            <a href="?page=home" class="btn btn-info btn-sm">
                                <i class="pe-7s-home mr-1"></i>Dashboard
                            </a>
                            <a href="?page=manage-course" class="btn btn-primary btn-sm">
                                <i class="pe-7s-study mr-1"></i>My Course
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-lock icon-gradient bg-tempting-azure"></i>
                        Change Password
                    </div>
                    <div class="card-body">
                        <form method="post" id="changeStudentPasswordFrm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="student_id" value="<?php echo $exmneId; ?>">
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="form-control" 
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       class="form-control" 
                                       minlength="6" 
                                       required>
                                <small class="form-text text-muted">Minimum 6 characters</small>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-control" 
                                       minlength="6" 
                                       required>
                            </div>

                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="pe-7s-key mr-1"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update Student Profile
    $("#updateStudentProfileFrm").on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        $.post("query/updateStudentProfileExe.php", formData, function(res) {
            if (res.res == "success") {
                Swal.fire("Success", res.msg, "success").then(() => {
                    location.reload();
                });
            } else {
                Swal.fire("Error", res.msg, "error");
            }
        }, 'json').fail(function() {
            Swal.fire("Error", "Something went wrong. Please try again.", "error");
        });
    });

    // Change Student Password
    $("#changeStudentPasswordFrm").on('submit', function(e) {
        e.preventDefault();
        
        let newPassword = $("#new_password").val();
        let confirmPassword = $("#confirm_password").val();
        
        if (newPassword !== confirmPassword) {
            Swal.fire("Error", "New passwords do not match!", "error");
            return;
        }
        
        if (newPassword.length < 6) {
            Swal.fire("Error", "Password must be at least 6 characters long!", "error");
            return;
        }
        
        let formData = $(this).serialize();
        
        $.post("query/changeStudentPasswordExe.php", formData, function(res) {
            if (res.res == "success") {
                Swal.fire("Success", res.msg, "success").then(() => {
                    $("#changeStudentPasswordFrm")[0].reset();
                });
            } else {
                Swal.fire("Error", res.msg, "error");
            }
        }, 'json').fail(function() {
            Swal.fire("Error", "Something went wrong. Please try again.", "error");
        });
    });
});
</script>