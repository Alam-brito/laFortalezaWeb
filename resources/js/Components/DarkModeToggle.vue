<script setup>
import { ref } from 'vue';

const isDarkMode = ref(localStorage.getItem('theme') === 'dark');

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};
</script>

<template>
    <button
        @click="toggleDarkMode"
        class="p-2 bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-full shadow">
        <svg v-if="!isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 3v1m0 16v1m8.485-7.485h-1m-15.97 0h-1m12.02-8.02l-.707-.707m-10.607 10.607l-.707-.707m15.556 0l-.707.707m-10.607-10.607l-.707.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M17.293 14.707a8 8 0 01-11.314-11.314 8.001 8.001 0 0111.314 11.314z" />
        </svg>
    </button>
</template>
