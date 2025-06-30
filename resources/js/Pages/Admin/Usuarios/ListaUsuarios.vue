<script setup>
import { usePage, useForm } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import Swal from 'sweetalert2';

const { users, roles, permisos } = usePage().props;

const userRoles = reactive({});
const userPermisos = reactive({});

const form = useForm({}); // Inicializar `useForm`

users.forEach(user => {
    userRoles[user.id] = user.roles.length > 0 ? user.roles : [];
    userPermisos[user.id] = user.permisos;
});

const newRole = ref('');
const newPermission = ref('');
const isAddingRole = ref(false);
const isAddingPermission = ref(false);

// Función para actualizar el rol y permisos del usuario
const updateUser = (id) => {
    const rolesArray = Array.isArray(userRoles[id]) ? [...new Set(userRoles[id])] : [userRoles[id]];
    const permisosArray = Array.isArray(userPermisos[id]) ? [...new Set(userPermisos[id])] : [userPermisos[id]];

    // Convertir los roles a números enteros
    const rolesNumericos = rolesArray
        .map(r => parseInt(r)) // Convertir a número
        .filter(r => !isNaN(r)); // Filtrar valores inválidos

    // Asegurar que los permisos sean strings
    const permisosStrings = permisosArray.filter(p => p);

    const form = useForm({
        roles: rolesNumericos, // Enviar solo IDs numéricos
        permisos: permisosStrings, // Permisos como strings
    });

    form.put(route('admin.usuarios.update', id), {
        onSuccess: () => {
            Swal.fire({
                title: '¡Éxito!',
                text: 'Rol y permisos actualizados correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                timer: 3000,
                timerProgressBar: true,
            });
            location.reload();
        },
        onError: (errors) => {
            console.log(errors);
            Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al actualizar el rol y permisos.',
                icon: 'error',
                confirmButtonText: 'Aceptar',
            });
        },
    });
};


const addRole = () => {
    if (!newRole.value.trim()) return;

    isAddingRole.value = true;
    useForm({ nombre: newRole.value }).post(route('admin.roles.store'), {

        onSuccess: () => {
            dialogVisible.value = false
            Swal.fire('¡Éxito!', 'Rol agregado.', 'success');
            newRole.value = '';
            location.reload();
        },
        onError: () => {
            Swal.fire('Error', 'No se pudo agregar.', 'error');
        },
        onFinish: () => {
            isAddingRole.value = false;
        }
    });
};

const addPermission = () => {
    if (!newPermission.value.trim()) return;

    isAddingPermission.value = true;
    useForm({ nombre: newPermission.value }).post(route('admin.permisos.store'), {
        onSuccess: () => {
            dialogVisible.value = false
            Swal.fire('¡Éxito!', 'Permiso agregado.', 'success');
            newPermission.value = '';
            location.reload();
        },
        onError: () => {
            Swal.fire('Error', 'No se pudo agregar.', 'error');
        },
        onFinish: () => {
            isAddingPermission.value = false;
        }
    });
};


const deleteRole = (id) => {
    dialogVisible.value = false
    Swal.fire({
        title: "¿Estás seguro?",
        text: "¡Este rol será eliminado permanentemente!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('admin.roles.delete', id), {
                onSuccess: () => {
                    dialogVisible.value = false
                    Swal.fire("¡Eliminado!", "El rol ha sido eliminado.", "success");
                    location.reload();
                },
                onError: () => {
                    Swal.fire("Error", "No se pudo eliminar el rol.", "error");
                }
            });
        }
    });
};

const deletePermission = (id) => {
    dialogVisible.value = false
    Swal.fire({
        title: "¿Estás seguro?",
        text: "¡Este permiso será eliminado permanentemente!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('admin.permisos.delete', id), {
                onSuccess: () => {
                    dialogVisible.value = false
                    Swal.fire("¡Eliminado!", "El permiso ha sido eliminado.", "success");
                    location.reload();
                },
                onError: () => {
                    Swal.fire("Error", "No se pudo eliminar el permiso.", "error");
                }
            });
        }
    });
};

// Función para alternar la selección de un permiso
const togglePermiso = (userId, permiso) => {
    if (userPermisos[userId].includes(permiso)) {
        userPermisos[userId] = userPermisos[userId].filter(p => p !== permiso); // Quitar permiso
    } else {
        userPermisos[userId].push(permiso); // Agregar permiso
    }
};

// Función para eliminar usuario
const deleteUser = (id) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'No podrás deshacer esta acción.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            const form = useForm({});
            form.delete(route('admin.usuarios.delete', id), {
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El usuario ha sido eliminado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        timer: 3000,
                        timerProgressBar: true,
                    });
                    location.reload();
                },
                onError: () => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al eliminar el usuario.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                    });
                },
            });
        }
    });
};

const dialogVisible = ref(false);

//open add modal
const openAddModel = () => {
    dialogVisible.value = true
}

