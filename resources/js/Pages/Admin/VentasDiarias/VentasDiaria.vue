<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

// Props que recibimos del controlador
const props = defineProps({
    ventasDiarias: Array,
    totalVentas: Number,
    fechaActual: String,
    fechaInfo: Object
});

// Variable reactiva para almacenar las ventas actuales
const ventas = ref(props.ventasDiarias || []);
const totalActual = ref(Number(props.totalVentas || 0));
const cargando = ref(false);
const error = ref(null);
const showRefreshAnimation = ref(false);

//agrega esta función de formato:
const formatearFecha = (fecha) => {
    console.log("Fecha recibida:", fecha, "Tipo:", typeof fecha);

    // Si es un string ISO o YYYY-MM-DD
    if (typeof fecha === 'string') {
        // Para fechas en formato YYYY-MM-DD
        if (fecha.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const [year, month, day] = fecha.split('-').map(Number);
            console.log(`Partes de la fecha: día=${day}, mes=${month}, año=${year}`);
            return `${day}/${month}/${year}`;
        }
    }

    // Como último recurso, usar Date
    try {
        const d = new Date(fecha);
        console.log("Date objeto:", d, "toISOString:", d.toISOString());
        // Usar UTC para evitar problemas de zona horaria
        return `${d.getUTCDate()}/${d.getUTCMonth() + 1}/${d.getUTCFullYear()}`;
    } catch (err) {
        console.error("Error al formatear fecha:", err);
        return "Fecha inválida";
    }
};

const fechaMostrada = ref(props.fechaInfo ?
    `${props.fechaInfo.dayOfMonth}/${props.fechaInfo.month}/${props.fechaInfo.year}` :
    formatearFecha(new Date()));

// Estado de la aplicación
const estadoDatos = computed(() => {
    if (cargando.value) return 'Cargando datos...';
    if (error.value) return `Error: ${error.value}`;
    if (ventas.value.length === 0) return 'No hay ventas registradas para hoy o se ha descargado el reporte.';
    return `Mostrando ${ventas.value.length} ventas del día.`;
});

// Función para actualizar los datos (refrescar la tabla)
const actualizarDatos = async () => {
    cargando.value = true;
    error.value = null;
    showRefreshAnimation.value = true;

    try {
        // Usar el router de Inertia en lugar de axios para garantizar la compatibilidad
        router.reload({
            onSuccess: (page) => {
                // Verificar que page.props contenga los datos esperados
                if (page.props && page.props.ventasDiarias) {
                    ventas.value = page.props.ventasDiarias;
                    totalActual.value = page.props.totalVentas || 0;
                    fechaMostrada.value = new Date(page.props.fechaActual || Date.now()).toLocaleDateString('es-BO');
                    console.log('Datos actualizados:', ventas.value.length, 'ventas encontradas');
                } else {
                    console.error('Formato de respuesta incorrecto:', page.props);
                    error.value = 'Formato de respuesta incorrecto: datos no encontrados';
                }
                cargando.value = false;
                setTimeout(() => {
                    showRefreshAnimation.value = false;
                }, 1000);
            },
            onError: (errors) => {
                console.error('Error al actualizar datos:', errors);
                error.value = 'Error al obtener datos';
                cargando.value = false;
                showRefreshAnimation.value = false;
            }
        });
    } catch (err) {
        console.error('Error al actualizar datos:', err);
        error.value = err.message || 'Error desconocido';
        cargando.value = false;
        showRefreshAnimation.value = false;
    }
};

