<script setup>
import { usePage, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const { categorias, almacenes } = usePage().props;

const formCategoria = useForm({
    id: null,
    nombre: '',
    descripcion: '',
});

const formAlmacen = useForm({
    id: null,
    nombre: '',
    ubicacion: '',
});

const dialogCategoriaVisible = ref(false);
const dialogAlmacenVisible = ref(false);

// Abrir diálogo para Categorías
const openCategoriaDialog = (categoria = null) => {
    if (categoria) {
        // Si se pasa una categoría, llenamos el formulario (modo edición)
        formCategoria.id = categoria.id;
        formCategoria.nombre = categoria.nombre;
        formCategoria.descripcion = categoria.descripcion;
    } else {
        // Si no se pasa una categoría, limpiamos el formulario (modo creación)
        formCategoria.reset();
    }
    dialogCategoriaVisible.value = true;
};

// Abrir diálogo para Almacenes
const openAlmacenDialog = (almacen = null) => {
    if (almacen) {
        // Si se pasa un almacén, llenamos el formulario (modo edición)
        formAlmacen.id = almacen.id;
        formAlmacen.nombre = almacen.nombre;
        formAlmacen.ubicacion = almacen.ubicacion;
    } else {
        // Si no se pasa un almacén, limpiamos el formulario (modo creación)
        formAlmacen.reset();
    }
    dialogAlmacenVisible.value = true;
};

// Guardar o Editar Categoría
const saveCategoria = () => {
    if (formCategoria.id) {
        // Modo edición
        formCategoria.put(route('almacen.categoria.update', formCategoria.id), {
            onSuccess: () => {
                dialogCategoriaVisible.value = false;
                formCategoria.reset();
                Inertia.reload();
            },
        });
    } else {
        // Modo creación
        formCategoria.post(route('almacen.categoria.store'), {
            onSuccess: () => {
                dialogCategoriaVisible.value = false;
                formCategoria.reset();
                Inertia.reload();
            },
        });
    }
};

// Guardar o Editar Almacén
const saveAlmacen = () => {
    if (formAlmacen.id) {
        // Modo edición
        formAlmacen.put(route('almacen.almacen.update', formAlmacen.id), {
            onSuccess: () => {
                dialogAlmacenVisible.value = false;
                formAlmacen.reset();
                Inertia.reload();
            },
        });
    } else {
        // Modo creación
        formAlmacen.post(route('almacen.almacen.store'), {
            onSuccess: () => {
                dialogAlmacenVisible.value = false;
                formAlmacen.reset();
                Inertia.reload();
            },
        });
    }
};

// Eliminar Categoría
const deleteCategoria = (id) => {
    if (confirm('¿Está seguro de que desea eliminar esta categoría?')) {
        formCategoria.delete(route('almacen.categoria.destroy', id));
    }
};

// Eliminar Almacén
const deleteAlmacen = (id) => {
    if (confirm('¿Está seguro de que desea eliminar este almacén?')) {
        formAlmacen.delete(route('almacen.almacen.destroy', id));
    }
};

</script>

<template>
    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 dark:bg-gray-900 p-4 gap-4">
        <!-- Categorías -->
        <div class="w-full md:w-1/2 bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex flex-col">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Categorías</h2>
            <button @click="openCategoriaDialog"
                class="mb-4 px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">
                ➕ Añadir Categoría
            </button>
            <div class="overflow-x-auto flex-grow">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-left">
                        <tr>
                            <th class="px-4 py-2 text-gray-600 dark:text-gray-300">Nombre</th>
                            <th class="px-4 py-2 text-gray-600 dark:text-gray-300">Descripción</th>
                            <th class="px-4 py-2 text-gray-600 dark:text-gray-300 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="categoria in categorias" :key="categoria.id" class="border-b dark:border-gray-700">
                            <td class="px-4 py-2 text-gray-800 dark:text-white">{{ categoria.nombre }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-white">{{ categoria.descripcion }}</td>
                            <td class="px-4 py-2 flex justify-center space-x-2">
                                <button @click="openCategoriaDialog(categoria)"
                                    class="px-2 py-1 bg-green-500 text-white rounded shadow hover:bg-green-600">
                                    ✏️ Editar
                                </button>
                                <button @click="deleteCategoria(categoria.id)"
                                    class="w-full px-2 py-1 bg-red-500 text-white rounded shadow hover:bg-red-600">
                                    🗑️ Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Almacenes -->
        <div class="w-full md:w-1/2 bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex flex-col">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Almacenes</h2>
            <button @click="openAlmacenDialog"
                class="mb-4 px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">
                ➕ Añadir Almacén
            </button>
            <div class="overflow-x-auto flex-grow">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-left">
                        <tr>
                            <th class="px-4 py-2 text-gray-600 dark:text-gray-300">Nombre</th>
                            <th class="px-4 py-2 text-gray-600 dark:text-gray-300">Ubicación</th>
                            <th class="px-4 py-2 text-gray-600 dark:text-gray-300 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="almacen in almacenes" :key="almacen.id" class="border-b dark:border-gray-700">
                            <td class="px-4 py-2 text-gray-800 dark:text-white">{{ almacen.nombre }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-white">{{ almacen.ubicacion }}</td>
                            <td class="px-4 py-2 flex justify-center space-x-2">
                                <button @click="openAlmacenDialog(almacen)"
                                    class="px-2 py-1 bg-green-500 text-white rounded shadow hover:bg-green-600">
                                    ✏️ Editar
                                </button>
                                <button @click="deleteAlmacen(almacen.id)"
                                    class="px-2 py-1 bg-red-500 text-white rounded shadow hover:bg-red-600">
                                    🗑️ Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dialog para Categorías -->
        <el-dialog v-model="dialogCategoriaVisible" :title="formCategoria.id ? 'Editar Categoría' : 'Añadir Categoría'">
            <form @submit.prevent="saveCategoria">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <input v-model="formCategoria.nombre" type="text"
                        class="w-full p-2 rounded-md dark:bg-gray-700 dark:text-white border border-gray-300" />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                    <textarea v-model="formCategoria.descripcion"
                        class="w-full p-2 rounded-md dark:bg-gray-700 dark:text-white border border-gray-300"></textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">
                    {{ formCategoria.id ? 'Actualizar' : 'Guardar' }}
                </button>
            </form>
        </el-dialog>

        <!-- Dialog para Almacenes -->
        <el-dialog v-model="dialogAlmacenVisible" :title="formAlmacen.id ? 'Editar Almacén' : 'Añadir Almacén'">
            <form @submit.prevent="saveAlmacen">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <input v-model="formAlmacen.nombre" type="text"
                        class="w-full p-2 rounded-md dark:bg-gray-700 dark:text-white border border-gray-300" />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación</label>
                    <textarea v-model="formAlmacen.ubicacion"
                        class="w-full p-2 rounded-md dark:bg-gray-700 dark:text-white border border-gray-300"></textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">
                    {{ formAlmacen.id ? 'Actualizar' : 'Guardar' }}
                </button>
            </form>
        </el-dialog>
    </div>
</template>
