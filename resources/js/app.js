const root = document.querySelector('#app');
const apiBase = root.dataset.apiBase.replace(/\/$/, '');
const useMock = root.dataset.useMock === 'true';
const storageKeys = { token: 'ruang.token', user: 'ruang.user', tasks: 'ruang.tasks', accounts: 'ruang.accounts' };
const demoEmail = 'demo@ruang.id';
const demoPassword = 'ruang1234';
const demoUser = { id: 'demo', name: 'Nara Putri', email: demoEmail };
const demoTasks = [
    { id: 'demo-1', title: 'Rapikan catatan untuk rapat mingguan', deadline: dateOffset(0), status: 'todo' },
    { id: 'demo-2', title: 'Kirim revisi proposal ke tim desain', deadline: dateOffset(1), status: 'todo' },
    { id: 'demo-3', title: 'Pilih tiga prioritas utama minggu ini', deadline: dateOffset(2), status: 'done' },
    { id: 'demo-4', title: 'Balas email dari tim kolaborasi', deadline: dateOffset(-1), status: 'done' },
];

let tasks = [];
let activeFilter = 'all';
let searchTerm = '';
let authMode = 'login';
let toastTimer;

function dateOffset(days) {
    const date = new Date();
    date.setDate(date.getDate() + days);
    return date.toISOString().slice(0, 10);
}

function readStorage(key, fallback = null) {
    try {
        const value = localStorage.getItem(key);
        return value ? JSON.parse(value) : fallback;
    } catch {
        return fallback;
    }
}

function writeStorage(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
}

function currentToken() {
    return localStorage.getItem(storageKeys.token);
}

function currentUser() {
    return readStorage(storageKeys.user);
}

function taskStorageKey() {
    const user = currentUser();
    return user?.id ? `${storageKeys.tasks}.${user.id}` : storageKeys.tasks;
}

function apiError(message, status = 422, errors = {}) {
    const error = new Error(message);
    error.status = status;
    error.errors = errors;
    return error;
}

function unwrap(payload) {
    return payload && typeof payload === 'object' && 'data' in payload ? payload.data : payload;
}

function normalizeTask(task) {
    return { id: task.id, title: task.title, deadline: String(task.deadline ?? '').slice(0, 10), status: task.status === 'done' ? 'done' : 'todo' };
}

/**
 * One switch controls the prototype data. Set FRONTEND_MOCK=false in Laravel's
 * config/app.php (or update this view's data-use-mock value) when API routes exist.
 * Every real API request carries the current Laravel Sanctum bearer token.
 */
