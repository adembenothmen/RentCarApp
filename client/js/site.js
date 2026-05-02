(function (window) {
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatPrice(value) {
        return '$' + Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function renderCarCard(car, linkUrl, index) {
        const delay = '0.' + ((index + 1) * 2);

        return `
            <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="${delay}s">
                <div class="categories-item p-4 h-100">
                    <div class="categories-item-inner h-100 d-flex flex-column">
                        <div class="categories-img rounded-top">
                            <img src="${escapeHtml(car.image)}" class="img-fluid w-100 rounded-top" style="height: 220px; object-fit: cover;" alt="${escapeHtml(car.name)}">
                        </div>
                        <div class="categories-content rounded-bottom p-4 d-flex flex-column h-100">
                            <h4>${escapeHtml(car.name)}</h4>
                            ${car.type ? `<p class="text-muted mb-2">${escapeHtml(car.type)}</p>` : ''}
                            <div class="mb-4">
                                <h4 class="bg-white text-primary rounded-pill py-2 px-4 mb-0">${formatPrice(car.price_per_day)}/Day</h4>
                            </div>
                            <div class="row gy-2 gx-0 text-center mb-4">
                                <div class="col-4 border-end border-white">
                                    <i class="fa fa-users text-dark"></i> <span class="text-body ms-1">${escapeHtml(car.seats)} Seat${Number(car.seats) === 1 ? '' : 's'}</span>
                                </div>
                                <div class="col-4 border-end border-white">
                                    <i class="fa fa-cogs text-dark"></i> <span class="text-body ms-1">${escapeHtml(car.transmission)}</span>
                                </div>
                                <div class="col-4">
                                    <i class="fa fa-gas-pump text-dark"></i> <span class="text-body ms-1">${escapeHtml(car.fuel)}</span>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <a href="${escapeHtml(linkUrl)}" class="btn btn-primary rounded-pill d-flex justify-content-center py-3">Rent Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function renderSelectedCarCard(car) {
        return `
            <div class="categories-item p-4">
                <div class="categories-item-inner">
                    <div class="categories-img rounded-top">
                        <img src="${escapeHtml(car.image)}" class="img-fluid w-100 rounded-top" alt="${escapeHtml(car.name)}">
                    </div>
                    <div class="categories-content rounded-bottom p-4">
                        <h4>Selected Car</h4>
                        <h3 class="text-primary">${escapeHtml(car.name)}</h3>
                        <h4 class="bg-white text-primary rounded-pill py-2 px-4 mb-0">${formatPrice(car.price_per_day)}/Day</h4>
                    </div>
                </div>
            </div>`;
    }

    function renderConfirmationCard(data) {
        const dayCount = Number(data.days || 0);
        const totalPrice = Number(data.total_price || 0);
        const pricePerDay = Number(data.price_per_day || 0);
        const paymentMethod = String(data.payment_method || '').replace(/-/g, ' ');

        return `
            <div class="alert alert-success wow fadeInUp" data-wow-delay="0.1s" role="alert">
                <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Success!</h4>
                <p class="mb-0">Your car rental booking has been confirmed. We will contact you shortly with additional details.</p>
            </div>
            <div class="card wow fadeInUp" data-wow-delay="0.2s" style="border-left: 4px solid #198754;">
                <div class="card-body p-5">
                    <h3 class="card-title mb-4">Reservation Details</h3>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Reservation Number</p>
                            <h5 class="text-primary">${escapeHtml(data.reservation_number)}</h5>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Booking Date</p>
                            <h5>${escapeHtml(data.timestamp)}</h5>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <p class="text-muted mb-2"><i class="fas fa-car text-primary me-2"></i>Vehicle</p>
                            <h4>${escapeHtml(data.car_name)}</h4>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-4 mt-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Check-In Date</p>
                            <h5>${escapeHtml(data.start_date)}</h5>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Check-Out Date</p>
                            <h5>${escapeHtml(data.end_date)}</h5>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Rental Duration</p>
                            <h5>${dayCount} day${dayCount !== 1 ? 's' : ''}</h5>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Price Per Day</p>
                            <h5>${formatPrice(pricePerDay)}</h5>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <div class="bg-light rounded p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Total Price:</h5>
                                    <h4 class="text-primary mb-0">${formatPrice(totalPrice)}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row mt-4 mb-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Full Name</p>
                            <h5>${escapeHtml(data.name)}</h5>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Email</p>
                            <h5>${escapeHtml(data.email)}</h5>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Phone</p>
                            <h5>${escapeHtml(data.phone)}</h5>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">CIN</p>
                            <h5>${escapeHtml(data.cin)}</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Payment Method</p>
                            <h5 class="text-capitalize">${escapeHtml(paymentMethod)}</h5>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function renderAlert(type, message) {
        return `<div class="alert alert-${escapeHtml(type)}" role="alert">${escapeHtml(message)}</div>`;
    }

    
    window.CentalApp = {
        escapeHtml,
        formatPrice,
        renderCarCard,
        renderSelectedCarCard,
        renderConfirmationCard,
        renderAlert,
    };
})(window);
