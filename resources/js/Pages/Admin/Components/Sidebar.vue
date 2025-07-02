<script setup>
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const userRoles = computed(() => usePage().props.userRoles ?? []);
const userPermissions = computed(() => usePage().props.userPermissions ?? []);
console.log("Roles", usePage().props.userRoles);
console.log("Permisos", usePage().props.userPermissions);

</script>
<template>
  <aside
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
    aria-label="Sidenav" id="drawer-navigation">
    <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
      <!--Rol encargado de reportes-->
      <ul v-if="userRoles.length === 1 && userRoles.includes('encargado_reportes')">
        <li>
          <Link :href="route('index.reporte')"
            class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
          <svg width="60" height="60" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <title>Carta de Correo 3D con Símbolo @</title>
            <!-- Sombra -->
            <ellipse cx="32" cy="57" rx="25" ry="6" fill="rgba(0, 0, 0, 0.3)" />
            <defs>
              <!-- Degradado para el sobre -->
              <linearGradient id="gradEnvelope" x1="5" y1="5" x2="55" y2="50" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#FFEB3B" />
                <stop offset="100%" stop-color="#FBC02D" />
              </linearGradient>
              <!-- Degradado para la solapa -->
              <linearGradient id="gradFlap" x1="5" y1="5" x2="30" y2="15" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#B2FF59" />
                <stop offset="100%" stop-color="#76FF03" />
              </linearGradient>
              <!-- Sombra -->
              <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                <feDropShadow dx="3" dy="3" stdDeviation="3" flood-color="rgba(0,0,0,0.5)" />
              </filter>
            </defs>

            <!-- Cuerpo del sobre con doble contorno -->
            <path d="M8 20 L56 20 L56 50 L8 50 Z" fill="url(#gradEnvelope)" stroke="black" stroke-width="4"
              filter="url(#shadow)" />
            <path d="M8 20 L56 20 L56 50 L8 50 Z" fill="none" stroke="white" stroke-width="2" />

            <!-- Detalles internos (líneas del pliegue de la carta) -->
            <path d="M8 20 L32 38 L56 20" stroke="black" stroke-width="3" />
            <path d="M8 20 L32 38 L56 20" stroke="white" stroke-width="1.5" />

            <!-- Símbolo "@" centrado con doble contorno -->
            <circle cx="32" cy="38" r="10" fill="#76FF03" stroke="black" stroke-width="4" filter="url(#shadow)" />
            <circle cx="32" cy="38" r="10" fill="none" stroke="white" stroke-width="2" />

            <text x="24" y="44" font-size="16px" font-weight="bold" fill="black">@</text>
          </svg>
          Reportes
          </Link>
        </li>
      </ul>
      <!--Fin rol encargado de reportes-->

      <!--Vista para el encargado de inventarios-->
      <ul v-else-if="userRoles.length === 1 && userRoles.includes('encargado_inv')">
        <li>
          <Link :href="route('admin.inventarios.index')"
            class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
          <svg width="64" height="60" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <title>Icono de Inventario 3D - Contorno Blanco y Negro</title>
            <!-- Sombra -->
            <ellipse cx="32" cy="58" rx="22" ry="6" fill="rgba(0, 0, 0, 0.3)" />
            <defs>
              <!-- Degradado para la caja -->
              <linearGradient id="gradBox" x1="10" y1="10" x2="50" y2="50" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#FFCA28" />
                <stop offset="100%" stop-color="#FF9800" />
              </linearGradient>
              <!-- Degradado para la tapa -->
              <linearGradient id="gradLid" x1="5" y1="5" x2="40" y2="20" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#FFA726" />
                <stop offset="100%" stop-color="#EF6C00" />
              </linearGradient>
              <!-- Degradado para las partes de color plomo -->
              <linearGradient id="gradPlomo" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#CFD8DC" />
                <stop offset="100%" stop-color="#B0BEC5" />
              </linearGradient>
              <!-- Sombra -->
              <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                <feDropShadow dx="2" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.5)" />
              </filter>
            </defs>

            <!-- Base de la caja -->
            <path d="M12 24 L32 12 L52 24 L32 36 Z" fill="url(#gradBox)" stroke="black" stroke-width="4" />
            <path d="M12 24 L32 12 L52 24 L32 36 Z" fill="none" stroke="white" stroke-width="2" />

            <!-- Tapa de la caja -->
            <path d="M10 20 L32 8 L54 20 L32 32 Z" fill="url(#gradLid)" stroke="black" stroke-width="4" />
            <path d="M10 20 L32 8 L54 20 L32 32 Z" fill="none" stroke="white" stroke-width="2" />

            <!-- Sombra interna de la caja -->
            <path d="M12 24 L32 36 L32 56 L12 44 Z" fill="rgba(0,0,0,0.3)" stroke="black" stroke-width="4" />
            <path d="M12 24 L32 36 L32 56 L12 44 Z" fill="none" stroke="white" stroke-width="2" />

            <path d="M52 24 L32 36 L32 56 L52 44 Z" fill="rgba(0,0,0,0.2)" stroke="black" stroke-width="4" />
            <path d="M52 24 L32 36 L32 56 L52 44 Z" fill="none" stroke="white" stroke-width="2" />

            <!-- Código de barras (Color plomo con doble contorno) -->
            <rect x="18" y="30" width="4" height="10" fill="url(#gradPlomo)" stroke="black" stroke-width="4" />
            <rect x="18" y="30" width="4" height="10" fill="none" stroke="white" stroke-width="2" />

            <rect x="24" y="30" width="2" height="10" fill="url(#gradPlomo)" stroke="black" stroke-width="4" />
            <rect x="24" y="30" width="2" height="10" fill="none" stroke="white" stroke-width="2" />

            <rect x="28" y="30" width="3" height="10" fill="url(#gradPlomo)" stroke="black" stroke-width="4" />
            <rect x="28" y="30" width="3" height="10" fill="none" stroke="white" stroke-width="2" />

            <rect x="34" y="30" width="5" height="10" fill="url(#gradPlomo)" stroke="black" stroke-width="4" />
            <rect x="34" y="30" width="5" height="10" fill="none" stroke="white" stroke-width="2" />

            <!-- Líneas laterales para mayor detalle (doble contorno) -->
            <path d="M32 12 V36" stroke="black" stroke-width="4" />
            <path d="M32 12 V36" stroke="white" stroke-width="2" />
          </svg>
          Inventario</Link>
        </li>
      </ul>
      <!--Fin vista para el encargado de inventarios-->

      <!--Vista para el encargado de ventas-->
      <ul v-else-if="userRoles.length === 1 && userRoles.includes('vendedor')">
        <li>
          <Link :href="route('admin.venta.index')"
            class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="55" height="55" fill="none">
            <!-- Gradientes para elementos principales -->
            <defs>
              <linearGradient id="roofGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#FF9E80" />
                <stop offset="100%" stop-color="#FF6E40" />
              </linearGradient>
              <linearGradient id="buildingGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#00E5FF" />
                <stop offset="100%" stop-color="#00B8D4" />
              </linearGradient>
              <linearGradient id="doorGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#B388FF" />
                <stop offset="100%" stop-color="#7C4DFF" />
              </linearGradient>
              <linearGradient id="animalGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#EEFF41" />
                <stop offset="100%" stop-color="#C6FF00" />
              </linearGradient>
              <linearGradient id="crossGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#FF4081" />
                <stop offset="100%" stop-color="#F50057" />
              </linearGradient>
              <linearGradient id="horseGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#76FF03" />
                <stop offset="100%" stop-color="#64DD17" />
              </linearGradient>
            </defs>

            <!-- Techo de la tienda -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72"
              fill="url(#roofGradient)" stroke="#FF3D00" />

            <!-- Estructura principal de la tienda -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M2.36 21h11.14M18 21h3.64m-1.39 0V9.349" fill="url(#buildingGradient)" stroke="#0091EA" />

            <!-- Puerta principal -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"
              fill="url(#doorGradient)" stroke="#6200EA" />

            <!-- Entrada lateral/segundo edificio -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21" fill="url(#animalGradient)"
              stroke="#76FF03" />

            <!-- Cruz Veterinaria -->
            <rect x="15.5" y="3.5" width="1.5" height="4" rx="0.5" fill="url(#crossGradient)" stroke="#FF1744"
              stroke-width="0.3" />
            <rect x="14" y="5" width="4.5" height="1.5" rx="0.5" fill="url(#crossGradient)" stroke="#FF1744"
              stroke-width="0.3" />

            <!-- Silueta de Caballo -->
            <path
              d="M4.5 6.5c0 0 0.3-1 1-1s1 0.5 1 0.5 0.3-0.5 0.7-0.5 0.8 0.5 0.8 0.5l0.5-1c0 0 0.5 0 0.5 0.5s-0.5 1.5-0.5 1.5 0.5 0 0.5 0.5-0.5 1-1 1c0 0 0-0.5-0.5-0.5s-0.5 0.5-0.5 0.5-0.5 0-0.5-0.5 0.3-0.5 0-0.5-0.5 0.5-1 0.5-1-1-1-1z"
              fill="url(#horseGradient)" stroke="#64DD17" stroke-width="0.3" />

            <!-- Reflejos/Brillos -->
            <circle cx="8.5" cy="15" r="0.6" fill="white" opacity="0.7" />
            <circle cx="16" cy="16" r="0.5" fill="white" opacity="0.7" />
          </svg>
          Tienda Local</Link>
        </li>

        <li>
          <Link :href="route('admin.ventaDiaria.index')"
            class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="53" height="53">
            <title>Stylized Money Payment Icon</title>

            <!-- Drop shadow for the whole icon -->
            <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
              <feDropShadow dx="0" dy="3" stdDeviation="2" flood-opacity="0.3" />
            </filter>

            <!-- Background glow effect -->
            <circle cx="32" cy="32" r="28" fill="rgba(76, 175, 80, 0.2)" />

            <!-- Gradients definition -->
            <defs>
              <linearGradient id="cardGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#7986CB" />
                <stop offset="100%" stop-color="#3949AB" />
              </linearGradient>

              <linearGradient id="moneyGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#66BB6A" />
                <stop offset="100%" stop-color="#43A047" />
              </linearGradient>

              <linearGradient id="circleGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#FFC107" />
                <stop offset="100%" stop-color="#FFA000" />
              </linearGradient>
            </defs>

            <!-- Main card body with double outline -->
            <rect x="6" y="18" width="52" height="32" rx="3" ry="3" fill="url(#cardGradient)" stroke="#000"
              stroke-width="2" filter="url(#shadow)" />
            <rect x="6" y="18" width="52" height="32" rx="3" ry="3" fill="none" stroke="#fff" stroke-width="1" />

            <!-- Top money coming out effect -->
            <rect x="10" y="6" width="44" height="12" rx="2" ry="2" fill="url(#moneyGradient)" stroke="#000"
              stroke-width="2" />
            <rect x="10" y="6" width="44" height="12" rx="2" ry="2" fill="none" stroke="#fff" stroke-width="1" />

            <!-- Money stack below -->
            <rect x="10" y="46" width="44" height="12" rx="2" ry="2" fill="url(#moneyGradient)" stroke="#000"
              stroke-width="2" />
            <rect x="10" y="46" width="44" height="12" rx="2" ry="2" fill="none" stroke="#fff" stroke-width="1" />

            <!-- Circle coin in center -->
            <circle cx="32" cy="34" r="8" fill="url(#circleGradient)" stroke="#000" stroke-width="2" />
            <circle cx="32" cy="34" r="8" fill="none" stroke="#fff" stroke-width="1" />

            <!-- Dollar sign -->
            <path d="M32,30 v8 M30,31 h4 M30,37 h4" stroke="#fff" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />

            <!-- Small circles for decorative buttons -->
            <circle cx="48" cy="28" r="2" fill="#fff" opacity="0.9" />
            <circle cx="16" cy="28" r="2" fill="#fff" opacity="0.9" />

            <!-- Highlight/shimmer -->
            <path d="M10,22 L54,22" stroke="#fff" stroke-width="1" opacity="0.6" />
            <path d="M14,10 L50,10" stroke="#fff" stroke-width="1" opacity="0.6" />
            <path d="M14,50 L50,50" stroke="#fff" stroke-width="1" opacity="0.6" />
          </svg>
          Ventas Diarias</Link>
        </li>

      </ul>
      <!--Fin vista para el encargado de ventas-->


      <!--Vista general con todas las funciones-->
      <ul v-else class="space-y-2">
        <li>
          <Link :href="route('admin.dashboard')"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
          <svg width="40" height="40" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <title>Gráfico Circular - Torta de Estadísticas</title>
            <defs>
              <!-- Degradado para la sección principal -->
              <linearGradient id="gradMain" x1="2" y1="2" x2="18" y2="18" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#FF4081" />
                <stop offset="100%" stop-color="#E91E63" />
              </linearGradient>
              <!-- Degradado para la segunda sección -->
              <linearGradient id="gradSecond" x1="10" y1="2" x2="18" y2="10" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#29B6F6" />
                <stop offset="100%" stop-color="#0288D1" />
              </linearGradient>
              <!-- Sombra -->
              <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                <feDropShadow dx="2" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.4)" />
              </filter>
            </defs>

            <!-- Primera parte del gráfico -->
            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" fill="url(#gradMain)" stroke="black" stroke-width="2"
              filter="url(#shadow)" />
            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" fill="url(#gradSecond)" stroke="black"
              stroke-width="2" filter="url(#shadow)" />

            <!-- Contorno blanco para destacar -->
            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" fill="none" stroke="white" stroke-width="1" />
            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" fill="none" stroke="white" stroke-width="1" />
          </svg>

          <span class="ml-3">Panel del administrador</span>
          </Link>
        </li>
        <li>
          <button type="button"
            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
            aria-controls="dropdown-pages" data-collapse-toggle="dropdown-pages">
            <svg width="40" height="40" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <title>Documento 3D - Celeste Matizado</title>
              <defs>
                <!-- Degradado para la hoja principal -->
                <linearGradient id="gradPage" x1="4" y1="2" x2="16" y2="18" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#81D4FA" />
                  <stop offset="100%" stop-color="#0288D1" />
                </linearGradient>
                <!-- Degradado para las líneas de texto -->
                <linearGradient id="gradLines" x1="6" y1="8" x2="14" y2="16" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#64B5F6" />
                  <stop offset="100%" stop-color="#1E88E5" />
                </linearGradient>
                <!-- Sombra -->
                <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                  <feDropShadow dx="2" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.4)" />
                </filter>
              </defs>

              <!-- Cuerpo de la hoja -->
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                fill="url(#gradPage)" stroke="black" stroke-width="2" filter="url(#shadow)" />

              <!-- Líneas de texto -->
              <path d="M6 10a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" fill="url(#gradLines)" stroke="black"
                stroke-width="2" />
              <path d="M7 13a1 1 0 100 2h6a1 1 0 100-2H7z" fill="url(#gradLines)" stroke="black" stroke-width="2" />

              <!-- Contorno blanco adicional -->
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                fill="none" stroke="white" stroke-width="1" />

              <path d="M6 10a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" fill="none" stroke="white" stroke-width="1" />
              <path d="M7 13a1 1 0 100 2h6a1 1 0 100-2H7z" fill="none" stroke="white" stroke-width="1" />
            </svg>

            <span class="flex-1 ml-3 text-left whitespace-nowrap">Páginas</span>
            <svg aria-hidden="true" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"></path>
            </svg>
          </button>
          <ul id="dropdown-pages" class="hidden py-2 space-y-2">
            <li>
              <Link :href="route('admin.usuarios.index')"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
              <svg width="64" height="55" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <title>Icono de Usuario Colorido</title>

                <!-- Sombra debajo -->
                <ellipse cx="32" cy="58" rx="20" ry="5" fill="rgba(0, 0, 0, 0.2)" />

                <!-- Cabeza (Verde Claro) con Sombra -->
                <circle cx="32" cy="18" r="14" fill="url(#headGradient)" stroke="#FFFFFF" stroke-width="2" />
                <circle cx="32" cy="18" r="14" fill="rgba(0, 0, 0, 0.1)" filter="url(#shadow)" />

                <!-- Cuerpo (Celeste) con degradado -->
                <path d="M12 50c0-10 8-18 20-18h0c12 0 20 8 20 18v6H12v-6z" fill="url(#bodyGradient)" stroke="#FFFFFF"
                  stroke-width="2" />

                <!-- Definiciones de degradados -->
                <defs>
                  <linearGradient id="headGradient" x1="18" y1="18" x2="46" y2="18" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#B2FF59" />
                    <stop offset="1" stop-color="#76FF03" />
                  </linearGradient>

                  <linearGradient id="bodyGradient" x1="12" y1="50" x2="52" y2="50" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#4FC3F7" />
                    <stop offset="1" stop-color="#0288D1" />
                  </linearGradient>

                  <!-- Sombra -->
                  <filter id="shadow" x="0" y="0" width="64" height="64">
                    <feOffset result="offOut" in="SourceAlpha" dx="2" dy="3" />
                    <feGaussianBlur result="blurOut" in="offOut" stdDeviation="2" />
                    <feBlend in="SourceGraphic" in2="blurOut" mode="normal" />
                  </filter>
                </defs>
              </svg>
              Usuarios</Link>
            </li>

            <li>
              <Link :href="route('admin.venta.index')"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="55" height="55" fill="none">
                <!-- Gradientes para elementos principales -->
                <defs>
                  <linearGradient id="roofGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FF9E80" />
                    <stop offset="100%" stop-color="#FF6E40" />
                  </linearGradient>
                  <linearGradient id="buildingGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#00E5FF" />
                    <stop offset="100%" stop-color="#00B8D4" />
                  </linearGradient>
                  <linearGradient id="doorGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#B388FF" />
                    <stop offset="100%" stop-color="#7C4DFF" />
                  </linearGradient>
                  <linearGradient id="animalGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#EEFF41" />
                    <stop offset="100%" stop-color="#C6FF00" />
                  </linearGradient>
                  <linearGradient id="crossGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FF4081" />
                    <stop offset="100%" stop-color="#F50057" />
                  </linearGradient>
                  <linearGradient id="horseGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#76FF03" />
                    <stop offset="100%" stop-color="#64DD17" />
                  </linearGradient>
                </defs>

                <!-- Techo de la tienda -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72"
                  fill="url(#roofGradient)" stroke="#FF3D00" />

                <!-- Estructura principal de la tienda -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M2.36 21h11.14M18 21h3.64m-1.39 0V9.349" fill="url(#buildingGradient)" stroke="#0091EA" />

                <!-- Puerta principal -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"
                  fill="url(#doorGradient)" stroke="#6200EA" />

                <!-- Entrada lateral/segundo edificio -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21" fill="url(#animalGradient)"
                  stroke="#76FF03" />

                <!-- Cruz Veterinaria -->
                <rect x="15.5" y="3.5" width="1.5" height="4" rx="0.5" fill="url(#crossGradient)" stroke="#FF1744"
                  stroke-width="0.3" />
                <rect x="14" y="5" width="4.5" height="1.5" rx="0.5" fill="url(#crossGradient)" stroke="#FF1744"
                  stroke-width="0.3" />

                <!-- Silueta de Caballo -->
                <path
                  d="M4.5 6.5c0 0 0.3-1 1-1s1 0.5 1 0.5 0.3-0.5 0.7-0.5 0.8 0.5 0.8 0.5l0.5-1c0 0 0.5 0 0.5 0.5s-0.5 1.5-0.5 1.5 0.5 0 0.5 0.5-0.5 1-1 1c0 0 0-0.5-0.5-0.5s-0.5 0.5-0.5 0.5-0.5 0-0.5-0.5 0.3-0.5 0-0.5-0.5 0.5-1 0.5-1-1-1-1z"
                  fill="url(#horseGradient)" stroke="#64DD17" stroke-width="0.3" />

                <!-- Reflejos/Brillos -->
                <circle cx="8.5" cy="15" r="0.6" fill="white" opacity="0.7" />
                <circle cx="16" cy="16" r="0.5" fill="white" opacity="0.7" />
              </svg>
              Tienda Local</Link>
            </li>

            <li>
              <Link :href="route('admin.ventaDiaria.index')"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="53" height="53">
                <title>Stylized Money Payment Icon</title>

                <!-- Drop shadow for the whole icon -->
                <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                  <feDropShadow dx="0" dy="3" stdDeviation="2" flood-opacity="0.3" />
                </filter>

                <!-- Background glow effect -->
                <circle cx="32" cy="32" r="28" fill="rgba(76, 175, 80, 0.2)" />

                <!-- Gradients definition -->
                <defs>
                  <linearGradient id="cardGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#7986CB" />
                    <stop offset="100%" stop-color="#3949AB" />
                  </linearGradient>

                  <linearGradient id="moneyGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#66BB6A" />
                    <stop offset="100%" stop-color="#43A047" />
                  </linearGradient>

                  <linearGradient id="circleGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FFC107" />
                    <stop offset="100%" stop-color="#FFA000" />
                  </linearGradient>
                </defs>

                <!-- Main card body with double outline -->
                <rect x="6" y="18" width="52" height="32" rx="3" ry="3" fill="url(#cardGradient)" stroke="#000"
                  stroke-width="2" filter="url(#shadow)" />
                <rect x="6" y="18" width="52" height="32" rx="3" ry="3" fill="none" stroke="#fff" stroke-width="1" />

                <!-- Top money coming out effect -->
                <rect x="10" y="6" width="44" height="12" rx="2" ry="2" fill="url(#moneyGradient)" stroke="#000"
                  stroke-width="2" />
                <rect x="10" y="6" width="44" height="12" rx="2" ry="2" fill="none" stroke="#fff" stroke-width="1" />

                <!-- Money stack below -->
                <rect x="10" y="46" width="44" height="12" rx="2" ry="2" fill="url(#moneyGradient)" stroke="#000"
                  stroke-width="2" />
                <rect x="10" y="46" width="44" height="12" rx="2" ry="2" fill="none" stroke="#fff" stroke-width="1" />

                <!-- Circle coin in center -->
                <circle cx="32" cy="34" r="8" fill="url(#circleGradient)" stroke="#000" stroke-width="2" />
                <circle cx="32" cy="34" r="8" fill="none" stroke="#fff" stroke-width="1" />

                <!-- Dollar sign -->
                <path d="M32,30 v8 M30,31 h4 M30,37 h4" stroke="#fff" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />

                <!-- Small circles for decorative buttons -->
                <circle cx="48" cy="28" r="2" fill="#fff" opacity="0.9" />
                <circle cx="16" cy="28" r="2" fill="#fff" opacity="0.9" />

                <!-- Highlight/shimmer -->
                <path d="M10,22 L54,22" stroke="#fff" stroke-width="1" opacity="0.6" />
                <path d="M14,10 L50,10" stroke="#fff" stroke-width="1" opacity="0.6" />
                <path d="M14,50 L50,50" stroke="#fff" stroke-width="1" opacity="0.6" />
              </svg>
              Ventas Diarias</Link>
            </li>

            <li>
              <Link :href="route('admin.productos.index')"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
              <svg width="64" height="55" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <title>Caja Abierta Fosforescente</title>

                <!-- Sombra -->
                <ellipse cx="32" cy="58" rx="22" ry="6" fill="rgba(0, 0, 0, 0.3)" />

                <!-- Base de la caja con doble contorno -->
                <polygon points="12,32 32,48 52,32 32,16" fill="url(#boxGradient)" stroke="black" stroke-width="4" />
                <polygon points="12,32 32,48 52,32 32,16" fill="none" stroke="white" stroke-width="2" />

                <!-- Lados de la caja con doble contorno -->
                <polygon points="12,32 12,48 32,64 32,48" fill="url(#sideGradient)" stroke="black" stroke-width="4" />
                <polygon points="12,32 12,48 32,64 32,48" fill="none" stroke="white" stroke-width="2" />

                <polygon points="52,32 52,48 32,64 32,48" fill="url(#sideGradient)" stroke="black" stroke-width="4" />
                <polygon points="52,32 52,48 32,64 32,48" fill="none" stroke="white" stroke-width="2" />

                <!-- Tapa izquierda abierta con doble contorno -->
                <polygon points="12,32 0,24 16,12 32,16" fill="url(#lidGradient)" stroke="black" stroke-width="4" />
                <polygon points="12,32 0,24 16,12 32,16" fill="none" stroke="white" stroke-width="2" />

                <!-- Tapa derecha abierta con doble contorno -->
                <polygon points="52,32 64,24 48,12 32,16" fill="url(#lidGradient)" stroke="black" stroke-width="4" />
                <polygon points="52,32 64,24 48,12 32,16" fill="none" stroke="white" stroke-width="2" />

                <!-- Brillo en la caja -->
                <circle cx="42" cy="20" r="4" fill="rgba(255, 255, 255, 0.7)" />
                <circle cx="22" cy="22" r="3" fill="rgba(255, 255, 255, 0.5)" />

                <!-- Definición de degradados -->
                <defs>
                  <linearGradient id="boxGradient" x1="12" y1="16" x2="52" y2="48" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#00FF00" />
                    <stop offset="100%" stop-color="#00C853" />
                  </linearGradient>
                  <linearGradient id="lidGradient" x1="0" y1="12" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#69F0AE" />
                    <stop offset="100%" stop-color="#00E676" />
                  </linearGradient>
                  <linearGradient id="sideGradient" x1="12" y1="32" x2="32" y2="64" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#00E676" />
                    <stop offset="100%" stop-color="#00C853" />
                  </linearGradient>
                </defs>
              </svg>
              Productos</Link>
            </li>

            <li>
              <Link :href="route('admin.promo.index')"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
              <svg width="60" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <title>Etiqueta de Descuento con Bordes Doble</title>
                <!-- Sombra -->
                <ellipse cx="29" cy="54" rx="22" ry="6" fill="rgba(0, 0, 0, 0.3)" />

                <defs>
                  <!-- Degradado para la etiqueta -->
                  <linearGradient id="gradTag" x1="5" y1="5" x2="55" y2="55" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#FF4081" />
                    <stop offset="100%" stop-color="#FF1744" />
                  </linearGradient>
                </defs>

                <!-- Cuerpo de la etiqueta con doble contorno -->
                <path d="M10 20 L40 10 L55 25 L30 50 L10 40 Z" fill="url(#gradTag)" stroke="black" stroke-width="4" />
                <path d="M10 20 L40 10 L55 25 L30 50 L10 40 Z" fill="none" stroke="white" stroke-width="2" />

                <!-- Agujero de la etiqueta -->
                <circle cx="50" cy="20" r="6" fill="white" stroke="black" stroke-width="3" />
                <circle cx="50" cy="20" r="2" fill="white" />

                <!-- Símbolo de porcentaje -->
                <text x="18" y="38" font-size="25" fill="white" class="stroke-black dark:stroke-gray-100"
                  stroke-width="1">%</text>

                <!-- Gancho superior -->
                <path d="M50 10 C55 0, 65 10, 55 20" stroke="black" stroke-width="4" fill="none" />
                <path d="M50 10 C55 0, 65 10, 55 20" stroke="white" stroke-width="2" fill="none" />

              </svg>
              Promoción</Link>
            </li>
            <!--Vista para el encargado de inventarios-->
            <li>
              <Link :href="route('admin.inventarios.index')"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
              <svg width="64" height="60" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <title>Icono de Inventario 3D - Contorno Blanco y Negro</title>
                <!-- Sombra -->
                <ellipse cx="32" cy="58" rx="22" ry="6" fill="rgba(0, 0, 0, 0.3)" />
                <defs>
                  <!-- Degradado para la caja -->
                  <linearGradient id="gradBox" x1="10" y1="10" x2="50" y2="50" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#FFCA28" />
                    <stop offset="100%" stop-color="#FF9800" />
                  </linearGradient>
                  <!-- Degradado para la tapa -->
                  <linearGradient id="gradLid" x1="5" y1="5" x2="40" y2="20" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#FFA726" />
                    <stop offset="100%" stop-color="#EF6C00" />
                  </linearGradient>
                  <!-- Degradado para las partes de color plomo -->
                  <linearGradient id="gradPlomo" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#CFD8DC" />
                    <stop offset="100%" stop-color="#B0BEC5" />
                  </linearGradient>
                  <!-- Sombra -->
                  <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                    <feDropShadow dx="2" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.5)" />
                  </filter>
                </defs>

                <!-- Base de la caja -->
                <path d="M12 24 L32 12 L52 24 L32 36 Z" fill="url(#gradBox)" stroke="black" stroke-width="4" />
                <path d="M12 24 L32 12 L52 24 L32 36 Z" fill="none" stroke="white" stroke-width="2" />

                <!-- Tapa de la caja -->
                <path d="M10 20 L32 8 L54 20 L32 32 Z" fill="url(#gradLid)" stroke="black" stroke-width="4" />
                <path d="M10 20 L32 8 L54 20 L32 32 Z" fill="none" stroke="white" stroke-width="2" />

                <!-- Sombra interna de la caja -->
                <path d="M12 24 L32 36 L32 56 L12 44 Z" fill="rgba(0,0,0,0.3)" stroke="black" stroke-width="4" />
                <path d="M12 24 L32 36 L32 56 L12 44 Z" fill="none" stroke="white" stroke-width="2" />

                <path d="M52 24 L32 36 L32 56 L52 44 Z" fill="rgba(0,0,0,0.2)" stroke="black" stroke-width="4" />
                <path d="M52 24 L32 36 L32 56 L52 44 Z" fill="none" stroke="white" stroke-width="2" />

                <!-- Código de barras (Color plomo con doble contorno) -->
                <rect x="18" y="30" width="4" height="10" fill="url(#gradPlomo)" stroke="black" stroke-width="4" />
                <rect x="18" y="30" width="4" height="10" fill="none" stroke="white" stroke-width="2" />

                <rect x="24" y="30" width="2" height="10" fill="url(#gradPlomo)" stroke="black" stroke-width="4" />
                <rect x="24" y="30" width="2" height="10" fill="none" stroke="white" stroke-width="2" />

                <rect x="28" y="30" width="3" height="10" fill="url(#gradPlomo)" stroke="black" stroke-width="4" />
                <rect x="28" y="30" width="3" height="10" fill="none" stroke="white" stroke-width="2" />

                <rect x="34" y="30" width="5" height="10" fill="url(#gradPlomo)" stroke="black" stroke-width="4" />
                <rect x="34" y="30" width="5" height="10" fill="none" stroke="white" stroke-width="2" />

                <!-- Líneas laterales para mayor detalle (doble contorno) -->
                <path d="M32 12 V36" stroke="black" stroke-width="4" />
                <path d="M32 12 V36" stroke="white" stroke-width="2" />
              </svg>
              Inventario</Link>
            </li>
            <li>
              <Link :href="route('admin.servicios.index')"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
              <svg width="60" height="60" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <title>Maletín Médico 3D con Jeringa</title>
                <!-- Sombra -->
                <ellipse cx="33" cy="54" rx="22" ry="6" fill="rgba(0, 0, 0, 0.3)" />
                <defs>
                  <!-- Degradado para el maletín -->
                  <linearGradient id="gradBag" x1="10" y1="10" x2="50" y2="50" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#B3E5FC" />
                    <stop offset="100%" stop-color="#29B6F6" />
                  </linearGradient>
                  <!-- Degradado para la jeringa -->
                  <linearGradient id="gradSyringe" x1="20" y1="10" x2="50" y2="40" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#FFEB3B" />
                    <stop offset="100%" stop-color="#FBC02D" />
                  </linearGradient>
                  <!-- Degradado para la aguja -->
                  <linearGradient id="gradNeedle" x1="30" y1="2" x2="34" y2="10" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#B0BEC5" />
                    <stop offset="100%" stop-color="#78909C" />
                  </linearGradient>
                  <!-- Sombra -->
                  <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                    <feDropShadow dx="2" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.5)" />
                  </filter>
                </defs>

                <!-- Cuerpo del maletín -->
                <rect x="10" y="18" width="44" height="30" rx="5" fill="url(#gradBag)" stroke="black" stroke-width="4"
                  filter="url(#shadow)" />
                <rect x="10" y="18" width="44" height="30" rx="5" fill="none" stroke="white" stroke-width="2" />

                <!-- Asa del maletín -->
                <rect x="24" y="10" width="16" height="8" rx="3" fill="url(#gradSyringe)" stroke="black"
                  stroke-width="3" filter="url(#shadow)" />
                <rect x="24" y="10" width="16" height="8" rx="3" fill="none" stroke="white" stroke-width="2" />

                <!-- Cuerpo de la jeringa (Más larga) -->
                <rect x="24" y="22" width="16" height="18" rx="3" fill="url(#gradSyringe)" stroke="black"
                  stroke-width="3" filter="url(#shadow)" />
                <rect x="24" y="22" width="16" height="18" rx="3" fill="none" stroke="white" stroke-width="2" />

                <!-- Líneas de medida de la jeringa -->
                <path d="M26 26 H34" stroke="black" stroke-width="2" />
                <path d="M26 30 H34" stroke="black" stroke-width="2" />
                <path d="M26 34 H34" stroke="black" stroke-width="2" />
                <path d="M26 38 H34" stroke="black" stroke-width="2" />

                <!-- Émbolo de la jeringa -->
                <rect x="29" y="18" width="6" height="6" fill="url(#gradSyringe)" stroke="black" stroke-width="2" />
                <rect x="29" y="18" width="6" height="6" fill="none" stroke="white" stroke-width="1" />

                <!-- Aguja de la jeringa -->
                <path d="M32 12 L32 18" stroke="black" stroke-width="3" filter="url(#shadow)" />
                <path d="M32 12 L32 18" stroke="white" stroke-width="2" />
                <circle cx="32" cy="11" r="2" fill="url(#gradNeedle)" stroke="black" stroke-width="1" />
              </svg>
              Servicios</Link>
            </li>

            <li>
              <Link :href="route('almacen.categoria.index')"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
              <svg width="64" height="60" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <title>Carpeta 3D - Almacén (Amarillo y Verde Fosforescente)</title>
                <!-- Sombra -->
                <ellipse cx="32" cy="57" rx="22" ry="6" fill="rgba(0, 0, 0, 0.3)" />
                <defs>
                  <!-- Degradado para la carpeta -->
                  <linearGradient id="gradFolder" x1="5" y1="5" x2="55" y2="50" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#FFEB3B" />
                    <stop offset="100%" stop-color="#FBC02D" />
                  </linearGradient>
                  <!-- Degradado para la pestaña (verde fosforescente) -->
                  <linearGradient id="gradTab" x1="5" y1="5" x2="30" y2="15" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#B2FF59" />
                    <stop offset="100%" stop-color="#76FF03" />
                  </linearGradient>
                  <!-- Sombra -->
                  <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                    <feDropShadow dx="2" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.5)" />
                  </filter>
                </defs>

                <!-- Cuerpo de la carpeta -->
                <path d="M8 20 L56 20 L56 50 L8 50 Z" fill="url(#gradFolder)" stroke="black" stroke-width="4"
                  filter="url(#shadow)" />
                <path d="M8 20 L56 20 L56 50 L8 50 Z" fill="none" stroke="white" stroke-width="2" />

                <!-- Pestaña superior de la carpeta -->
                <path d="M8 20 L26 10 L36 10 L36 20 Z" fill="url(#gradTab)" stroke="black" stroke-width="4"
                  filter="url(#shadow)" />
                <path d="M8 20 L26 10 L36 10 L36 20 Z" fill="none" stroke="white" stroke-width="2" />

                <!-- Detalles internos de almacenamiento (simulación de documentos) -->
                <rect x="12" y="28" width="40" height="4" fill="black" />
                <rect x="12" y="36" width="30" height="4" fill="black" />
                <rect x="12" y="44" width="20" height="4" fill="black" />

                <!-- Sombras internas -->
                <path d="M8 20 L56 50 L56 50 L8 50 Z" fill="rgba(0,0,0,0.2)" />
              </svg>
              Cat.Alm.
              </Link>
            </li>
            <li>
              <Link :href="route('index.reporte')"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
              <svg width="60" height="60" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <title>Carta de Correo 3D con Símbolo @</title>
                <!-- Sombra -->
                <ellipse cx="32" cy="57" rx="25" ry="6" fill="rgba(0, 0, 0, 0.3)" />
                <defs>
                  <!-- Degradado para el sobre -->
                  <linearGradient id="gradEnvelope" x1="5" y1="5" x2="55" y2="50" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#FFEB3B" />
                    <stop offset="100%" stop-color="#FBC02D" />
                  </linearGradient>
                  <!-- Degradado para la solapa -->
                  <linearGradient id="gradFlap" x1="5" y1="5" x2="30" y2="15" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#B2FF59" />
                    <stop offset="100%" stop-color="#76FF03" />
                  </linearGradient>
                  <!-- Sombra -->
                  <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                    <feDropShadow dx="3" dy="3" stdDeviation="3" flood-color="rgba(0,0,0,0.5)" />
                  </filter>
                </defs>

                <!-- Cuerpo del sobre con doble contorno -->
                <path d="M8 20 L56 20 L56 50 L8 50 Z" fill="url(#gradEnvelope)" stroke="black" stroke-width="4"
                  filter="url(#shadow)" />
                <path d="M8 20 L56 20 L56 50 L8 50 Z" fill="none" stroke="white" stroke-width="2" />

                <!-- Detalles internos (líneas del pliegue de la carta) -->
                <path d="M8 20 L32 38 L56 20" stroke="black" stroke-width="3" />
                <path d="M8 20 L32 38 L56 20" stroke="white" stroke-width="1.5" />

                <!-- Símbolo "@" centrado con doble contorno -->
                <circle cx="32" cy="38" r="10" fill="#76FF03" stroke="black" stroke-width="4" filter="url(#shadow)" />
                <circle cx="32" cy="38" r="10" fill="none" stroke="white" stroke-width="2" />

                <text x="24" y="44" font-size="16px" font-weight="bold" fill="black">@</text>
              </svg>
              Reportes
              </Link>
            </li>
          </ul>
        </li>
      </ul>

      <ul class="pt-5 mt-5 space-y-2 border-t border-gray-200 dark:border-gray-700">
        <li>
          <Link :href="route('admin.dashboard')"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg transition duration-75 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white group">
          <svg width="55" height="50" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <title>Estadísticas 3D con Doble Contorno</title>
            <defs>
              <!-- Degradado para las barras -->
              <linearGradient id="gradBar1" x1="8" y1="32" x2="16" y2="64" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#26C6DA" />
                <stop offset="100%" stop-color="#0288D1" />
              </linearGradient>
              <linearGradient id="gradBar2" x1="24" y1="20" x2="32" y2="64" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#FFA726" />
                <stop offset="100%" stop-color="#FB8C00" />
              </linearGradient>
              <linearGradient id="gradBar3" x1="40" y1="12" x2="48" y2="64" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#66BB6A" />
                <stop offset="100%" stop-color="#2E7D32" />
              </linearGradient>
              <!-- Sombra -->
              <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
                <feDropShadow dx="2" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.4)" />
              </filter>
            </defs>

            <!-- Fondo con sombra -->
            <rect x="4" y="4" width="56" height="56" rx="8" fill="white" stroke="black" stroke-width="4"
              filter="url(#shadow)" />
            <rect x="4" y="4" width="56" height="56" rx="8" fill="none" stroke="white" stroke-width="2" />

            <!-- Línea base -->
            <line x1="8" y1="56" x2="56" y2="56" stroke="black" stroke-width="4" />
            <line x1="8" y1="56" x2="56" y2="56" stroke="white" stroke-width="2" />

            <!-- Barras de estadísticas con doble contorno -->
            <rect x="10" y="32" width="12" height="24" fill="url(#gradBar1)" stroke="black" stroke-width="4" />
            <rect x="10" y="32" width="12" height="24" fill="none" stroke="white" stroke-width="2" />

            <rect x="26" y="20" width="12" height="36" fill="url(#gradBar2)" stroke="black" stroke-width="4" />
            <rect x="26" y="20" width="12" height="36" fill="none" stroke="white" stroke-width="2" />

            <rect x="42" y="12" width="12" height="44" fill="url(#gradBar3)" stroke="black" stroke-width="4" />
            <rect x="42" y="12" width="12" height="44" fill="none" stroke="white" stroke-width="2" />

            <!-- Sombra interna para efecto 3D -->
            <rect x="10" y="40" width="12" height="16" fill="rgba(0,0,0,0.2)" />
            <rect x="26" y="28" width="12" height="28" fill="rgba(0,0,0,0.2)" />
            <rect x="42" y="20" width="12" height="36" fill="rgba(0,0,0,0.2)" />
          </svg>

          <span class="ml-3">Estadísticas</span>
          </Link>
        </li>
      </ul>
    </div>
  </aside>
</template>