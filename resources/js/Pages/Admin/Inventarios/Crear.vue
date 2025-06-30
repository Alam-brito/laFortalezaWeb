<script setup>
import { ref, watch } from 'vue';
import axios from 'axios'; // Usar Axios para peticiones AJAX
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    tipo: 'ingreso', // Tipo de ajuste
    glosa: '',
    productos: [
        {
            id_producto: '', // ID del producto
            id_almacen: '',  // ID del almacén
            cantidad: ''     // Cantidad
        }
    ]
});


// Listas dinámicas
const categorias = ref([]);
const productos = ref([]);
const almacenes = ref([]);

// Cargar categorías y almacenes al iniciar el componente
const fetchInitialData = async () => {
    categorias.value = await axios.get(route('ruta.categorias')).then(res => res.data);
    almacenes.value = await axios.get(route('ruta.almacenes')).then(res => res.data);
};

// Cargar productos al seleccionar una categoría
const fetchProductos = async (categoriaId) => {
    if (categoriaId) {
        productos.value = await axios.get(route('ruta.productos', categoriaId)).then(res => res.data);
    } else {
        productos.value = [];
    }
};

// Reaccionar al cambio de categoría
watch(() => form.categoriaId, (newCategoriaId) => {
    fetchProductos(newCategoriaId);
});

// Llamar al cargar el componente
fetchInitialData();


//=======================================
//Cambios para permitir hacer el ajuste de mushos a muchos
//======================================
// Agregar más productos al ajuste de inventario
const agregarProducto = () => {
    form.productos.push({
        id_producto: '',
        id_almacen: '',
        cantidad: ''
    });
};

// Eliminar un producto de la lista de ajuste
const eliminarProducto = (index) => {
    if (form.productos.length > 1) {
        form.productos.splice(index, 1);
    }
};

// Función para enviar el formulario al backend
const submit = () => {
    form.post(route('admin.inventarios.ajustar'), {
        onSuccess: () => {
            form.reset();
            alert('Ajuste realizado correctamente.');
        },
        onError: (errors) => {
            console.error('Errores:', errors);
        }
    });
};

</script>

<template>
    <section class="bg-gray-50 dark:bg-gray-900 p-8">
        <div class="flex flex-col items-center justify-top px-6 py-8 mx-auto min-h-screen overflow-y-auto">
            <div
                class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <p class="flex items-center text-2xl font-semibold text-gray-900 dark:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        INVENTARIOS
                    </p>
                    <!-- Mostrar mensaje de éxito -->
                    <div v-if="successMessage"
                        class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
                        {{ successMessage }}
                    </div>
                    <form @submit.prevent="submit" class="space-y-4 md:space-y-2 dark:bg-gray-800" action="#">
                        <h3 class="mt-4 font-semibold">Productos a Ajustar:</h3>

                        <!-- Glosa -->
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Glosa:</label>
                        <input type="text" v-model="form.glosa" placeholder="Ingrese la glosa" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />

                        <label for="categoria"
                            class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Categoría:</label>
                        <select v-model="form.categoriaId" id="categoria" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="" disabled>Selecciona una categoria</option>
                            <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                {{ categoria.nombre }}
                            </option>
                        </select>

                        <!-- Tipo de ajuste -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Tipo de
                                ajuste:</label>
                            <select v-model="form.tipo" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Egreso</option>
                            </select>
                        </div>
                        <!-- Repetible para varios productos -->
                        <div v-for="(producto, index) in form.productos" :key="index"
                            class="border p-4 rounded-lg mb-2 bg-gray-100 dark:bg-gray-800">
                            <label
                                class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Producto:</label>
                            <select v-model="producto.id_producto" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="" disabled>Selecciona un producto</option>
                                <option v-for="prod in productos" :key="prod.id" :value="prod.id">{{ prod.nombre }}
                                </option>
                            </select>

                            <label
                                class="block mt-2 mb-1 text-sm font-medium text-gray-900 dark:text-white">Almacén:</label>
                            <select v-model="producto.id_almacen" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="" disabled>Selecciona un almacén</option>
                                <option v-for="alm in almacenes" :key="alm.id" :value="alm.id">{{ alm.nombre }}</option>
                            </select>

                            <label
                                class="block mt-2 mb-1 text-sm font-medium text-gray-900 dark:text-white">Cantidad:</label>
                            <input type="number" v-model="producto.cantidad" min="1" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                            <button type="button" @click="eliminarProducto(index)" v-if="form.productos.length > 1"
                                class="mt-2 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">
                                Eliminar
                            </button>
                        </div>

                        <button type="button" @click="agregarProducto"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Agregar Producto
                        </button>

                        <!-- Botón de envío -->
                        <button type="submit" class="w-full mt-4 bg-lime-600 text-white px-5 py-2.5 rounded-lg">
                            Guardar Ajuste
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>