const api = {
    async request(path, { method = 'GET', body, token = currentToken() } = {}) {
        const response = await fetch(`${apiBase}${path}`, {
            method,
            headers: {
                Accept: 'application/json',
                ...(body ? { 'Content-Type': 'application/json' } : {}),
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
            },
            ...(body ? { body: JSON.stringify(body) } : {}),
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const error = apiError(payload.message || 'Permintaan belum berhasil. Coba lagi.', response.status, payload.errors || {});
            throw error;
        }
        return unwrap(payload);
    },

    async login(credentials) {
        if (!useMock) return this.request('/login', { method: 'POST', body: credentials, token: null });
        await delay(450);
        const accounts = readStorage(storageKeys.accounts, []);
        if (credentials.email.toLowerCase() === demoEmail && credentials.password === demoPassword) {
            seedDemoTasks();
            return { user: demoUser, token: 'mock-demo-token' };
        }
        const account = accounts.find((item) => item.email.toLowerCase() === credentials.email.toLowerCase());
        if (!account || account.password !== credentials.password) throw apiError('Email atau kata sandi belum cocok.', 422);
        return { user: { id: account.id, name: account.name, email: account.email }, token: `mock-${account.id}-token` };
    },

    async register(details) {
        if (!useMock) return this.request('/register', { method: 'POST', body: details, token: null });
        await delay(500);
        const accounts = readStorage(storageKeys.accounts, []);
        if (details.email.toLowerCase() === demoEmail || accounts.some((item) => item.email.toLowerCase() === details.email.toLowerCase())) {
            throw apiError('Email ini sudah memiliki akun.', 422, { email: ['Email ini sudah terdaftar.'] });
        }
        const account = { id: `user-${Date.now()}`, name: details.name, email: details.email, password: details.password };
        writeStorage(storageKeys.accounts, [...accounts, account]);
        writeStorage(`${storageKeys.tasks}.${account.id}`, []);
        return { user: { id: account.id, name: account.name, email: account.email }, token: `mock-${account.id}-token` };
    },

    async listTasks() {
        if (!useMock) {
            const result = await this.request('/tasks');
            const list = Array.isArray(result) ? result : (result?.data ?? []);
            return list.map(normalizeTask);
        }
        await delay(360);
        const stored = readStorage(taskStorageKey());
        if (!stored) {
            writeStorage(taskStorageKey(), []);
            return [];
        }
        return stored.map(normalizeTask);
    },

    async createTask(input) {
        if (!useMock) return normalizeTask(await this.request('/tasks', { method: 'POST', body: input }));
        await delay(300);
        const created = { ...input, id: `task-${Date.now()}`, status: 'todo' };
        writeStorage(taskStorageKey(), [created, ...readStorage(taskStorageKey(), [])]);
        return normalizeTask(created);
    },

    async updateTask(id, input) {
        if (!useMock) return normalizeTask(await this.request(`/tasks/${encodeURIComponent(id)}`, { method: 'PUT', body: input }));
        await delay(280);
        const updated = { ...input, id, status: input.status || 'todo' };
        writeStorage(taskStorageKey(), readStorage(taskStorageKey(), []).map((task) => String(task.id) === String(id) ? updated : task));
        return normalizeTask(updated);
    },

    async updateStatus(id, status) {
        if (!useMock) return normalizeTask(await this.request(`/tasks/${encodeURIComponent(id)}/status`, { method: 'PATCH', body: { status } }));
        await delay(220);
        const updated = readStorage(taskStorageKey(), []).map((task) => String(task.id) === String(id) ? { ...task, status } : task).find((task) => String(task.id) === String(id));
        if (!updated) throw apiError('Tugas tidak ditemukan.', 404);
        writeStorage(taskStorageKey(), readStorage(taskStorageKey(), []).map((task) => String(task.id) === String(id) ? updated : task));
        return normalizeTask(updated);
    },

    async deleteTask(id) {
        if (!useMock) return this.request(`/tasks/${encodeURIComponent(id)}`, { method: 'DELETE' });
        await delay(220);
        writeStorage(taskStorageKey(), readStorage(taskStorageKey(), []).filter((task) => String(task.id) !== String(id)));
    },
};

function delay(milliseconds) {
    return new Promise((resolve) => window.setTimeout(resolve, milliseconds));
}

function seedDemoTasks() {
    const key = `${storageKeys.tasks}.${demoUser.id}`;
    if (!localStorage.getItem(key)) writeStorage(key, demoTasks);
}

function saveCurrentTasks() {
    writeStorage(taskStorageKey(), tasks);
}

function setSession(session) {
    if (!session?.token || !session?.user) throw new Error('Respons autentikasi belum menyertakan token dan pengguna.');
    localStorage.setItem(storageKeys.token, session.token);
    writeStorage(storageKeys.user, session.user);
}

function clearSession() {
    localStorage.removeItem(storageKeys.token);
    localStorage.removeItem(storageKeys.user);
    localStorage.removeItem(storageKeys.tasks);
}

const authScreen = document.querySelector('#auth-screen');
const workspace = document.querySelector('#workspace');
const authForm = document.querySelector('#auth-form');
const authAlert = document.querySelector('#auth-alert');
const taskList = document.querySelector('#task-list');
const taskDialog = document.querySelector('#task-dialog');
const taskForm = document.querySelector('#task-form');

function setAuthMode(mode) {
    authMode = mode;
    const registering = mode === 'register';
    document.querySelector('#name-field').hidden = !registering;
    document.querySelector('#password-confirm-field').hidden = !registering;
    document.querySelector('#forgot-link').hidden = registering;
    document.querySelector('#auth-name').required = registering;
    document.querySelector('#auth-password-confirm').required = registering;
    document.querySelector('#auth-password').autocomplete = registering ? 'new-password' : 'current-password';
    document.querySelector('#auth-title').innerHTML = registering ? 'Buat ruang yang<br>jadi milikmu.' : 'Senang melihatmu<br>kembali.';
    document.querySelector('#auth-subtitle').textContent = registering ? 'Mulai dengan langkah kecil. Kamu bisa mengubahnya nanti.' : 'Masuk dan lanjutkan dari tempatmu berhenti.';
    document.querySelector('#auth-submit span:first-child').textContent = registering ? 'Buat akun' : 'Masuk ke ruangku';
    document.querySelectorAll('[data-auth-tab]').forEach((tab) => {
        const active = tab.dataset.authTab === mode;
        tab.classList.toggle('is-active', active);
        tab.setAttribute('aria-selected', String(active));
    });
    authAlert.hidden = true;
}

function showAuthError(error) {
    clearFieldErrors('#auth-form [data-error]');
    Object.entries(error.errors || {}).forEach(([field, messages]) => {
        const node = document.querySelector(`[data-error="${field}"]`);
        if (node) node.textContent = Array.isArray(messages) ? messages[0] : messages;
    });
    authAlert.textContent = error.message || 'Terjadi kendala. Silakan coba lagi.';
    authAlert.hidden = false;
}

function clearFieldErrors(selector) {
    document.querySelectorAll(selector).forEach((node) => { node.textContent = ''; });
}

function setButtonBusy(button, busy, label) {
    button.disabled = busy;
    button.dataset.originalLabel ||= button.innerHTML;
    button.innerHTML = busy ? `<span class="spinner" aria-hidden="true"></span><span>${label}</span>` : button.dataset.originalLabel;
}

authForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    authAlert.hidden = true;
    clearFieldErrors('#auth-form [data-error]');
    const form = new FormData(authForm);
    const details = Object.fromEntries(form.entries());
    const errors = {};
    if (authMode === 'register' && !details.name.trim()) errors.name = 'Nama wajib diisi.';
    if (!/^\S+@\S+\.\S+$/.test(details.email.trim())) errors.email = 'Masukkan alamat email yang valid.';
    if (!details.password || details.password.length < (authMode === 'register' ? 8 : 1)) errors.password = authMode === 'register' ? 'Kata sandi minimal 8 karakter.' : 'Kata sandi wajib diisi.';
    if (authMode === 'register' && details.password !== details.password_confirmation) errors.password_confirmation = 'Ulangi kata sandi dengan benar.';
    if (Object.keys(errors).length) return showAuthError({ message: 'Periksa kembali isianmu.', errors });

    const button = document.querySelector('#auth-submit');
    setButtonBusy(button, true, authMode === 'register' ? 'Menyiapkan ruang...' : 'Membuka ruang...');
    try {
        const session = authMode === 'register' ? await api.register(details) : await api.login({ email: details.email, password: details.password });
        setSession(session);
        await enterWorkspace();
        showToast(authMode === 'register' ? 'Ruangmu siap. Selamat datang!' : `Selamat datang kembali, ${session.user.name.split(' ')[0]}.`);
    } catch (error) {
        showAuthError(error);
    } finally {
        setButtonBusy(button, false);
    }
});

