<script setup>
import { ref } from 'vue';
import UserLayouts from './Layouts/UserLayouts.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

// Props enviados desde el backend
const props = defineProps({
    productos: Array,
    count: Number,
    user: Object,
    total: Number,
});

const carrito = ref(props.productos || []); // Inicializamos el carrito con los detalles enviados
console.log('Props Detalles:', props.productos);
console.log('Carrito Inicial:', carrito.value);
const isProcessing = ref(false); // Para mostrar indicador de proceso

const qrImage = ref(null); // Ref para almacenar la URL de la imagen QR
const isLoading = ref(false); // Indica si se está cargando el QR
const showModal = ref(false); // Controla la visualización del modal

// URL de la API para generar QR
const generar_qr = '/qr';

const generateQRCode = async () => {
    isLoading.value = true; // Mostrar el indicador de carga
    try {
        console.log('Datos enviados para generar QR:', {
            telefono: props.user.telefono,
            razon_social: props.user.razon_social,
            nit: props.user.nit,
            total: props.total,
            correo: props.user.email,
        });

        const response = await axios.post(generar_qr, {
            telefono: props.user.telefono,
            razon_social: props.user.razon_social,
            nit: props.user.nit,
            total: props.total,
            correo: props.user.email,
        });

        if (response.data.success) {
            qrImage.value = `data:image/png;base64,${response.data.qrImage}`;
            showModal.value = true; // Mostrar el modal con el QR
        } else {
            alert('Error al generar el QR: ' + response.data.message);
        }
    } catch (error) {
        console.error('Error al comunicarse con el servidor:', error);
        alert('Error, debe llenar todos los datos necesarios.');
    } finally {
        isLoading.value = false; // Ocultar el indicador de carga
    }
};

const closeModal = () => {
    showModal.value = false; // Cierra el modal
    window.location.href = '/'; // Redirige a la página principal
};

