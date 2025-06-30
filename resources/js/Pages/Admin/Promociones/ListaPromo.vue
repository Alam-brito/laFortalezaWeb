<script setup>
import { usePage, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const promociones = usePage().props.promociones;

const form = useForm({
    descuento: '',
    fecha_final: '',
});

const successMessage = ref(''); // Estado para el mensaje de éxito
const submit = () => {
    // Acción para añadir un nuevo producto
    form.post(route('admin.promo.crear'), {
        onSuccess: () => {
            successMessage.value = 'Promoción añadida.';
            form.reset();
            dialogVisible.value = false;
            //location.reload();
            Inertia.reload(); // Actualiza la lista de productos
        },
    });

};

const isAddPromo = ref(false);
const dialogVisible = ref(false);

//open add modal
const openAddModel = () => {
    //clearForm(); // Limpiar los campos antes de añadir una promo
    isAddPromo.value = true
    dialogVisible.value = true
}

const deletePromo = (id) => {
    form.delete(route('admin.promo.delete', id), {
        onSuccess: () => {
            successMessage.value = 'Promoción eliminada';
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

};

</script>

<template>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg h-screen">
        <!--Inicio de ventana-->
        <el-dialog v-model="dialogVisible" :title="'Añadir Promoción'" width="500" :before-close="handleClose" class="dark:bg-gray-800">
            <!--Inicio de formulario-->
            <form @submit.prevent="submit()" enctype="multipart/form-data" class="max-w-md mx-auto">
                <div class="relative z-0 w-full mb-5 group dark:bg-gray-800">
                    <input type="number" v-model="form.descuento" id="descuento"
                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                        placeholder=" " required />
                    <label for="descuento"
                        class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Ingresa
                        la cantidad del descuento</label>
                </div>
                <!--Iicio calendario-->
                <div class="mb-4 w-full md:w-1/3">
                    <label for="fecha_final" class="block text-sm font-medium text-gray-700 dark:text-gray-600">
                        Fecha Final
                    </label>
                    <input type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        id="fecha_final" v-model="form.fecha_final" required min="{{ date('Y-m-d') }}">
                </div>
                <!--Fin calendario-->
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Añadir</button>
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
                        Descuento de la promoción
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Fin de la promoción
                    </th>
                    <td class="px-6 py-4">
                        <el-button type="primary" @click="openAddModel">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Añadir Promoción</el-button>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="promo in promociones" :key="promo.id"
                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ promo.descuento }} %
                    </th>
                    <td class="px-6 py-4">
                        {{ promo.fecha_final }}
                    </td>
                    <td class="px-6 py-4">
                        <el-button type="danger" @click="deletePromo(promo.id)">
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