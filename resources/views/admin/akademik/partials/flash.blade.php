@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="flex items-center gap-3 p-4 rounded-xl bg-[color:var(--md-sys-color-tertiary-container,#e8f5e9)] text-[color:var(--md-sys-color-on-tertiary-container,#1b5e20)] border border-[color:var(--md-sys-color-tertiary,#2e7d32)]/20">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check_circle</span>
        <span class="text-sm font-medium">{{ session('success') }}</span>
        <button @click="show = false" class="ml-auto text-current/60 hover:text-current">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="flex items-center gap-3 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">error</span>
        <span class="text-sm font-medium">{{ session('error') }}</span>
        <button @click="show = false" class="ml-auto text-current/60 hover:text-current">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="flex items-start gap-3 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200">
        <span class="material-symbols-outlined mt-0.5" style="font-variation-settings: 'FILL' 1;">warning</span>
        <ul class="text-sm font-medium list-disc ml-1 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
