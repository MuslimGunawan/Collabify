<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    avatar_url: string;
}

interface Document {
    id: number;
    title: string;
    content: string;
    user_id: number | null;
    user: User | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    documents: Document[];
    localIp: string;
    auth: {
        user: User;
    };
}>();

const logout = () => {
    router.post('/logout');
};

const createDocument = () => {
    router.post('/documents');
};

const renameDocument = (doc: Document) => {
    const newTitle = prompt('Masukkan nama baru untuk dokumen ini:', doc.title || 'Untitled Document');
    if (newTitle !== null && newTitle.trim() !== '') {
        router.put(`/documents/${doc.id}`, { title: newTitle.trim() });
    }
};

const deleteDocument = (doc: Document) => {
    if (confirm(`Apakah Anda yakin ingin menghapus dokumen "${doc.title || 'Untitled Document'}"?`)) {
        router.delete(`/documents/${doc.id}`);
    }
};

const connectionUrl = computed(() => {
    return `http://${props.localIp}:8000`;
});

const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

onMounted(() => {
    if (window.Echo) {
        window.Echo.channel('documents')
            .listen('.document.updated', () => {
                router.reload({ only: ['documents'] });
            })
            .listen('.document.deleted', () => {
                router.reload({ only: ['documents'] });
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('documents');
    }
});
</script>

<template>
    <Head title="Dashboard - Collabify" />
    <div class="min-h-screen bg-slate-50">
        <!-- Top Navigation -->
        <nav class="bg-white shadow-sm border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <span class="text-xl font-extrabold text-indigo-600">Collabify</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link
                            href="/profile"
                            class="flex items-center space-x-2 hover:opacity-80 transition-opacity"
                            title="Edit Profil Saya"
                        >
                            <img
                                :src="auth.user.avatar_url"
                                alt="Avatar"
                                class="h-8 w-8 rounded-full border border-slate-200 object-cover bg-white"
                            />
                            <span class="text-sm text-slate-700">
                                Halo, <strong class="font-semibold text-slate-900">{{ auth.user.name }}</strong>
                            </span>
                        </Link>
                        <button
                            @click="logout"
                            class="inline-flex items-center px-3 py-1.5 border border-slate-300 text-xs font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <nav class="hidden"></nav>
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Alert Wifi IP -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 mb-8 flex flex-col md:flex-row md:items-center md:justify-between shadow-sm">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-blue-900 mb-1">🔗 Hubungkan Perangkat Lain (WiFi Sama)</h3>
                    <p class="text-sm text-blue-800">
                        Untuk berkolaborasi secara realtime, pastikan perangkat lain terhubung ke WiFi yang sama dengan komputer ini, lalu buka alamat berikut:
                    </p>
                    <div class="mt-2 flex items-center space-x-2">
                        <span class="px-3 py-1 bg-white border border-blue-300 rounded font-mono text-sm select-all font-semibold text-indigo-700">
                            {{ connectionUrl }}
                        </span>
                        <span class="text-xs text-blue-600">(Alamat IP ini dideteksi secara otomatis)</span>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Dokumen Saya</h2>
                    <p class="text-sm text-slate-500">Daftar dokumen kolaboratif yang tersedia untuk diedit.</p>
                </div>
                <button
                    @click="createDocument"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                >
                    + Buat Dokumen Baru
                </button>
            </div>

            <!-- Documents List -->
            <div class="bg-white shadow rounded-lg border border-slate-200 overflow-hidden">
                <div v-if="documents.length === 0" class="p-12 text-center">
                    <svg
                        class="mx-auto h-12 w-12 text-slate-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">Belum ada dokumen</h3>
                    <p class="mt-1 text-sm text-slate-500">Mulai dengan membuat dokumen baru.</p>
                    <div class="mt-6">
                        <button
                            @click="createDocument"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Buat Dokumen
                        </button>
                    </div>
                </div>

                <ul v-else class="divide-y divide-slate-200">
                    <li v-for="doc in documents" :key="doc.id" class="hover:bg-slate-50 transition-colors">
                        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="min-w-0 flex-1 w-full">
                                <Link
                                    :href="`/documents/${doc.id}`"
                                    class="text-base font-semibold text-indigo-600 hover:text-indigo-800 truncate block"
                                >
                                    {{ doc.title || 'Untitled Document' }}
                                </Link>
                                <div class="mt-1 flex flex-col sm:flex-row sm:items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                    <span>Dibuat oleh: <strong class="text-slate-700">{{ doc.user?.name || 'Anonim' }}</strong></span>
                                    <span class="hidden sm:inline text-slate-300">•</span>
                                    <span>Terakhir diubah: <strong class="text-slate-700">{{ formatDate(doc.updated_at) }}</strong></span>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto shrink-0">
                                <button
                                    @click="renameDocument(doc)"
                                    class="inline-flex items-center px-3 py-1.5 border border-slate-300 text-xs font-semibold rounded-md text-slate-700 bg-white hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-sm"
                                >
                                    Ubah Nama
                                </button>
                                <button
                                    @click="deleteDocument(doc)"
                                    class="inline-flex items-center px-3 py-1.5 border border-red-200 text-xs font-semibold rounded-md text-red-700 bg-red-50 hover:bg-red-100 hover:text-red-900 transition-colors shadow-sm"
                                >
                                    Hapus
                                </button>
                                <Link
                                    :href="`/documents/${doc.id}`"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm"
                                >
                                    Buka Editor
                                </Link>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </main>
    </div>
</template>
