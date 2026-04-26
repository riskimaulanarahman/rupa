<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\LoyaltyRewardController;
use App\Http\Controllers\OutletAuthController;
use App\Http\Controllers\OutletLandingController;
use App\Http\Controllers\OutletLandingManagementController;
use App\Http\Controllers\OutletSwitchController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\Platform\BankAccountController as PlatformBankAccountController;
use App\Http\Controllers\Platform\BillingController as PlatformBillingController;
use App\Http\Controllers\Platform\BrandingController as PlatformBrandingController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\LandingPageController as PlatformLandingPageController;
use App\Http\Controllers\Platform\PermissionDefaultController as PlatformPermissionDefaultController;
use App\Http\Controllers\Platform\PlanController as PlatformPlanController;
use App\Http\Controllers\Platform\RevenueController as PlatformRevenueController;
use App\Http\Controllers\Platform\TenantController as PlatformTenantController;
use App\Http\Controllers\Portal\AuthController as PortalAuthController;
use App\Http\Controllers\Portal\CustomerPortalController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantHQController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TreatmentRecordController;
use Illuminate\Support\Facades\Route;

// --- SaaS Platform Routes (Main Domain) ---
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/platform/billing/{invoice}/approve-via-email', [PlatformBillingController::class, 'approveViaEmail'])
    ->name('platform.billing.approve-via-email')
    ->middleware('signed');
Route::get('/platform/billing/{invoice}/reject-via-email', [PlatformBillingController::class, 'rejectViaEmail'])
    ->name('platform.billing.reject-via-email')
    ->middleware('signed');

Route::group(['prefix' => 'register', 'as' => 'register.'], function () {
    Route::get('/', [RegistrationController::class, 'index'])->name('index');
    Route::get('/plan/{plan:slug}', [RegistrationController::class, 'showForm'])->name('show');
    Route::post('/', [RegistrationController::class, 'store'])->name('store');
    Route::get('/success', [RegistrationController::class, 'success'])->name('success');
});

// --- Platform Admin Routes (Super Admin) ---
Route::group(['middleware' => ['auth', 'role:superadmin'], 'prefix' => 'platform', 'as' => 'platform.'], function () {
    Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');

    Route::group(['prefix' => 'tenants', 'as' => 'tenants.'], function () {
        Route::get('/', [PlatformTenantController::class, 'index'])->name('index');
        Route::get('/{tenant}', [PlatformTenantController::class, 'show'])->name('show');
        Route::patch('/{tenant}', [PlatformTenantController::class, 'update'])->name('update');
        Route::put('/{tenant}/module-access', [PlatformTenantController::class, 'updateModuleAccess'])->name('module-access.update');
        Route::post('/{tenant}/toggle', [PlatformTenantController::class, 'toggleStatus'])->name('toggle');
    });

    Route::resource('plans', PlatformPlanController::class)->except(['show']);
    Route::get('/revenue', [PlatformRevenueController::class, 'index'])->name('revenue.index');

    Route::group(['prefix' => 'billing', 'as' => 'billing.'], function () {
        Route::get('/', [PlatformBillingController::class, 'index'])->name('index');
        Route::get('/{invoice}', [PlatformBillingController::class, 'show'])->name('show');
        Route::post('/{invoice}/mark-paid', [PlatformBillingController::class, 'markPaid'])->name('markPaid');
        Route::post('/{invoice}/approve', [PlatformBillingController::class, 'approve'])->name('approve');
        Route::post('/{invoice}/reject', [PlatformBillingController::class, 'reject'])->name('reject');
    });

    Route::resource('bank-accounts', PlatformBankAccountController::class)->except(['show']);
    Route::get('/branding/favicon', [PlatformBrandingController::class, 'favicon'])->name('branding.favicon');
    Route::post('/branding/favicon', [PlatformBrandingController::class, 'updateFavicon'])->name('branding.favicon.update');
    Route::post('/branding/favicon/remove', [PlatformBrandingController::class, 'removeFavicon'])->name('branding.favicon.remove');

    Route::group(['prefix' => 'landing', 'as' => 'landing.'], function () {
        Route::get('/', [PlatformLandingPageController::class, 'index'])->name('index');
        Route::get('/{landingContent}/edit', [PlatformLandingPageController::class, 'edit'])->name('edit');
        Route::put('/{landingContent}', [PlatformLandingPageController::class, 'update'])->name('update');
    });

    Route::get('/permissions/defaults', [PlatformPermissionDefaultController::class, 'index'])->name('permissions.defaults');
    Route::put('/permissions/defaults', [PlatformPermissionDefaultController::class, 'update'])->name('permissions.defaults.update');
});

