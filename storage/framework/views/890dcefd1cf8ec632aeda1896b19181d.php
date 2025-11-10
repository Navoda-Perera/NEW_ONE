

<?php $__env->startSection('title', 'Add New Customer'); ?>

<?php $__env->startSection('content'); ?>
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-person-plus me-2 text-success"></i>
                    Add New Customer
                </h2>
                <p class="text-muted mb-0">Create a new customer account for your location</p>
            </div>
            <a href="<?php echo e(route('pm.customers.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Customers
            </a>
        </div>
    </div>
</div>

<!-- Customer Creation Form -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-lg">
            <!-- Form Header -->
            <div class="card-header bg-gradient text-white py-4" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <div class="text-center">
                    <h4 class="mb-2">
                        <i class="bi bi-person-circle me-2"></i>
                        Customer Registration Form
                    </h4>
                    <p class="mb-0 opacity-75">Fill in the customer details below</p>
                </div>
            </div>

            <!-- Form Body -->
            <div class="card-body p-4">
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger shadow-sm">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Please correct the following errors:</strong>
                        </div>
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('pm.customers.store')); ?>">
                    <?php echo csrf_field(); ?>

                    <!-- Personal Information Section -->
                    <div class="section-header mb-3">
                        <h5 class="text-primary fw-bold border-bottom pb-2">
                            <i class="bi bi-person me-2"></i>Personal Information
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Full Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>Full Name *
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="name" 
                                   name="name" 
                                   value="<?php echo e(old('name')); ?>" 
                                   placeholder="Enter full name"
                                   required>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- NIC Number -->
                        <div class="col-md-6">
                            <label for="nic" class="form-label fw-semibold">
                                <i class="bi bi-credit-card me-1"></i>NIC Number *
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg <?php $__errorArgs = ['nic'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="nic" 
                                   name="nic" 
                                   value="<?php echo e(old('nic')); ?>" 
                                   placeholder="e.g., 199012345678 or 901234567V"
                                   pattern="^(\d{12}|\d{9}[VX])$"
                                   required>
                            <?php $__errorArgs = ['nic'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Company Information Section -->
                    <div class="section-header mb-3">
                        <h5 class="text-primary fw-bold border-bottom pb-2">
                            <i class="bi bi-building me-2"></i>Company Information
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Company Name -->
                        <div class="col-md-6">
                            <label for="company_name" class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>Company Name
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="company_name" 
                                   name="company_name" 
                                   value="<?php echo e(old('company_name')); ?>" 
                                   placeholder="Enter company name (optional)">
                            <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Company BR Number -->
                        <div class="col-md-6">
                            <label for="company_br" class="form-label fw-semibold">
                                <i class="bi bi-card-text me-1"></i>Company BR Number
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg <?php $__errorArgs = ['company_br'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="company_br" 
                                   name="company_br" 
                                   value="<?php echo e(old('company_br')); ?>" 
                                   placeholder="Enter BR number (optional)">
                            <?php $__errorArgs = ['company_br'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="section-header mb-3">
                        <h5 class="text-primary fw-bold border-bottom pb-2">
                            <i class="bi bi-telephone me-2"></i>Contact Information
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Email Address -->
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i>Email Address
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo e(old('email')); ?>" 
                                   placeholder="customer@example.com (optional)">
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Mobile Number -->
                        <div class="col-md-6">
                            <label for="mobile" class="form-label fw-semibold">
                                <i class="bi bi-phone me-1"></i>Mobile Number *
                            </label>
                            <input type="tel" 
                                   class="form-control form-control-lg <?php $__errorArgs = ['mobile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="mobile" 
                                   name="mobile" 
                                   value="<?php echo e(old('mobile')); ?>" 
                                   placeholder="07xxxxxxxx"
                                   pattern="^0[0-9]{9}$"
                                   required>
                            <?php $__errorArgs = ['mobile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label for="address" class="form-label fw-semibold">
                                <i class="bi bi-geo-alt me-1"></i>Full Address *
                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="address" 
                                      name="address" 
                                      rows="3" 
                                      placeholder="Enter complete address including street, city, postal code"
                                      required><?php echo e(old('address')); ?></textarea>
                            <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <div class="section-header mb-3">
                        <h5 class="text-primary fw-bold border-bottom pb-2">
                            <i class="bi bi-shield-lock me-2"></i>Account Security
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Password -->
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">
                                <i class="bi bi-lock me-1"></i>Password *
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Minimum 8 characters"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <small class="text-muted">Password must be at least 8 characters long</small>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="bi bi-lock-fill me-1"></i>Confirm Password *
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Confirm password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Location Assignment -->
                    <div class="section-header mb-3">
                        <h5 class="text-primary fw-bold border-bottom pb-2">
                            <i class="bi bi-building me-2"></i>Location Assignment
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-info-circle me-2"></i>
                                <div>
                                    <strong>Auto-Assignment:</strong> Customer will be automatically assigned to your location.
                                    <?php if(isset($locations) && $locations->count() > 0): ?>
                                        <br><small class="text-muted">Location: <?php echo e($locations->first()->name ?? 'Your Current Location'); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="card-footer bg-light p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    All fields marked with (*) are required
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="reset" class="btn btn-light btn-lg">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-person-plus me-2"></i>Create Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
/* Modern Form Styling */
.form-control-lg {
    padding: 0.75rem 1rem;
    font-size: 1rem;
    border-radius: 0.5rem;
}

.section-header h5 {
    color: var(--bs-primary);
    font-weight: 600;
}

.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-header {
    border: none;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
}

/* Input Focus Effects */
.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Alert Styling */
.alert {
    border: none;
    border-radius: 0.5rem;
}

/* Validation Styling */
.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    font-size: 0.875rem;
    font-weight: 500;
}

/* Password Toggle Button */
.input-group .btn {
    border-left: none;
}

.input-group .form-control:focus + .btn {
    border-color: #28a745;
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
// Password Toggle Function
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Form Validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirmation');

    // Real-time password confirmation validation
    passwordConfirm.addEventListener('input', function() {
        if (password.value !== passwordConfirm.value) {
            passwordConfirm.setCustomValidity('Passwords do not match');
        } else {
            passwordConfirm.setCustomValidity('');
        }
    });

    // NIC format validation
    const nicInput = document.getElementById('nic');
    nicInput.addEventListener('input', function() {
        const nicPattern = /^(\d{12}|\d{9}[VXvx])$/;
        const value = this.value.toUpperCase();
        
        if (value && !nicPattern.test(value)) {
            this.setCustomValidity('Please enter a valid NIC number (12 digits or 9 digits with V/X)');
        } else {
            this.setCustomValidity('');
            this.value = value; // Convert to uppercase
        }
    });

    // Mobile number validation
    const mobileInput = document.getElementById('mobile');
    mobileInput.addEventListener('input', function() {
        const mobilePattern = /^0[0-9]{9}$/;
        
        if (this.value && !mobilePattern.test(this.value)) {
            this.setCustomValidity('Please enter a valid mobile number (10 digits starting with 0)');
        } else {
            this.setCustomValidity('');
        }
    });
});

// Auto-fill demo (for testing purposes)
function fillDemoData() {
    document.getElementById('name').value = 'John Doe';
    document.getElementById('nic').value = '199012345678';
    document.getElementById('company_name').value = 'ABC Trading Company';
    document.getElementById('company_br').value = 'BR123456';
    document.getElementById('email').value = 'john.doe@example.com';
    document.getElementById('mobile').value = '0712345678';
    document.getElementById('address').value = 'No. 123, Main Street, Colombo 01';
    document.getElementById('password').value = 'password123';
    document.getElementById('password_confirmation').value = 'password123';
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.modern-pm', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\NEW_ONE\resources\views/pm/customers/modern-create.blade.php ENDPATH**/ ?>