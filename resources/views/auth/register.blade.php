<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <input type="hidden" name="role" id="role-input" value="{{ old('role', 'mahasiswa') }}">

        {{-- Role Tabs --}}
        <div class="mb-4">
            <x-input-label :value="__('Daftar sebagai')" />
            <div class="flex gap-2 mt-1">
                <button type="button" id="tab-mahasiswa" onclick="switchRole('mahasiswa')"
                        class="flex-1 py-2 px-3 text-sm font-semibold rounded-md border transition-colors duration-200
                               {{ old('role', 'mahasiswa') !== 'dosen' ? 'bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 border-gray-800 dark:border-gray-200' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                    Mahasiswa
                </button>
                <button type="button" id="tab-dosen" onclick="switchRole('dosen')"
                        class="flex-1 py-2 px-3 text-sm font-semibold rounded-md border transition-colors duration-200
                               {{ old('role') === 'dosen' ? 'bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 border-gray-800 dark:border-gray-200' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                    Dosen
                </button>
            </div>
        </div>

        {{-- Dosen Approval Notice --}}
        <div id="dosen-notice" class="{{ old('role') === 'dosen' ? '' : 'hidden' }} mb-4 text-sm text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-md p-3">
            Akun dosen memerlukan <strong>persetujuan admin</strong> sebelum dapat digunakan.
        </div>

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                          :value="old('name')" required autofocus autocomplete="name"
                          placeholder="Nama lengkap Anda" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                          :value="old('email')" required autocomplete="username"
                          placeholder="contoh@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- NIM (Mahasiswa) --}}
        <div id="field-nim" class="mt-4 {{ old('role') === 'dosen' ? 'hidden' : '' }}">
            <x-input-label for="nim" :value="__('NIM')" />
            <x-text-input id="nim" class="block mt-1 w-full" type="text" name="nim"
                          :value="old('nim')" autocomplete="off" maxlength="20"
                          placeholder="Nomor Induk Mahasiswa" />
            <x-input-error :messages="$errors->get('nim')" class="mt-2" />
        </div>

        {{-- Kode Kelas (Mahasiswa) --}}
        <div id="field-class" class="mt-4 {{ old('role') === 'dosen' ? 'hidden' : '' }}">
            <x-input-label for="student_class_id" :value="__('Kode Kelas')" />
            <select id="student_class_id" name="student_class_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">Pilih Kode Kelas</option>
                @isset($studentClasses)
                    @foreach($studentClasses as $klass)
                        <option value="{{ $klass->id }}" {{ old('student_class_id') == $klass->id ? 'selected' : '' }}>
                            {{ $klass->name }}
                        </option>
                    @endforeach
                @endisset
            </select>
            <x-input-error :messages="$errors->get('student_class_id')" class="mt-2" />
        </div>

        {{-- NIP (Dosen) --}}
        <div id="field-nip" class="mt-4 {{ old('role') === 'dosen' ? '' : 'hidden' }}">
            <x-input-label for="nip" :value="__('NIP')" />
            <x-text-input id="nip" class="block mt-1 w-full" type="text" name="nip"
                          :value="old('nip')" autocomplete="off" maxlength="20"
                          placeholder="Nomor Induk Pegawai" />
            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                          required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                          name="password_confirmation" required autocomplete="new-password"
                          placeholder="Ulangi password Anda" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
               href="{{ route('login') }}">
                {{ __('Sudah punya akun?') }}
            </a>

            <x-primary-button class="ms-4" id="btn-submit">
                <span id="btn-text">{{ __('Daftar') }}</span>
            </x-primary-button>
        </div>
    </form>

    <script>
        function switchRole(role) {
            const roleInput   = document.getElementById('role-input');
            const tabMhs      = document.getElementById('tab-mahasiswa');
            const tabDosen    = document.getElementById('tab-dosen');
            const fieldNim    = document.getElementById('field-nim');
            const fieldNip    = document.getElementById('field-nip');
            const fieldClass  = document.getElementById('field-class');
            const notice      = document.getElementById('dosen-notice');
            const nimInput    = document.getElementById('nim');
            const nipInput    = document.getElementById('nip');
            const classInput  = document.getElementById('student_class_id');

            const activeClass   = ['bg-gray-800', 'dark:bg-gray-200', 'text-white', 'dark:text-gray-800', 'border-gray-800', 'dark:border-gray-200'];
            const inactiveClass = ['bg-white', 'dark:bg-gray-700', 'text-gray-600', 'dark:text-gray-300', 'border-gray-300', 'dark:border-gray-600', 'hover:bg-gray-50', 'dark:hover:bg-gray-600'];

            roleInput.value = role;

            if (role === 'mahasiswa') {
                tabMhs.classList.add(...activeClass);
                tabMhs.classList.remove(...inactiveClass);
                tabDosen.classList.add(...inactiveClass);
                tabDosen.classList.remove(...activeClass);

                fieldNim.classList.remove('hidden');
                fieldNip.classList.add('hidden');
                fieldClass.classList.remove('hidden');
                notice.classList.add('hidden');

                nimInput.required = true;
                nipInput.required = false;
                nipInput.value    = '';
                classInput.required = true;
            } else {
                tabDosen.classList.add(...activeClass);
                tabDosen.classList.remove(...inactiveClass);
                tabMhs.classList.add(...inactiveClass);
                tabMhs.classList.remove(...activeClass);

                fieldNip.classList.remove('hidden');
                fieldNim.classList.add('hidden');
                fieldClass.classList.add('hidden');
                notice.classList.remove('hidden');

                nipInput.required = true;
                nimInput.required = false;
                nimInput.value    = '';
                classInput.required = false;
                classInput.value    = '';
            }
        }
    </script>
</x-guest-layout>
