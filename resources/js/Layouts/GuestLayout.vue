<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 text-gray-800 dark:text-white" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-gray-200 dark:bg-gray-800 px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