// Setup Wizard routes (legacy - kept for safety or platform setup)
Route::group(['middleware' => 'setup.required', 'prefix' => 'setup', 'as' => 'setup.'], function () {
    Route::get('/', [SetupController::class, 'index'])->name('index');
    Route::get('/details', [SetupController::class, 'details'])->name('details');
    Route::post('/details', [SetupController::class, 'storeDetails'])->name('storeDetails');
    Route::get('/account', [SetupController::class, 'account'])->name('account');
    Route::post('/complete', [SetupController::class, 'complete'])->name('complete');
});

// --- Outlet/Tenant Authenticated & Public Routes ---
// Note: IdentifyTenant and CheckSubscription middleware are applied globally in bootstrap/app.php

// Public Booking routes
Route::group(['prefix' => 'booking', 'as' => 'booking.', 'middleware' => ['outlet.booking.available']], function () {
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::get('/slots', [BookingController::class, 'slots'])->name('slots');
    Route::post('/', [BookingController::class, 'store'])->name('store');
    Route::get('/confirmation/{appointment}', [BookingController::class, 'confirmation'])->name('confirmation');
    Route::get('/status', [BookingController::class, 'checkStatus'])->name('status');
    Route::post('/cancel/{appointment}', [BookingController::class, 'cancel'])->name('cancel');
});