document.querySelectorAll('[data-auth-tab]').forEach((tab) => tab.addEventListener('click', () => setAuthMode(tab.dataset.authTab)));
document.querySelector('[data-password-toggle]').addEventListener('click', (event) => {
    const input = document.querySelector('#auth-password');
    input.type = input.type === 'password' ? 'text' : 'password';
    event.currentTarget.setAttribute('aria-label', input.type === 'password' ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
});
document.querySelector('#forgot-link').addEventListener('click', (event) => {
    event.preventDefault();
    showToast('Fitur pemulihan kata sandi akan tersedia setelah API autentikasi aktif.');
});

async function enterWorkspace() {
    authScreen.hidden = true;
    workspace.hidden = false;
    const user = currentUser();
    updateUser(user);
    renderDate();
    renderLoading();
    try {
        tasks = await api.listTasks();
        renderTasks();
    } catch (error) {
        renderError(error);
    }
}

function updateUser(user) {
    if (!user) return;
    const name = user.name || user.email?.split('@')[0] || 'Teman';
    const firstName = name.split(' ')[0];
    const initial = firstName.slice(0, 1).toUpperCase();
    document.querySelector('#profile-name').textContent = name;
    document.querySelector('#profile-email').textContent = user.email || '';
    document.querySelector('#profile-avatar').textContent = initial;
    document.querySelector('#top-avatar').textContent = initial;
    const hour = new Date().getHours();
    const greeting = hour < 11 ? 'pagi' : hour < 15 ? 'siang' : hour < 19 ? 'sore' : 'malam';
    document.querySelector('#greeting-title').innerHTML = `Selamat ${greeting}, ${escapeHtml(firstName)} <span>✳</span>`;
}

function renderDate() {
    const now = new Date();
    const full = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(now);
    const short = new Intl.DateTimeFormat('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }).format(now);
    document.querySelector('#today-label').textContent = short;
    document.querySelector('#footer-date').textContent = full;
}

function renderLoading() {
    taskList.innerHTML = '<div class="loading-state"><span class="spinner" aria-hidden="true"></span> Memuat daftar tugasmu...</div>';
}

function renderError(error) {
    taskList.innerHTML = `<div class="task-empty"><div class="empty-illustration">⌁</div><h3>Belum bisa memuat tugas</h3><p>${escapeHtml(error.message || 'Periksa koneksi lalu coba lagi.')}</p><button class="empty-add" type="button" data-retry>↻ Coba lagi</button></div>`;
    taskList.querySelector('[data-retry]').addEventListener('click', enterWorkspace);
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
}

function formatDeadline(dateString) {
    if (!dateString) return 'Tanpa batas waktu';
    const today = dateOffset(0);
    const tomorrow = dateOffset(1);
    if (dateString === today) return 'Hari ini';
    if (dateString === tomorrow) return 'Besok';
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(`${dateString}T00:00:00`));
}

function visibleTasks() {
    const needle = searchTerm.toLowerCase();
    return tasks.filter((task) => {
        const matchesFilter = activeFilter === 'all' || (activeFilter === 'today' ? task.deadline === dateOffset(0) : task.status === activeFilter);
        return matchesFilter && task.title.toLowerCase().includes(needle);
    });
}

function renderTasks() {
    const doneCount = tasks.filter((task) => task.status === 'done').length;
    const openCount = tasks.length - doneCount;
    const shown = visibleTasks();
    document.querySelector('#stat-total').textContent = tasks.length;
    document.querySelector('#stat-open').textContent = openCount;
    document.querySelector('#stat-done').textContent = doneCount;
    document.querySelector('#task-count').textContent = shown.length;
    document.querySelector('#nav-total').textContent = tasks.length;
    document.querySelector('#task-footer-count').textContent = `Menampilkan ${shown.length} dari ${tasks.length} tugas`;
    document.querySelector('#clear-search').hidden = !searchTerm;

    if (!shown.length) {
        const noTasks = tasks.length === 0;
        const title = searchTerm ? 'Tidak ada tugas yang cocok' : noTasks ? 'Ruangmu masih lapang' : 'Belum ada tugas di sini';
        const description = searchTerm ? 'Coba kata kunci lain, atau hapus pencarian untuk melihat semua tugas.' : noTasks ? 'Tambahkan satu hal kecil yang ingin kamu selesaikan hari ini.' : 'Semua tugas di daftar ini akan muncul di sini.';
        taskList.innerHTML = `<div class="task-empty"><div class="empty-illustration">${noTasks ? '✳' : '☷'}</div><h3>${title}</h3><p>${description}</p>${noTasks ? '<button class="empty-add" type="button" data-open-task>＋ Tambahkan tugas pertama</button>' : ''}</div>`;
        taskList.querySelector('[data-open-task]')?.addEventListener('click', openCreateDialog);
        return;
    }

    taskList.innerHTML = shown.map((task) => {
        const done = task.status === 'done';
        return `<article class="task-row ${done ? 'is-done' : ''}" data-task-id="${escapeHtml(task.id)}">
            <button class="task-checkbox" type="button" aria-label="${done ? 'Tandai belum selesai' : 'Tandai selesai'}: ${escapeHtml(task.title)}" aria-pressed="${done}" data-task-action="toggle">✓</button>
            <div class="task-main"><p class="task-title">${escapeHtml(task.title)}</p><div class="task-meta"><span class="task-due"><span aria-hidden="true">◷</span> ${escapeHtml(formatDeadline(task.deadline))}</span></div></div>
            <span class="task-status ${done ? 'is-done' : ''}">${done ? 'Selesai' : 'Berjalan'}</span>
            <div class="task-actions"><button class="task-action" type="button" aria-label="Edit ${escapeHtml(task.title)}" data-task-action="edit">✎</button><button class="task-action delete" type="button" aria-label="Hapus ${escapeHtml(task.title)}" data-task-action="delete">⌫</button></div>
        </article>`;
    }).join('');
}

taskList.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-task-action]');
    if (!button) return;
    const row = button.closest('[data-task-id]');
    const task = tasks.find((item) => String(item.id) === row.dataset.taskId);
    if (!task) return;
    button.disabled = true;
    try {
        if (button.dataset.taskAction === 'toggle') {
            const updated = await api.updateStatus(task.id, task.status === 'done' ? 'todo' : 'done');
            tasks = tasks.map((item) => item.id === task.id ? updated : item);
            showToast(updated.status === 'done' ? 'Satu langkah lagi selesai ✳' : 'Tugas dikembalikan ke daftar aktif.');
        } else if (button.dataset.taskAction === 'edit') {
            openEditDialog(task);
        } else if (button.dataset.taskAction === 'delete') {
            await api.deleteTask(task.id);
            tasks = tasks.filter((item) => item.id !== task.id);
            showToast('Tugas berhasil dihapus.');
        }
        saveCurrentTasks();
        renderTasks();
    } catch (error) {
        showToast(error.message || 'Perubahan belum tersimpan.', true);
        button.disabled = false;
    }
});

