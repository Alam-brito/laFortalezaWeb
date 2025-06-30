<script setup>
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';

// Manejo del modo oscuro
const isDarkMode = ref(localStorage.getItem('dark') === 'true');

onMounted(() => {
  // Aplicar estado inicial del modo oscuro
  document.documentElement.classList.toggle('dark', isDarkMode.value);

  // Observar cambios y persistir en localStorage
  watch(isDarkMode, (newVal) => {
    localStorage.setItem('dark', newVal);
    document.documentElement.classList.toggle('dark', newVal);
  });
});
</script>

<template>
  <nav
    class="bg-white border-b border-gray-200 px-4 py-2.5 dark:bg-gray-800 dark:border-gray-700 fixed left-0 right-0 top-0 z-50">
    <div class="flex flex-wrap justify-between items-center">
      <div class="flex justify-start items-center">
        <!-- Sidebar Toggle -->
        <button data-drawer-target="drawer-navigation" data-drawer-toggle="drawer-navigation"
          aria-controls="drawer-navigation"
          class="p-2 mr-2 text-gray-600 rounded-lg cursor-pointer md:hidden hover:text-gray-900 hover:bg-gray-100 focus:bg-gray-100 dark:focus:bg-gray-700 focus:ring-2 focus:ring-gray-100 dark:focus:ring-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
          <svg aria-hidden="true" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
              d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
              clip-rule="evenodd"></path>
          </svg>
          <svg aria-hidden="true" class="hidden w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd"></path>
          </svg>
          <span class="sr-only">Toggle sidebar</span>
        </button>

        <!-- Logo -->
        <a href="#" class="flex items-center justify-between mr-4">
          <!--Imágen para el logo-->
            <img class="w-14 h-12 md:w-14 md:h-12 mr-2" src="/assets/images/logo.png" alt="logo">
          <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">
            La Fortaleza
          </span>
        </a>
      </div>

      <div class="flex items-center lg:order-2">
        <!-- Botón de modo oscuro -->
        <button type="button"
          class="p-2 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none"
          @click="isDarkMode = !isDarkMode">
          <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-6 h-6 text-white">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-6 h-6 text-black">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
          </svg>
        </button>

        <!-- Dropdown -->
        <button type="button"
          class="flex mx-3 text-sm bg-gray-800 rounded-full md:mr-6 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
          id="user-menu-button" aria-expanded="false" data-dropdown-toggle="dropdown">
          <span class="sr-only">Abrir menú</span>
          <img class="w-11 h-12 rounded-full"
            src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRpDv63Rr98BekxAVUku2Jbmfcjbn3rP5e2IF8d_9fFshq6AJCbsGS1LY6wlFpwaZDTYn4&usqp=CAU"
            alt="user photo" />
        </button>
        <div
          class="hidden z-50 my-4 w-56 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
          id="dropdown">
          <div class="py-3 px-4">
            <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $page.props.auth.user.name
            }}</span>
            <span class="block text-sm text-gray-900 truncate dark:text-white">{{ $page.props.auth.user.email }}</span>
          </div>

          <ul class="py-1 text-gray-700 dark:text-gray-300" aria-labelledby="dropdown">
            <li>
              <DropdownLink
                class="block py-2 px-4 text-sm hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                :href="route('user.home')" as="button" type="button">Ir a la tienda</DropdownLink>
            </li>
          </ul>

          <ul class="py-1 text-gray-700 dark:text-gray-300" aria-labelledby="dropdown">
            <li>
              <DropdownLink class="block py-2 px-4 text-sm hover:bg-red-100 dark:hover:bg-red-600 dark:hover:text-white"
                :href="route('logout')" method="post" as="button" type="button">Salir</DropdownLink>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</template>
