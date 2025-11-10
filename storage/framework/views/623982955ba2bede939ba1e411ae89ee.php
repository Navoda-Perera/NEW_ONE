<?php $__env->startSection('title', 'Customers Management'); ?>

<?php $__env->startSection('content'); ?>
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-people me-2 text-danger"></i>
                    Customers Management
                </h2>
                <p class="text-muted mb-0">Manage and view all customers for your location</p>
            </div>
            <a href="<?php echo e(route('pm.customers.create')); ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-person-plus me-2"></i>
                Add New Customer
            </a>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('pm.customers.index')); ?>" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   name="search"
                                   placeholder="Search by name, email, NIC, or mobile..."
                                   value="<?php echo e(request('search')); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo e(route('pm.customers.index')); ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Customers List -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-people-fill text-danger me-2"></i>
                    All Customers
                    <span class="badge bg-light text-dark ms-2"><?php echo e($customers->total()); ?></span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if($customers->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Customer Info</th>
                                    <th>Contact</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="bg-pm-accent text-white rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 35px; height: 35px; font-size: 14px; font-weight: bold;">
                                                <?php echo e(strtoupper(substr($customer->name, 0, 1))); ?>

                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-semibold text-dark"><?php echo e($customer->name); ?></div>
                                            <small class="text-muted">
                                                <i class="bi bi-card-text me-1"></i><?php echo e($customer->nic); ?>

                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <?php if($customer->email): ?>
                                                <div class="small">
                                                    <i class="bi bi-envelope me-1 text-muted"></i>
                                                    <?php echo e($customer->email); ?>

                                                </div>
                                            <?php endif; ?>
                                            <?php if($customer->mobile): ?>
                                                <div class="small">
                                                    <i class="bi bi-phone me-1 text-muted"></i>
                                                    <?php echo e($customer->mobile); ?>

                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <?php if($customer->company_name): ?>
                                                <div class="fw-medium"><?php echo e($customer->company_name); ?></div>
                                                <?php if($customer->company_br): ?>
                                                    <small class="text-muted">BR: <?php echo e($customer->company_br); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <small class="text-muted">Individual</small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($customer->is_active): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-pm-accent">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo e($customer->created_at->format('M d, Y')); ?>

                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="#" class="btn btn-outline-primary btn-sm" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if($customers->hasPages()): ?>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Showing <?php echo e($customers->firstItem()); ?> to <?php echo e($customers->lastItem()); ?>

                                    of <?php echo e($customers->total()); ?> results
                                </div>
                                <?php echo e($customers->links()); ?>

                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <i class="bi bi-people display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No customers found</h4>
                        <p class="text-muted mb-4">No customers are assigned to your location yet.</p>
                        <a href="<?php echo e(route('pm.customers.create')); ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-person-plus me-2"></i>
                            Add First Customer
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.modern-pm', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Desktop\NEW_ONE\resources\views/pm/customers/modern-index.blade.php ENDPATH**/ ?>