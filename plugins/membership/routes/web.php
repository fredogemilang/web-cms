<?php

use Illuminate\Support\Facades\Route;
use Plugins\Membership\Models\Membership;
use Plugins\Membership\Http\Controllers\MembershipRegistrationController;

// Admin Routes
Route::prefix(config('admin.path', 'admin'))->name('admin.')->middleware(['web', 'auth'])->group(function () {
    
    Route::prefix('membership')->name('membership.')->middleware('permission:memberships.view')->group(function () {
        // Members Management
        Route::get('/', function () {
            return view('membership::admin.index');
        })->name('index');
        
        Route::get('/pending', function () {
            return view('membership::admin.index');
        })->name('pending');
        
        Route::get('/{membership}', function (Membership $membership) {
            $membership->load('user');
            return view('membership::admin.show', compact('membership'));
        })->name('show');

        // Membership Actions
        Route::post('/{membership}/approve', function (Membership $membership) {
            $membership->approve(auth()->id());
            return back()->with('success', 'Membership approved successfully.');
        })->name('approve')->middleware('permission:memberships.edit');

        Route::post('/{membership}/reject', function (Membership $membership) {
            $membership->reject();
            return back()->with('success', 'Membership rejected.');
        })->name('reject')->middleware('permission:memberships.edit');

        Route::post('/{membership}/suspend', function (Membership $membership) {
            $membership->suspend();
            return back()->with('success', 'Membership suspended.');
        })->name('suspend')->middleware('permission:memberships.edit');

        Route::post('/{membership}/reactivate', function (Membership $membership) {
            $membership->reactivate();
            return back()->with('success', 'Membership reactivated.');
        })->name('reactivate')->middleware('permission:memberships.edit');

        Route::delete('/{membership}', function (Membership $membership) {
            $membership->delete();
            return redirect()->route('admin.membership.index')->with('success', 'Membership deleted successfully.');
        })->name('destroy')->middleware('permission:memberships.delete');

        // Export
        Route::get('/export/csv', function () {
            $memberships = Membership::with('user')->get();
            
            $filename = 'members-' . now()->format('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($memberships) {
                $file = fopen('php://output', 'w');
                
                // Write header row
                fputcsv($file, ['ID', 'Name', 'Email', 'Status', 'Joined Date', 'Registered Date']);
                
                // Write data rows
                foreach ($memberships as $membership) {
                    fputcsv($file, [
                        $membership->id,
                        $membership->user->name,
                        $membership->user->email,
                        ucfirst($membership->status),
                        $membership->joined_at ? $membership->joined_at->format('Y-m-d') : 'N/A',
                        $membership->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        })->name('export');
    });
});

// Member Portal Routes (Optional - for future)
Route::prefix('member')->name('member.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $membership = Membership::where('user_id', auth()->id())
                                ->with('user')
                                ->first();
        return view('membership::member.dashboard', compact('membership'));
    })->name('dashboard');
});

// Public Routes
Route::prefix('membership')->name('membership.')->middleware(['web'])->group(function () {
    Route::get('/', function () {
        return view('iccom::membership.register');
    })->name('register');

    Route::post('/', [MembershipRegistrationController::class, 'store'])->name('store');

    Route::get('/success', function () {
        return view('iccom::membership.success');
    })->name('success');
});
