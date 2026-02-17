<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Upload Bukti Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-2 sm:px-6 lg:px-8">
            @php
                $hoursLeft = now()->diffInHours($registration->expires_at, false);
                $isUrgent = $hoursLeft <= 4;
                $hasExistingFile = $registration->payment_proof && \Storage::disk('public')->exists($registration->payment_proof);
                $imageUrl = $hasExistingFile ? asset('storage/' . $registration->payment_proof) : null;
            @endphp

            @if(session('success'))
                <div class="mb-4 p-3 sm:p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm sm:text-base">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm sm:text-base">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Info Pendaftaran -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4 mb-6">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">No. Pendaftaran</p>
                                <p class="font-mono font-medium text-gray-900 dark:text-white">{{ $registration->registration_number }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Jadwal</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $registration->examSchedule->title }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $registration->examSchedule->exam_date->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Biaya</p>
                                <p class="font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($registration->examSchedule->price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Petunjuk Pembayaran -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 sm:p-4 mb-6">
                        <p class="text-xs sm:text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Petunjuk Pembayaran:</p>
                        <ul class="text-xs sm:text-sm text-blue-700 dark:text-blue-300 space-y-1">
                            <li>Transfer: <strong>{{ $registration->examSchedule->bank_name ?? 'Bank BCA' }} {{ $registration->examSchedule->bank_account ?? '123-456-7890' }}</strong> a.n. <strong>{{ $registration->examSchedule->account_holder ?? 'EPT' }}</strong></li>
                            <li>Nominal: <strong>Rp {{ number_format($registration->examSchedule->price, 0, ',', '.') }}</strong></li>
                            <li>Keterangan: <strong>{{ $registration->registration_number }}</strong></li>
                        </ul>
                    </div>

                    <!-- File yang sudah diupload -->
                    @if($hasExistingFile)
                    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-300 rounded-lg">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">✓ Bukti Pembayaran Berhasil Diupload</p>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Menunggu Verifikasi</span>
                        </div>
                        <img src="{{ $imageUrl }}" alt="Bukti Pembayaran" class="max-w-full h-auto max-h-64 mx-auto rounded-lg border border-green-300 mb-3">
                        <p class="text-xs text-center text-green-600 mb-4">Upload: {{ $registration->payment_uploaded_at->format('d M Y, H:i') }}</p>
                        
                        <div class="flex gap-3 justify-center">
                            <a href="{{ route('mahasiswa.registrations.show', $registration) }}" 
                               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                Lihat Detail
                            </a>
                            <a href="{{ route('mahasiswa.registrations.index') }}" 
                               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                Daftar Ujian
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Upload Section -->
                    <form method="POST" action="{{ route('mahasiswa.registrations.payment.store', $registration) }}" enctype="multipart/form-data" id="upload-form">
                        @csrf

                        <!-- Tombol Pilih File -->
                        <div class="mb-4">
                            <label for="payment_proof" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md cursor-pointer transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Pilih File
                            </label>
                            <input type="file" id="payment_proof" name="payment_proof" class="hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="showPreview(this)">
                            <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">Format: JPG, PNG, PDF (Max 5MB)</span>
                        </div>

                        <!-- Preview File -->
                        <div id="preview-section" class="hidden mb-6">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Preview File:</p>
                                    <button type="button" onclick="removeFile()" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                                </div>
                                <img id="preview-image" class="max-w-full h-auto max-h-64 mx-auto rounded-lg border border-gray-300 dark:border-gray-600">
                                <div class="mt-2 flex items-center justify-between">
                                    <p id="preview-filename" class="text-sm text-gray-600 dark:text-gray-400 truncate"></p>
                                    <p id="preview-filesize" class="text-sm text-gray-500 ml-2"></p>
                                </div>
                            </div>
                        </div>

                        @error('payment_proof')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Progress Bar -->
                        <div id="upload-progress" class="hidden mb-6">
                            <div class="relative pt-1">
                                <div class="flex mb-2 items-center justify-between">
                                    <div>
                                        <span id="progress-text" class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                                            Mengupload...
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span id="progress-percent" class="text-xs font-semibold inline-block text-indigo-600">
                                            0%
                                        </span>
                                    </div>
                                </div>
                                <div class="overflow-hidden h-4 mb-4 text-xs flex rounded bg-indigo-200">
                                    <div id="progress-bar" style="width:0%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-600 transition-all duration-300"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="mb-6">
                            <label for="payment_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Catatan (Opsional)
                            </label>
                            <textarea id="payment_note" name="payment_note" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Contoh: Transfer dari Bank Mandiri...">{{ old('payment_note') }}</textarea>
                        </div>

                        <!-- Tombol Upload & Kembali -->
                        <div class="flex gap-3">
                            <a href="{{ route('mahasiswa.registrations.show', $registration) }}" 
                               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                Kembali
                            </a>
                            <a href="{{ route('mahasiswa.registrations.index') }}" 
                               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                Daftar Ujian
                            </a>
                            <button type="button" id="upload-btn" onclick="submitUpload()"
                                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition">
                                Upload Bukti Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showPreview(input) {
            var file = input.files[0];
            if (!file) return;

            var validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!validTypes.includes(file.type)) {
                alert('Format file harus JPG, PNG, atau PDF!');
                input.value = '';
                return;
            }

            var maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Ukuran file maksimal 5MB!');
                input.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                var previewSection = document.getElementById('preview-section');
                var previewImage = document.getElementById('preview-image');
                var previewFilename = document.getElementById('preview-filename');
                var previewFilesize = document.getElementById('preview-filesize');

                if (file.type === 'application/pdf') {
                    previewImage.alt = 'PDF File';
                    previewImage.src = '{{ asset("images/pdf-icon.png") }}';
                } else {
                    previewImage.src = e.target.result;
                }

                previewFilename.textContent = file.name;
                previewFilesize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                previewSection.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        function removeFile() {
            var input = document.getElementById('payment_proof');
            var previewSection = document.getElementById('preview-section');
            
            input.value = '';
            previewSection.classList.add('hidden');
        }

        function submitUpload() {
            var fileInput = document.getElementById('payment_proof');
            var form = document.getElementById('upload-form');
            var btn = document.getElementById('upload-btn');
            
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Silakan pilih file terlebih dahulu!');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = 'Mengupload...';
            
            var progressSection = document.getElementById('upload-progress');
            var progressBar = document.getElementById('progress-bar');
            var progressPercent = document.getElementById('progress-percent');
            var progressText = document.getElementById('progress-text');
            
            progressSection.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressBar.classList.remove('bg-green-600', 'bg-red-600');
            progressBar.classList.add('bg-indigo-600');
            progressPercent.textContent = '0%';
            progressText.textContent = 'Mengupload...';

            var formData = new FormData(form);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("mahasiswa.registrations.payment.store", $registration) }}', true);
            
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var percentComplete = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percentComplete + '%';
                    progressPercent.textContent = percentComplete + '%';
                    progressText.textContent = percentComplete < 100 ? 'Mengupload...' : 'Menyimpan...';
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success || xhr.responseURL.includes('payment')) {
                        progressText.textContent = 'Berhasil!';
                        progressBar.classList.remove('bg-indigo-600');
                        progressBar.classList.add('bg-green-600');
                        progressBar.style.width = '100%';
                        
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        progressText.textContent = 'Gagal!';
                        progressBar.classList.remove('bg-indigo-600');
                        progressBar.classList.add('bg-red-600');
                        btn.disabled = false;
                        btn.innerHTML = 'Upload Bukti Pembayaran';
                        alert('Upload gagal. Silakan coba lagi.');
                    }
                } else {
                    progressText.textContent = 'Gagal!';
                    progressBar.classList.remove('bg-indigo-600');
                    progressBar.classList.add('bg-red-600');
                    btn.disabled = false;
                    btn.innerHTML = 'Upload Bukti Pembayaran';
                    
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        alert('Error: ' + (errorResponse.message || 'Terjadi kesalahan'));
                    } catch(e) {
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    }
                }
            });

            xhr.addEventListener('error', function() {
                progressText.textContent = 'Error!';
                progressBar.classList.remove('bg-indigo-600');
                progressBar.classList.add('bg-red-600');
                btn.disabled = false;
                btn.innerHTML = 'Upload Bukti Pembayaran';
                alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
            });

            xhr.send(formData);
        }
    </script>
</x-app-layout>
