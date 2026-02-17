<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Pendaftaran') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-2 sm:px-6 lg:px-8">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm sm:text-base">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('warning'))
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg text-sm sm:text-base">
                    {{ session('warning') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm sm:text-base">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Registration Number & Status -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 pb-4 sm:pb-6 border-b border-gray-200 dark:border-gray-700 gap-3">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Nomor Pendaftaran</p>
                            <p class="font-mono text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $registration->registration_number }}
                            </p>
                        </div>
                        <div class="self-start sm:self-auto">
                            <span class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-medium
                                @if($registration->status === 'pending_payment')
                                    bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($registration->status === 'awaiting_verification')
                                    bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @elseif($registration->status === 'verified')
                                    bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($registration->status === 'rejected')
                                    bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @elseif($registration->status === 'cancelled')
                                    bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                @elseif($registration->status === 'expired')
                                    bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                @endif
                            ">
                                {{ $statusLabels[$registration->status] ?? $registration->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Countdown Timer for Pending Payment -->
                    @if($registration->status === 'pending_payment' && !$registration->isExpired())
                        @php
                            $hoursLeft = now()->diffInHours($registration->expires_at, false);
                            $isUrgent = $hoursLeft <= 4;
                        @endphp
                        <div class="mb-4 sm:mb-6 p-3 sm:p-4 {{ $isUrgent ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200' }} border rounded-lg">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 {{ $isUrgent ? 'text-red-500' : 'text-yellow-500' }} mr-2 sm:mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-xs sm:text-sm font-medium {{ $isUrgent ? 'text-red-800' : 'text-yellow-800' }} dark:text-yellow-200">
                                        Sisa Waktu Pembayaran
                                    </p>
                                    <p class="text-base sm:text-lg font-bold {{ $isUrgent ? 'text-red-600' : 'text-yellow-700' }}" id="countdown">
                                        {{ now()->diff($registration->expires_at)->format('%h jam %i menit') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($registration->isExpired() && $registration->status === 'pending_payment')
                        <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-red-500 mr-2 sm:mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm sm:text-base text-red-800 font-medium">
                                    Pendaftaran telah kadaluarsa. Silakan daftar untuk jadwal lainnya.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Exam Schedule Details -->
                    <div class="mb-4 sm:mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                            Informasi Ujian
                        </h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Nama Ujian</p>
                                    <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                        {{ $registration->examSchedule->title }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Tanggal Ujian</p>
                                    <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                        {{ $registration->examSchedule->exam_date->format('l, d F Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Waktu</p>
                                    <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                        {{ $registration->examSchedule->start_time->format('H:i') }} - {{ $registration->examSchedule->end_time->format('H:i') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Biaya</p>
                                    <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                        Rp {{ number_format($registration->examSchedule->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    @if($registration->status === 'pending_payment' && !$registration->isExpired())
                        <!-- Upload Payment Form (Integrated) -->
                        <div class="mb-4 sm:mb-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                                Upload Bukti Pembayaran
                            </h3>
                            
                            <!-- Petunjuk Pembayaran -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 sm:p-4 mb-4">
                                <p class="text-xs sm:text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Petunjuk Pembayaran:</p>
                                <ul class="text-xs sm:text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                    <li>Transfer: <strong>{{ $registration->examSchedule->bank_name ?? 'Bank BCA' }} {{ $registration->examSchedule->bank_account ?? '123-456-7890' }}</strong> a.n. <strong>{{ $registration->examSchedule->account_holder ?? 'EPT' }}</strong></li>
                                    <li>Nominal: <strong>Rp {{ number_format($registration->examSchedule->price, 0, ',', '.') }}</strong></li>
                                    <li>Keterangan: <strong>{{ $registration->registration_number }}</strong></li>
                                </ul>
                            </div>

                            <!-- Upload Form -->
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
                                <div id="preview-section" class="hidden mb-4">
                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex items-center justify-between mb-3">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Preview:</p>
                                            <button type="button" onclick="removeFile()" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                                        </div>
                                        <img id="preview-image" class="max-w-full h-auto max-h-48 mx-auto rounded-lg border border-gray-300 dark:border-gray-600">
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
                                <div id="upload-progress" class="hidden mb-4">
                                    <div class="relative pt-1">
                                        <div class="flex mb-2 items-center justify-between">
                                            <div>
                                                <span id="progress-text" class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                                                    Mengupload...
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <span id="progress-percent" class="text-xs font-semibold inline-block text-indigo-600">0%</span>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden h-4 text-xs flex rounded bg-indigo-200">
                                            <div id="progress-bar" style="width:0%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-600 transition-all duration-300"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Catatan -->
                                <div class="mb-4">
                                    <label for="payment_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Catatan (Opsional)
                                    </label>
                                    <textarea id="payment_note" name="payment_note" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Contoh: Transfer dari Bank Mandiri...">{{ old('payment_note') }}</textarea>
                                </div>

                                <!-- Tombol Upload -->
                                <button type="button" id="upload-btn" onclick="submitUpload()"
                                        class="w-full sm:w-auto px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition">
                                    Upload Bukti Pembayaran
                                </button>
                            </form>
                        </div>
                    @elseif($registration->payment_uploaded_at)
                        <!-- Payment Info (sudah upload) -->
                        <div class="mb-4 sm:mb-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                                Informasi Pembayaran
                            </h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <div>
                                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Tanggal Upload</p>
                                        <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                            {{ $registration->payment_uploaded_at->format('d F Y, H:i') }}
                                        </p>
                                    </div>
                                    @if($registration->payment_verified_at)
                                        <div>
                                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Tanggal Verifikasi</p>
                                            <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                                {{ $registration->payment_verified_at->format('d F Y, H:i') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Diverifikasi Oleh</p>
                                            <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                                {{ $registration->verifiedBy->name ?? '-' }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($registration->payment_proof)
                                    @php
                                        $filePath = asset('storage/' . $registration->payment_proof);
                                        $extension = strtolower(pathinfo($registration->payment_proof, PATHINFO_EXTENSION));
                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
                                        $isImage = in_array($extension, $imageExtensions);
                                    @endphp
                                    
                                    <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-600">
                                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-2">Bukti Pembayaran</p>
                                        
                                        @if($isImage)
                                            <img src="{{ $filePath }}" alt="Preview Bukti Pembayaran" 
                                                 class="max-w-full h-auto rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer" 
                                                 style="max-height: 200px;" 
                                                 onclick="openPreviewModal('{{ $filePath }}')">
                                            <p class="text-xs text-gray-500 mt-1">Klik gambar untuk memperbesar</p>
                                        @else
                                            <a href="{{ $filePath }}" target="_blank" 
                                               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                Buka File
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Rejection/Cancellation Reason -->
                    @if($registration->rejection_reason && in_array($registration->status, ['rejected', 'cancelled']))
                        <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-xs sm:text-sm font-medium text-red-800 dark:text-red-200 mb-1">
                                {{ $registration->status === 'rejected' ? 'Alasan Penolakan' : 'Alasan Pembatalan' }}
                            </p>
                            <p class="text-sm sm:text-base text-red-700 dark:text-red-300">{{ $registration->rejection_reason }}</p>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <a href="{{ route('mahasiswa.registrations.index') }}" 
                           class="inline-flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition w-full sm:w-auto">
                            Daftar Ujian
                        </a>

                        @if($registration->status === 'pending_payment' && !$registration->isExpired())
                            <button type="button" 
                                    onclick="document.getElementById('cancel-modal').classList.remove('hidden')"
                                    class="inline-flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-white uppercase tracking-widest hover:bg-red-700 transition w-full sm:w-auto">
                                Batalkan
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    @if($registration->status === 'pending_payment' && !$registration->isExpired())
        <div id="cancel-modal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 sm:p-6 max-w-md w-full">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                    Batalkan Pendaftaran
                </h3>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mb-4">
                    Apakah Anda yakin ingin membatalkan pendaftaran ini? Tindakan ini tidak dapat dibatalkan.
                </p>
                
                <form method="POST" action="{{ route('mahasiswa.registrations.cancel', $registration) }}">
                    @csrf
                    @method('DELETE')
                    
                    <div class="mb-4">
                        <label for="cancel_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Alasan Pembatalan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="cancel_reason" name="cancel_reason" rows="3" required minlength="10"
                                  class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm sm:text-base"
                                  placeholder="Minimal 10 karakter..."></textarea>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                        <button type="button" 
                                onclick="document.getElementById('cancel-modal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition w-full sm:w-auto">
                            Tutup
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 transition w-full sm:w-auto">
                            Ya, Batalkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Preview Gambar -->
    <div id="image-preview-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" onclick="closePreviewModal(event)">
        <div class="relative max-w-4xl w-full">
            <button onclick="closePreviewModal()" class="absolute top-2 right-2 text-white bg-gray-800 hover:bg-gray-700 rounded-full p-2">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img id="preview-image" src="" alt="Preview" class="max-w-full max-h-screen mx-auto rounded-lg">
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
                    progressText.textContent = 'Berhasil!';
                    progressBar.classList.remove('bg-indigo-600');
                    progressBar.classList.add('bg-green-600');
                    progressBar.style.width = '100%';
                    
                    window.location.reload();
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

        function openPreviewModal(imageSrc) {
            document.getElementById('preview-image').src = imageSrc;
            document.getElementById('image-preview-modal').classList.remove('hidden');
        }

        function closePreviewModal(event) {
            if (!event || event.target === document.getElementById('image-preview-modal')) {
                document.getElementById('image-preview-modal').classList.add('hidden');
            }
        }
    </script>
</x-app-layout>