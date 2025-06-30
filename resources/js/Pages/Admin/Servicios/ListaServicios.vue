<script setup>
import { usePage, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const servicios = usePage().props.servicios;

const form = useForm({
    id: null,
    nombre: '',
    descripcion: '',
    precio: '',
    imagen: null,
});

const successMessage = ref(''); // Estado para el mensaje de éxito
const errorMessages = ref(''); // Estado para el mensaje de éxito
const submit = () => {
    // Acción para añadir un nuevo producto
    form.post(route('admin.servicios.crear'), {
        onSuccess: () => {
            successMessage.value = 'Servicio añadido.';
            form.reset();
            dialogVisible.value = false;
            //location.reload();
            Inertia.reload(); // Actualiza la lista de productos
        },
    });

};

// Función personalizada para manejar la subida del archivo
const customUpload = ({ file }) => {
    form.imagen = file; // Asigna el archivo al formulario
    console.log('Archivo subido:', file);
};

const clearForm = () => {
    form.id = null;
    form.nombre = '';
    form.descripcion = '';
    form.precio = '';
    form.imagen = null;
    previewImage.value = '';
};

const isAddPromo = ref(false);
const dialogVisible = ref(false);
const editMode = ref(false);

//open add modal

const openAddModel = () => {
    clearForm(); // Limpiar los campos antes de añadir una servicio
    isAddPromo.value = true
    dialogVisible.value = true
    editMode.value = false;
}

const previewImage = ref('');
const editModel = (servicio) => {
    editMode.value = true;
    isAddPromo.value = false;
    dialogVisible.value = true;

    // Asignar los valores del producto al formulario
    form.id = servicio.id; // Asegúrate de tener esto
    form.nombre = servicio.nombre || '';
    form.descripcion = servicio.descripcion || '';
    form.precio = servicio.precio || '';
    previewImage.value = servicio.imagen ? `/storage/${servicio.imagen}` : null;
};

const deleteSer = (id) => {
    if (confirm('¿Está seguro de que deseas eliminar este servicio?')) {
        form.delete(route('admin.servicios.delete', id), {
            onSuccess: () => {
                successMessage.value = 'Servicio eliminado';
                // Opcional: oculta el mensaje después de unos segundos
                setTimeout(() => {
                    successMessage.value = '';
                }, 2000);
                //location.reload();
                Inertia.reload(); // Recarga los datos de la página
            },
            onError: () => {
                alert('Hubo un error al intentar eliminar la promoción.');
            },
        });
    }
};

const updateSer = () => {
    if (confirm('¿Está seguro de que deseas editar este servicio?')) {
        form.put(route('admin.servicios.update', form.id), {
            onSuccess: () => {
                successMessage.value = 'Servicio editado correctamente.';
                dialogVisible.value = false;
                setTimeout(() => {
                    successMessage.value = '';
                }, 2000);
                Inertia.reload(); // Recarga los datos de la página

            },
            onError: (error) => {
                // Muestra error específico
                errorMessages.value = error.response.data.errors
                    ? error.response.data.errors
                    : 'Hubo un error al editar el producto.';
                alert("E")
            },
        });
    }
};

</script>

<template>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg h-screen">
        <!--Inicio de ventana-->
        <el-dialog v-model="dialogVisible" :title="editMode ? 'Editar Servicio' : 'Añadir Servicio'" width="500"
            :before-close="handleClose" class="dark:bg-gray-900">
            <!--Inicio de formulario-->
            <form @submit.prevent="editMode ? updateSer() : submit()" class="max-w-md mx-auto">
                <div class="relative z-0 w-full mb-5 group">
                    <input type="text" v-model="form.nombre" id="nombre"
                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                        placeholder=" " required />
                    <label for="nombre"
                        class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Nombre
                        del servicio</label>
                </div>
                <div class="relative z-0 w-full mb-5 group">
                    <label for="descripcion"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-500">Descripción</label>
                    <textarea id="descripcion" rows="2" v-model="form.descripcion"
                        class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="Escribe la descripción del servicio aquí..."></textarea>
                </div>
                <div class="relative z-0 w-full mb-5 group">
                    <input type="number" id="precio"
                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                        placeholder=" " required v-model="form.precio" />
                    <label for="precio"
                        class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Precio</label>
                </div>
                <div v-if="previewImage">
                    <img :src="previewImage" class="w-33 h-33 object-cover mb-1" />
                </div>
                <!--Inicio cargar imágen-->
                <div class="relative z-0 w-full mb-3 group dark:bg-gray-900">
                    <el-upload v-model="form.imagen" class="upload-demo dark:bg-gray-900" drag style="height: 160px;"
                        :http-request="customUpload" multiple>
                        <el-icon style="font-size: 20px;" class="el-icon--upload"><upload-filled /></el-icon>
                        <div class="el-upload__text" style="font-size: 12px;">
                            Arrastra el archivo o <em>haz click aquí</em>
                        </div>
                    </el-upload>
                </div>
                <!--fin cargar imágen-->

                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">{{
                        editMode
                            ? 'Editar' : 'Añadir' }}</button>
            </form>
            <!--Fin de formulario-->
        </el-dialog>
        <!--Fin de ventana-->
        <!-- Mostrar mensaje de éxito -->
        <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ successMessage }}
        </div>
        <!-- Fin Mostrar mensaje de éxito -->
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-black-700 uppercase bg-blue-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Nombre del servicio
                    </th>
                    <th scope="col" class="px-6 py-3">
                        descripción
                    </th>
                    <th scope="col" class="px-6 py-3">
                        precio
                    </th>
                    <th scope="col" class="px-6 py-3">
                        imagen
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Acciones
                    </th>
                    <td class="px-6 py-4">
                        <el-button type="primary" @click="openAddModel">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Añadir Servicio</el-button>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="servicio in servicios" :key="servicio.id"
                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ servicio.nombre }}
                    </th>
                    <td class="px-6 py-4">
                        {{ servicio.descripcion }}
                    </td>
                    <td class="px-6 py-4">
                        {{ servicio.precio }} Bs.
                    </td>
                    <!--vista de imagen-->
                    <td class="text-black text-black-700 px-4 py-3">
                        <figure v-if="servicio.imagen" class="max-w-lg">
                            <img v-if="servicio.imagen" :src="`/storage/${servicio.imagen}`" width="50" height="50">
                            <figcaption class="mt-2 text-sm text-center text-gray-500 dark:text-gray-400">
                            </figcaption>
                        </figure>
                        <span v-else>No hay imagen</span>
                    </td>
                    <!--fin vista de imagen-->
                    <td class="px-6 py-4">
                        <el-button type="success" plain @click="editModel(servicio)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                            </svg>

                            Editar</el-button>
                    </td>
                    <td class="px-6 py-4">
                        <el-button type="danger" @click="deleteSer(servicio.id)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Eliminar</el-button>
                    </td>

                </tr>
            </tbody>
        </table>
    </div>
</template>