// Guest routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['module.access:dashboard'])
        ->name('dashboard');

    // Service Categories
    Route::middleware('module.access:service_categories')->group(function () {
        Route::resource('service-categories', ServiceCategoryController::class)->except(['show']);
        Route::post('service-categories/reorder', [ServiceCategoryController::class, 'reorder'])->name('service-categories.reorder');
    });

    // Services
    Route::middleware('module.access:services')->group(function () {
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::patch('services/{service}/toggle-active', [ServiceController::class, 'toggleActive'])->name('services.toggle-active');
    });

    // Product Categories & Products (feature: products)
    Route::middleware(['module.access:products', 'feature:products'])->group(function () {
        Route::resource('product-categories', ProductCategoryController::class)->except(['show']);
        Route::post('product-categories/reorder', [ProductCategoryController::class, 'reorder'])->name('product-categories.reorder');
        Route::resource('products', ProductController::class);
        Route::patch('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');
        Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    });

    // Customers
    Route::middleware('module.access:customers')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    // Appointments
    Route::middleware('module.access:appointments')->group(function () {
        Route::resource('appointments', AppointmentController::class);
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
        Route::get('appointments-slots', [AppointmentController::class, 'getAvailableSlots'])->name('appointments.slots');
        Route::get('appointments-events', [AppointmentController::class, 'calendarEvents'])->name('appointments.events');
    });

    // Treatment Records (feature: treatment_records - only for clinic)
    Route::middleware(['module.access:treatment_records', 'feature:treatment_records'])->group(function () {
        Route::resource('treatment-records', TreatmentRecordController::class);
        Route::delete('treatment-records/{treatment_record}/photo', [TreatmentRecordController::class, 'deletePhoto'])->name('treatment-records.delete-photo');
        Route::get('treatment-records/{treatment_record}/pdf', [TreatmentRecordController::class, 'exportPdf'])->name('treatment-records.pdf');
        Route::get('treatment-records-customer-pdf', [TreatmentRecordController::class, 'exportCustomerPdf'])->name('treatment-records.customer-pdf');
    });

    // Packages (feature: packages)
    Route::middleware(['module.access:packages', 'feature:packages'])->group(function () {
        Route::resource('packages', PackageController::class);
        Route::patch('packages/{package}/toggle-active', [PackageController::class, 'toggleActive'])->name('packages.toggle-active');
    });

    // Customer Packages (feature: customer_packages)
    Route::middleware(['module.access:customer_packages', 'feature:customer_packages'])->group(function () {
        Route::get('customer-packages', [CustomerPackageController::class, 'index'])->name('customer-packages.index');
        Route::get('customer-packages/create', [CustomerPackageController::class, 'create'])->name('customer-packages.create');
        Route::post('customer-packages', [CustomerPackageController::class, 'store'])->name('customer-packages.store');
        Route::get('customer-packages/{customer_package}', [CustomerPackageController::class, 'show'])->name('customer-packages.show');
        Route::post('customer-packages/{customer_package}/use-session', [CustomerPackageController::class, 'useSession'])->name('customer-packages.use-session');
        Route::post('customer-packages/{customer_package}/cancel', [CustomerPackageController::class, 'cancel'])->name('customer-packages.cancel');
        Route::get('api/packages/{package}', [CustomerPackageController::class, 'getPackageDetails'])->name('web.api.packages.show');
        Route::get('api/customers/{customer}/packages', [CustomerPackageController::class, 'getCustomerPackages'])->name('web.api.customers.packages');
        Route::get('api/customers/{customer}/points', [CustomerController::class, 'getCustomerPoints'])->name('web.api.customers.points');
    });

    // Transactions
    Route::middleware('module.access:transactions')->group(function () {
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::post('transactions/{transaction}/pay', [TransactionController::class, 'pay'])->name('transactions.pay');
        Route::post('transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::get('transactions/{transaction}/invoice', [TransactionController::class, 'invoice'])->name('transactions.invoice');
        Route::get('api/services/{service}/price', [TransactionController::class, 'getServicePrice'])->name('api.services.price');
        Route::get('api/packages/{package}/price', [TransactionController::class, 'getPackagePrice'])->name('api.packages.price');
        Route::get('api/products/{product}/price', [TransactionController::class, 'getProductPrice'])->name('api.products.price');
    });

    // Subscription Expiry (All roles can see view-only)
    Route::get('/subscription/expired', [SubscriptionController::class, 'expired'])->name('subscription.expired');

    // Outlet Management
    Route::middleware('module.access:outlets')->group(function () {
        Route::get('/hq', [TenantHQController::class, 'index'])->name('tenant.hq.index');
        Route::post('/outlets/switch/{outlet}', [OutletSwitchController::class, 'switch'])->name('tenant.outlets.switch');

        Route::group(['prefix' => 'outlets', 'as' => 'tenant.outlets.'], function () {
            Route::get('/', [TenantHQController::class, 'outlets'])->name('index');
            Route::get('/create', [TenantHQController::class, 'createOutlet'])->name('create');
            Route::post('/', [TenantHQController::class, 'storeOutlet'])->name('store');
            Route::post('/{outlet}/toggle', [TenantHQController::class, 'toggleOutlet'])->name('toggle');
        });
    });

    // Billing
    Route::middleware('module.access:billing')->group(function () {
        Route::group(['prefix' => 'billing', 'as' => 'tenant.billing.'], function () {
            Route::get('/', [SubscriptionController::class, 'billing'])->name('index');
            Route::post('/switch-plan', [SubscriptionController::class, 'switchPlan'])->name('switch-plan');
            Route::post('/{invoice}/submit-payment', [SubscriptionController::class, 'submitPayment'])->name('submit-payment');
        });
    });

    // Staff
    Route::middleware('module.access:staff')->group(function () {
        Route::resource('staff', StaffController::class)->except(['show']);
        Route::post('staff/{user}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');
    });

    // Reports
    Route::middleware(['module.access:reports', 'revenue.access'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/services', [ReportController::class, 'services'])->name('services');
        Route::get('/appointments', [ReportController::class, 'appointments'])->name('appointments');
        Route::get('/staff', [ReportController::class, 'staff'])->name('staff');
        Route::get('/loyalty', [ReportController::class, 'loyalty'])->name('loyalty');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/export/revenue', [ReportController::class, 'exportRevenue'])->name('export.revenue');
        Route::get('/export/customers', [ReportController::class, 'exportCustomers'])->name('export.customers');
    });

    // Settings
    Route::middleware('module.access:settings')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('/clinic', [SettingController::class, 'clinic'])->name('clinic');
        Route::post('/clinic', [SettingController::class, 'updateClinic'])->name('clinic.update');
        Route::get('/hours', [SettingController::class, 'hours'])->name('hours');
        Route::post('/hours', [SettingController::class, 'updateHours'])->name('hours.update');
        Route::get('/branding', [SettingController::class, 'branding'])->name('branding');
        Route::post('/branding', [SettingController::class, 'updateBranding'])->name('branding.update');
        Route::post('/branding/remove-logo', [SettingController::class, 'removeLogo'])->name('branding.remove-logo');
        Route::get('/landing', [OutletLandingManagementController::class, 'edit'])->name('landing.edit');
        Route::put('/landing', [OutletLandingManagementController::class, 'update'])->name('landing.update');
    });

    // Import Data
    Route::middleware('module.access:import_data')->prefix('imports')->name('imports.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::get('/{entity}/create', [ImportController::class, 'create'])->name('create');
        Route::post('/{entity}/upload', [ImportController::class, 'upload'])->name('upload');
        Route::get('/{entity}/preview', [ImportController::class, 'preview'])->name('preview');
        Route::post('/{entity}/process', [ImportController::class, 'process'])->name('process');
        Route::get('/{entity}/template', [ImportController::class, 'template'])->name('template');
        Route::get('/log/{import}', [ImportController::class, 'show'])->name('show');
        Route::delete('/log/{import}', [ImportController::class, 'destroy'])->name('destroy');
    });

    // Loyalty Program (feature: loyalty)
    Route::middleware(['module.access:loyalty', 'feature:loyalty'])->prefix('loyalty')->name('loyalty.')->group(function () {
        Route::get('/', [LoyaltyController::class, 'index'])->name('index');
        Route::get('/customers', [LoyaltyController::class, 'customers'])->name('customers');
        Route::get('/customers/{customer}/history', [LoyaltyController::class, 'customerHistory'])->name('customer-history');
        Route::post('/customers/{customer}/redeem', [LoyaltyController::class, 'redeem'])->name('redeem');
        Route::post('/customers/{customer}/adjust', [LoyaltyController::class, 'adjustPoints'])->name('adjust-points');
        Route::get('/redemptions', [LoyaltyController::class, 'redemptions'])->name('redemptions');
        Route::post('/redemptions/use', [LoyaltyController::class, 'useRedemption'])->name('use-redemption');
        Route::post('/redemptions/{redemption}/cancel', [LoyaltyController::class, 'cancelRedemption'])->name('cancel-redemption');
        Route::get('/check-code', [LoyaltyController::class, 'checkCode'])->name('check-code');
        Route::get('/referrals', [LoyaltyController::class, 'referrals'])->name('referrals');

        // Rewards (owner/admin)
        Route::middleware('role:owner,admin')->group(function () {
            Route::resource('rewards', LoyaltyRewardController::class);
            Route::patch('rewards/{reward}/toggle-active', [LoyaltyRewardController::class, 'toggleActive'])->name('rewards.toggle-active');
        });
    });
});

