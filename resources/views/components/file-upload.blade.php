@props([
    'label' => null,
    'description' => null,
    'hint' => null,
    'accept' => '*',
    'multiple' => false,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
])

@php
    $modelAttribute = $attributes->wire('model')->value ?? 'files';
    $inputId = 'file-input-' . \Illuminate\Support\Str::slug($modelAttribute);
    $uploadAreaId = 'upload-area-' . \Illuminate\Support\Str::slug($modelAttribute);
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $inputId }}" @class([
            'block text-sm font-medium mb-2',
            'text-gray-700 dark:text-gray-300',
        ])>
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    @if ($description)
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
            {{ $description }}
        </p>
    @endif

    <div @class([
        'relative w-full',
        'opacity-50 cursor-not-allowed' => $disabled || $readonly,
    ])>
        <!-- Hidden file input -->
        <input 
            id="{{ $inputId }}"
            type="file"
            {{ $attributes }}
            accept="{{ $accept }}"
            {{ $multiple ? 'multiple' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $disabled || $readonly ? 'disabled' : '' }}
            style="display: none;"
        />

        <!-- Upload area -->
        <div id="{{ $uploadAreaId }}"
             @class([
                'relative rounded-lg border-2 border-dashed p-6 text-center transition-colors',
                'border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-gray-50 dark:hover:bg-gray-800/50' => !($disabled || $readonly),
                'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30' => $disabled || $readonly,
                'cursor-pointer' => !($disabled || $readonly),
            ])>
            
            <!-- Upload icon -->
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-6-12l-6-6m0 0l-6 6m6-6v18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

            <!-- Text -->
            <div class="mt-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    Click to upload @if($multiple) files @else a file @endif
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    or drag and drop
                </p>
            </div>

            <!-- Hint -->
            @if ($hint)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                    {{ $hint }}
                </p>
            @endif
        </div>
    </div>

    <!-- Slot for file list or additional content -->
    @if ($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>

<script>
    // Use event delegation on document (persists across Livewire re-renders)
    // This ensures file picker works after component re-render/form submission
    
    document.addEventListener('click', function(e) {
        const uploadArea = e.target.closest('[id="{{ $uploadAreaId }}"]');
        if (uploadArea) {
            const fileInput = document.getElementById('{{ $inputId }}');
            if (fileInput) {
                fileInput.click();
            }
        }
    });

    document.addEventListener('dragenter', function(e) {
        const uploadArea = e.target.closest('[id="{{ $uploadAreaId }}"]');
        if (uploadArea) {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.add('border-blue-400', 'dark:border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        }
    }, false);

    document.addEventListener('dragover', function(e) {
        const uploadArea = e.target.closest('[id="{{ $uploadAreaId }}"]');
        if (uploadArea) {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.add('border-blue-400', 'dark:border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        }
    }, false);

    document.addEventListener('dragleave', function(e) {
        const uploadArea = e.target.closest('[id="{{ $uploadAreaId }}"]');
        if (uploadArea && !uploadArea.contains(e.relatedTarget)) {
            uploadArea.classList.remove('border-blue-400', 'dark:border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        }
    }, false);

    document.addEventListener('drop', function(e) {
        const uploadArea = e.target.closest('[id="{{ $uploadAreaId }}"]');
        if (uploadArea) {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.remove('border-blue-400', 'dark:border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            
            const fileInput = document.getElementById('{{ $inputId }}');
            const files = e.dataTransfer.files;
            if (files && files.length > 0 && fileInput) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('input', { bubbles: true }));
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }, false);

    // Clear file input value when form is reset (allows file picker to open again)
    document.addEventListener('livewire:init', function() {
        Livewire.on('fileInputReset', function() {
            const fileInput = document.getElementById('{{ $inputId }}');
            if (fileInput) {
                fileInput.value = '';
            }
        });
    });
</script>