// Confirmar la compra
const confirmarCompra = async () => {
    isProcessing.value = true;
    try {
        const response = await axios.post('/pago/callback', {
            carrito: carrito.value,
            total: props.total,
            user: props.user,
        });

        if (response.data.success) {
            Swal.fire({
                title: '¡Éxito!',
                text: response.data.message,
                icon: 'success',
                confirmButtonText: 'Aceptar',
            }).then(() => {
                router.get('/factura');
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: response.data.message,
                icon: 'error',
                confirmButtonText: 'Aceptar',
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({
            title: 'Error',
            text: 'Hubo un problema al procesar la compra.',
            icon: 'error',
            confirmButtonText: 'Aceptar',
        });
    } finally {
        isProcessing.value = false;
    }
};

</script>

<template>
    <UserLayouts>
        <section class="text-gray-900 bg-gray-100 dark:text-gray-300 dark:bg-gray-900">
            <div class="container px-4 md:px-5 py-12 md:py-24 mx-auto flex flex-col md:flex-row gap-8">
                <!-- Lista del carrito -->
                <div
                    class="w-full lg:w-2/3 rounded-lg overflow-x-auto shadow-lg transition-all duration-300 transform hover:shadow-xl">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-600 uppercase bg-gray-200 dark:text-gray-300 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="px-4 md:px-16 py-3"><span class="sr-only">Image</span></th>
                                    <th scope="col" class="px-4 md:px-6 py-3">Producto</th>
                                    <th scope="col" class="px-4 md:px-6 py-3">Cant.</th>
                                    <th scope="col" class="px-4 md:px-6 py-3">Precio</th>
                                    <th scope="col" class="px-4 md:px-6 py-3 hidden md:table-cell">Imágen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(detalle, index) in carrito" :key="index"
                                    class="bg-gray-100 border-b border-gray-300 hover:bg-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors duration-200">
                                    <td class="p-2 md:p-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor"
                                            class="text-black-300 size-5 md:size-6 dark:text-gray-300 animate-pulse">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 6h.008v.008H6V6Z" />
                                        </svg>
                                    </td>
                                    <td
                                        class="px-3 md:px-6 py-3 font-semibold text-gray-900 dark:text-gray-300 text-xs md:text-sm">
                                        {{ detalle.nombre }}
                                    </td>
                                    <td class="px-3 md:px-6 py-3 text-xs md:text-sm">{{ detalle.cantidad }}</td>
                                    <td class="px-3 md:px-6 py-3 text-xs md:text-sm">Bs. {{ detalle.subtotal }}</td>
                                    <td class="px-3 md:px-6 py-3 hidden md:table-cell"><img :src="detalle.imagen"
                                            width="50" height="50"
                                            class="rounded shadow-sm hover:shadow-md transition-shadow duration-300">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Formulario de datos -->
                <div
                    class="w-full lg:w-1/3 bg-white dark:bg-gray-800 flex flex-col md:ml-auto mt-8 md:mt-0 p-6 rounded-lg shadow-lg transition-all duration-300 transform hover:shadow-xl">
                    <h2
                        class="text-gray-900 dark:text-gray-100 text-xl mb-1 font-medium title-font relative 
                               after:content-[''] after:absolute after:bottom-0 after:left-0 after:h-1 after:w-12 after:bg-indigo-500 after:rounded-full pb-2">
                        CONFIRMACIÓN
                    </h2>
                    <p class="leading-relaxed mb-5 text-gray-700 dark:text-gray-400">Llena los siguientes datos para
                        generar QR</p>

                    <!-- Inputs de datos con animaciones -->
                    <div class="relative mb-4 group">
                        <label for="telefono"
                            class="leading-7 text-sm text-gray-500 dark:text-gray-400 font-medium transition-all duration-300 group-hover:text-indigo-500">
                            Teléfono
                        </label>
                        <input type="number" id="telefono" name="telefono" v-model="props.user.telefono" class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg border border-gray-300 dark:border-gray-600 
                                   focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 py-2 px-4 leading-8 transition-all duration-300 
                                   ease-in-out shadow-sm focus:shadow-md" />
                    </div>
                    <div class="relative mb-4 group">
                        <label for="razon_social"
                            class="leading-7 text-sm text-gray-500 dark:text-gray-400 font-medium transition-all duration-300 group-hover:text-indigo-500">
                            Razón Social
                        </label>
                        <input type="text" id="razon_social" name="razon_social" v-model="props.user.razon_social"
                            class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg border border-gray-300 dark:border-gray-600 
                                   focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 py-2 px-4 leading-8 transition-all duration-300 
                                   ease-in-out shadow-sm focus:shadow-md" />
                    </div>
                    <div class="relative mb-4 group">
                        <label for="nit"
                            class="leading-7 text-sm text-gray-500 dark:text-gray-400 font-medium transition-all duration-300 group-hover:text-indigo-500">
                            NIT
                        </label>
                        <input type="number" id="nit" name="nit" v-model="props.user.nit" class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg border border-gray-300 dark:border-gray-600 
                                   focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 py-2 px-4 leading-8 transition-all duration-300 
                                   ease-in-out shadow-sm focus:shadow-md" />
                    </div>
                    <div class="relative mb-4 group">
                        <label for="number"
                            class="leading-7 text-sm text-gray-500 dark:text-gray-400 font-medium transition-all duration-300 group-hover:text-indigo-500">
                            Total
                        </label>
                        <input type="number" id="total" name="total" :value="props.total" readonly class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg border border-gray-300 dark:border-gray-600 
                                   py-2 px-4 leading-8 transition-all duration-300 ease-in-out shadow-sm" />
                    </div>
                    <div class="relative mb-6 group">
                        <label for="correo"
                            class="leading-7 text-sm text-gray-500 dark:text-gray-400 font-medium transition-all duration-300 group-hover:text-indigo-500">
                            Correo
                        </label>
                        <input type="email" id="correo" name="correo" v-model="props.user.email" class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg border border-gray-300 dark:border-gray-600 
                                   focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 py-2 px-4 leading-8 transition-all duration-300 
                                   ease-in-out shadow-sm focus:shadow-md" />
                    </div>

                    <button @click="generateQRCode" class="text-white bg-indigo-500 border-0 py-3 px-6 focus:outline-none hover:bg-indigo-600 rounded-lg text-lg 
                               transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg
                               flex items-center justify-center gap-2">
                        <span>Generar QR</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <p class="text-center text-gray-600 dark:text-gray-400 pb-6">Total de visitas: {{ count }}</p>
        </section>

        <!-- Modal para mostrar el QR con animación -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 
                    animate-fadeIn" style="animation-duration: 300ms;">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-lg w-11/12 md:w-auto
                      animate-scaleIn"
                style="animation-duration: 300ms; animation-delay: 150ms; animation-fill-mode: both;">
                <h3 class="text-2xl font-semibold mb-4 text-center text-gray-900 dark:text-gray-100">QR Generado</h3>
                <div class="relative">
                    <img :src="qrImage" alt="QR Code" class="w-full max-w-md mx-auto mb-6 rounded-lg shadow-md" />
                    <div
                        class="absolute inset-0 bg-indigo-500 bg-opacity-20 rounded-lg animate-pulse pointer-events-none">
                    </div>
                </div>
                <button @click="confirmarCompra" class="text-white bg-green-500 hover:bg-green-600 focus:bg-green-700 rounded-lg py-3 px-4 w-full
                           transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg
                           flex items-center justify-center gap-2">
                    <span>Confirmar compra</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Indicador de carga mejorado -->
        <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center z-40
                    animate-fadeIn" style="animation-duration: 300ms;">
            <div class="flex items-center justify-center mb-4">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-500"></div>
            </div>
            <div class="text-white text-lg font-bold bg-indigo-500 bg-opacity-80 px-6 py-3 rounded-lg shadow-lg
                          animate-pulse">
                Generando QR...
            </div>
        </div>

        <!-- Indicador de procesamiento de compra -->
        <div v-if="isProcessing" class="fixed inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center z-40
                    animate-fadeIn" style="animation-duration: 300ms;">
            <div class="flex items-center justify-center mb-4">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-green-500"></div>
            </div>
            <div class="text-white text-lg font-bold bg-green-500 bg-opacity-80 px-6 py-3 rounded-lg shadow-lg
                          animate-pulse">
                Procesando compra...
            </div>
        </div>
    </UserLayouts>
</template>

<style scoped>
/* Animaciones adicionales */
@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes scaleIn {
    from {
        transform: scale(0.95);
        opacity: 0;
    }

    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-fadeIn {
    animation-name: fadeIn;
}

.animate-scaleIn {
    animation-name: scaleIn;
}

/* Mejoras responsivas adicionales */
@media (max-width: 640px) {

    input,
    button {
        font-size: 0.95rem;
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
    }

    h2 {
        font-size: 1.25rem;
    }

    table {
        font-size: 0.8rem;
    }
}
</style>