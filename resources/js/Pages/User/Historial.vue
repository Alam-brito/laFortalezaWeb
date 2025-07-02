<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import UserLayouts from "./Layouts/UserLayouts.vue";

// Props del controlador
const props = defineProps({
    historial: Array,
    cliente_nombre: String,
    error: String,
    mensaje: String
});

// Estados reactivos
const isLoading = ref(false);
const showDeleteModal = ref(false);
const ventaToDelete = ref(null);

// Computed
const totalCompras = computed(() => props.historial?.length || 0);
const totalGastado = computed(() => {
    return props.historial?.reduce((sum, compra) => sum + parseFloat(compra.venta.total), 0).toFixed(2) || '0.00';
});

// Métodos
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatCurrency = (amount) => {
    return `Bs ${parseFloat(amount).toFixed(2)}`;
};

const getPaymentTypeIcon = (tipo) => {
    switch (tipo.toLowerCase()) {
        case 'qr':
            return '📱';
        case 'efectivo':
            return '💵';
        case 'tarjeta':
            return '💳';
        default:
            return '💰';
    }
};

const getStatusBadgeClass = (estado) => {
    switch (estado.toLowerCase()) {
        case 'completado':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pendiente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'cancelado':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const confirmDelete = (venta) => {
    ventaToDelete.value = venta;
    showDeleteModal.value = true;
};

const deleteVenta = async () => {
    if (!ventaToDelete.value) return;

    isLoading.value = true;

    try {
        await router.delete(`/historial/${ventaToDelete.value.venta.venta_id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                ventaToDelete.value = null;
                // Recargar la página para actualizar la lista
                router.reload();
            },
            onError: (errors) => {
                console.error('Error al eliminar:', errors);
                alert('Error al eliminar la venta');
            }
        });
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar la venta');
    } finally {
        isLoading.value = false;
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    ventaToDelete.value = null;
};
</script>

<template>
    <UserLayouts>
        <div class="min-h-screen bg-gradient-to-br from-teal-50 to-yellow-50 dark:from-gray-900 dark:to-gray-800 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                    📋 Historial de Compras
                                </h1>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Bienvenido {{ cliente_nombre }}, aquí puedes ver todas tus compras
                                </p>
                            </div>
                            <div class="mt-4 sm:mt-0 text-right">
                                <div class="text-2xl font-bold text-teal-600 dark:text-teal-400">
                                    {{ formatCurrency(totalGastado) }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Total gastado en {{ totalCompras }} compras
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensajes de error o vacío -->
                <div v-if="error"
                    class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="text-red-800 dark:text-red-200">
                            ❌ {{ error }}
                        </div>
                    </div>
                </div>

                <div v-if="mensaje"
                    class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="text-blue-800 dark:text-blue-200">
                            ℹ️ {{ mensaje }}
                        </div>
                    </div>
                </div>

                <!-- Lista de compras -->
                <div v-if="historial && historial.length > 0" class="space-y-6">
                    <div v-for="(compra, index) in historial" :key="compra.venta.venta_id"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">

                        <!-- Header de la compra -->
                        <div
                            class="bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-700 dark:to-teal-800 px-6 py-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-white/20 rounded-full p-2">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-white">
                                            Compra #{{ compra.venta.venta_id }}
                                        </h3>
                                        <p class="text-teal-100">
                                            {{ formatDate(compra.venta.fecha) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 sm:mt-0 flex items-center space-x-4">
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-white">
                                            {{ formatCurrency(compra.venta.total) }}
                                        </div>
                                        <div class="text-teal-100 text-sm">
                                            {{ getPaymentTypeIcon(compra.venta.tipo_pago) }} {{ compra.venta.tipo_pago
                                            }}
                                        </div>
                                    </div>

                                    <span
                                        :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusBadgeClass(compra.venta.estado_pago)]">
                                        {{ compra.venta.estado_pago }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Productos de la compra -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                <div v-for="producto in compra.productos" :key="producto.producto_id"
                                    class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">

                                    <!-- Imagen del producto -->
                                    <div class="flex-shrink-0">
                                        <img :src="producto.imagen_url" :alt="producto.producto_nombre"
                                            class="w-16 h-16 object-cover rounded-lg shadow-md"
                                            @error="$event.target.src = '/images/no-image.png'" />
                                    </div>

                                    <!-- Información del producto -->
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                            {{ producto.producto_nombre }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            {{ producto.categoria_nombre }}
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                                Cant: {{ producto.cantidad }}
                                            </span>
                                            <span class="text-sm font-medium text-teal-600 dark:text-teal-400">
                                                {{ formatCurrency(producto.subtotal_final) }}
                                            </span>
                                        </div>
                                        <div v-if="producto.descuento"
                                            class="text-xs text-green-600 dark:text-green-400">
                                            🏷️ {{ producto.descuento }}% descuento
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción de la venta si existe -->
                            <div v-if="compra.productos.some(p => p.descripcion_venta)"
                                class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    📝 {{compra.productos.find(p => p.descripcion_venta)?.descripcion_venta}}
                                </p>
                            </div>

                            <!-- Footer con acciones -->
                            <div
                                class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-4 border-t border-gray-200 dark:border-gray-600">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-3 sm:mb-0">
                                    {{ compra.total_productos }} producto{{ compra.total_productos !== 1 ? 's' : '' }} •
                                    {{ compra.cantidad_total }} unidad{{ compra.cantidad_total !== 1 ? 'es' : '' }}
                                </div>

                                <button @click="confirmDelete(compra)"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Eliminar compra
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div v-else-if="!error && !mensaje" class="text-center py-12">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
                        <div class="text-6xl mb-4">🛒</div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            No tienes compras aún
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            ¡Comienza a explorar nuestros productos y realiza tu primera compra!
                        </p>
                        <Link :href="route('user.home')" as="button" type="button"
                            class="inline-flex items-center px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors">
                            Ver productos
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación de eliminación -->
        <div v-if="showDeleteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-md w-full">
                <div class="text-center">
                    <div class="text-6xl mb-4">⚠️</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        ¿Eliminar compra?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Esta acción no se puede deshacer. La compra #{{ ventaToDelete?.venta.venta_id }} será eliminada
                        permanentemente.
                    </p>

                    <div class="flex space-x-4">
                        <button @click="cancelDelete"
                            class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                            Cancelar
                        </button>
                        <button @click="deleteVenta" :disabled="isLoading"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors disabled:opacity-50">
                            {{ isLoading ? 'Eliminando...' : 'Eliminar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </UserLayouts>
</template>

<style scoped>
/* Animaciones personalizadas */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}
</style>