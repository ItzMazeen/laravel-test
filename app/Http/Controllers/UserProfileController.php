<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    public function show()
    {
        return view('pages.user-profile');
    }

    public function update(Request $request)
    {
        // Validate the inputs
        $attributes = $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'firstname' => ['max:100'],
            'lastname' => ['max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore(auth()->user()->id)],
            'address' => ['max:100'],
            'city' => ['max:100'],
            'country' => ['max:100'],
            'postal' => ['max:100'],
            'about' => ['max:255']
        ]);

        $user = auth()->user();

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            
            Log::info('File upload attempt:', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            // Get file list before upload
            $filesBefore = $this->getProfilePhotos();
            
            // Generate a unique filename first
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $expectedPath = 'profile_photos/' . $filename;
            
            // Attempt Laravel's store method with specific filename
            $path = Storage::disk('public')->putFileAs('profile_photos', $file, $filename);
            
            Log::info('Storage attempt result:', [
                'expected_path' => $expectedPath,
                'returned_path' => $path,
                'path_type' => gettype($path),
                'is_string' => is_string($path),
                'is_empty' => empty($path)
            ]);

            // Check if file actually exists (ignore Laravel's return value)
            $fileExists = Storage::disk('public')->exists($expectedPath);
            
            Log::info('File existence check:', [
                'expected_path' => $expectedPath,
                'file_exists' => $fileExists,
                'laravel_returned' => $path
            ]);

            // If file exists, use the expected path regardless of return value
            if ($fileExists) {
                $path = $expectedPath;
                Log::info('File successfully saved, using expected path:', ['path' => $path]);
            } else {
                Log::warning('File does not exist, trying fallback method');
                // If file doesn't exist at expected location, try to find it manually
                // Get file list after upload attempt
                $filesAfter = $this->getProfilePhotos();
                
                // Find the new file
                $newFiles = array_diff($filesAfter, $filesBefore);
                
                if (!empty($newFiles)) {
                    // Get the newest file
                    $newestFile = array_pop($newFiles);
                    $path = 'profile_photos/' . basename($newestFile);
                    
                    Log::info('Found uploaded file manually:', [
                        'detected_path' => $path,
                        'full_path' => $newestFile
                    ]);
                } else {
                    // Last resort: try to find file by timestamp
                    $path = $this->findRecentFile($file);
                }
            }

            // Only update if we have a valid path
            if ($path && is_string($path) && !empty($path) && Storage::disk('public')->exists($path)) {
                // Delete old photo if exists
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
            // No photo uploaded
            unset($attributes['photo']);
        }

        Log::info('Final update attributes:', $attributes);

        // Update user
        $user->update($attributes);

        return back()->with('succes', 'Profile successfully updated');
    }

    /**
     * Get list of profile photos
     */
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