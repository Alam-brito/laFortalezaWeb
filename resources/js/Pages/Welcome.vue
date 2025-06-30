<script setup>
import { Head, Link } from '@inertiajs/vue3';

import { onMounted } from 'vue';


defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="La Fortaleza" />
    <div class="relative bg-gradient-to-br from-gray-50 to-gray-200 text-black dark:from-black dark:to-gray-900 dark:text-white">
        <img
            id="background"
            class="absolute inset-0 object-cover w-full h-full opacity-20"
            src="assets/images/background.png"
        />
        <div class="relative flex min-h-screen flex-col items-center justify-center">
            <div class="relative w-full max-w-3xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-1 items-center gap-6 py-12 text-center lg:grid-cols-3 lg:text-left">
                    <!-- Logo -->
                    <div class="lg:col-start-2">
                        <img
                            class="w-48 h-auto mx-auto lg:w-64"
                            src="assets/images/logo.png"
                            alt="La Fortaleza Logo"
                        />
                    </div>

                    

                    <!-- Navigation -->
                    <nav v-if="canLogin" class="flex justify-center gap-4 lg:col-start-3 lg:justify-end">

                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-md bg-[#FF2D20] px-4 py-2 text-white shadow-md transition hover:bg-[#e5281c] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#FF2D20] dark:bg-white dark:text-black dark:hover:bg-gray-300"
                        >
                            Panel
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-md px-4 py-2 text-gray-700 ring-1 ring-gray-300 shadow-sm transition hover:text-gray-900 hover:ring-gray-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-500 dark:text-gray-200 dark:ring-gray-600 dark:hover:ring-gray-500"
                            >
                                Iniciar sesión
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-md bg-[#1ddbb5] px-4 py-2 text-white shadow-md transition hover:bg-[#e5281c] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#FF2D20] dark:bg-white dark:text-black dark:hover:bg-gray-300"
                            >
                                Registrarse
                            </Link>
                        </template>
                    </nav>
                </header>

                <!-- Footer -->
                <footer class="mt-12 border-t border-gray-300 py-6 text-center text-sm text-gray-700 dark:border-gray-700 dark:text-gray-400">
                    <p>
                        Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                    </p>
                    <p class="mt-2 text-xs">
                        © {{ new Date().getFullYear() }} La Fortaleza. Todos los derechos reservados.
                    </p>
                </footer>
            </div>
        </div>
    </div>
</template>