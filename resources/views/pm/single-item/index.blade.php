@extends('layouts.modern-pm')

@section('title', 'Add Single Item')

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-box-seam me-2 text-danger"></i>
                    Single Item Management
                </h2>
                <p class="text-muted mb-0">Add individual postal items for SLP Courier, COD, and Register Post services.</p>
            </div>
            <div class="text-end">
                <div class="badge bg-light text-dark fs-6 px-3 py-2">
                    <i class="bi bi-geo-alt text-danger me-1"></i>
                    {{ $location ? $location->name : 'No location' }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Service Cards -->
<div class="row g-4">
    <!-- SLP Courier Card -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100 service-card courier-card">
            <div class="card-body text-center p-4">
                <div class="service-icon mb-3">
                    <i class="bi bi-truck display-4 text-primary"></i>
                </div>
                <h4 class="card-title text-primary fw-bold mb-3">SLP Courier</h4>
                <p class="card-text text-muted mb-3">
                    Add single courier item with weight-based pricing calculation.
                </p>
                
                <div class="features-list mb-4">
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Sender Details</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Receiver Details</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Weight & Postage</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Barcode Tracking</span>
                    </div>
                </div>
                
                <a href="{{ route('pm.single-item.slp-form') }}" class="btn btn-primary btn-lg w-100 service-btn">
                    <i class="bi bi-plus-circle me-2"></i>Add SLP Item
                </a>
            </div>
        </div>
    </div>

    <!-- COD Card -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100 service-card cod-card">
            <div class="card-body text-center p-4">
                <div class="service-icon mb-3">
                    <i class="bi bi-cash-coin display-4 text-warning"></i>
                </div>
                <h4 class="card-title text-warning fw-bold mb-3">Cash on Delivery (COD)</h4>
                <p class="card-text text-muted mb-3">
                    Add COD item with amount collection and postage calculation.
                </p>
                
                <div class="features-list mb-4">
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>COD Amount</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Postage Calculation</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Payment Collection</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Receipt Generation</span>
                    </div>
                </div>
                
                <a href="{{ route('pm.single-item.cod-form') }}" class="btn btn-warning btn-lg w-100 service-btn">
                    <i class="bi bi-plus-circle me-2"></i>Add COD Item
                </a>
            </div>
        </div>
    </div>

    <!-- Register Post Card -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100 service-card register-card">
            <div class="card-body text-center p-4">
                <div class="service-icon mb-3">
                    <i class="bi bi-envelope-check display-4 text-danger"></i>
                </div>
                <h4 class="card-title text-danger fw-bold mb-3">Register Post</h4>
                <p class="card-text text-muted mb-3">
                    Add registered postal item with tracking and delivery confirmation.
                </p>
                
                <div class="features-list mb-4">
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Registered Tracking</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Delivery Confirmation</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Weight-based Pricing</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Official Receipt</span>
                    </div>
                </div>
                
                <a href="{{ route('pm.single-item.register-form') }}" class="btn btn-danger btn-lg w-100 service-btn">
                    <i class="bi bi-plus-circle me-2"></i>Add Register Item
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Location Information Card -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm location-info-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="location-icon me-3">
                                <i class="bi bi-geo-alt-fill fs-3 text-danger"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Current Location</h5>
                                <p class="text-muted mb-0">
                                    {{ $location ? $location->name : 'No location assigned' }} - All items will be created under this location
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="pm-info">
                            <span class="badge badge-pm-accent bg-opacity-10 text-muted px-3 py-2">
                                <i class="bi bi-person-badge me-1"></i>
                                PM: {{ $user->name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.service-card {
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15) !important;
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    z-index: 1;
}

.courier-card::before {
    background: linear-gradient(90deg, #007bff, #0056b3);
}

.cod-card::before {
    background: linear-gradient(90deg, #ffc107, #ff8f00);
}

.register-card::before {
    background: linear-gradient(90deg, #dc3545, #c82333);
}

.service-icon {
    padding: 20px;
    border-radius: 50%;
    display: inline-block;
    margin-bottom: 1rem;
}

.courier-card .service-icon {
    background: rgba(0, 123, 255, 0.1);
}

.cod-card .service-icon {
    background: rgba(255, 193, 7, 0.1);
}

.register-card .service-icon {
    background: rgba(220, 53, 69, 0.1);
}

.features-list {
    text-align: left;
    margin: 0 auto;
    max-width: 200px;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    font-size: 0.9rem;
    font-weight: 500;
}

.service-btn {
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.service-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.location-info-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
}

.location-icon {
    width: 60px;
    height: 60px;
    background: rgba(220, 53, 69, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .service-card {
        margin-bottom: 2rem;
    }
    
    .features-list {
        max-width: 100%;
    }
}
</style>
@endsection
