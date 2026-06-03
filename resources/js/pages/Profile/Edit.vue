<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const page = usePage();
const logoMode = computed(() => page.props.logoMode as string || 'media');
const logoText = computed(() => page.props.logoText as string || 'Collabify');

const form = useForm({
    name: props.user.name,
    avatar: null as File | null,
    password: '',
    password_confirmation: '',
    favicon: null as File | null,
    logo: null as File | null,
    logo_mode: (page.props.logoMode as string) || 'media',
    logo_text: (page.props.logoText as string) || 'Collabify',
});

// For local preview
const avatarPreview = ref(props.user.avatar_url);
const faviconPreview = ref('/storage/custom_favicon.png?t=' + Date.now());
const logoPreview = ref(props.logoUrl || '');

// Interactive Cropper Modal state
const showCropModal = ref(false);
const cropImageSource = ref('');
const cropTarget = ref<'favicon' | 'logo'>('favicon');

// Dimensions & fit state of loaded image in viewport
const imgNaturalWidth = ref(0);
const imgNaturalHeight = ref(0);
const imgFitW = ref(0);
const imgFitH = ref(0);
const vpWidth = computed(() => cropTarget.value === 'favicon' ? 280 : 400);
const vpHeight = computed(() => cropTarget.value === 'favicon' ? 280 : 200);

// CSS Transform values
const zoom = ref(1.0);
const rotateDeg = ref(0);
const offsetX = ref(0);
const offsetY = ref(0);

// Drag & Pan state variables
const isDragging = ref(false);
const startDragX = ref(0);
const startDragY = ref(0);
const startOffsetX = ref(0);
const startOffsetY = ref(0);

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
        initCropper(file, 'favicon');
        target.value = '';
    }
};

const onLogoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        initCropper(file, 'logo');
        target.value = '';
    }
};

const initCropper = (file: File, target: 'favicon' | 'logo') => {
    cropTarget.value = target;
    const reader = new FileReader();
    reader.onload = (event) => {
        if (event.target?.result) {
            cropImageSource.value = event.target.result as string;
            
            const img = new Image();
            img.src = cropImageSource.value;
            img.onload = () => {
                imgNaturalWidth.value = img.naturalWidth;
                imgNaturalHeight.value = img.naturalHeight;
                
                const vpw = vpWidth.value;
                const vph = vpHeight.value;
                const fitRatio = Math.min(vpw / img.naturalWidth, vph / img.naturalHeight);
                imgFitW.value = img.naturalWidth * fitRatio;
                imgFitH.value = img.naturalHeight * fitRatio;
                
                zoom.value = 1.0;
                rotateDeg.value = 0;
                offsetX.value = 0;
                offsetY.value = 0;
                
                showCropModal.value = true;
            };
        }
    };
    reader.readAsDataURL(file);
};

const startDrag = (e: MouseEvent | TouchEvent) => {
    isDragging.value = true;
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    startDragX.value = clientX;
    startDragY.value = clientY;
    startOffsetX.value = offsetX.value;
    startOffsetY.value = offsetY.value;
    
    if (e.cancelable) {
        e.preventDefault();
    }
};

const onDrag = (e: MouseEvent | TouchEvent) => {
    if (!isDragging.value) return;
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    
    offsetX.value = startOffsetX.value + (clientX - startDragX.value);
    offsetY.value = startOffsetY.value + (clientY - startDragY.value);
};

const endDrag = () => {
    isDragging.value = false;
};

