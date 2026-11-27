<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Setting;
use App\Services\SupportMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function __construct(protected SupportMail $mail) {}

    /**
     * Store a contact-form inquiry (Admin → Inquiries) and notify the sales inbox.
     */
    public function submit(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'service' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'min:3', 'max:3000'],
        ]);

        $serviceMap = [
            'install' => 'New lift installation',
            'escalator' => 'Escalator installation',
            'repair' => 'Lift repair & servicing',
            'amc' => 'Monthly maintenance contract',
            'parts' => 'Spare parts',
            'diagnosis' => 'Fault diagnosis / technical support',
            'product' => 'Product price enquiry',
            'other' => 'Other',
        ];

        $dept = $validated['department'] ?? ($serviceMap[$validated['service'] ?? ''] ?? 'Lift & Escalator Inquiry');
        $email = $validated['email'] ?: ($validated['phone'] ? $validated['phone'].'@customer.local' : 'visitor@innovativeengbd.com');

        $inquiry = Inquiry::create([
            'name' => $validated['name'],
            'email' => $email,
            'phone' => $validated['phone'] ?? null,
            'department' => $dept,
            'service_id' => $validated['service'] ?? null,
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'status' => 'new',
            'priority' => 'normal',
        ]);

        Log::info("New website inquiry #{$inquiry->id} created from {$inquiry->name} ({$inquiry->phone}) - [{$inquiry->department}]");

        try {
            $salesEmail = Setting::get('sales_email') ?: Setting::get('support_email') ?: 'palash_pe@yahoo.com';
            $this->mail->send(
                $salesEmail,
                "New Inquiry: [{$inquiry->department}] from {$inquiry->name}",
                "New inquiry received on Innovative Engineering website:\n\n"
                ."Name: {$inquiry->name}\n"
                ."Phone: {$inquiry->phone}\n"
                ."Email: {$inquiry->email}\n"
                ."Service/Dept: {$inquiry->department}\n\n"
                ."Message:\n{$inquiry->message}\n\n"
                ."IP Address: {$inquiry->ip_address}\n"
                ."Date: {$inquiry->created_at->format('Y-m-d H:i:s')}\n\n"
                ."Open in Admin Panel: ".route('admin.inquiries.show', $inquiry)
            );
        } catch (\Throwable $e) {
            Log::warning("Inquiry email notification failed: " . $e->getMessage());
        }

        $message = 'Thank you! Your inquiry has been received. Our engineering team will contact you shortly.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'inquiry_id' => $inquiry->id,
            ]);
        }

        return back()->with('contact_success', $message);
    }
}
