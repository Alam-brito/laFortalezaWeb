<script setup>
import { usePage, useForm, router } from "@inertiajs/vue3";
import { computed, ref, watch, onMounted, onUnmounted } from "vue";
import UserLayouts from "./Layouts/UserLayouts.vue";
import Hero from "./Layouts/Hero.vue";
import Swal from 'sweetalert2';

defineProps(['count', 'totalPages']); // Añadido totalPages para paginación
const productos = computed(() => usePage().props.productos);
const currentPage = ref(1);
const isLoading = ref(false);

// Modo de visualización (grid o list)
const viewMode = ref('grid');

// Carrito
const carrito = ref([]);
// Animación de añadido al carrito
const cartAnimation = ref(false);
const lastAddedProduct = ref(null);

//Constante para la animación del carrito
const showCartAnimation = ref(false);

const startCartAnimation = () => {
    showCartAnimation.value = true;

    // La animación dura 3 segundos antes de hacer el checkout real
    setTimeout(() => {
        showCartAnimation.value = false;
        checkout(); // Esta es tu función original de checkout
    }, 3000);
};

// Formulario de Inertia
const form = useForm({
    productos: [],
});

// Añadir producto al carrito con animación
const addToCar = (producto) => {
    const index = carrito.value.findIndex((item) => item.id === producto.id);
    const descuento = producto.productos_descuentos || 0;
    const subtotalConDescuento = producto.precio - (producto.precio * (descuento / 100));

    if (index === -1) {
        carrito.value.push({
            ...producto,
            cantidad: 1,
            subtotal: subtotalConDescuento
        });
    } else {
        carrito.value[index].cantidad += 1;
        carrito.value[index].subtotal = carrito.value[index].cantidad * (producto.precio - (producto.precio * (descuento / 100)));
    }

    // Animar el carrito
    lastAddedProduct.value = producto;
    cartAnimation.value = true;
    setTimeout(() => {
        cartAnimation.value = false;
    }, 1000);

    // Si hay productos en el carrito, muestra el modal flotante
    mostrarModalCarrito.value = carrito.value.length > 0;
};

// Detectar si se vacía el carrito y ocultar el modal
watch(carrito, (newValue) => {
    mostrarModalCarrito.value = newValue.length > 0;
}, { deep: true });

// Reducir cantidad de producto en el carrito
const decreaseQuantity = (productoId) => {
    const index = carrito.value.findIndex((item) => item.id === productoId);
    if (index !== -1 && carrito.value[index].cantidad > 1) {
        carrito.value[index].cantidad -= 1;
        const descuento = carrito.value[index].productos_descuentos || 0;
        carrito.value[index].subtotal = carrito.value[index].cantidad * (carrito.value[index].precio - (carrito.value[index].precio * (descuento / 100)));
    }
};

// Eliminar producto del carrito
const removeFromCar = (productoId) => {
    carrito.value = carrito.value.filter((item) => item.id !== productoId);
};

// Enviar el carrito al backend
const checkout = () => {
    form.productos = carrito.value;

    form.post(route('user.compra'), {
        onSuccess: () => {
            Swal.fire({
                title: '¡Éxito!',
                text: 'Añadido al carrito exitosamente, ¡Gracias por tu preferencia!',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                timer: 4000,
                timerProgressBar: true,
            });
        },
        onError: () => {
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al procesar la solicitud.',
                icon: 'error',
                confirmButtonText: 'Aceptar',
            });
        },
    });
};

