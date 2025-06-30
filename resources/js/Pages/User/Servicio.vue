<script setup>
import { usePage, useForm, router } from "@inertiajs/vue3";
import { computed, ref, watch, onMounted } from "vue";
import UserLayouts from "./Layouts/UserLayouts.vue";
import Hero from "./Layouts/Hero.vue";
import Swal from 'sweetalert2';

defineProps(['count']);
const servicios = computed(() => usePage().props.servicios);
const search = computed(() => usePage().props.search);

// Carrito
const carrito = ref([]);

// Formulario de Inertia
const form = useForm({
    servicios: [],
});

// Añadir producto al carrito
const addToCar = (servicio) => {
    const index = carrito.value.findIndex((item) => item.id === servicio.id);

    if (index === -1) {
        // Nuevo servicio en el carrito
        carrito.value.push({
            ...servicio,
            cantidad: 1,
            subtotal: parseFloat(servicio.precio) || 0,
        });
    } else {
        // Si ya existe, solo aumenta la cantidad
        carrito.value[index].cantidad += 1;
        carrito.value[index].subtotal = carrito.value[index].cantidad * (parseFloat(servicio.precio) || 0);
    }

    sessionStorage.setItem("carrito", JSON.stringify(carrito.value)); // Guardar cambios en sessionStorage
};




const decreaseQuantity = (productoId) => {
    const index = carrito.value.findIndex((item) => item.id === productoId);
    if (index !== -1) {
        if (carrito.value[index].cantidad > 1) {
            carrito.value[index].cantidad -= 1;
            carrito.value[index].subtotal = carrito.value[index].cantidad * (parseFloat(carrito.value[index].precio) || 0);
        } else {
            // Si ya tiene 1 y se presiona disminuir, se elimina
            carrito.value.splice(index, 1);
        }
    }
    sessionStorage.setItem("carrito", JSON.stringify(carrito.value)); // Guardar cambios en sessionStorage
};



// Eliminar producto del carrito
const removeFromCar = (productoId) => {
    carrito.value = carrito.value.filter((item) => item.id !== productoId);
    sessionStorage.setItem("carrito", JSON.stringify(carrito.value)); // Guardar cambios en sessionStorage
};


//Resaltar el resutado de la búsqueda
const highlightMatch = (text, searchTerm) => {
    if (!searchTerm) return text; // Si no hay búsqueda, devuelve el texto completo.
    const regex = new RegExp(`(${searchTerm})`, 'gi'); // Crear una expresión regular para las coincidencias.
    return text.replace(regex, '<span class="bg-yellow-400">$1</span>'); // Envuelve las coincidencias con un fondo amarillo.
};

const irAOrdenServicio = () => {
    console.log("Servicios seleccionados antes de redirigir:", carrito.value); // DEPURAR

    router.get(route('user.orden_servicio'), { carrito: carrito.value });
};

</script>

<template>
    <UserLayouts>
        <div class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-300">
            <!--Sección-->
            <Hero></Hero>
            <!--Fin-->
            <div class="bg-gray-100 dark:bg-gray-800">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <h2
                        class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-200 text-center sm:text-left">
                        Lista de Servicios
                    </h2>
                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        <div v-for="servicio in servicios" :key="servicio.id" class="group relative p-4 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg bg-white dark:bg-gray-800 
                        transition-all duration-300 hover:shadow-2xl hover:scale-105 hover:-translate-y-2">
                            <div
                                class="relative w-full h-48 sm:h-56 bg-gray-300 dark:bg-gray-700 overflow-hidden rounded-md">
                                <img :src="servicio.imagen" :alt="servicio.nombre"
                                    class="object-cover w-full h-full group-hover:opacity-75 transition-opacity duration-300" />
                            </div>

                            <!-- Botón de Añadir al Carrito -->
                            <div class="absolute top-2 right-2 flex items-center justify-center">
                                <button @click="addToCar(servicio)"
                                    class="bg-green-500 p-3 rounded-full transition-all duration-300 transform hover:scale-110 active:scale-95 active:bg-green-700 shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-4 flex flex-col items-center sm:items-start">
                                <h3
                                    class="text-sm font-semibold text-gray-900 dark:text-gray-200 text-center sm:text-left">
                                    <span v-html="highlightMatch(servicio.nombre, search)"></span>
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 text-center sm:text-left">
                                    <span v-html="highlightMatch(servicio.descripcion, search)"></span>
                                </p>
                                <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-200">Bs. {{
                                    servicio.precio }}</p>
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
                                    d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                            </svg>
                            <div
                                class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-gray-300 dark:border-gray-700 rounded-full -top-2 -end-2">
                                {{ carrito.reduce((acc, item) => acc + item.cantidad, 0) }}
                            </div>
                        </button>
                    </div>
                    <!-- Contenido del carrito -->
                    <div v-if="carrito.length === 0" class="text-gray-500 dark:text-gray-400 text-center">
                        Sin servicios solicitados.
                    </div>
                    <!-- Contenido del carrito -->
                    <div v-else class="p-4 md:p-6">
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
                                Bs. {{ carrito.reduce((acc, item) => acc + item.subtotal, 0) }}
                            </p>
                        </div>

                        <!-- Botón de solicitud de servicio -->
                        <div class="mt-6 text-center sm:text-left">
                            <button
                                class="bg-green-600 text-gray-100 px-6 py-2 rounded-lg hover:bg-green-700 transition text-sm sm:text-base w-full sm:w-auto"
                                @click="irAOrdenServicio">
                                Solicitar servicio
                            </button>
                        </div>
                    </div>

                </div>
            </div>
            <p>Total de visitas: {{ count }}</p>
        </div>
    </UserLayouts>
</template>