document.querySelector('#task-search').addEventListener('input', (event) => {
    searchTerm = event.target.value.trim();
    renderTasks();
});
document.querySelector('#clear-search').addEventListener('click', () => {
    document.querySelector('#task-search').value = '';
    searchTerm = '';
    renderTasks();
});
document.querySelector('#filter-button').addEventListener('click', () => {
    document.querySelector('#filter-menu').hidden = !document.querySelector('#filter-menu').hidden;
});
document.querySelector('#filter-menu').addEventListener('click', (event) => {
    const option = event.target.closest('[data-filter]');
    if (!option) return;
    activeFilter = option.dataset.filter;
    document.querySelector('#filter-label').textContent = ({ all: 'Semua', todo: 'Belum selesai', done: 'Selesai' })[activeFilter];
    document.querySelector('#filter-menu').hidden = true;
    renderTasks();
});
document.querySelectorAll('[data-filter-link]').forEach((link) => link.addEventListener('click', () => {
    activeFilter = link.dataset.filterLink;
    document.querySelector('#filter-label').textContent = ({ all: 'Semua', today: 'Hari ini', todo: 'Belum selesai', done: 'Selesai' })[activeFilter];
    document.querySelectorAll('.nav-item').forEach((item) => item.classList.toggle('is-current', item === link));
    renderTasks();
}));

