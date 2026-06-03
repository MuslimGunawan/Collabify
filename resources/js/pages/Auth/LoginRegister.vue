<script setup lang="ts">
import { useForm, Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const logoUrl = computed(() => page.props.logoUrl as string | null);

const activeTab = ref<'login' | 'register'>('login');

const loginForm = useForm({
    email: '',
    password: '',
});

const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const guestForm = useForm({
    name: '',
});

const submitLogin = () => {
    loginForm.post('/login', {
        onFinish: () => loginForm.reset('password'),
    });
};

const submitRegister = () => {
    registerForm.post('/register', {
        onFinish: () => registerForm.reset('password', 'password_confirmation'),
    });
};

const submitGuest = () => {
    guestForm.post('/guest-login');
};
</script>

<template>
    <Head title="Login / Register - Collabify" />
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center flex flex-col items-center">
            <img
                v-if="logoUrl"
                :src="logoUrl"
                alt="Logo"
                class="h-16 w-auto max-h-16 object-contain mb-2"
            />
            <h1 v-else class="text-4xl font-extrabold text-indigo-600 tracking-tight">Collabify</h1>
            <p class="mt-2 text-sm text-slate-600">
                Real-time Collaborative Document Editor
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-md sm:rounded-lg sm:px-10 border border-slate-100">
                
                <!-- Quick Guest Join -->
                <div class="mb-8 p-4 bg-indigo-50 border border-indigo-100 rounded-md">
                    <h3 class="text-sm font-semibold text-indigo-900 mb-1">⚡ Quick Guest Join</h3>
                    <p class="text-xs text-indigo-700 mb-3">
                        Gunakan ini untuk testing cepat dengan WiFi di HP/device lain. Cukup isi nama saja!
                    </p>
                    <form @submit.prevent="submitGuest" class="space-y-3">
                        <div>
                            <input
                                v-model="guestForm.name"
                                type="text"
                                placeholder="Nama Anda"
                                required
                                class="w-full px-3 py-1.5 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <div v-if="guestForm.errors.name" class="mt-1 text-xs text-red-600">
                                {{ guestForm.errors.name }}
                            </div>
                        </div>
                        <button
                            type="submit"
                            :disabled="guestForm.processing"
                            class="w-full flex justify-center py-1.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        >
                            {{ guestForm.processing ? 'Joining...' : 'Masuk sebagai Tamu' }}
                        </button>
                    </form>
                </div>

                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-2 text-slate-500">atau gunakan akun</span>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-slate-200 mb-6">
                    <button
                        @click="activeTab = 'login'"
                        class="w-1/2 pb-3 text-center font-medium text-sm border-b-2"
                        :class="activeTab === 'login' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    >
                        Login
                    </button>
                    <button
                        @click="activeTab = 'register'"
                        class="w-1/2 pb-3 text-center font-medium text-sm border-b-2"
                        :class="activeTab === 'register' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    >
                        Register
                    </button>
                </div>

                <!-- Login Form -->
                <form v-if="activeTab === 'login'" @submit.prevent="submitLogin" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                        <div class="mt-1">
                            <input
                                v-model="loginForm.email"
                                id="email"
                                type="email"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                        </div>
                        <div v-if="loginForm.errors.email" class="mt-1 text-xs text-red-600">
                            {{ loginForm.errors.email }}
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <div class="mt-1">
                            <input
                                v-model="loginForm.password"
                                id="password"
                                type="password"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                        </div>
                        <div v-if="loginForm.errors.password" class="mt-1 text-xs text-red-600">
                            {{ loginForm.errors.password }}
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="loginForm.processing"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        >
                            {{ loginForm.processing ? 'Logging in...' : 'Sign In' }}
                        </button>
                    </div>
                </form>

                <!-- Register Form -->
                <form v-else @submit.prevent="submitRegister" class="space-y-6">
                    <div>
                        <label for="reg-name" class="block text-sm font-medium text-slate-700">Name</label>
                        <div class="mt-1">
                            <input
                                v-model="registerForm.name"
                                id="reg-name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                        </div>
                        <div v-if="registerForm.errors.name" class="mt-1 text-xs text-red-600">
                            {{ registerForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label for="reg-email" class="block text-sm font-medium text-slate-700">Email Address</label>
                        <div class="mt-1">
                            <input
                                v-model="registerForm.email"
                                id="reg-email"
                                type="email"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                        </div>
                        <div v-if="registerForm.errors.email" class="mt-1 text-xs text-red-600">
                            {{ registerForm.errors.email }}
                        </div>
                    </div>

                    <div>
                        <label for="reg-password" class="block text-sm font-medium text-slate-700">Password</label>
                        <div class="mt-1">
                            <input
                                v-model="registerForm.password"
                                id="reg-password"
                                type="password"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                        </div>
                        <div v-if="registerForm.errors.password" class="mt-1 text-xs text-red-600">
                            {{ registerForm.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label for="reg-password-confirm" class="block text-sm font-medium text-slate-700">Confirm Password</label>
                        <div class="mt-1">
                            <input
                                v-model="registerForm.password_confirmation"
                                id="reg-password-confirm"
                                type="password"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="registerForm.processing"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        >
                            {{ registerForm.processing ? 'Registering...' : 'Register Account' }}
                        </button>
                    </div>
                </form>

            </div>
            <!-- Footer Copyright -->
            <footer class="mt-8 text-center text-xs text-slate-400 space-y-1.5">
                <p>© 2026 Collabify. Dibuat oleh <strong class="text-slate-500 font-semibold">Muslim Gunawan</strong>. All Rights Reserved.</p>
                <p class="text-slate-400/80">Kami menerima inovasi, masukan baru, dan kerja sama pengembangan.</p>
            </footer>
        </div>
    </div>
</template>
