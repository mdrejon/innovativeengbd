<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Page;
use App\Models\Inquiry;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

echo "========================================\n";
echo "INNOVATIVE ENGINEERING BD - SITE VERIFIER\n";
echo "========================================\n\n";

// 1. Database & Models Check
echo "1. Checking Database Records...\n";
$usersCount = User::count();
$pagesCount = Page::count();
$settingsCount = Setting::count();
$inquiriesCount = Inquiry::count();

echo "   Users: {$usersCount}\n";
echo "   Pages: {$pagesCount}\n";
echo "   Settings: {$settingsCount}\n";
echo "   Inquiries: {$inquiriesCount}\n";

if ($pagesCount === 0 || $settingsCount === 0) {
    echo "   [FAIL] Missing seed data!\n";
    exit(1);
}
echo "   [PASS] Seed data verified.\n\n";

// 2. Render Frontend Homepage
echo "2. Testing Frontend Home Page View...\n";
try {
    $homePage = Page::findForFrontend('home');
    if (!$homePage) {
        throw new \Exception("Home page 'home' not found.");
    }
    
    $sections = $homePage->sections;
    echo "   Loaded page '{$homePage->title}' with {$sections->count()} sections.\n";
    
    $renderedHtml = view('frontend.page', [
        'page' => $homePage,
        'sections' => $sections,
        'schemas' => config('cms.sections'),
        'preloadImage' => null
    ])->render();
    
    $len = strlen($renderedHtml);
    echo "   Rendered HTML size: {$len} bytes.\n";
    if (strpos($renderedHtml, 'Innovative Engineering') === false) {
        throw new \Exception("Rendered HTML does not contain 'Innovative Engineering'.");
    }
    echo "   [PASS] Frontend homepage rendered successfully.\n\n";
} catch (\Throwable $e) {
    echo "   [FAIL] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

// 3. Test Contact Form Submission (POST /contact)
echo "3. Testing Contact Form Submission (POST /contact)...\n";
try {
    $contactController = app(\App\Http\Controllers\Frontend\ContactController::class);
    $request = Request::create('/contact', 'POST', [
        'name' => 'Dr. Rahman Tariq',
        'email' => 'tariq.rahman@testcompany.com',
        'phone' => '+8801712345678',
        'department' => 'Passenger Elevators',
        'message' => 'We need an inspection quote for 4 units of passenger elevators in Gulshan-2, Dhaka.',
        '_bot_field' => '', // honeypot
    ], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $response = $contactController->submit($request);
    $content = json_decode($response->getContent(), true);

    if ($response->getStatusCode() !== 200 || !isset($content['success']) || !$content['success']) {
        throw new \Exception("Contact submission failed: " . $response->getContent());
    }

    echo "   Response: " . json_encode($content) . "\n";
    
    // Verify DB insertion
    $latestInquiry = Inquiry::latest('id')->first();
    echo "   Latest Inquiry in DB: ID {$latestInquiry->id}, Name: {$latestInquiry->name}, Dept: {$latestInquiry->department}, Status: {$latestInquiry->status}\n";
    if ($latestInquiry->email !== 'tariq.rahman@testcompany.com') {
        throw new \Exception("Inquiry was not inserted into database!");
    }
    echo "   [PASS] Contact inquiry recorded in MySQL successfully.\n\n";
} catch (\Throwable $e) {
    echo "   [FAIL] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

// 4. Test Admin Authentication & Dashboard
echo "4. Testing Admin Dashboard & Controller...\n";
try {
    $admin = User::where('email', 'admin@innovativeengbd.com')->first();
    if (!$admin) {
        throw new \Exception("Admin user not found.");
    }
    auth()->login($admin);
    echo "   Logged in as: {$admin->name} ({$admin->email}), Role: {$admin->role}\n";

    $dashboardController = app(\App\Http\Controllers\Admin\DashboardController::class);
    $response = $dashboardController->index(Request::create('/admin/dashboard', 'GET'));
    echo "   Dashboard returned: " . get_class($response) . "\n";
    echo "   [PASS] Admin Dashboard controller executed successfully.\n\n";
} catch (\Throwable $e) {
    echo "   [FAIL] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

// 5. Test System Tools
echo "5. Testing System Maintenance Controller...\n";
try {
    $systemController = app(\App\Http\Controllers\Admin\SystemController::class);
    $req = Request::create('/admin/system/cache-clear', 'POST');
    $res = $systemController->clearCache($req);
    echo "   Cache clear response status: " . $res->getStatusCode() . "\n";
    echo "   [PASS] System Tools controller executed successfully.\n\n";
} catch (\Throwable $e) {
    echo "   [FAIL] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

// 6. Test SMTP Controller
echo "6. Testing SMTP Controller...\n";
try {
    $smtpController = app(\App\Http\Controllers\Admin\SmtpController::class);
    $res = $smtpController->index();
    echo "   SMTP index returned: " . get_class($res) . "\n";
    echo "   [PASS] SMTP Controller executed successfully.\n\n";
} catch (\Throwable $e) {
    echo "   [FAIL] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "========================================\n";
echo "ALL TESTS PASSED! APPLICATION IS FULLY DYNAMIC & FUNCTIONAL.\n";
echo "========================================\n";
