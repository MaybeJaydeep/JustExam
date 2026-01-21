<?php
// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    header("location: index.php");
    exit;
}

$adminId = $_SESSION['admin']['admin_id'];

// Get current admin information
try {
    $stmt = $conn->prepare("SELECT * FROM admin_acc WHERE admin_id = ?");
    $stmt->execute([$adminId]);
    $adminInfo = $stmt->fetch();
    
    if (!$adminInfo) {
        die("Admin account not found");
    }
} catch (PDOException $e) {
    error_log("Admin Profile Error: " . $e->getMessage());
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
                        ADMIN PROFILE
                        <div class="page-title-subheading">
                            Manage your account settings and security
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-user icon-gradient bg-plum-plate"></i>
                        Profile Information
                    </div>
                    <div class="card-body">
                        <form method="post" id="updateAdminProfileFrm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="admin_id" value="<?php echo $adminId; ?>">
                            
                            <div class="form-group">
                                <label for="admin_username">Username/Email</label>
                                <input type="email" 
                                       id="admin_username" 
                                       name="admin_username" 
                                       class="form-control" 
                                       value="<?php echo escape($adminInfo['admin_user']); ?>" 
                                       required>
                                <small class="form-text text-muted">This will be your login username</small>
                            </div>

                            <div class="form-group">
                                <label>Account Status</label>
                                <div class="badge badge-success badge-lg">Active</div>
                            </div>

                            <div class="form-group">
                                <label>Account Created</label>
                                <p class="form-control-plaintext">
                                    <?php echo isset($adminInfo['created_at']) ? date('M d, Y H:i', strtotime($adminInfo['created_at'])) : 'N/A'; ?>
                                </p>
                            </div>

                            <div class="form-group">
                                <label>Last Updated</label>
                                <p class="form-control-plaintext">
                                    <?php echo isset($adminInfo['updated_at']) ? date('M d, Y H:i', strtotime($adminInfo['updated_at'])) : 'N/A'; ?>
                                </p>
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

            <!-- Change Password -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-lock icon-gradient bg-strong-bliss"></i>
                        Change Password
                    </div>
                    <div class="card-body">
                        <form method="post" id="changeAdminPasswordFrm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="admin_id" value="<?php echo $adminId; ?>">
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="form-control" 
                                       required>
                                <small class="form-text text-muted">Enter your current password to verify</small>
                            </div>

                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       class="form-control" 
                                       minlength="8" 
                                       required>
                                <small class="form-text text-muted">Minimum 8 characters</small>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-control" 
                                       minlength="8" 
                                       required>
                                <small class="form-text text-muted">Re-enter your new password</small>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-warning">
                                    <i class="pe-7s-key mr-1"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Information -->
        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-shield icon-gradient bg-tempting-azure"></i>
                        Security Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="pe-7s-shield fa-2x text-success mb-2"></i>
                                    <h5>Password Security</h5>
                                    <p class="text-muted">Bcrypt Encrypted</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="pe-7s-lock fa-2x text-primary mb-2"></i>
                                    <h5>Session Security</h5>
                                    <p class="text-muted">Auto Timeout</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="pe-7s-check fa-2x text-info mb-2"></i>
                                    <h5>CSRF Protection</h5>
                                    <p class="text-muted">Token Validated</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="pe-7s-attention fa-2x text-warning mb-2"></i>
                                    <h5>Brute Force</h5>
                                    <p class="text-muted">Protected</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <h6><i class="pe-7s-info mr-2"></i>Security Tips:</h6>
                            <ul class="mb-0">
                                <li>Use a strong password with at least 8 characters</li>
                                <li>Include uppercase, lowercase, numbers, and special characters</li>
                                <li>Don't share your admin credentials with anyone</li>
                                <li>Log out when finished using the admin panel</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update Admin Profile
    $("#updateAdminProfileFrm").on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        $.post("query/updateAdminProfileExe.php", formData, function(res) {
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

    // Change Admin Password
    $("#changeAdminPasswordFrm").on('submit', function(e) {
        e.preventDefault();
        
        let newPassword = $("#new_password").val();
        let confirmPassword = $("#confirm_password").val();
        
        if (newPassword !== confirmPassword) {
            Swal.fire("Error", "New passwords do not match!", "error");
            return;
        }
        
        if (newPassword.length < 8) {
            Swal.fire("Error", "Password must be at least 8 characters long!", "error");
            return;
        }
        
        let formData = $(this).serialize();
        
        $.post("query/changeAdminPasswordExe.php", formData, function(res) {
            if (res.res == "success") {
                Swal.fire("Success", res.msg, "success").then(() => {
                    $("#changeAdminPasswordFrm")[0].reset();
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