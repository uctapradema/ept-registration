<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>EPT - English Proficiency Test | Universitas Ngudi Waluyo</title>
    <meta name="description" content="English Proficiency Test - Universitas Ngudi Waluyo" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/png" href="{{ asset('saasintro/assets/images/logo/logo_ept1.png') }}" />

    <link rel="stylesheet" href="{{ asset('saasintro/assets/css/bootstrap-5.0.0-beta2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('saasintro/assets/css/LineIcons.2.0.css') }}" />
    <link rel="stylesheet" href="{{ asset('saasintro/assets/css/tiny-slider.css') }}" />
    <link rel="stylesheet" href="{{ asset('saasintro/assets/css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('saasintro/assets/css/main.css') }}" />
</head>

<body>
    <!-- preloader start -->
    <div class="preloader">
        <div class="loader">
            <div class="spinner">
                <div class="spinner-container">
                    <div class="spinner-rotator">
                        <div class="spinner-left">
                            <div class="spinner-circle"></div>
                        </div>
                        <div class="spinner-right">
                            <div class="spinner-circle"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- preloader end -->

    <!-- ========================= header start ========================= -->
    <header class="header">
        <div class="navbar-area">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <nav class="navbar navbar-expand-lg">
                            <a class="navbar-brand" href="{{ url('/') }}">
                                <img src="{{ asset('saasintro/assets/images/logo/logo_ept1.png') }}" alt="Logo" style="max-width: 280px;" />
                            </a>
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                            </button>

                            <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                                <div class="ms-auto">
                                    <ul id="nav" class="navbar-nav ms-auto">
                                        <li class="nav-item">
                                            <a class="page-scroll active" href="#home">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="page-scroll" href="#about">About</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="page-scroll" href="#features">Features</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="page-scroll" href="#pricing">Schedule</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- navbar collapse -->
                            <div class="header-btn">
                                @auth
                                    <a href="{{ route('mahasiswa.dashboard') }}" class="main-btn btn-hover">Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="main-btn btn-hover border-btn me-2">Sign in</a>
                                    <a href="{{ route('register') }}" class="main-btn btn-hover">Get Started</a>
                                @endauth
                            </div>
                        </nav>
                        <!-- navbar -->
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- ========================= header end ========================= -->

    <!-- ========================= hero-section start ========================= -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-6 col-lg-6 col-md-10">
                    <div class="hero-content">
                        <h1>Uji Kemampuan Bahasa Inggris Anda</h1>
                        <p>Dapatkan sertifikat bahasa Inggris resmi dari Universitas Ngudi Waluyo. Divalidasi dengan standar internasional dan diakui dunia kerja.</p>

                        <a href="{{ route('register') }}" class="main-btn btn-hover">Daftar Sekarang</a>
                        <a href="#features" class="main-btn btn-hover border-btn ms-2">Lihat Selengkapnya</a>
                    </div>
                </div>
                <div class="col-xxl-6 col-xl-6 col-lg-6">
                    <div class="hero-image text-center text-lg-end">
                        <img src="https://images.unsplash.com/photo-1543269865-0a740d43b90c?q=80&w=800&auto=format&fit=crop" alt="Students">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ========================= hero-section end ========================= -->

    <!-- ========================= about-section start ========================= -->
    <section id="about" class="about-section">

        <div class="container">
            <div class="row">
                <div class="col-lg-6 order-last order-lg-first">
                    <div class="about-image">
                        <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=800&auto=format&fit=crop" alt="About EPT">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-content-wrapper">
                        <div class="section-title">
                            <h2 class="mb-20">Apa itu EPT?</h2>
                            <p class="mb-30">English Proficiency Test (EPT) adalah ujian kemampuan bahasa Inggris yang diselenggarakan oleh Universitas Ngudi Waluyo. Sertifikat yang dihasilkan diakui secara nasional dan internasional.</p>
                            <p class="mb-30">EPT digunakan sebagai syarat kelulusan, wisuda, dan persyaratan kerja di berbagai institusi. Dengan mengikuti EPT, Anda akan mendapatkan sertifikat resmi yang berlaku selama 2 tahun.</p>
                            <a href="{{ route('register') }}" class="main-btn btn-hover border-btn">Daftar Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ========================= about-section end ========================= -->

    <!-- ========================= feature-section start ========================= -->
    <section id="features" class="feature-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-md-10">
                    <div class="section-title mb-60">
                        <h2 class="mb-20">Mengapa Memilih EPT?</h2>
                        <p>Layanan uji kemampuan bahasa Inggris dengan standar internasional untuk mendukung karir Anda.</p>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="single-feature">
                                <div class="feature-icon">
                                    <i class="lni lni-checkmark-circle"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Sertifikat Resmi</h4>
                                    <p>Dapatkan sertifikat bahasa Inggris yang diakui secara resmi oleh institusi pendidikan dan perusahaan.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="single-feature">
                                <div class="feature-icon">
                                    <i class="lni lni-book"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Standar Internasional</h4>
                                    <p>Format tes disesuaikan dengan standar TOEFL dan IELTS dengan sistem penilaian akurat.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="single-feature">
                                <div class="feature-icon">
                                    <i class="lni lni-timer"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Hasil Cepat</h4>
                                    <p>Dapatkan hasil tes dalam waktu 3-5 hari kerja. Sertifikat dapat langsung digunakan.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="single-feature">
                                <div class="feature-icon">
                                    <i class="lni lni-briefcase"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Siap Kerja</h4>
                                    <p>Sertifikat EPT membantu Anda bersaing di dunia kerja dengan kemampuan bahasa Inggris terstandar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ========================= feature-section end ========================= -->

    <!-- ========================= pricing-section start ========================= -->
    <section id="pricing" class="feature-section pt-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title text-center mb-60">
                        <h2 class="mb-20">Jadwal Ujian</h2>
                        <p>Pilih jadwal yang tersedia dan daftarkan diri Anda</p>
                    </div>
                </div>
            </div>

            @if($examSchedules->count() > 0)
            <div class="row justify-content-center">
                @foreach($examSchedules as $schedule)
                <div class="col-lg-4 col-md-8 mb-4">
                    <div class="single-feature">
                        <div class="feature-icon">
                            <i class="lni lni-calendar"></i>
                        </div>
                        <div class="feature-content">
                            <h4>{{ $schedule->title }}</h4>
                            <p class="mb-3">{{ $schedule->exam_date->format('d F Y') }}</p>
                            <h3 class="mb-3">Rp {{ number_format($schedule->price, 0, ',', '.') }}</h3>
                            <ul class="text-start">
                                <li class="mb-2"><i class="lni lni-clock me-2"></i>{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }} WIB</li>
                                <li class="mb-2"><i class="lni lni-users me-2"></i>Tersedia: {{ $schedule->availableQuota() }} / {{ $schedule->quota }}</li>
                                <li class="mb-2"><i class="lni lni-flag me-2"></i>Deadline: {{ $schedule->registration_deadline->format('d F Y') }}</li>
                            </ul>
                            @if($schedule->isAvailable())
                                @auth
                                    <a href="{{ route('mahasiswa.registrations.create', $schedule) }}" class="main-btn btn-hover mt-4 d-block text-center">Daftar Sekarang</a>
                                @else
                                    <a href="{{ route('register') }}" class="main-btn btn-hover mt-4 d-block text-center">Daftar Sekarang</a>
                                @endauth
                            @else
                                <button class="main-btn btn-hover mt-4 d-block text-center" disabled>Pendaftaran Ditutup</button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="row">
                <div class="col-lg-12 text-center">
                    <p class="text-muted">Belum ada jadwal ujian yang tersedia. Silakan hubungi administrator.</p>
                </div>
            </div>
            @endif
        </div>
    </section>
    <!-- ========================= pricing-section end ========================= -->

    <!-- ========================= cta-section start ========================= -->
    <section id="cta" class="cta-section pt-130 pb-100">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-10">
                    <div class="cta-content-wrapper">
                        <div class="section-title">
                            <h2 class="mb-20">Siapkan Masa Depan Anda!</h2>
                            <p class="mb-30">Daftar sekarang dan uji kemampuan bahasa Inggris Anda dengan standar internasional. Tingkatkan peluang karir Anda dengan sertifikat EPT dari Universitas Ngudi Waluyo.</p>
                            <a href="{{ route('register') }}" class="main-btn btn-hover border-btn">Daftar Sekarang</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="cta-image text-lg-end">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=800&auto=format&fit=crop" alt="CTA">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ========================= cta-section end ========================= -->

    <!-- ========================= footer start ========================= -->
    <footer class="footer pt-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-10">
                    <div class="footer-widget">
                        <div class="logo">
                            <a href="{{ url('/') }}"> 
                                <img src="{{ asset('saasintro/assets/images/logo/logo_ept1.png') }}" alt="logo" style="max-width: 150px;">
                            </a>
                        </div>
                        <p class="desc">English Proficiency Test Universitas Ngudi Waluyo. Membangun generasi muda yang kompeten dalam bahasa Inggris.</p>
                        <ul class="social-links">
                            <li><a href="#0"><i class="lni lni-facebook"></i></a></li>
                            <li><a href="#0"><i class="lni lni-linkedin"></i></a></li>
                            <li><a href="#0"><i class="lni lni-instagram"></i></a></li>
                            <li><a href="#0"><i class="lni lni-twitter"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="footer-widget">
                        <h3>Quick Links</h3>
                        <ul class="links">
                            <li><a href="#home">Home</a></li>
                            <li><a href="#about">About</a></li>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#pricing">Schedule</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h3>Contact</h3>
                        <ul class="links">
                            <li>ept@unw.ac.id</li>
                            <li>Universitas Ngudi Waluyo</li>
                            <li>Jl. Diponegoro No.186, Ngablak, Gedanganak, Kec. Ungaran Timur, Kabupaten Semarang, Provinsi Jawa Tengah</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright text-center">
                <p class="mb-0">&copy; {{ date('Y') }} EPT UNW. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <!-- ========================= footer end ========================= -->

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top btn-hover">
        <i class="lni lni-chevron-up"></i>
    </a>

    <!-- ========================= JS here ========================= -->
    <script src="{{ asset('saasintro/assets/js/bootstrap-5.0.0-beta2.min.js') }}"></script>
    <script src="{{ asset('saasintro/assets/js/tiny-slider.js') }}"></script>
    <script src="{{ asset('saasintro/assets/js/wow.min.js') }}"></script>
    <script src="{{ asset('saasintro/assets/js/polyfill.js') }}"></script>
    <script src="{{ asset('saasintro/assets/js/main.js') }}"></script>
</body>

</html>
