<script setup>
import { ref, onMounted, watch } from 'vue';
import { initFlowbite } from 'flowbite';
import Navbar from './Navbar.vue';
import Sidebar from './Sidebar.vue';

// Manejo del modo oscuro
const isDarkMode = ref(localStorage.getItem('dark') === 'true');

// Inicialización de Flowbite y modo oscuro
onMounted(() => {
  initFlowbite(); // Inicializa Flowbite

  // Aplica el estado inicial del modo oscuro
  document.documentElement.classList.toggle('dark', isDarkMode.value);

  // Observa cambios en el modo oscuro y actualiza localStorage
  watch(isDarkMode, (newVal) => {
    localStorage.setItem('dark', newVal);
    document.documentElement.classList.toggle('dark', newVal);
  });
});
</script>

<template>
  <div>
    <!-- Navbar -->
    <Navbar />

    <!-- Sidebar -->
    <Sidebar />

    <!-- Contenido principal -->
    <main class="p-2 md:ml-64 h-auto pt-20 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
      <slot />
    </main>
  </div>
</template>