function openCreateDialog() {
    taskForm.reset();
    document.querySelector('#task-id').value = '';
    document.querySelector('#task-dialog-title').textContent = 'Tambahkan tugas';
    document.querySelector('#task-dialog-description').textContent = 'Tuliskan satu hal yang ingin kamu selesaikan.';
    document.querySelector('#dialog-eyebrow').textContent = 'LANGKAH BERIKUTNYA';
    document.querySelector('#task-deadline').value = dateOffset(0);
    document.querySelector('#task-alert').hidden = true;
    clearFieldErrors('#task-form [data-task-error]');
    taskDialog.showModal();
    document.querySelector('#task-title').focus();
}

function openEditDialog(task) {
    taskForm.reset();
    document.querySelector('#task-id').value = task.id;
    document.querySelector('#task-title').value = task.title;
    document.querySelector('#task-deadline').value = task.deadline;
    document.querySelector('#task-dialog-title').textContent = 'Ubah tugas';
    document.querySelector('#task-dialog-description').textContent = 'Perbarui detail langkah yang ingin kamu selesaikan.';
    document.querySelector('#dialog-eyebrow').textContent = 'SESUAIKAN RENCANAMU';
    document.querySelector('#task-alert').hidden = true;
    clearFieldErrors('#task-form [data-task-error]');
    taskDialog.showModal();
    document.querySelector('#task-title').focus();
}

