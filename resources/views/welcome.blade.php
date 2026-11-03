<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7f5">
    <title>{{ config('app.name', 'Ruang') }} — fokus pada hal penting</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app" data-api-base="{{ url('/api') }}" data-use-mock="{{ config('app.frontend_mock', true) ? 'true' : 'false' }}">
        <main class="auth-shell" id="auth-screen">
            <section class="auth-story">
                <a class="brand brand-light" href="#" aria-label="Ruang beranda">
                    <span class="brand-mark">r.</span><span>ruang<span class="brand-dot">.</span></span>
                </a>
                <div class="story-copy">
                    <span class="eyebrow"><span class="eyebrow-dot"></span> SEDIKIT DEMI SEDIKIT</span>
                    <h1>Ruang untuk<br>menuntaskan <em>hal penting.</em></h1>
                    <p>Susun yang perlu dikerjakan. Sisakan energi untuk hal yang benar-benar berarti.</p>
                </div>
                <div class="story-note"><span class="note-spark">✳</span><span>“Fokus bukan soal melakukan lebih banyak,<br>tapi memilih yang paling berarti.”</span></div>
                <div class="story-orbit orbit-one"></div><div class="story-orbit orbit-two"></div>
            </section>
            <section class="auth-panel">
                <div class="auth-top"><span class="auth-top-label">RUANG KERJAMU</span><span class="secure-label"><span class="secure-dot"></span> Ruang pribadimu</span></div>
                <div class="auth-card">
                    <div class="auth-heading">
                        <div class="welcome-icon" aria-hidden="true">✳</div>
                        <p class="eyebrow muted-eyebrow">MARI MULAI</p>
                        <h2 id="auth-title">Senang melihatmu<br>kembali.</h2>
                        <p class="auth-subtitle" id="auth-subtitle">Masuk dan lanjutkan dari tempatmu berhenti.</p>
                    </div>
                    <div class="auth-tabs" role="tablist" aria-label="Pilih akses">
                        <button class="auth-tab is-active" type="button" data-auth-tab="login" role="tab" aria-selected="true">Masuk</button>
                        <button class="auth-tab" type="button" data-auth-tab="register" role="tab" aria-selected="false">Buat akun</button>
                    </div>
                    <form id="auth-form" novalidate>
                        <div class="field" id="name-field" hidden>
                            <label for="auth-name">Nama lengkap</label>
                            <input id="auth-name" name="name" type="text" placeholder="Contoh: Nara Putri" autocomplete="name">
                            <small class="field-error" data-error="name"></small>
                        </div>
                        <div class="field">
                            <label for="auth-email">Alamat email</label>
                            <input id="auth-email" name="email" type="email" placeholder="nama@email.com" autocomplete="email" required>
                            <small class="field-error" data-error="email"></small>
                        </div>
                        <div class="field">
                            <div class="label-row"><label for="auth-password">Kata sandi</label><a href="#" class="text-link" id="forgot-link">Lupa kata sandi?</a></div>
                            <div class="password-wrap"><input id="auth-password" name="password" type="password" placeholder="Minimal 8 karakter" autocomplete="current-password" required><button class="password-toggle" type="button" aria-label="Tampilkan kata sandi" data-password-toggle>◎</button></div>
                            <small class="field-error" data-error="password"></small>
                        </div>
                        <div class="field" id="password-confirm-field" hidden>
                            <label for="auth-password-confirm">Ulangi kata sandi</label>
                            <input id="auth-password-confirm" name="password_confirmation" type="password" placeholder="Ketik ulang kata sandi" autocomplete="new-password">
                            <small class="field-error" data-error="password_confirmation"></small>
                        </div>
                        <div class="form-alert" id="auth-alert" role="alert" hidden></div>
                        <button class="button button-primary auth-submit" id="auth-submit" type="submit"><span>Masuk ke ruangku</span><span class="button-arrow" aria-hidden="true">↗</span></button>
                    </form>
                    <p class="demo-hint"><span class="hint-icon">✦</span><span>Ingin mencoba dulu? Gunakan <strong>demo@ruang.id</strong><br>dengan kata sandi <strong>ruang1234</strong>.</span></p>
                    <p class="auth-footnote">Dengan melanjutkan, kamu menyetujui <a href="#">Ketentuan</a> dan <a href="#">Privasi</a>.</p>
                </div>
                <div class="auth-footer"><span>© {{ date('Y') }} Ruang</span><span>Dibuat untuk hari yang lebih ringan <span class="footer-heart">♥</span></span></div>
            </section>
        </main>

        <main class="workspace" id="workspace" hidden>
            <aside class="sidebar" id="sidebar">
                <a class="brand" href="#dashboard" aria-label="Ruang beranda"><span class="brand-mark">r.</span><span>ruang<span class="brand-dot">.</span></span></a>
                <div class="workspace-label">WORKSPACE</div>
                <button class="workspace-picker" type="button"><span class="workspace-avatar">R</span><span class="workspace-name">Ruang pribadiku<small>Personal</small></span><span class="picker-chevron">⌄</span></button>
                <div class="nav-label">MENU</div>
                <nav class="side-nav" aria-label="Menu utama">
                    <a class="nav-item is-current" href="#dashboard"><span class="nav-icon">▦</span> Ringkasan</a>
                    <a class="nav-item" href="#tasks" data-filter-link="all"><span class="nav-icon">☷</span> Semua tugas <span class="nav-count" id="nav-total">0</span></a>
                    <a class="nav-item" href="#tasks" data-filter-link="today"><span class="nav-icon">◷</span> Hari ini</a>
                    <a class="nav-item" href="#tasks" data-filter-link="done"><span class="nav-icon">✓</span> Selesai</a>
                </nav>
                <div class="sidebar-bottom">
                    <div class="sidebar-tip"><span class="tip-spark">✳</span><p><strong>Istirahat juga bagian dari progres.</strong><br>Tarik napas sebentar sebelum lanjut.</p><span class="tip-leaf">❋</span></div>
                    <div class="profile-row"><div class="profile-avatar" id="profile-avatar">N</div><div class="profile-copy"><strong id="profile-name">Nara Putri</strong><small id="profile-email">nara@email.com</small></div><button class="icon-button logout-button" type="button" id="logout-button" aria-label="Keluar">↗</button></div>
                </div>
            </aside>
            <section class="main-panel">
                <header class="topbar">
                    <button class="mobile-menu icon-button" type="button" id="mobile-menu" aria-label="Buka menu">☰</button>
                    <div class="breadcrumb"><span>Workspace</span><span class="breadcrumb-slash">/</span><strong>Ringkasan</strong></div>
                    <div class="topbar-actions"><span class="today-label" id="today-label"></span><button class="icon-button notification-button" type="button" aria-label="Notifikasi">♧<i></i></button><div class="top-avatar" id="top-avatar">N</div></div>
                </header>
                <div class="dashboard-content">
                    <section class="greeting-row">
                        <div><p class="eyebrow muted-eyebrow"><span class="greeting-sun">☼</span> HARI INI, RUANG KECILMU</p><h1 id="greeting-title">Selamat pagi, Nara <span>✳</span></h1><p class="greeting-copy">Pelan-pelan saja. Satu langkah kecil tetap berarti.</p></div>
                        <button class="button button-primary add-task-top" type="button" data-open-task><span>＋</span> Tambah tugas</button>
                    </section>
                    <section class="summary-grid" aria-label="Ringkasan tugas">
                        <article class="summary-card summary-total"><div class="summary-top"><span>Total tugas</span><span class="summary-icon">☷</span></div><div class="summary-value" id="stat-total">0</div><div class="summary-caption"><span class="stat-dot dot-ink"></span><span>semua yang perlu dilakukan</span></div><div class="mini-bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></article>
                        <article class="summary-card summary-open"><div class="summary-top"><span>Belum selesai</span><span class="summary-icon icon-yellow">◷</span></div><div class="summary-value" id="stat-open">0</div><div class="summary-caption"><span class="stat-dot dot-yellow"></span><span>masih dalam proses</span></div><div class="mini-bars yellow-bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></article>
                        <article class="summary-card summary-done"><div class="summary-top"><span>Selesai</span><span class="summary-icon icon-green">✓</span></div><div class="summary-value" id="stat-done">0</div><div class="summary-caption"><span class="stat-dot dot-green"></span><span>langkah yang sudah tercapai</span></div><div class="mini-bars green-bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></article>
                    </section>
                    <section class="task-section" id="tasks">
                        <div class="task-heading"><div><p class="eyebrow muted-eyebrow">TETAP PADA YANG PENTING</p><h2>Daftar tugas <span class="task-total-pill" id="task-count">0</span></h2></div><div class="task-tools"><label class="search-box"><span aria-hidden="true">⌕</span><input id="task-search" type="search" placeholder="Cari tugas..." aria-label="Cari tugas"></label><button class="filter-button" type="button" id="filter-button"><span>☷</span> <span id="filter-label">Semua</span> <span class="filter-chevron">⌄</span></button><div class="filter-menu" id="filter-menu" hidden><button type="button" data-filter="all">Semua tugas</button><button type="button" data-filter="todo">Belum selesai</button><button type="button" data-filter="done">Selesai</button></div></div></div>
                        <div class="task-list" id="task-list" aria-live="polite"></div>
                        <div class="task-footer"><span id="task-footer-count">Menampilkan 0 tugas</span><button type="button" class="clear-search" id="clear-search" hidden>Hapus pencarian</button></div>
                    </section>
                    <footer class="dashboard-footer"><span>Ruang kecilmu, langkah besarmu <span>✳</span></span><span id="footer-date"></span></footer>
                </div>
            </section>
        </main>

        <dialog class="task-dialog" id="task-dialog" aria-labelledby="task-dialog-title">
            <form id="task-form" novalidate>
                <div class="dialog-heading"><div><p class="eyebrow muted-eyebrow" id="dialog-eyebrow">LANGKAH BERIKUTNYA</p><h2 id="task-dialog-title">Tambahkan tugas</h2><p id="task-dialog-description">Tuliskan satu hal yang ingin kamu selesaikan.</p></div><button class="icon-button dialog-close" type="button" id="close-dialog" aria-label="Tutup">×</button></div>
                <input type="hidden" name="id" id="task-id">
                <div class="field"><label for="task-title">Nama tugas</label><input id="task-title" name="title" type="text" placeholder="Contoh: Siapkan presentasi" maxlength="255" required><small class="field-error" data-task-error="title"></small></div>
                <div class="field"><label for="task-deadline">Batas waktu</label><input id="task-deadline" name="deadline" type="date" required><small class="field-error" data-task-error="deadline"></small></div>
                <div class="form-alert" id="task-alert" role="alert" hidden></div>
                <div class="dialog-actions"><button class="button button-quiet" type="button" id="cancel-dialog">Batal</button><button class="button button-primary" id="save-task" type="submit">Simpan tugas <span>↗</span></button></div>
            </form>
        </dialog>
        <div class="toast-region" id="toast-region" aria-live="polite" aria-atomic="true"></div>
        <noscript>Aktifkan JavaScript untuk menggunakan Ruang.</noscript>
    </div>
</body>
</html>
