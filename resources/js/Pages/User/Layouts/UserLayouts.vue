<script setup>
import { onMounted } from 'vue';
import { initFlowbite } from 'flowbite';
import Header from './Header.vue';
import Footer from './Footer.vue';

onMounted(() => {
    initFlowbite();
});
</script>

<template>
    <Header />

    <main class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100">
        <slot />
    </main>

    <Footer />
</template>