// Outlet slug-based public/auth routes (keep as the last catch-all routes)
Route::group([
    'prefix' => '{outletSlug}',
    'as' => 'outlet.',
    'middleware' => ['resolve.outlet', 'subscription'],
], function () {
    Route::get('/', [OutletLandingController::class, 'show'])->name('landing.show');

    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [OutletAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [OutletAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [OutletAuthController::class, 'logout'])->name('logout');
    });

    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        // Guest routes (not logged in as customer)
        Route::group(['middleware' => 'customer.guest'], function () {
            Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [PortalAuthController::class, 'login'])->name('login.submit');
            Route::get('/register', [PortalAuthController::class, 'showRegister'])->name('register');
            Route::post('/register', [PortalAuthController::class, 'register'])->name('register.submit');
        });

        // Authenticated customer routes
        Route::group(['middleware' => 'customer.auth'], function () {
            Route::post('/logout', [PortalAuthController::class, 'logout'])->name('logout');
            Route::get('/', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
            Route::put('/profile', [CustomerPortalController::class, 'updateProfile'])->name('profile.update');
            Route::get('/appointments', [CustomerPortalController::class, 'appointments'])->name('appointments');
            Route::get('/appointments/{id}', [CustomerPortalController::class, 'appointmentDetail'])->name('appointments.show');
            Route::get('/treatments', [CustomerPortalController::class, 'treatments'])->name('treatments');
            Route::get('/treatments/{id}', [CustomerPortalController::class, 'treatmentDetail'])->name('treatments.show');
            Route::get('/packages', [CustomerPortalController::class, 'packages'])->name('packages');
            Route::get('/packages/{id}', [CustomerPortalController::class, 'packageDetail'])->name('packages.show');
            Route::get('/loyalty', [CustomerPortalController::class, 'loyalty'])->name('loyalty');
            Route::get('/transactions', [CustomerPortalController::class, 'transactions'])->name('transactions');
            Route::get('/transactions/{id}', [CustomerPortalController::class, 'transactionDetail'])->name('transactions.show');
        });
    });

    Route::group(['prefix' => 'booking', 'as' => 'booking.', 'middleware' => ['outlet.booking.available']], function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/slots', [BookingController::class, 'slots'])->name('slots');
        Route::post('/', [BookingController::class, 'store'])->name('store');
        Route::get('/confirmation/{appointment}', [BookingController::class, 'confirmation'])->name('confirmation');
        Route::get('/status', [BookingController::class, 'checkStatus'])->name('status');
        Route::post('/cancel/{appointment}', [BookingController::class, 'cancel'])->name('cancel');
    });
});