const applyCrop = () => {
    const width = cropTarget.value === 'favicon' ? 512 : 1024;
    const height = cropTarget.value === 'favicon' ? 512 : 512;
    
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    
    if (!ctx) return;
    
    ctx.clearRect(0, 0, width, height);
    
    const img = new Image();
    img.src = cropImageSource.value;
    img.onload = () => {
        ctx.translate(width / 2, height / 2);
        
        const vpw = vpWidth.value;
        const ratio = width / vpw;
        
        ctx.rotate((rotateDeg.value * Math.PI) / 180);
        ctx.scale(zoom.value, zoom.value);
        ctx.translate(offsetX.value * ratio, offsetY.value * ratio);
        
        ctx.drawImage(
            img,
            -imgFitW.value * ratio / 2,
            -imgFitH.value * ratio / 2,
            imgFitW.value * ratio,
            imgFitH.value * ratio
        );
        
        canvas.toBlob((blob) => {
            if (blob) {
                const croppedFile = new File([blob], `cropped_${cropTarget.value}.png`, { type: 'image/png' });
                if (cropTarget.value === 'favicon') {
                    form.favicon = croppedFile;
                    faviconPreview.value = URL.createObjectURL(croppedFile);
                } else {
                    form.logo = croppedFile;
                    logoPreview.value = URL.createObjectURL(croppedFile);
                }
            }
            showCropModal.value = false;
        }, 'image/png');
    };
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
                        
                        <!-- Dynamic Logo in Header -->
                        <img
                            v-if="logoMode === 'media' && logoUrl"
                            :src="logoUrl"
                            alt="Logo"
                            class="h-10 sm:h-12 w-auto max-h-12 object-contain"
                        />
                        <span v-else class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">{{ logoText }}</span>
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
                            <!-- Favicon Preview (Enlarged) -->
                            <img
                                :src="faviconPreview"
                                alt="Favicon Preview"
                                class="h-24 w-24 sm:h-32 sm:w-32 rounded-lg border border-slate-300 shadow-sm object-contain bg-white p-2"
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
                            <p class="mt-1.5 text-xs text-slate-400">Pilih gambar ikon tab baru. Setelah memilih, Anda akan diarahkan ke editor interaktif.</p>
                            <div v-if="form.errors.favicon" class="text-xs text-red-600 mt-1 font-semibold">
                                {{ form.errors.favicon }}
                            </div>
                        </div>
                    </div>

                    <!-- Logo Settings Section (Only for Main Client) -->
                    <div v-if="isMainClient" class="pt-4 border-t border-slate-200 space-y-4">
                        <h4 class="text-sm font-bold text-slate-900">Konfigurasi Logo Aplikasi</h4>
                        
                        <!-- Mode Selector -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Mode Logo</label>
                            <div class="flex items-center space-x-6">
                                <label class="flex items-center cursor-pointer select-none">
                                    <input 
                                        type="radio" 
                                        v-model="form.logo_mode" 
                                        value="text" 
                                        class="h-4 w-4 text-indigo-650 border-slate-300 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-slate-750 font-medium">Teks Logo</span>
                                </label>
                                <label class="flex items-center cursor-pointer select-none">
                                    <input 
                                        type="radio" 
                                        v-model="form.logo_mode" 
                                        value="media" 
                                        class="h-4 w-4 text-indigo-650 border-slate-300 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-slate-750 font-medium">Media Upload (Gambar)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mode Text Input -->
                        <div v-if="form.logo_mode === 'text'" class="space-y-1">
                            <label for="logo_text" class="block text-sm font-semibold text-slate-700">Teks Logo Aplikasi</label>
                            <input
                                v-model="form.logo_text"
                                type="text"
                                id="logo_text"
                                placeholder="Masukkan nama/teks logo..."
                                class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-900"
                            />
                            <div v-if="form.errors.logo_text" class="text-xs text-red-600 mt-1 font-semibold">
                                {{ form.errors.logo_text }}
                            </div>
                        </div>

                        <!-- Mode Media Upload (Show only if logo_mode is 'media') -->
                        <div v-if="form.logo_mode === 'media'" class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-6">
                            <div class="flex-shrink-0 relative">
                                <!-- Logo Preview (Enlarged) -->
                                <div class="h-24 w-48 sm:h-32 sm:w-64 rounded-lg border border-slate-300 shadow-sm flex items-center justify-center bg-white p-2 overflow-hidden">
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        alt="Logo Preview"
                                        class="max-h-full max-w-full object-contain"
                                    />
                                    <span v-else class="text-xs text-slate-400 font-semibold uppercase">Belum ada logo</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Upload File Logo</label>
                                <input
                                    @change="onLogoChange"
                                    type="file"
                                    accept="image/*"
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 file:cursor-pointer hover:file:bg-indigo-100"
                                />
                                <p class="mt-1.5 text-xs text-slate-400">Pilih gambar logo aplikasi baru. Setelah memilih, Anda akan diarahkan ke editor interaktif.</p>
                                <div v-if="form.errors.logo" class="text-xs text-red-600 mt-1 font-semibold">
                                    {{ form.errors.logo }}
                                </div>
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

    <!-- Interactive Cropper Modal -->
    <div v-if="showCropModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full border border-slate-200 overflow-hidden flex flex-col transform transition-all duration-300 scale-100">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-800">
                    Atur & Potong {{ cropTarget === 'favicon' ? 'Favicon (Tab Icon)' : 'Logo Aplikasi' }}
                </h3>
                <button @click="showCropModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body (Cropper Viewport) -->
            <div class="p-6 flex flex-col items-center justify-center bg-slate-950 relative">
                <!-- Instructions -->
                <div class="absolute top-2 left-4 text-[10px] text-slate-400 z-10 select-none">
                    Drag/Geser gambar | Gunakan slider untuk zoom & rotasi
                </div>
                
                <!-- Viewport Box -->
                <div 
                    class="relative border-2 border-dashed border-indigo-500/50 bg-slate-900 overflow-hidden cursor-move select-none"
                    :style="{
                        width: vpWidth + 'px',
                        height: vpHeight + 'px',
                        borderRadius: cropTarget === 'favicon' ? '50%' : '8px'
                    }"
                    @mousedown="startDrag"
                    @mousemove="onDrag"
                    @mouseup="endDrag"
                    @mouseleave="endDrag"
                    @touchstart="startDrag"
                    @touchmove="onDrag"
                    @touchend="endDrag"
                >
                    <!-- Guideline overlays -->
                    <div class="absolute inset-0 border border-white/10 pointer-events-none"></div>
                    <div v-if="cropTarget === 'favicon'" class="absolute inset-0 border border-dashed border-indigo-400/20 rounded-full pointer-events-none"></div>
                    
                    <!-- The Interactive Image -->
                    <img
                        :src="cropImageSource"
                        alt="Crop Preview"
                        class="absolute pointer-events-none max-w-none origin-center"
                        :style="{
                            width: imgFitW + 'px',
                            height: imgFitH + 'px',
                            left: '50%',
                            top: '50%',
                            transform: `translate(-50%, -50%) translate(${offsetX}px, ${offsetY}px) rotate(${rotateDeg}deg) scale(${zoom})`
                        }"
                    />
                </div>
            </div>
            
            <!-- Modal Controls -->
            <div class="p-6 bg-white space-y-4">
                <!-- Zoom Slider -->
                <div class="space-y-1">
                    <div class="flex justify-between text-xs font-semibold text-slate-650">
                        <span>Perbesaran (Zoom): {{ Math.round(zoom * 100) }}%</span>
                        <div class="flex space-x-2">
                            <button type="button" @click="zoom = Math.max(0.1, zoom - 0.1)" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-[10px]">-</button>
                            <button type="button" @click="zoom = Math.min(3.0, zoom + 0.1)" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-[10px]">+</button>
                        </div>
                    </div>
                    <input 
                        v-model.number="zoom" 
                        type="range" 
                        min="0.1" 
                        max="3" 
                        step="0.01"
                        class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                    />
                </div>
                
                <!-- Rotation Slider -->
                <div class="space-y-1">
                    <div class="flex justify-between text-xs font-semibold text-slate-655">
                        <span>Rotasi: {{ rotateDeg }}°</span>
                        <div class="flex space-x-2">
                            <button type="button" @click="rotateDeg = (rotateDeg - 90 + 360) % 360" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-[10px]">-90°</button>
                            <button type="button" @click="rotateDeg = (rotateDeg + 90) % 360" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-[10px]">+90°</button>
                        </div>
                    </div>
                    <input 
                        v-model.number="rotateDeg" 
                        type="range" 
                        min="-180" 
                        max="180" 
                        step="1"
                        class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                    />
                </div>
                
                <!-- Reset Button & Actions -->
                <div class="flex justify-between items-center pt-2">
                    <button 
                        type="button"
                        @click="() => { zoom = 1.0; rotateDeg = 0; offsetX = 0; offsetY = 0; }" 
                        class="text-xs text-indigo-650 hover:text-indigo-850 font-semibold"
                    >
                        Atur Ulang (Reset)
                    </button>
                    <div class="flex space-x-2">
                        <button 
                            type="button"
                            @click="showCropModal = false" 
                            class="px-4 py-2 border border-slate-300 text-xs font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button 
                            type="button"
                            @click="applyCrop" 
                            class="px-4 py-2 text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm"
                        >
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
