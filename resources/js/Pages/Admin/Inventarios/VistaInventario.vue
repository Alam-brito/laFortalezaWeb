<script setup>
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Inertia } from '@inertiajs/inertia';
const inventarios = usePage().props.inventarios;
const form = useForm({});
const successMessage = ref(''); // Estado para el mensaje de éxito

// Función para manejar el envío del formulario
const submit = (id) => {
    form.delete(route('admin.inventario.delete', id), {
        onSuccess: () => {
            //location.reload(); // Forzar recarga de la página  
            // Mensaje de éxito
            successMessage.value = 'Inventario borrado correctamente.';
            // Ocultar el mensaje después de unos segundos
            setTimeout(() => {
                successMessage.value = '';
            }, 2000);
            Inertia.reload();
        },
    });
};

//Buscador
const search = ref("");
watch(search, (newValue) => {
    const url = new URL(route('admin.inventarios.index')); // Asegúrate de que la ruta esté definida correctamente
    url.searchParams.set('search', newValue);

    // Enviar la solicitud al backend usando Inertia.js
    router.visit(url.toString(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
});
//Resaltar el resutado de la búsqueda
const highlightMatch = (text, searchTerm) => {
    if (!searchTerm) return text; // Si no hay búsqueda, devuelve el texto completo.
    const regex = new RegExp(`(${searchTerm})`, 'gi'); // Crear una expresión regular para las coincidencias.
    return text.replace(regex, '<span class="bg-yellow-400">$1</span>'); // Envuelve las coincidencias con un fondo amarillo.
};
</script>

<template>
    <section class="flex flex-col md:flex-row min-h-screen bg-gray-100 dark:bg-gray-900 p-4 gap-4">
        <!-- Mostrar mensaje de éxito -->
        <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ successMessage }}
        </div>
        <div class="mx-auto max-w-screen-xl px-4 lg:px-12">
            <!-- Start coding here -->
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div
                    class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                    <div class="w-full md:w-1/2">
                        <form class="flex items-center">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                        fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input v-model="search" type="text" id="search"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Buscar">
                            </div>
                        </form>
                    </div>
                    <div
                        class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                        <button type="button"
                            class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">
                            <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewbox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                            </svg>
                            <Link :href="route('admin.inventarios.crear')">Ajustar inventario</Link>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-blue-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">Producto</th>
                                <th scope="col" class="px-4 py-3">Almacén</th>
                                <th scope="col" class="px-4 py-3">Stock</th>
                                <th scope="col" class="px-4 py-3">Tipo de Ajuste</th>
                                <th scope="col" class="px-4 py-3">Cantidad</th>
                                <th scope="col" class="px-4 py-3">Fecha de Actualización</th>
                                <th scope="col" class="px-4 py-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="inventario in inventarios" :key="inventario.detalle_id"
                                class="border-b dark:border-gray-700">
                                <td class="px-4 py-3">
                                    <span v-html="highlightMatch(inventario.producto_nombre, search)"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-html="highlightMatch(inventario.almacen_nombre, search)"></span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ inventario.stock }}
                                </td>
                                <td class="px-4 py-3">
                                    <span v-html="highlightMatch(inventario.tipo_ajuste, search)"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="inventario.tipo_ajuste == 'ingreso'">
                                        + {{ inventario.cantidad }} uds.
                                    </div>
                                    <div v-else>
                                        - {{ inventario.cantidad }} uds.
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ inventario.fecha_actualizacion }}</td>
                                <td class="px-4 py-3 flex items-center justify-end">
                                    <button :id="`dropdown-button-${inventario.detalle_id}`"
                                        :data-dropdown-toggle="`dropdown-${inventario.detalle_id}`"
                                        class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                                        type="button">
                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                        </svg>
                                    </button>
                                    <div :id="`dropdown-${inventario.detalle_id}`"
                                        class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                                        <div class="py-1">
                                            <PrimaryButton @click="submit(inventario.detalle_id)"
                                                class="w-full block bg-red-600 py-2 px-4 text-sm text-white-700 hover:bg-red-500 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-black">
                                                Eliminar
                                            </PrimaryButton>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</template>