document.querySelectorAll('[data-open-task]').forEach((button) => button.addEventListener('click', openCreateDialog));
document.querySelector('#close-dialog').addEventListener('click', () => taskDialog.close());
document.querySelector('#cancel-dialog').addEventListener('click', () => taskDialog.close());
taskDialog.addEventListener('click', (event) => {
    if (event.target === taskDialog) taskDialog.close();
});

taskForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFieldErrors('#task-form [data-task-error]');
    const title = document.querySelector('#task-title').value.trim();
    const deadline = document.querySelector('#task-deadline').value;
    const errors = {};
    if (!title) errors.title = 'Judul wajib diisi.';
    if (!deadline) errors.deadline = 'Batas waktu wajib dipilih.';
    if (Object.keys(errors).length) {
        Object.entries(errors).forEach(([field, message]) => { document.querySelector(`[data-task-error="${field}"]`).textContent = message; });
        return;
    }
    const id = document.querySelector('#task-id').value;
    const button = document.querySelector('#save-task');
    setButtonBusy(button, true, 'Menyimpan...');
    try {
        const payload = { title, deadline, status: tasks.find((task) => String(task.id) === id)?.status || 'todo' };
        const saved = id ? await api.updateTask(id, payload) : await api.createTask(payload);
        tasks = id ? tasks.map((task) => String(task.id) === id ? saved : task) : [saved, ...tasks];
        saveCurrentTasks();
        taskDialog.close();
        renderTasks();
        showToast(id ? 'Perubahan tugas tersimpan.' : 'Tugas baru ditambahkan.');
    } catch (error) {
        const formAlert = document.querySelector('#task-alert');
        formAlert.textContent = error.message || 'Tugas belum tersimpan. Silakan coba lagi.';
        formAlert.hidden = false;
    } finally {
        setButtonBusy(button, false);
    }
});

function showToast(message, isError = false) {
    const region = document.querySelector('#toast-region');
    region.innerHTML = `<div class="toast ${isError ? 'error' : ''}"><span class="toast-icon" aria-hidden="true">${isError ? '!' : '✳'}</span>${escapeHtml(message)}</div>`;
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => { region.innerHTML = ''; }, 3400);
}

document.querySelector('#logout-button').addEventListener('click', () => {
    clearSession();
    tasks = [];
    workspace.hidden = true;
    authScreen.hidden = false;
    authForm.reset();
    setAuthMode('login');
});
document.querySelector('#mobile-menu').addEventListener('click', () => document.querySelector('#sidebar').classList.toggle('is-open'));
document.querySelector('.main-panel').addEventListener('click', (event) => {
    if (window.innerWidth <= 760 && !event.target.closest('#mobile-menu')) document.querySelector('#sidebar').classList.remove('is-open');
});
document.addEventListener('click', (event) => {
    if (!event.target.closest('.task-tools')) document.querySelector('#filter-menu').hidden = true;
});

if (currentToken() && currentUser()) {
    void enterWorkspace();
} else {
    authScreen.hidden = false;
    workspace.hidden = true;
}
