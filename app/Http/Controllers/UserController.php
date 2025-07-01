<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('pages.user-management', compact('users'));
    }
    public function destroy($id)
    {
    $user = User::findOrFail($id);
    $user->delete();

    return back()->with('succes', 'User deleted succesfully');

    }
    public function edit($id)
    {
    $user = User::findOrFail($id);
    return view('pages.user-edit', compact('user')); // use a new blade view
    }

public function update(Request $request, $id)
{
    // Find the user to update by ID
    $user = User::findOrFail($id);

    // Validate inputs — note the unique rule ignoring the user being updated, NOT auth user
    $attributes = $request->validate([
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'firstname' => ['max:100'],
        'lastname' => ['max:100'],
        'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        'address' => ['max:100'],
        'city' => ['max:100'],
        'country' => ['max:100'],
        'postal' => ['max:100'],
        'about' => ['max:255']
    ]);

    if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
        $file = $request->file('photo');

        Log::info('File upload attempt:', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        // List files before upload
        $filesBefore = $this->getProfilePhotos();

        // Generate unique filename
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $expectedPath = 'profile_photos/' . $filename;

        // Store file with explicit filename
        $path = Storage::disk('public')->putFileAs('profile_photos', $file, $filename);

        Log::info('Storage attempt result:', [
            'expected_path' => $expectedPath,
            'returned_path' => $path,
            'path_type' => gettype($path),
            'is_string' => is_string($path),
            'is_empty' => empty($path)
        ]);

        // Check file existence ignoring Laravel return value
        $fileExists = Storage::disk('public')->exists($expectedPath);

        Log::info('File existence check:', [
            'expected_path' => $expectedPath,
            'file_exists' => $fileExists,
            'laravel_returned' => $path
        ]);

        if ($fileExists) {
            $path = $expectedPath;
            Log::info('File successfully saved, using expected path:', ['path' => $path]);
        } else {
            Log::warning('File does not exist, trying fallback method');

            $filesAfter = $this->getProfilePhotos();

            $newFiles = array_diff($filesAfter, $filesBefore);

            if (!empty($newFiles)) {
                $newestFile = array_pop($newFiles);
                $path = 'profile_photos/' . basename($newestFile);

                Log::info('Found uploaded file manually:', [
                    'detected_path' => $path,
                    'full_path' => $newestFile
                ]);
            } else {
                $path = $this->findRecentFile($file);
            }
        }

        if ($path && is_string($path) && !empty($path) && Storage::disk('public')->exists($path)) {
            // Delete old photo if exists and is valid
            if ($user->photo && $user->photo !== '0' && $user->photo !== 'false') {
                Storage::disk('public')->delete($user->photo);
            }
            $attributes['photo'] = $path;

            Log::info('Photo path set for database:', ['path' => $path]);
        } else {
            Log::error('Could not determine photo path - upload failed');
            unset($attributes['photo']);
            return back()->with('error', 'Photo upload failed. Please try again.');
        }
    } else {
        // No new photo uploaded, remove photo from attributes to keep existing
        unset($attributes['photo']);
    }

    Log::info('Final update attributes:', $attributes);

    // Update the user identified by $id, not auth()->user()
    $user->update($attributes);

    return back()->with('succes', 'User successfully updated');
}
    private function getProfilePhotos()
    {
        $photosPath = storage_path('app/public/profile_photos');
        if (!is_dir($photosPath)) {
            return [];
        }
        
        $files = scandir($photosPath);
        return array_filter($files, function($file) use ($photosPath) {
            return is_file($photosPath . '/' . $file) && !in_array($file, ['.', '..']);
        });
    }

    /**
     * Find the most recently created file that matches our upload
     */
    private function findRecentFile($uploadedFile)
    {
        $photosPath = storage_path('app/public/profile_photos');
        $files = glob($photosPath . '/*');
        
        if (empty($files)) {
            return false;
        }
        
        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Get the newest file
        $newestFile = $files[0];
        $newestTime = filemtime($newestFile);
        
        // Check if it was created within the last 10 seconds
        if (time() - $newestTime <= 10) {
            $relativePath = 'profile_photos/' . basename($newestFile);
            Log::info('Found recent file by timestamp:', [
                'path' => $relativePath,
                'created_seconds_ago' => time() - $newestTime
            ]);
            return $relativePath;
        }
        
        return false;
    }
    
}