// Buscador
const search = ref("");
watch(search, (newValue) => {
    const url = new URL(route('user.home'));
    url.searchParams.set('search', newValue);
    url.searchParams.set('page', 1); // Reset to page 1 when searching

    // Enviar la solicitud al backend usando Inertia.js
    router.visit(url.toString(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
});

// Resaltar el resultado de la búsqueda
const highlightMatch = (text, searchTerm) => {
    if (!searchTerm) return text; // Si no hay búsqueda, devuelve el texto completo.
    const regex = new RegExp(`(${searchTerm})`, 'gi'); // Crear una expresión regular para las coincidencias.
    return text.replace(regex, '<span class="bg-yellow-400">$1</span>'); // Envuelve las coincidencias con un fondo amarillo.
};

// Para el punto flotante del carrito
const mostrarModalCarrito = ref(false);

const irAlCarrito = () => {
    const carritoSection = document.getElementById("carritoSection");
    if (carritoSection) {
        carritoSection.scrollIntoView({ behavior: "smooth" });
    }
};

// Implementación de carga infinita / paginación
const loadMoreProducts = () => {
    if (isLoading.value) return;

    const totalPages = usePage().props.totalPages || 1;
    if (currentPage.value >= totalPages) return;

    isLoading.value = true;
    currentPage.value++;

    const url = new URL(window.location.href);
    url.searchParams.set('page', currentPage.value);

    router.visit(url.toString(), {
        preserveScroll: true,
        preserveState: true,
        only: ['productos'],
        onSuccess: () => {
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
            currentPage.value--;
        }
    });
};

// Detectar scroll para carga infinita
const handleScroll = () => {
    // Si estamos cerca del final de la página, cargar más productos
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
        loadMoreProducts();
    }
};

// Configurar listener de scroll al montar y limpiar al desmontar
onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

// Cambiar de página (para paginación manual)
const changePage = (page) => {
    if (page < 1 || page > usePage().props.totalPages) return;

    currentPage.value = page;
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);

    router.visit(url.toString(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <UserLayouts>
        <!--Carrito animado para realizar la compra-->
        <div v-if="showCartAnimation" class="cart-animation-container">
            <div class="cart-animation">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="cart-icon">
                    <path
                        d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                <div class="cart-items">
                    <div class="cart-item" v-for="(_, index) in 3" :key="index"></div>
                </div>
                <div class="cart-wheels">
                    <div class="wheel"></div>
                    <div class="wheel"></div>
                </div>
            </div>
        </div>

        <!-- Modal flotante del carrito con animación -->
        <transition name="bounce">
            <div v-if="carrito.length > 0" class="fixed bottom-10 right-10 z-50">
                <button @click="irAlCarrito"
                    class="relative inline-flex items-center p-3 text-sm font-medium text-gray-900 bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-400 dark:text-gray-100 dark:bg-blue-700 dark:hover:bg-blue-800 shadow-lg transform transition-all duration-300 hover:scale-110"
                    :class="{ 'animate-pulse': cartAnimation }">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                    <div class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-gray-300 dark:border-gray-700 rounded-full -top-2 -end-2"
                        :class="{ 'animate-bounce': cartAnimation }">
                        {{carrito.reduce((acc, item) => acc + item.cantidad, 0)}}
                    </div>
                </button>
            </div>
        </transition>

        <!-- Animación de producto añadido al carrito -->
        <transition name="product-added">
            <div v-if="cartAnimation && lastAddedProduct"
                class="fixed top-5 right-5 z-50 bg-green-100 dark:bg-green-800 border border-green-400 text-green-700 dark:text-green-200 px-4 py-3 rounded flex items-center shadow-lg">
                <img :src="lastAddedProduct.imagen" :alt="lastAddedProduct.nombre"
                    class="w-10 h-10 object-cover rounded mr-3">
                <div>
                    <p class="font-bold text-sm">¡Añadido al carrito!</p>
                    <p class="text-xs">{{ lastAddedProduct.nombre }}</p>
                </div>
            </div>
        </transition>

        <div class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-300">

            <!--Sección-->
            <Hero></Hero>
            <!--Fin-->
            <!-- Banner promocional (similar a Farmacorp) -->
            <div class="bg-green-400 text-white py-2 px-4 text-center text-3xl font-bold">
                ¡Aprovecha las promociones carnavaleras! 🎁 🚀
            </div>
            <div class="bg-gray-100 dark:bg-gray-800">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <!-- Encabezado con contador de productos y nombre -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-200">
                            Productos <span class="text-gray-500 dark:text-gray-400 text-lg font-normal">({{
                                productos.length }} productos)</span>
                        </h2>

                        <!-- Controles de visualización -->
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-700 dark:text-gray-300 mr-2">Ordenar por</span>
                            <div class="flex border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                <button @click="viewMode = 'grid'"
                                    :class="[viewMode === 'grid' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300', 'p-2']">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                    </svg>
                                </button>
                                <button @click="viewMode = 'list'"
                                    :class="[viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300', 'p-2']">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Vista de productos en grid -->
                    <div v-if="viewMode === 'grid'"
                        class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <div v-for="producto in productos" :key="producto.id"
                            class="group relative p-2 border border-gray-200 dark:border-gray-700 rounded-lg shadow bg-white dark:bg-gray-800 
                            transition-all duration-300 hover:shadow-lg hover:border-blue-300 hover:scale-105 hover:z-10">
                            <div class="relative w-full h-36 overflow-hidden rounded-md">
                                <img :src="producto.imagen" :alt="producto.nombre"
                                    class="object-cover w-full h-full group-hover:opacity-90 transition-opacity duration-300" />
                            </div>

                            <!-- Badge de descuento (si tiene) -->
                            <div v-if="producto.productos_descuentos > 0"
                                class="absolute top-1 left-1 bg-green-500 text-white px-1.5 py-0.5 rounded-full text-xs font-semibold">
                                -{{ producto.productos_descuentos }}%
                            </div>

                            <!-- Botón de Añadir al Carrito -->
                            <div class="absolute top-1 right-1">
                                <button @click="addToCar(producto)"
                                    class="bg-green-500 p-1.5 rounded-full transition-all duration-300 transform hover:scale-110 active:scale-95 active:bg-green-700 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-white">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-2 flex flex-col">
                                <h3 class="text-xs font-medium text-gray-800 dark:text-gray-200 line-clamp-2 h-8">
                                    <span v-html="highlightMatch(producto.nombre, search)"></span>
                                </h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2 h-8">
                                    <span v-html="highlightMatch(producto.descripcion, search)"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Vista de productos en lista -->
                    <div v-else class="space-y-3">
                        <div v-for="producto in productos" :key="producto.id"
                            class="group relative flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow bg-white dark:bg-gray-800 
                            transition-all duration-300 hover:shadow-lg hover:border-blue-300 hover:scale-102 hover:z-10">

                            <!-- Imagen del producto -->
                            <div class="relative w-20 h-20 sm:w-24 sm:h-24 overflow-hidden rounded-md flex-shrink-0">
                                <img :src="producto.imagen" :alt="producto.nombre"
                                    class="object-cover w-full h-full group-hover:opacity-90 transition-opacity duration-300" />

                                <!-- Badge de descuento (si tiene) -->
                                <div v-if="producto.productos_descuentos > 0"
                                    class="absolute top-0 left-0 bg-green-500 text-white px-1.5 py-0.5 text-xs font-semibold">
                                    -{{ producto.productos_descuentos }}%
                                </div>
                            </div>

                            <!-- Información del producto -->
                            <div class="ml-4 flex-grow">
                                <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    <span v-html="highlightMatch(producto.nombre, search)"></span>
                                </h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                    <span v-html="highlightMatch(producto.descripcion, search)"></span>
                                </p>
                            </div>

                            <!-- Botón de Añadir al Carrito -->
                            <div class="ml-4">
                                <button @click="addToCar(producto)"
                                    class="bg-green-500 p-2 rounded-full transition-all duration-300 transform hover:scale-110 active:scale-95 active:bg-green-700 shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-white">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Paginación manual (alternativa a la carga infinita) -->
                    <div class="mt-8 flex justify-center">
                        <nav class="inline-flex rounded-md shadow-sm" aria-label="Paginación">
                            <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                                class="px-3 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200"
                                :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }">
                                <span class="sr-only">Anterior</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                class="px-4 py-2 border-t border-b border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                Página {{ currentPage }} de {{ usePage().props.totalPages || 1 }}
                            </div>

                            <button @click="changePage(currentPage + 1)"
                                :disabled="currentPage >= (usePage().props.totalPages || 1)"
                                class="px-3 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200"
                                :class="{ 'opacity-50 cursor-not-allowed': currentPage >= (usePage().props.totalPages || 1) }">
                                <span class="sr-only">Siguiente</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </nav>
                    </div>

                    <!-- Indicador de carga para la paginación infinita -->
                    <div v-if="isLoading" class="mt-8 flex justify-center">
                        <div class="animate-pulse flex space-x-4">
                            <div class="flex-1 space-y-4 py-1">
                                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-3/4 mx-auto"></div>
                                <div class="space-y-2">
                                    <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
                                    <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-5/6"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Lista del carrito-->
                <div class="mt-10 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <!-- Encabezado con contador -->
                    <div
                        class="flex justify-between items-center border-b pb-2 mb-4 border-gray-300 dark:border-gray-700">
                        <button type="button"
                            class="relative inline-flex items-center p-3 text-sm font-medium text-gray-900 bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-400 dark:text-gray-100 dark:bg-blue-700 dark:hover:bg-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                            <div
                                class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-gray-300 dark:border-gray-700 rounded-full -top-2 -end-2">
                                {{carrito.reduce((acc, item) => acc + item.cantidad, 0)}}
                            </div>
                        </button>
                    </div>
                    <!-- Contenido del carrito -->
                    <div v-if="carrito.length === 0" class="text-gray-500 dark:text-gray-400 text-center">
                        Tu carrito está vacío.
                    </div>
                    <!-- Contenido del carrito -->
                    <div id="carritoSection" v-else class="p-4 md:p-6">
                        <!-- Lista de productos -->
                        <div v-for="item in carrito" :key="item.id"
                            class="flex flex-col sm:flex-row items-center justify-between py-4 border-b border-gray-300 dark:border-gray-700 space-y-4 sm:space-y-0">

                            <!-- Imagen y Nombre del Producto -->
                            <div class="flex items-center space-x-4 w-full sm:w-auto">
                                <img :src="item.imagen" alt="Producto"
                                    class="h-16 w-16 sm:h-20 sm:w-20 md:h-24 md:w-24 object-cover rounded-md" />
                                <div class="text-center sm:text-left">
                                    <h3 class="text-sm md:text-base font-medium text-gray-900 dark:text-gray-200">{{
                                        item.nombre }}</h3>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Cantidad: {{
                                        item.cantidad }}</p>
                                </div>
                                <!--Para ver la categoria de cada producto-->
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                    Categoria: {{ item.categoria_nombre }}
                                </p>
                            </div>
                            <!-- Precio y Subtotal -->
                            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                                <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">Bs. {{ item.precio }}
                                </p>
                                <p class="font-semibold text-gray-900 dark:text-gray-200 text-sm sm:text-base">Subtotal:
                                    Bs. {{ item.subtotal }}</p>
                            </div>

                            <!-- Botones de cantidad y eliminación -->
                            <div class="flex space-x-2">
                                <button @click="decreaseQuantity(item.id)"
                                    class="bg-gray-300 dark:bg-gray-700 px-3 py-2 rounded text-xs sm:text-sm text-gray-800 dark:text-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                    </svg>
                                </button>
                                <button @click="removeFromCar(item.id)" type="button"
                                    class="bg-red-500 text-white px-4 sm:px-5 py-2.5 rounded-lg hover:bg-red-600 text-xs sm:text-sm">
                                    Eliminar
                                </button>
                            </div>
                        </div>

                        <!-- Total del carrito -->
                        <div class="flex justify-between mt-4 text-sm sm:text-base">
                            <p class="font-semibold text-gray-900 dark:text-gray-200">Total:</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-200">
                                Bs. {{carrito.reduce((acc, item) => acc + item.subtotal, 0)}}
                            </p>
                        </div>

                        <!-- Botón de pago -->
                        <div class="mt-6 text-center sm:text-left">
                            <button
                                class="bg-green-600 text-gray-100 px-6 py-2 rounded-lg hover:bg-green-700 transition text-sm sm:text-base w-full sm:w-auto"
                                @click="startCartAnimation">
                                Realizar Compra
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <p>Total de visitas: {{ count }}</p>
        </div>
    </UserLayouts>
</template>

<style scoped>
/* Animación de entrada y salida */
.bounce-enter-active {
    animation: bounceIn 0.5s cubic-bezier(0.215, 0.610, 0.355, 1.000);
    will-change: transform, opacity;
}

.bounce-leave-active {
    animation: fadeOut 0.3s cubic-bezier(0.215, 0.610, 0.355, 1.000) forwards;
    will-change: transform, opacity;
}

/* Animación de entrada (rebote) con rebote secundario */
@keyframes bounceIn {
    0% {
        transform: translate3d(0, 100px, 0);
        opacity: 0;
    }

    60% {
        transform: translate3d(0, -15px, 0);
        opacity: 1;
    }

    80% {
        transform: translate3d(0, 5px, 0);
    }

    100% {
        transform: translate3d(0, 0, 0);
    }
}

/* Animación de salida (desvanecimiento) mejorada */
@keyframes fadeOut {
    0% {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    100% {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
}

/* Variante para animación horizontal (opcional) */
.bounce-horizontal-enter-active {
    animation: bounceInHorizontal 0.5s cubic-bezier(0.215, 0.610, 0.355, 1.000);
    will-change: transform, opacity;
}

.bounce-horizontal-leave-active {
    animation: fadeOutHorizontal 0.3s cubic-bezier(0.215, 0.610, 0.355, 1.000) forwards;
    will-change: transform, opacity;
}

@keyframes bounceInHorizontal {
    0% {
        transform: translate3d(100px, 0, 0);
        opacity: 0;
    }

    60% {
        transform: translate3d(-15px, 0, 0);
        opacity: 1;
    }

    80% {
        transform: translate3d(5px, 0, 0);
    }

    100% {
        transform: translate3d(0, 0, 0);
    }
}

@keyframes fadeOutHorizontal {
    0% {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    100% {
        opacity: 0;
        transform: translate3d(20px, 0, 0);
    }
}

.cart-animation-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    background-color: rgba(0, 0, 0, 0.7);
}

.cart-animation {
    position: relative;
    width: 120px;
    height: 100px;
    animation: driveCart 3s ease-in-out forwards;
}

.cart-icon {
    width: 100%;
    height: 100%;
    fill: none;
    stroke: white;
    stroke-width: 1.5;
    animation: cartBounce 0.5s ease-in-out infinite alternate;
}

.cart-items {
    position: absolute;
    top: 20%;
    left: 30%;
    width: 40%;
    height: 40%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-around;
}

.cart-item {
    width: 15px;
    height: 15px;
    background-color: #4ade80;
    border-radius: 50%;
    margin: 2px;
    animation: itemBounce 0.3s ease-in-out infinite alternate;
    animation-delay: calc(var(--i, 0) * 0.1s);
}

.cart-item:nth-child(1) {
    --i: 0;
}

.cart-item:nth-child(2) {
    --i: 1;
}

.cart-item:nth-child(3) {
    --i: 2;
}

.cart-wheels {
    position: absolute;
    bottom: 5px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 20px;
}

.wheel {
    width: 15px;
    height: 15px;
    background-color: #f59e0b;
    border-radius: 50%;
    border: 2px solid white;
    animation: wheelSpin 0.5s linear infinite;
}

@keyframes driveCart {
    0% {
        transform: translateX(-100vw) scale(0.8);
    }

    40% {
        transform: translateX(0) scale(1.2);
    }

    60% {
        transform: translateX(0) scale(1.2);
    }

    100% {
        transform: translateX(100vw) scale(0.8);
    }
}

@keyframes cartBounce {
    0% {
        transform: translateY(0);
    }

    100% {
        transform: translateY(-5px);
    }
}

@keyframes itemBounce {
    0% {
        transform: translateY(0);
    }

    100% {
        transform: translateY(-3px);
    }
}

@keyframes wheelSpin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>