//Para quitar un rol del usuario
const removeUserRole = (userId, rolId) => {
    if (!rolId) return; // Verificamos que haya un rol seleccionado

    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Este rol será eliminado del usuario.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = useForm({}); // Inicializar correctamente el formulario
            form.delete(route('admin.usuarios.removerRol', { userId, rolId }), {
                onSuccess: () => {
                    Swal.fire("¡Eliminado!", "El rol ha sido eliminado.", "success");
                    location.reload(); // Recargar para ver los cambios
                },
                onError: () => {
                    Swal.fire("Error", "No se pudo eliminar el rol.", "error");
                }
            });
        }
    });
};

</script>

<template>
    <div
        class="relative overflow-x-auto shadow-lg sm:rounded-lg mx-4 mt-4 bg-white dark:bg-gray-900 transition-all duration-300">
        <div class="flex justify-end p-4">
            <button @click="openAddModel"
                class="flex items-center bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Administrar Roles y Permisos
            </button>
        </div>

        <el-dialog v-model="dialogVisible" width="800px" class="dark:bg-gray-800 rounded-2xl overflow-hidden"
            :modal-class="'transition-all duration-300'">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Gestión de Roles y Permisos
                </h2>

                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-inner">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200">Roles</h3>
                        <div class="space-y-3">
                            <div v-for="rol in roles" :key="rol.id"
                                class="flex items-center justify-between bg-white dark:bg-gray-600 p-3 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                <span class="text-gray-700 dark:text-gray-200">{{ rol.nombre }}</span>
                                <button @click="deleteRole(rol.id)"
                                    class="text-red-500 hover:text-red-700 transition-colors p-1 rounded-full hover:bg-red-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-inner">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200">Permisos</h3>
                        <div class="space-y-3">
                            <div v-for="permiso in permisos" :key="permiso.id"
                                class="flex items-center justify-between bg-white dark:bg-gray-600 p-3 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                <span class="text-gray-700 dark:text-gray-200">{{ permiso.nombre }}</span>
                                <button @click="deletePermission(permiso.id)"
                                    class="text-red-500 hover:text-red-700 transition-colors p-1 rounded-full hover:bg-red-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <input v-model="newRole" placeholder="Nuevo rol"
                            class="w-full px-4 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all">
                        <button @click="addRole" :disabled="isAddingRole"
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-all flex items-center justify-center">
                            <span v-if="!isAddingRole">Agregar Rol</span>
                            <svg v-else class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                    fill="none" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-2">
                        <input v-model="newPermission" placeholder="Nuevo permiso"
                            class="w-full px-4 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all">
                        <button @click="addPermission" :disabled="isAddingPermission"
                            class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-all flex items-center justify-center">
                            <span v-if="!isAddingPermission">Agregar Permiso</span>
                            <svg v-else class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                    fill="none" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </el-dialog>

        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                <tr>
                    <th class="px-6 py-4">Usuario</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Roles</th>
                    <th class="px-6 py-4">Permisos</th>
                    <th class="px-6 py-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id"
                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                        {{ user.name }}
                    </td>
                    <td class="px-6 py-4">{{ user.email }}</td>
                    <td class="px-6 py-4 space-y-2">
                        <div class="flex flex-wrap gap-2">
                            <span v-for="rol in user.roles" :key="rol"
                                class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ rol }}
                            </span>
                        </div>

                        <div class="relative mt-2">
                            <select v-model="userRoles[user.id]"
                                class="w-full px-3 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-white appearance-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-8">
                                <option value="" disabled>Seleccionar rol</option>
                                <option v-for="rol in roles" :key="rol.id" :value="rol.id" class="dark:bg-gray-700">
                                    {{ rol.nombre }}
                                </option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <button @click="removeUserRole(user.id, userRoles[user.id])"
                            class="mt-2 w-full px-3 py-1.5 text-sm bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors flex items-center justify-center gap-1"
                            :disabled="!userRoles[user.id]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Quitar Rol
                        </button>
                    </td>

                    <td class="px-6 py-4">
                        <div class="max-h-40 overflow-y-auto p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div v-for="permiso in permisos" :key="permiso.id"
                                class="flex items-center gap-3 p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md transition-colors">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" :value="permiso.nombre"
                                        :checked="userPermisos[user.id].includes(permiso.nombre)"
                                        @change="togglePermiso(user.id, permiso.nombre)" class="hidden">
                                    <div
                                        class="w-5 h-5 border-2 border-gray-300 dark:border-gray-500 rounded-md flex items-center justify-center transition-colors">
                                        <svg v-if="userPermisos[user.id].includes(permiso.nombre)"
                                            class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </label>
                                <span class="text-gray-700 dark:text-gray-200 text-sm">{{ permiso.nombre }}</span>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-2">
                            <button @click="updateUser(user.id)"
                                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center gap-2 transition-transform hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Guardar
                            </button>
                            <button @click="deleteUser(user.id)"
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center gap-2 transition-transform hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>