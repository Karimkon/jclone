@extends('layouts.admin')

@section('title', 'Create Campaign - Admin Dashboard')
@section('page-title', 'Create Campaign')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.campaigns.store') }}" method="POST" id="campaignForm">
        @csrf
        <input type="hidden" name="action" id="formAction" value="draft">

        <div class="space-y-6">
            <!-- Campaign Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Campaign Details</h3>

                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Campaign Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="e.g. February Newsletter, Flash Sale Announcement">
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type Selector -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Type *</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="email" {{ old('type', 'email') === 'email' ? 'checked' : '' }}
                                       class="text-indigo-600 focus:ring-indigo-500" onchange="toggleTypeFields()">
                                <span class="text-sm"><i class="fas fa-envelope text-blue-500 mr-1"></i> Email</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="sms" {{ old('type') === 'sms' ? 'checked' : '' }}
                                       class="text-indigo-600 focus:ring-indigo-500" onchange="toggleTypeFields()">
                                <span class="text-sm"><i class="fas fa-sms text-purple-500 mr-1"></i> SMS</span>
                            </label>
                        </div>
                    </div>

                    <!-- Audience -->
                    <div>
                        <label for="audience" class="block text-sm font-medium text-gray-700 mb-1">
                            Target Audience *
                            <span id="audienceCount" class="ml-2 text-indigo-600 font-normal"></span>
                        </label>
                        <select name="audience" id="audience" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                onchange="updateAudienceCount(); toggleCustomFilters()">
                            <option value="all" {{ old('audience') === 'all' ? 'selected' : '' }}>All Users</option>
                            <option value="buyers" {{ old('audience') === 'buyers' ? 'selected' : '' }}>Buyers Only</option>
                            <option value="vendors" {{ old('audience') === 'vendors' ? 'selected' : '' }}>Vendors Only</option>
                            <option value="newsletter" {{ old('audience') === 'newsletter' ? 'selected' : '' }}>Newsletter Subscribers</option>
                            <option value="custom" {{ old('audience') === 'custom' ? 'selected' : '' }}>Custom Selection</option>
                        </select>
                    </div>

                    <!-- Custom Filters (shown when audience=custom) -->
                    <div id="customFilters" class="hidden bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Roles</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach(['buyer', 'vendor', 'admin', 'support'] as $role)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="filters[roles][]" value="{{ $role }}"
                                       class="text-indigo-600 rounded focus:ring-indigo-500"
                                       {{ in_array($role, old('filters.roles', [])) ? 'checked' : '' }}
                                       onchange="updateAudienceCount()">
                                <span class="text-sm">{{ ucfirst($role) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Subject (shown only for email type) -->
            <div id="subjectField" class="bg-white rounded-xl shadow-sm p-6">
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject *</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g. Don't miss our February deals!">
                    @error('subject')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Message Content -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Message Content</h3>

                <!-- Email Editor -->
                <div id="emailEditor">
                    <textarea name="message" id="emailMessage" class="w-full">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 flex gap-2">
                        <button type="button" onclick="previewEmail()" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                            <i class="fas fa-eye mr-1"></i> Preview Email
                        </button>
                    </div>
                </div>

                <!-- SMS Editor -->
                <div id="smsEditor" class="hidden">
                    <textarea name="sms_message" id="smsMessage" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Type your SMS message here..." maxlength="160"
                              oninput="updateCharCount()">{{ old('sms_message') }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">
                        <span id="charCount">0</span>/160 characters
                    </p>
                </div>
            </div>

            <!-- Schedule -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Scheduling</h3>
                <div>
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">Schedule for later (optional)</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to send immediately or save as draft.</p>
                    @error('scheduled_at')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3 justify-end">
                <a href="{{ route('admin.campaigns.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </a>
                <button type="button" onclick="submitForm('draft')" class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-save mr-1"></i> Save Draft
                </button>
                <button type="button" onclick="submitForm('schedule')" class="px-6 py-2.5 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                    <i class="fas fa-clock mr-1"></i> Schedule
                </button>
                <button type="button" onclick="confirmSend()" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-paper-plane mr-1"></i> Send Now
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="font-semibold text-gray-900">Email Preview</h3>
            <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div id="previewContent" class="p-4"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    let editor;

    document.addEventListener('DOMContentLoaded', function() {
        initTinyMCE();
        toggleTypeFields();
        toggleCustomFilters();
        updateAudienceCount();
    });

    function initTinyMCE() {
        tinymce.init({
            selector: '#emailMessage',
            height: 400,
            menubar: false,
            plugins: 'lists link image code table hr',
            toolbar: 'undo redo | blocks | bold italic underline | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image | hr | code',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
            setup: function(ed) {
                editor = ed;
            }
        });
    }

    function toggleTypeFields() {
        const type = document.querySelector('input[name="type"]:checked')?.value || 'email';
        const subjectField = document.getElementById('subjectField');
        const emailEditor = document.getElementById('emailEditor');
        const smsEditor = document.getElementById('smsEditor');

        if (type === 'email') {
            subjectField.classList.remove('hidden');
            emailEditor.classList.remove('hidden');
            smsEditor.classList.add('hidden');
        } else {
            subjectField.classList.add('hidden');
            emailEditor.classList.add('hidden');
            smsEditor.classList.remove('hidden');
            updateCharCount();
        }

        // Newsletter only supports email
        const audienceSelect = document.getElementById('audience');
        const newsletterOption = audienceSelect.querySelector('option[value="newsletter"]');
        if (type === 'sms') {
            newsletterOption.disabled = true;
            if (audienceSelect.value === 'newsletter') {
                audienceSelect.value = 'all';
            }
        } else {
            newsletterOption.disabled = false;
        }

        updateAudienceCount();
    }

    function toggleCustomFilters() {
        const audience = document.getElementById('audience').value;
        document.getElementById('customFilters').classList.toggle('hidden', audience !== 'custom');
    }

    function updateCharCount() {
        const text = document.getElementById('smsMessage').value;
        document.getElementById('charCount').textContent = text.length;
    }

    function updateAudienceCount() {
        const audience = document.getElementById('audience').value;
        const type = document.querySelector('input[name="type"]:checked')?.value || 'email';
        const checkedRoles = Array.from(document.querySelectorAll('input[name="filters[roles][]"]:checked')).map(el => el.value);

        const data = { audience, type, filters: { roles: checkedRoles } };

        fetch('{{ route("admin.campaigns.audience-count") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('audienceCount').textContent = `(~${data.count} recipients)`;
        })
        .catch(() => {});
    }

    function submitForm(action) {
        document.getElementById('formAction').value = action;

        // Sync TinyMCE content if email type
        const type = document.querySelector('input[name="type"]:checked')?.value || 'email';
        if (type === 'sms') {
            // Copy SMS message to the main message field
            const smsMsg = document.getElementById('smsMessage').value;
            if (editor) editor.setContent(smsMsg);
            document.getElementById('emailMessage').value = smsMsg;
        } else if (editor) {
            editor.save();
        }

        document.getElementById('campaignForm').submit();
    }

    function confirmSend() {
        const audience = document.getElementById('audience');
        const audienceText = audience.options[audience.selectedIndex].text;

        if (confirm(`Are you sure you want to send this campaign to "${audienceText}" now? This action cannot be undone.`)) {
            submitForm('send');
        }
    }

    function previewEmail() {
        if (editor) editor.save();
        const message = document.getElementById('emailMessage').value;
        const subject = document.getElementById('subject').value;

        fetch('{{ route("admin.campaigns.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message, subject })
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('previewContent').innerHTML = data.html;
            document.getElementById('previewModal').classList.remove('hidden');
        })
        .catch(() => alert('Failed to generate preview.'));
    }

    function closePreview() {
        document.getElementById('previewModal').classList.add('hidden');
    }
</script>
@endsection
