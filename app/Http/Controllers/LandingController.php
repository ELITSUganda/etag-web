<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display the main landing page
     */
    public function index()
    {
        return view('landing.index');
    }

    /**
     * Handle contact form submission
     */
    public function contact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Send email or save to database
            // For now, just log it
            \Log::info('Contact form submission', $validated);

            return response()->json([
                'code' => '1',
                'message' => 'Thank you for your message. We will get back to you soon.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '0',
                'message' => 'Failed to process your message.',
                'data' => null
            ], 500);
        }
    }
}
