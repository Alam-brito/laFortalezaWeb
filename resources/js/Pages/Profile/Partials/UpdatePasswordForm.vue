<script setup>
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const showConfirmationModal = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    showConfirmationModal.value = true;
};

const confirmPasswordUpdate = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showConfirmationModal.value = false;
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
            showConfirmationModal.value = false;
        },
    });
};

const closeConfirmationModal = () => {
    showConfirmationModal.value = false;
};
</script>

<template>
    <div
        class="max-w-xl mx-auto bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden transition-all duration-300 ease-in-out transform hover:scale-[1.01]">
        <!-- Confirmation Modal -->
        <div v-if="showConfirmationModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div
                class="bg-white dark:bg-gray-700 rounded-2xl shadow-2xl p-8 max-w-md w-full transform transition-all duration-300 ease-out scale-100 opacity-100">
                <div class="flex flex-col items-center space-y-6">
                    <svg class="w-24 h-24 text-yellow-500 animate-pulse" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>

                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                            Confirmar Cambio de Contraseña
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            ¿Estás seguro de que deseas cambiar tu contraseña? Asegúrate de recordarla.
                        </p>
                    </div>

                    <div class="flex space-x-4 w-full">
                        <button @click="confirmPasswordUpdate"
                            class="flex-1 bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition-colors">
                            Confirmar
                        </button>
                        <button @click="closeConfirmationModal"
                            class="flex-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 py-3 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-8 sm:px-10">
            <header class="mb-8 text-center">
                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-3 tracking-tight">
                    Actualizar Contraseña
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                    Asegúrese de que su cuenta utilice una contraseña larga y aleatoria para permanecer seguro.
                </p>
            </header>

            <form @submit.prevent="updatePassword" class="space-y-6">
                <div class="group relative">
                    <InputLabel for="current_password" value="Contraseña actual"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300 group-focus-within:text-indigo-600" />

                    <div class="relative">
                        <TextInput id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                            type="password" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg 
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
                                   dark:bg-gray-700 dark:text-gray-200 
                                   transition-all duration-300 ease-in-out
                                   group-focus-within:shadow-md" autocomplete="current-password" />
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400 dark:text-gray-500 
                                   transition-all duration-300 group-focus-within:text-indigo-500 
                                   group-focus-within:rotate-6 group-focus-within:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>

                    <InputError :message="form.errors.current_password" class="mt-2 text-sm" />
                </div>

                <div class="group relative">
                    <InputLabel for="password" value="Nueva contraseña"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300 group-focus-within:text-indigo-600" />

                    <div class="relative">
                        <TextInput id="password" ref="passwordInput" v-model="form.password" type="password" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg 
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
                                   dark:bg-gray-700 dark:text-gray-200 
                                   transition-all duration-300 ease-in-out
                                   group-focus-within:shadow-md" autocomplete="new-password" />
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400 dark:text-gray-500 
                                   transition-all duration-300 group-focus-within:text-indigo-500 
                                   group-focus-within:-rotate-6 group-focus-within:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>

                    <InputError :message="form.errors.password" class="mt-2 text-sm" />
                </div>

                <div class="group relative">
                    <InputLabel for="password_confirmation" value="Confirmar contraseña"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300 group-focus-within:text-indigo-600" />

                    <div class="relative">
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg 
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
                                   dark:bg-gray-700 dark:text-gray-200 
                                   transition-all duration-300 ease-in-out
                                   group-focus-within:shadow-md" autocomplete="new-password" />
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400 dark:text-gray-500 
                                   transition-all duration-300 group-focus-within:text-indigo-500 
                                   group-focus-within:rotate-6 group-focus-within:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 4.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>

                    <InputError :message="form.errors.password_confirmation" class="mt-2 text-sm" />
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0 sm:space-x-4">
                    <PrimaryButton :disabled="form.processing" class="w-full sm:w-auto px-6 py-3 rounded-lg transition-all duration-300 
                               hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2
                               bg-indigo-600 hover:bg-indigo-700 text-white font-semibold">
                        <span class="flex items-center justify-center">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Guardar Cambios
                        </span>
                    </PrimaryButton>

                    <Transition enter-active-class="transition ease-out duration-300"
                        enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                        <p v-if="form.recentlySuccessful"
                            class="text-sm text-green-600 dark:text-green-400 animate-pulse">
                            ✓ Contraseña actualizada exitosamente
                        </p>
                    </Transition>
                </div>
            </form>
        </div>
    </div>
</template>