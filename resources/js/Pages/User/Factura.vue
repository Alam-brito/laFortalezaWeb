<script setup>
import UserLayouts from './Layouts/UserLayouts.vue';
import { usePage } from '@inertiajs/vue3';
import html2pdf from 'html2pdf.js';

defineProps(['count']);

const factura = usePage().props.factura;
console.log('Factura:', factura); // Depuración: Verifica que factura.productos contiene varios elementos

const generarPDF = () => {
    const elemento = document.getElementById("factura-pdf");
    html2pdf()
        .set({ margin: 10, filename: `Factura_${factura.fecha}.pdf`, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 } })
        .from(elemento)
        .save();
};
</script>
<template>
    <UserLayouts>
        <div id="factura-pdf"
        class="w-full max-w-5xl mx-auto bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 transition-colors duration-300">
            <div class="text-center border-b pb-4">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Factura</h2>
                <p class="text-gray-500 dark:text-gray-300">Detalles de tu compra</p>
            </div>

            <div class="mt-6 border-b pb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Método de Pago</h3>
                <p class="text-gray-600 dark:text-gray-200"><strong>Tipo:</strong> {{ factura.metodo_pago }}</p>
            </div>

            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Productos</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                <th class="border border-gray-300 p-2">Producto</th>
                                <th class="border border-gray-300 p-2">Descripción</th>
                                <th class="border border-gray-300 p-2">Cantidad</th>
                                <th class="border border-gray-300 p-2">Precio</th>
                                <th class="border border-gray-300 p-2">Descuento</th>
                                <th class="border border-gray-300 p-2">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(producto, index) in factura.productos" :key="index"
                                class="text-gray-700 dark:text-gray-200 text-center">
                                <td class="border border-gray-300 p-2">{{ producto.producto }}</td>
                                <td class="border border-gray-300 p-2">{{ producto.descripcion }}</td>
                                <td class="border border-gray-300 p-2">{{ producto.cantidad }}</td>
                                <td class="border border-gray-300 p-2">Bs. {{ Number(producto.precio).toFixed(2) }}</td>
                                <td class="border border-gray-300 p-2">-{{ producto.descuento }}%</td>
                                <td class="border border-gray-300 p-2">Bs. {{ Number(producto.subtotal).toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Total a Pagar -->
            <div class="mt-6 text-right border-t pt-4">
                <p class="text-lg"><strong>Subtotal:</strong> Bs. {{ factura.subtotal.toFixed(2) }}</p>
                <p class="text-lg text-red-600"><strong>Descuento Total:</strong> Bs. {{ factura.descuento.toFixed(2) }}
                </p>
                <p class="text-xl font-bold text-gray-800 dark:text-gray-200 mt-2"><strong>Total:</strong> Bs. {{
                    factura.total.toFixed(2) }}</p>
            </div>
            <!-- Botón de Descarga -->
            <div class="mt-6 text-center">
                <button @click="generarPDF"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-blue-700 transition">
                    Descargar Factura (PDF)
                </button>
            </div>
        </div>
        <p>Total de visitas: {{ count }}</p>  
    </UserLayouts>
</template>
