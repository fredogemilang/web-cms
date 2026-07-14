<?php

namespace App\Http\Controllers;

use App\Models\PartnershipInquiry;
use Illuminate\Http\Request;

class PartnershipController extends Controller
{
    /**
     * Handle the partnership inquiry form submission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'partnership_type' => ['required', 'string', 'in:corporate,university,community,media,other'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'company_name.required' => 'Company / Organization name is required.',
            'contact_name.required' => 'Contact person name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'partnership_type.required' => 'Please select a partnership type.',
            'message.required' => 'Please write a message or proposal summary.',
        ]);

        PartnershipInquiry::create($validated + ['status' => 'new']);

        return redirect()->back()->with('partner_success', 'Thank you! Your partnership inquiry has been submitted. We will get back to you soon.');
    }
}