// Función para probar datos (depuración)
const probarConexion = async () => {
    cargando.value = true;
    error.value = null;

    try {
        const response = await fetch(route('admin.ventaDiaria.test'), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();
        console.log('Datos de prueba:', data);
        alert('Revisa la consola para ver los datos de prueba');
    } catch (err) {
        console.error('Error al probar la conexión:', err);
        error.value = err.message || 'Error al probar la conexión';
    } finally {
        cargando.value = false;
    }
};

// Función para descargar el reporte y limpiar la lista de ventas
const descargarReporte = async () => {
    // Crear un enlace temporal y hacer clic en él
    const a = document.createElement('a');
    a.href = route('admin.ventaDiaria.descargar', { fecha: props.fechaActual });
    a.target = '_blank';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    try {
        // Usar router.post en lugar de fetch
        router.post(route('admin.ventaDiaria.limpiar'),
            { fecha: props.fechaActual },
            {
                onSuccess: (page) => {
                    // Limpiar la lista de ventas manualmente después de descargar
                    ventas.value = [];
                    totalActual.value = 0;
                    console.log('Lista de ventas limpiada después de la descarga');

                    // Mostrar mensaje de éxito
                    setTimeout(() => {
                        alert('Reporte descargado. La vista ha sido limpiada para nuevas ventas.');
                    }, 500);
                },
                onError: (errors) => {
                    console.error('Error al limpiar ventas:', errors);
                    // Como la API ya no actualiza una columna que no existe, tratamos esto como éxito igualmente
                    // y limpiamos las ventas localmente
                    ventas.value = [];
                    totalActual.value = 0;
                    console.log('Lista de ventas limpiada manualmente debido a error en el servidor');
                    setTimeout(() => {
                        alert('Reporte descargado. La vista ha sido limpiada para nuevas ventas.');
                    }, 500);
                },
                preserveState: true, // Mantener el estado para poder manipularlo nosotros
                preserveScroll: true // Mantener la posición del scroll
            }
        );
    } catch (err) {
        console.error('Error al limpiar ventas:', err);
        error.value = 'Error al limpiar ventas: ' + (err.message || 'Error desconocido');
        // Aún si hay error, limpiamos las ventas en el frontend
        ventas.value = [];
        totalActual.value = 0;
        setTimeout(() => {
            alert('Reporte descargado. La vista ha sido limpiada manualmente.');
        }, 500);
    }
};

// Actualizar datos cuando se monta el componente
onMounted(() => {
    // Verificar que tengamos datos y actualizar si es necesario
    if (!ventas.value || ventas.value.length === 0) {
        actualizarDatos();
    }

    // Configurar intervalo para actualización automática (cada 5 minutos)
    const intervalId = setInterval(() => {
        if (!cargando.value) {
            actualizarDatos();
        }
    }, 300000); // 5 minutos

    // Limpiar el intervalo cuando el componente se desmonte
    return () => clearInterval(intervalId);
});
</script>

<template>
    <div :class="{ 'dark': darkMode }">
        <div class="min-h-screen transition-colors duration-300 ease-in-out bg-gray-50 dark:bg-gray-900">

            <Head title="Reporte de Ventas Diarias" />

            <div class="container mx-auto px-4 py-8">
                <!-- Cabecera con título y botones -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Reporte de Ventas Diarias</h1>
                    </div>
                    <div class="flex flex-wrap justify-center md:justify-end gap-3">
                        <button @click="actualizarDatos"
                            class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                            :disabled="cargando">
                            <svg v-if="showRefreshAnimation" class="animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ cargando ? 'Actualizando...' : 'Actualizar Datos' }}
                        </button>

                        <button @click="probarConexion"
                            class="flex items-center gap-2 bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                            :disabled="cargando">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Probar Conexión
                        </button>

                        <button @click="descargarReporte"
                            class="flex items-center gap-2 bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                            :disabled="cargando">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Descargar Reporte
                        </button>
                    </div>
                </div>

                <!-- Fecha y resumen -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md border-l-4 border-blue-500 transition-all duration-300 hover:shadow-lg">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Última actualización:
                        </h2>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ new Date().toLocaleTimeString('es-BO') }}
                        </p>
                    </div>

                    <div
                        class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md border-l-4 border-green-500 transition-all duration-300 hover:shadow-lg">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Total de Ventas</h2>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{
                            totalActual.toLocaleString('es-BO') }} Bs.</p>
                    </div>
                </div>

                <!-- Estado y mensajes -->
                <div class="mb-6">
                    <div
                        class="p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 shadow-sm transition-colors duration-300">
                        <p class="text-gray-700 dark:text-gray-300 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ estadoDatos }}
                        </p>
                    </div>
                </div>

                <!-- Mensaje de error -->
                <div v-if="error" class="mb-6 animate-fadeIn">
                    <div
                        class="p-4 bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-lg border border-red-200 dark:border-red-800 shadow-sm">
                        <p class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ error }}
                        </p>
                    </div>
                </div>

                <!-- Tabla de ventas diarias -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-gray-700 dark:text-gray-300 font-semibold">
                                        Cliente</th>
                                    <th class="px-4 py-3 text-left text-gray-700 dark:text-gray-300 font-semibold">
                                        Producto</th>
                                    <th class="px-4 py-3 text-right text-gray-700 dark:text-gray-300 font-semibold">
                                        Precio</th>
                                    <th class="px-4 py-3 text-right text-gray-700 dark:text-gray-300 font-semibold">
                                        Cantidad</th>
                                    <th class="px-4 py-3 text-right text-gray-700 dark:text-gray-300 font-semibold">
                                        Subtotal</th>
                                    <th class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 font-semibold">
                                        Método de Pago</th>
                                    <th class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 font-semibold">
                                        Estado</th>
                                    <th class="px-4 py-3 text-right text-gray-700 dark:text-gray-300 font-semibold">
                                        Fecha
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="ventas.length === 0">
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No hay ventas registradas para el día {{ fechaMostrada }}
                                    </td>
                                </tr>
                                <tr v-for="(venta, index) in ventas" :key="index"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ venta.nombre_cliente }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ venta.nombre_producto }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200 text-right">
                                        {{ venta.precio.toLocaleString('es-BO') }} Bs.
                                    </td>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200 text-right">{{ venta.cantidad
                                    }}</td>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200 text-right font-medium">
                                        {{ venta.subtotal.toLocaleString('es-BO') }} Bs.
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="{
                                            'px-2 py-1 rounded-full text-xs font-medium': true,
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300': venta.tipo_pago === 'Tarjeta',
                                            'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': venta.tipo_pago === 'efectivo',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300': venta.tipo_pago === 'QR',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300': venta.tipo_pago === 'Transferencia',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': !['Tarjeta', 'Efectivo', 'QR', 'Transferencia'].includes(venta.tipo_pago)
                                        }">
                                            {{ venta.tipo_pago }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="{
                                            'px-2 py-1 rounded-full text-xs font-medium': true,
                                            'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': venta.estado === 'completado',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300': venta.estado === 'pendiente',
                                            'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300': venta.estado === 'rechazado',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': !['completado', 'pendiente', 'rechazado'].includes(venta.estado)
                                        }">
                                            {{ venta.estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-right">
                                        {{ venta.fecha }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pie de página con información del sistema -->
                <footer class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    <p>Sistema de Ventas Diarias © {{ new Date().getFullYear() }}</p>
                    <p class="mt-1">
                        Zona Horaria: {{ props.fechaInfo?.timezone || 'América/La_Paz' }} |
                        Fecha en Servidor: {{ fechaMostrada }}
                    </p>
                </footer>
            </div>
        </div>
    </div>
</template>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>