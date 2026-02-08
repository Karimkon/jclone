<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $advertisements = Advertisement::latest()->paginate(10);
        return view('admin.advertisements.index', compact('advertisements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.advertisements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'media_type' => 'required|in:image,video',
            'media_file' => 'required|file|max:10240|mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4', // Max 10MB
            'link' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('media_file')) {
            $path = $request->file('media_file')->store('advertisements', 'public');
            $validated['media_path'] = $path;
        }

        Advertisement::create($validated);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Advertisement $advertisement)
    {
        return view('admin.advertisements.edit', compact('advertisement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Advertisement $advertisement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'media_type' => 'required|in:image,video',
            'media_file' => 'nullable|file|max:10240|mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4',
            'link' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('media_file')) {
            // Delete old file
            if ($advertisement->media_path) {
                Storage::disk('public')->delete($advertisement->media_path);
            }
            $path = $request->file('media_file')->store('advertisements', 'public');
            $validated['media_path'] = $path;
        }

        $advertisement->update($validated);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Advertisement $advertisement)
    {
        if ($advertisement->media_path) {
            Storage::disk('public')->delete($advertisement->media_path);
        }
        
        $advertisement->delete();

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement deleted successfully.');
    }

    /**
     * Toggle the status of the advertisement.
     */
    public function toggleStatus(Advertisement $advertisement)
    {
        $advertisement->update(['is_active' => !$advertisement->is_active]);

        return back()->with('success', 'Advertisement status updated.');
    }
}
