<?php
// Profile management logic
require_once '../../../../config/databade.php';
session_start();

// Get user info from session
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$store = isset($_SESSION['store']) ? $_SESSION['store'] : 'Unknown Store';
$job_role = isset($_SESSION['job_role']) ? $_SESSION['job_role'] : 'rep';

// Fetch additional user details from database
$userDetails = [];
try {
    $stmt = $conn->prepare("SELECT email, telephone FROM signup WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $userDetails = $result->fetch_assoc();
    }
} catch (Exception $e) {
    $error_message = "Error fetching user details: " . $e->getMessage();
}
?>

<div class="section-card fade-transition" id="profile-section">
    <div class="section-header">
        <i class="fas fa-user-circle"></i> My Profile
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-profile">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="avatar-container">
                    <div class="avatar-circle">
                        <span class="initials"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                    </div>
                </div>
                <h4 class="mt-3 mb-1"><?php echo htmlspecialchars($username); ?></h4>
                <p class="text-muted"><?php echo ucfirst(htmlspecialchars($job_role)); ?></p>
                <p class="badge badge-info"><?php echo htmlspecialchars($store); ?></p>
                
                <!-- Logout Button -->
                <!-- <a href="../../controllers/logout.php" class="btn btn-danger btn-lg mt-3 profile-logout-btn">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a> -->
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="profile-form">
                            <div class="form-group">
                                <label for="profile-username">Username</label>
                                <input type="text" class="form-control" id="profile-username" value="<?php echo htmlspecialchars($username); ?>" readonly>
                                <small class="form-text text-muted">Username cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile-store">Store</label>
                                <input type="text" class="form-control" id="profile-store" value="<?php echo htmlspecialchars($store); ?>" readonly>
                                <small class="form-text text-muted">Store assignment cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile-email">Email Address</label>
                                <input type="email" class="form-control" id="profile-email" name="email" value="<?php echo htmlspecialchars($userDetails['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="profile-telephone">Telephone</label>
                                <input type="text" class="form-control" id="profile-telephone" name="telephone" value="<?php echo htmlspecialchars($userDetails['telephone'] ?? ''); ?>">
                            </div>
                            
                            <div class="mt-3 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form id="password-form">
                            <div class="form-group">
                                <label for="current-password">Current Password</label>
                                <input type="password" class="form-control" id="current-password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new-password">New Password</label>
                                <input type="password" class="form-control" id="new-password" name="new_password" required>
                                <small class="form-text text-muted">Password must be at least 6 characters long</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm-password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                            </div>
                            
                            <div class="mt-3 text-right">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key mr-1"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-container {
    display: flex;
    justify-content: center;
}
.avatar-circle {
    width: 150px;
    height: 150px;
    background-color: #2c3e50;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 64px;
    font-weight: bold;
    border: 4px solid #e8e9eb;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    margin-bottom: 10px;
}
.initials {
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 2px;
}

.profile-logout-btn {
    font-size: 18px;
    padding: 10px 20px;
    width: 100%;
    max-width: 220px;
    margin: 15px auto 0;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    border-radius: 6px;
    transition: all 0.2s ease;
}

.profile-logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(0,0,0,0.2);
}

@media (max-width: 576px) {
    .profile-logout-btn {
        font-size: 16px;
        padding: 12px 16px;
    }
}
</style>

<!-- Include SweetAlert2 if not already included -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Profile form submission
    $('#profile-form').submit(function(e) {
        e.preventDefault();
        
        const email = $('#profile-email').val();
        const telephone = $('#profile-telephone').val();
        
        // Validate email format
        if (email && !isValidEmail(email)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email',
                text: 'Please enter a valid email address',
            });
            return false;
        }
        
        // Send AJAX request
        $.ajax({
            url: 'process/update_profile.php',
            type: 'POST',
            data: {
                email: email,
                telephone: telephone
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Profile updated successfully',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to update profile'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again later.'
                });
            }
        });
    });
    
    // Password form submission
    $('#password-form').submit(function(e) {
        e.preventDefault();
        
        const currentPassword = $('#current-password').val();
        const newPassword = $('#new-password').val();
        const confirmPassword = $('#confirm-password').val();
        
        // Validate passwords
        if (newPassword.length < 6) {
            Swal.fire({
                icon: 'error',
                title: 'Password Too Short',
                text: 'Password must be at least 6 characters long'
            });
            return false;
        }
        
        if (newPassword !== confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Passwords Do Not Match',
                text: 'New password and confirmation password do not match'
            });
            return false;
        }
        
        // Send AJAX request
        $.ajax({
            url: 'process/change_password.php',
            type: 'POST',
            data: {
                current_password: currentPassword,
                new_password: newPassword
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Password changed successfully',
                    });
                    
                    // Clear form
                    $('#password-form')[0].reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to change password'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again later.'
                });
            }
        });
    });
    
    // Validate email format
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Phone number validation
    $('#profile-telephone').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>
