@extends('layouts.admin')

@section('title', 'Edit Campaign - Admin Dashboard')
@section('page-title', 'Edit Campaign')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 4px 6px;
        min-height: 42px;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #e0e7ff;
        border: none;
        color: #3730a3;
        border-radius: 0.375rem;
        padding: 2px 8px;
        font-size: 0.75rem;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color: #6366f1; margin-right: 4px; }
    .select2-dropdown { border-radius: 0.5rem; border: 1px solid #d1d5db; }
    .select2-search--dropdown .select2-search__field { border-radius: 0.375rem; border: 1px solid #d1d5db; padding: 6px 10px; }
    .select2-results__option { padding: 8px 12px; font-size: 0.875rem; }
    .select2-results__option--highlighted { background-color: #6366f1 !important; }
    .select2-results__option .user-email { font-size: 0.75rem; color: #6b7280; }
    .select2-results__option--highlighted .user-email { color: #c7d2fe; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.campaigns.update', $campaign) }}" method="POST" id="campaignForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="action" id="formAction" value="draft">

        <div class="space-y-6">
            <!-- Campaign Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Campaign Details</h3>

                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Campaign Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $campaign->title) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type Selector -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Type *</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="email" {{ old('type', $campaign->type) === 'email' ? 'checked' : '' }}
                                       class="text-indigo-600 focus:ring-indigo-500" onchange="toggleTypeFields()">
                                <span class="text-sm"><i class="fas fa-envelope text-blue-500 mr-1"></i> Email</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="sms" {{ old('type', $campaign->type) === 'sms' ? 'checked' : '' }}
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
                            <option value="all"            {{ old('audience', $campaign->audience) === 'all'            ? 'selected' : '' }}>All Users</option>
                            <option value="buyers"         {{ old('audience', $campaign->audience) === 'buyers'         ? 'selected' : '' }}>Buyers Only</option>
                            <option value="vendors"        {{ old('audience', $campaign->audience) === 'vendors'        ? 'selected' : '' }}>Vendors Only</option>
                            <option value="newsletter"     {{ old('audience', $campaign->audience) === 'newsletter'     ? 'selected' : '' }}>Newsletter Subscribers</option>
                            <option value="custom"         {{ old('audience', $campaign->audience) === 'custom'         ? 'selected' : '' }}>Custom (by Role)</option>
                            <option value="specific_users" {{ old('audience', $campaign->audience) === 'specific_users' ? 'selected' : '' }}>Specific Users</option>
                        </select>
                    </div>

                    <!-- Custom Role Filters -->
                    @php $savedRoles = old('filters.roles', $campaign->filters['roles'] ?? []); @endphp
                    <div id="customFilters" class="{{ in_array(old('audience', $campaign->audience), ['custom']) ? '' : 'hidden' }} bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Roles</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach(['buyer', 'vendor', 'admin', 'support'] as $role)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="filters[roles][]" value="{{ $role }}"
                                       class="text-indigo-600 rounded focus:ring-indigo-500"
                                       {{ in_array($role, $savedRoles) ? 'checked' : '' }}
                                       onchange="updateAudienceCount()">
                                <span class="text-sm">{{ ucfirst($role) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Specific Users Select2 -->
                    @php $savedUserIds = old('filters.user_ids', $campaign->filters['user_ids'] ?? []); @endphp
                    <div id="specificUsersField" class="{{ in_array(old('audience', $campaign->audience), ['specific_users']) ? '' : 'hidden' }}">
                        <label for="userSelect2" class="block text-sm font-medium text-gray-700 mb-1">
                            Search &amp; Select Users *
                            <span class="text-gray-400 font-normal ml-1">— type to search by name or email</span>
                        </label>
                        <select id="userSelect2" name="filters[user_ids][]" multiple
                                class="w-full" style="width:100%">
                            @foreach($savedUserIds as $uid)
                                @php $u = App\Models\User::find($uid) @endphp
                                @if($u)
                                    <option value="{{ $u->id }}" selected>{{ $u->name }} — {{ $u->email }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Email Subject -->
            <div id="subjectField" class="bg-white rounded-xl shadow-sm p-6">
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject *</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $campaign->subject) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('subject')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Message Content -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Message Content</h3>

                <div id="emailEditor">
                    <p class="text-xs text-gray-500 mb-2">Write your email message below. Line breaks will be preserved in the sent email.</p>
                    <textarea name="message" id="emailMessage" rows="12"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                              placeholder="Hello,&#10;&#10;...">{{ old('message', $campaign->message) }}</textarea>
                    @error('message')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 flex gap-2">
                        <button type="button" onclick="previewEmail()" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                            <i class="fas fa-eye mr-1"></i> Preview Email
                        </button>
                    </div>
                </div>

                <div id="smsEditor" class="hidden">
                    <textarea name="sms_message" id="smsMessage" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              maxlength="160" oninput="updateCharCount()">{{ old('sms_message', $campaign->type === 'sms' ? $campaign->message : '') }}</textarea>
                    <p class="text-sm text-gray-500 mt-1"><span id="charCount">0</span>/160 characters</p>
                </div>
            </div>

            <!-- Schedule -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Scheduling</h3>
                <div>
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">Schedule for later (optional)</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                           value="{{ old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}">
                    @error('scheduled_at')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3 justify-end">
                <a href="{{ route('admin.campaigns.show', $campaign) }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
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
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        toggleTypeFields();
        toggleCustomFilters();
        updateAudienceCount();
        initSelect2();
    });

    function initSelect2() {
        $('#userSelect2').select2({
            placeholder: 'Type a name or email to search...',
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: '{{ route("admin.campaigns.search-users") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) { return { q: params.term }; },
                processResults: function(data) {
                    return {
                        results: data.map(function(u) {
                            return { id: u.id, text: u.name, email: u.email, role: u.role };
                        })
                    };
                },
                cache: true
            },
            templateResult: function(u) {
                if (u.loading) return u.text;
                return $('<span>' + u.text + '<br><small class="user-email">' + (u.email || '') + ' · ' + (u.role || '') + '</small></span>');
            },
            templateSelection: function(u) { return u.text || u.id; }
        }).on('change', function() {
            const count = $(this).val() ? $(this).val().length : 0;
            document.getElementById('audienceCount').textContent = count > 0 ? '(' + count + ' selected)' : '';
        });
    }

    function toggleCustomFilters() {
        const audience = document.getElementById('audience').value;
        document.getElementById('customFilters').classList.toggle('hidden', audience !== 'custom');
        document.getElementById('specificUsersField').classList.toggle('hidden', audience !== 'specific_users');
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

        const audienceSelect = document.getElementById('audience');
        const newsletterOption = audienceSelect.querySelector('option[value="newsletter"]');
        if (type === 'sms') {
            newsletterOption.disabled = true;
            if (audienceSelect.value === 'newsletter') audienceSelect.value = 'all';
        } else {
            newsletterOption.disabled = false;
        }
        updateAudienceCount();
    }

    function updateCharCount() {
        const text = document.getElementById('smsMessage').value;
        document.getElementById('charCount').textContent = text.length;
    }

    function updateAudienceCount() {
        const audience = document.getElementById('audience').value;
        if (audience === 'specific_users') {
            const count = ($('#userSelect2').val() || []).length;
            document.getElementById('audienceCount').textContent = count > 0 ? '(' + count + ' selected)' : '';
            return;
        }

        const type = document.querySelector('input[name="type"]:checked')?.value || 'email';
        const checkedRoles = Array.from(document.querySelectorAll('input[name="filters[roles][]"]:checked')).map(el => el.value);

        fetch('{{ route("admin.campaigns.audience-count") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ audience, type, filters: { roles: checkedRoles } })
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('audienceCount').textContent = '(~' + data.count + ' recipients)';
        })
        .catch(() => {});
    }

    function submitForm(action) {
        document.getElementById('formAction').value = action;
        const type = document.querySelector('input[name="type"]:checked')?.value || 'email';
        if (type === 'sms') {
            document.getElementById('emailMessage').value = document.getElementById('smsMessage').value;
        }
        document.getElementById('campaignForm').submit();
    }

    function confirmSend() {
        const audience = document.getElementById('audience');
        const audienceText = audience.options[audience.selectedIndex].text;
        const countText = document.getElementById('audienceCount').textContent;
        if (confirm('Are you sure you want to send this campaign to "' + audienceText + '" ' + countText + ' now?')) {
            submitForm('send');
        }
    }

    function previewEmail() {
        const message = document.getElementById('emailMessage').value;
        const subject = document.getElementById('subject').value;

        fetch('{{ route("admin.campaigns.preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
