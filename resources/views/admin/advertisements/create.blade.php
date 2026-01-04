@extends('layouts.admin')

@section('title', 'Add Advertisement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Advertisement</h1>
            <p class="text-gray-600">Create a new advertisement</p>
        </div>
        <a href="{{ route('admin.advertisements.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl">
        <form action="{{ route('admin.advertisements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" id="title" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('title') }}" required>
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="media_type" class="block text-sm font-medium text-gray-700 mb-1">Media Type</label>
                <select name="media_type" id="media_type" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="image" {{ old('media_type') == 'image' ? 'selected' : '' }}>Image</option>
                    <option value="video" {{ old('media_type') == 'video' ? 'selected' : '' }}>Video</option>
                </select>
                @error('media_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="media_file" class="block text-sm font-medium text-gray-700 mb-1">File (Image or Video)</label>
                <input type="file" name="media_file" id="media_file" class="w-full border border-gray-300 p-2 rounded-lg" required>
                <p class="text-xs text-gray-500 mt-1">Images: jpg, png, gif. Videos: mp4, webm. Max 10MB.</p>
                @error('media_file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="link" class="block text-sm font-medium text-gray-700 mb-1">Link (Optional)</label>
                <input type="url" name="link" id="link" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('link') }}" placeholder="https://...">
                @error('link')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" checked>
                    <span class="ml-2 text-sm text-gray-600">Active</span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition font-medium">
                    Create Advertisement
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
