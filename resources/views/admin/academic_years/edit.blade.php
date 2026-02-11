<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Tahun Akademik') }}
            </h2>
            <a href="{{ route('admin.academic-years.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.academic-years.update', $academicYear) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Year Start -->
                            <div>
                                <x-input-label for="year_start" :value="__('Tahun Mulai')" />
                                <x-text-input id="year_start" class="block mt-1 w-full" type="text" name="year_start" :value="old('year_start', $academicYear->year_start)" required autofocus />
                                <x-input-error :messages="$errors->get('year_start')" class="mt-2" />
                            </div>

                            <!-- Year End -->
                            <div>
                                <x-input-label for="year_end" :value="__('Tahun Selesai')" />
                                <x-text-input id="year_end" class="block mt-1 w-full" type="text" name="year_end" :value="old('year_end', $academicYear->year_end)" required />
                                <x-input-error :messages="$errors->get('year_end')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Is Active -->
                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="is_active" {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Set as Active Academic Year') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.academic-years.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
