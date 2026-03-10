@extends('layouts.admin')

@section('title', 'New Broadcast - Admin Dashboard')
@section('page-title', 'New Broadcast')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        padding: 6px 12px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #dc2626;
        box-shadow: 0 0 0 2px rgba(220,38,38,0.15);
        outline: none;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
        padding-left: 0;
        color: #111827;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
    .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9ca3af; }
    .select2-dropdown { border-radius: 0.5rem; border: 1px solid #d1d5db; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1); }
    .select2-search--dropdown .select2-search__field { border-radius: 0.375rem; border: 1px solid #d1d5db; padding: 6px 10px; font-size: 0.875rem; }
    .select2-results__option { padding: 10px 12px; font-size: 0.875rem; }
    .select2-results__option--highlighted { background-color: #dc2626 !important; }
    .select2-results__option .user-meta { font-size: 0.75rem; color: #6b7280; margin-top: 2px; }
    .select2-results__option--highlighted .user-meta { color: #fecaca; }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto">
    <form action="{{ route('admin.broadcasts.store') }}" method="POST" id="broadcastForm">
        @csrf
        <input type="hidden" name="action" id="formAction" value="draft">

        <div class="space-y-6">
            <!-- Message Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Message Details</h3>

                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="e.g. Important Update, New Feature Available">
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Message Body *</label>
                        <textarea name="body" id="body" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                  placeholder="Write your message to users here...">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">Image URL <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="url" name="image_url" id="image_url" value="{{ old('image_url') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="https://...">
                        @error('image_url')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="route" class="block text-sm font-medium text-gray-700 mb-1">In-app Route <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="route" id="route" value="{{ old('route', '/notifications') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="/notifications">
                        <p class="text-xs text-gray-400 mt-1">Where to navigate in the app when the user taps the notification.</p>
                        @error('route')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Audience -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Target Audience</h3>

                <div class="space-y-4">
                    <div>
                        <label for="audience" class="block text-sm font-medium text-gray-700 mb-1">
                            Send To *
                            <span id="audienceCount" class="ml-2 text-red-600 font-normal"></span>
                        </label>
                        <select name="audience" id="audience" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                onchange="toggleSpecificUser(); updateAudienceCount()">
                            <option value="all"           {{ old('audience') === 'all'           ? 'selected' : '' }}>All Users</option>
                            <option value="buyers"        {{ old('audience') === 'buyers'        ? 'selected' : '' }}>Buyers Only</option>
                            <option value="vendors"       {{ old('audience') === 'vendors'       ? 'selected' : '' }}>Vendors Only</option>
                            <option value="specific_user" {{ old('audience') === 'specific_user' ? 'selected' : '' }}>Specific User</option>
                        </select>
                        @error('audience')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Specific User — Select2 single search -->
                    <div id="specificUserField" class="{{ old('audience') === 'specific_user' ? '' : 'hidden' }}">
                        <label for="userSelect2" class="block text-sm font-medium text-gray-700 mb-1">
                            Find User *
                            <span class="text-gray-400 font-normal ml-1">— search by name or email</span>
                        </label>
                        <select id="userSelect2" name="user_id" style="width:100%">
                            @if(old('user_id'))
                                @php $u = App\Models\User::find(old('user_id')) @endphp
                                @if($u)
                                    <option value="{{ $u->id }}" selected>{{ $u->name }} — {{ $u->email }}</option>
                                @endif
                            @else
                                <option value=""></option>
                            @endif
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3 justify-end">
                <a href="{{ route('admin.broadcasts.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </a>
                <button type="button" onclick="submitForm('draft')" class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-save mr-1"></i> Save Draft
                </button>
                <button type="button" onclick="confirmSend()" class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-paper-plane mr-1"></i> Send Now
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initSelect2();
        updateAudienceCount();
    });

    // ─── Select2 init ─────────────────────────────────────────────────
    function initSelect2() {
        $('#userSelect2').select2({
            placeholder: 'Type a name or email to search...',
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: '{{ route("admin.broadcasts.search-users") }}',
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
                if (u.loading) return $('<span><i class="fas fa-spinner fa-spin mr-1"></i>Searching...</span>');
                return $('<div><div style="font-weight:500">' + u.text + '</div><div class="user-meta">' + (u.email || '') + ' · ' + (u.role || '') + '</div></div>');
            },
            templateSelection: function(u) { return u.text || u.id; }
        }).on('change', function() {
            const val = $(this).val();
            document.getElementById('audienceCount').textContent = val ? '(1 recipient)' : '';
        });
    }

    // ─── Toggle Specific User field ───────────────────────────────────
    function toggleSpecificUser() {
        const audience = document.getElementById('audience').value;
        const field = document.getElementById('specificUserField');
        field.classList.toggle('hidden', audience !== 'specific_user');
        if (audience !== 'specific_user') {
            $('#userSelect2').val(null).trigger('change');
        }
    }

    // ─── Audience count ───────────────────────────────────────────────
    function updateAudienceCount() {
        const audience = document.getElementById('audience').value;
        if (audience === 'specific_user') {
            const val = $('#userSelect2').val();
            document.getElementById('audienceCount').textContent = val ? '(1 recipient)' : '';
            return;
        }

        fetch('{{ route("admin.broadcasts.audience-count") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ audience })
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('audienceCount').textContent = '(~' + data.count.toLocaleString() + ' recipients)';
        })
        .catch(() => {});
    }

    // ─── Form submission ──────────────────────────────────────────────
    function submitForm(action) {
        document.getElementById('formAction').value = action;
        document.getElementById('broadcastForm').submit();
    }

    function confirmSend() {
        const audienceSelect = document.getElementById('audience');
        const audienceText = audienceSelect.options[audienceSelect.selectedIndex].text;
        const count = document.getElementById('audienceCount').textContent;

        if (confirm('Send this broadcast to "' + audienceText + '" ' + count + ' now? This cannot be undone.')) {
            submitForm('send');
        }
    }
</script>
@endsection
