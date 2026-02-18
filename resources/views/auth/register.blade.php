<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- NIM -->
        <div>
            <x-input-label for="nim" :value="__('NIM')" />
            <x-text-input id="nim" class="block mt-1 w-full" type="text" name="nim" :value="old('nim')" required autofocus autocomplete="nim" placeholder="Contoh: 2024001" />
            <x-input-error :messages="$errors->get('nim')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" placeholder="Nama lengkap mahasiswa" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="email@student.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Nomor HP')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="Contoh: 081234567890" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Faculty -->
        <div class="mt-4">
            <x-input-label for="faculty" :value="__('Fakultas')" />
            <select id="faculty" name="faculty" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm" required onchange="updateMajorOptions()">
                <option value="">Pilih Fakultas</option>
                <option value="Fakultas Kesehatan" {{ old('faculty') == 'Fakultas Kesehatan' ? 'selected' : '' }}>Fakultas Kesehatan</option>
                <option value="Fakultas Ekonomi Hukum dan Humaniora" {{ old('faculty') == 'Fakultas Ekonomi Hukum dan Humaniora' ? 'selected' : '' }}>Fakultas Ekonomi Hukum dan Humaniora</option>
                <option value="Fakultas Komputer dan Pendidikan" {{ old('faculty') == 'Fakultas Komputer dan Pendidikan' ? 'selected' : '' }}>Fakultas Komputer dan Pendidikan</option>
                <option value="Fakultas Magister" {{ old('faculty') == 'Fakultas Magister' ? 'selected' : '' }}>Fakultas Magister</option>
            </select>
            <x-input-error :messages="$errors->get('faculty')" class="mt-2" />
        </div>

        <!-- Major (Program Studi) -->
        <div class="mt-4">
            <x-input-label for="major" :value="__('Program Studi')" />
            <select id="major" name="major" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm" required>
                <option value="">Pilih Program Studi</option>
            </select>
            <x-input-error :messages="$errors->get('major')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- CAPTCHA -->
        <div class="mt-4">
            <x-input-label for="captcha" :value="__('Verifikasi Keamanan (CAPTCHA)')" />
            <div class="flex items-center gap-3 mt-1">
                <div class="bg-gray-100 dark:bg-gray-800 rounded p-2">
                    {!! captcha_img('flat') !!}
                </div>
                <button type="button" onclick="location.reload()" class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300" title="Refresh CAPTCHA">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <x-text-input id="captcha" class="block mt-2 w-full" type="text" name="captcha" required placeholder="Masukkan kode di atas" />
            <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Sudah punya akun?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Daftar') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        const majorsByFaculty = {
            'Fakultas Kesehatan': [
                'Sarjana Keperawatan',
                'Sarjana Kebidanan',
                'Sarjana Kesehatan Masyarakat',
                'Sarjana Gizi',
                'Sarjana Farmasi',
                'Sarjana Ilmu Keolahragaan',
                'Diploma 3 Keperawatan',
                'Profesi Ners',
                'Profesi Kebidanan',
                'Pendidikan Profesi Apoteker'
            ],
            'Fakultas Ekonomi Hukum dan Humaniora': [
                'Sarjana Ilmu Hukum',
                'Sarjana Sastra Inggris',
                'Sarjana Sastra Jepang',
                'Sarjana Bisnis Digital',
                'Diploma 4 Akuntansi Perpajakan',
                'Diploma 4 Bisnis dan Manajemen Ritel'
            ],
            'Fakultas Komputer dan Pendidikan': [
                'Sarjana Teknik Informatika',
                'Sarjana Pendidikan Guru SD',
                'Sarjana Pendidikan Guru PAUD',
                'Sarjana Pendidikan Vokasional Desain Fashion'
            ],
            'Fakultas Magister': [
                'Magister Manajemen Pendidikan',
                'Magister Hukum',
                'Magister Keperawatan',
                'Magister Kesehatan Masyarakat'
            ]
        };

        function updateMajorOptions() {
            const facultySelect = document.getElementById('faculty');
            const majorSelect = document.getElementById('major');
            const selectedFaculty = facultySelect.value;

            // Clear current options
            majorSelect.innerHTML = '<option value="">Pilih Program Studi</option>';

            if (selectedFaculty && majorsByFaculty[selectedFaculty]) {
                majorsByFaculty[selectedFaculty].forEach(function(major) {
                    const option = document.createElement('option');
                    option.value = major;
                    option.textContent = major;
                    majorSelect.appendChild(option);
                });
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateMajorOptions();
        });
    </script>
</x-guest-layout>
