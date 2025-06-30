<script setup>
import { usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch, nextTick } from 'vue';
import UserLayouts from './Layouts/UserLayouts.vue';
import Swal from 'sweetalert2';
import html2pdf from 'html2pdf.js';

defineProps(['count']);

const user = computed(() => usePage().props.auth.user);
const serviciosSeleccionados = ref(JSON.parse(sessionStorage.getItem("carrito") || "[]"));

const montoTotal = computed(() => {
    return serviciosSeleccionados.value.reduce((acc, servicio) => acc + (servicio.subtotal || 0), 0);
});

const form = useForm({
    id_cliente: user.value?.id || '',
    servicios: serviciosSeleccionados.value.map(servicio => ({
        id_servicio: servicio.id,
        nombre: servicio.nombre,
        precio: servicio.precio,
        cantidad: servicio.cantidad,
        subtotal: servicio.subtotal
    })),
    monto_total: montoTotal.value,
    fecha: new Date().toISOString().split('T')[0],
});

watch(montoTotal, (nuevoTotal) => {
    form.monto_total = nuevoTotal;
});

// Función para generar y descargar el PDF
const generarPDF = async () => {
    console.log("Generando PDF...");
    const elemento = document.getElementById("factura-pdf");

    if (elemento) {
        return new Promise((resolve) => {
            html2pdf()
                .set({
                    margin: 10,
                    filename: `Factura_${form.fecha}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 }
                })
                .from(elemento)
                .save()
                .then(() => {
                    console.log("PDF descargado");
                    resolve();
                });
        });
    } else {
        console.error("No se encontró el elemento factura-pdf en el DOM.");
    }
};

// Función para solicitar el servicio
const solicitarServicio = async () => {
    console.log("Verificando datos antes de enviar...");

    if (!form.servicios.length) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar al menos un servicio.',
            icon: 'error',
            confirmButtonText: 'Aceptar',
        });
        return;
    }

    //Paso 1: Generar el PDF antes de continuar
    await generarPDF();

    //Paso 2: Mostrar SweetAlert después de descargar el PDF
    Swal.fire({
        title: '¡Éxito!',
        text: 'Orden de servicio generada correctamente.',
        icon: 'success',
        confirmButtonText: 'Aceptar',
    }).then(() => {
        console.log("Enviando la orden al servidor...");

        // Paso 3: Enviar la orden al servidor después de aceptar el Swal
        form.post(route('user.orden_servicio.store'), {
            onSuccess: () => {
                console.log("Orden enviada correctamente");
            },
            onError: () => {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al crear la orden.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            },
        });
    });
};
</script>

<template>
    <UserLayouts>
        <div id="factura-pdf" class="max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-200">Resumen de Orden de Servicio</h2>

            <div class="mt-4 p-4 border rounded-md bg-gray-100 dark:bg-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-gray-200">Datos del Cliente</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Nombre:</strong> {{ user.name }}</p>
                <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Correo:</strong> {{ user.email }}</p>
                <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Fecha de Solicitud:</strong> {{ form.fecha
                    }}</p>
            </div>

            <div v-if="form.servicios.length > 0" class="mt-4">
                <h3 class="font-semibold text-gray-900 dark:text-gray-200">Servicios Solicitados</h3>
                <table class="w-full border-collapse mt-2">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-gray-700 dark:text-gray-300 text-left">Servicio</th>
                            <th class="px-4 py-2 text-gray-700 dark:text-gray-300 text-left">Precio (Bs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="servicio in form.servicios" :key="servicio.id_servicio"
                            class="border-b dark:border-gray-700">
                            <td class="px-4 py-2 text-gray-800 dark:text-white">{{ servicio.nombre }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-white">Bs. {{ servicio.precio }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 p-4 border rounded-md bg-gray-100 dark:bg-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-gray-200">Monto Total</h3>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-200">Bs. {{ form.monto_total }}</p>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" @click="solicitarServicio"
                    class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    Confirmar y Solicitar
                </button>
            </div>
        </div>
    </UserLayouts>
</template>
