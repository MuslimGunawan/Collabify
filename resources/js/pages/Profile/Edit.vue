<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    avatar_url: string;
}

const props = defineProps<{
    user: User;
    isMainClient: boolean;
    logoUrl?: string | null;
}>();

const form = useForm({
    name: props.user.name,
    avatar: null as File | null,
    password: '',
    password_confirmation: '',
    favicon: null as File | null,
    logo: null as File | null,
});

// For local preview
const avatarPreview = ref(props.user.avatar_url);
const faviconPreview = ref('/storage/custom_favicon.png?t=' + Date.now());
const logoPreview = ref(props.logoUrl || '');

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const onFaviconChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.favicon = file;
        faviconPreview.value = URL.createObjectURL(file);
    }
};

const onLogoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post('/profile', {
        onSuccess: () => {
            // Success redirection is handled by controller
        },
    });
};
</script>

<template>
    <Head title="Edit Profil - Collabify" />
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <!-- Top Navigation -->
        <nav class="bg-white shadow-sm border-b border-slate-200">
            <div class="max-w-3xl mx-auto px-4 sm:px-6">
                <div class="flex justify-between h-16">
                    <div class="flex items-center space-x-3">
                        <Link href="/" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">
                            ← Dashboard
                        </Link>
                        <span class="text-slate-300">|</span>
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            alt="Logo"
                            class="h-6 w-auto max-h-6 object-contain"
                        />
                        <span v-else class="text-base font-extrabold text-slate-800">Collabify</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 max-w-3xl w-full mx-auto py-8 px-4 sm:px-6">
            <div class="bg-white shadow rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Pengaturan Profil Akun</h3>
                    <p class="text-sm text-slate-500">Perbarui informasi profil, sandi, dan foto avatar kolaborasi Anda.</p>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Profile Picture Upload Section -->
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-6">
                        <div class="flex-shrink-0 relative">
                            <!-- Avatar Preview -->
                            <img
                                :src="avatarPreview"
                                alt="Avatar Preview"
                                class="h-24 w-24 rounded-full border border-slate-300 shadow-sm object-cover bg-white"
                            />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Foto Profil / Avatar</label>
                            <input
                                @change="onFileChange"
                                type="file"
                                accept="image/*"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 file:cursor-pointer hover:file:bg-indigo-100"
                            />
                            <p class="mt-1.5 text-xs text-slate-400">PNG, JPG, JPEG sampai dengan 2MB. Jika kosong, avatar hewan lucu Google Docs akan otomatis digunakan.</p>
                            <div v-if="form.errors.avatar" class="text-xs text-red-600 mt-1 font-semibold">
                                {{ form.errors.avatar }}
                            </div>
                        </div>
                    </div>

                    <!-- Favicon Upload Section (Only for Main Client) -->
                    <div v-if="isMainClient" class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-6 pt-4 border-t border-slate-200">
                        <div class="flex-shrink-0 relative">
                            <!-- Favicon Preview -->
                            <img
                                :src="faviconPreview"
                                alt="Favicon Preview"
                                class="h-12 w-12 rounded border border-slate-300 shadow-sm object-cover bg-white p-1"
                                @error="(e) => (e.target as HTMLImageElement).src = '/favicon.svg'"
                            />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Ikon Tab Browser (Favicon)</label>
                            <input
                                @change="onFaviconChange"
                                type="file"
                                accept="image/*"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 file:cursor-pointer hover:file:bg-indigo-100"
                            />
                            <p class="mt-1.5 text-xs text-slate-400">Pilih gambar icon tab baru. Hanya muncul dan berlaku untuk Server Utama (Host).</p>
                            <div v-if="form.errors.favicon" class="text-xs text-red-600 mt-1 font-semibold">
                                {{ form.errors.favicon }}
                            </div>
                        </div>
                    </div>

                    <!-- Logo Upload Section (Only for Main Client) -->
                    <div v-if="isMainClient" class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-6 pt-4 border-t border-slate-200">
                        <div class="flex-shrink-0 relative">
                            <!-- Logo Preview -->
                            <div class="h-12 w-24 rounded border border-slate-300 shadow-sm flex items-center justify-center bg-white p-1 overflow-hidden">
                                <img
                                    v-if="logoPreview"
                                    :src="logoPreview"
                                    alt="Logo Preview"
                                    class="max-h-full max-w-full object-contain"
                                />
                                <span v-else class="text-xs text-slate-400 font-semibold uppercase">Collabify</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Logo Aplikasi</label>
                            <input
                                @change="onLogoChange"
                                type="file"
                                accept="image/*"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 file:cursor-pointer hover:file:bg-indigo-100"
                            />
                            <p class="mt-1.5 text-xs text-slate-400">Pilih gambar logo aplikasi baru (disarankan landscape). Hanya muncul dan berlaku untuk Server Utama (Host).</p>
                            <div v-if="form.errors.logo" class="text-xs text-red-600 mt-1 font-semibold">
                                {{ form.errors.logo }}
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-200" />

                    <!-- Form Inputs -->
                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                            <input
                                v-model="form.name"
                                type="text"
                                id="name"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-900"
                            />
                            <div v-if="form.errors.name" class="text-xs text-red-600 mt-1 font-semibold">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <!-- Email (Read-only) -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-500 mb-1">Alamat Email (Tidak dapat diubah)</label>
                            <input
                                :value="user.email"
                                type="email"
                                id="email"
                                disabled
                                class="w-full px-3 py-2 border border-slate-200 rounded-md bg-slate-50 text-sm text-slate-400 cursor-not-allowed"
                            />
                        </div>

                        <hr class="border-slate-200" />

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Sandi Baru (Kosongkan jika tidak diubah)</label>
                            <input
                                v-model="form.password"
                                type="password"
                                id="password"
                                placeholder="••••••••"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-900"
                            />
                            <div v-if="form.errors.password" class="text-xs text-red-600 mt-1 font-semibold">
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">Konfirmasi Sandi Baru</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                id="password_confirmation"
                                placeholder="••••••••"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-900"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                        <Link
                            href="/"
                            class="inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none disabled:opacity-50 transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
