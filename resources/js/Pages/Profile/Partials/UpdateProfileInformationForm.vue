<script setup>
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const showConfirmationModal = ref(false);
const confirmationAction = ref('');

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

const handleSubmit = () => {
    showConfirmationModal.value = true;
    confirmationAction.value = 'guardar';

    // Simulate form submission after confirmation
    form.patch(route('profile.update'), {
        onSuccess: () => {
            showConfirmationModal.value = false;
        }
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
                    <svg class="w-24 h-24 text-indigo-500 animate-pulse" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                            Confirmar Cambios
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            ¿Estás seguro de que deseas guardar los cambios en tu perfil?
                        </p>
                    </div>

                    <div class="flex space-x-4 w-full">
                        <button @click="form.patch(route('profile.update'))"
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
                    Información del Perfil
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                    Actualice la información de su perfil de manera segura y sencilla.
                </p>
            </header>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="group relative">
                    <InputLabel for="name" value="Nombre"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300 group-focus-within:text-indigo-600" />

                    <div class="relative">
                        <TextInput id="name" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg 
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
                                   dark:bg-gray-700 dark:text-gray-200 
                                   transition-all duration-300 ease-in-out
                                   group-focus-within:shadow-md" v-model="form.name" required autofocus
                            autocomplete="name" />
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400 dark:text-gray-500 
                                   transition-all duration-300 group-focus-within:text-indigo-500 
                                   group-focus-within:rotate-6 group-focus-within:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    <InputError class="mt-2 text-sm" :message="form.errors.name" />
                </div>

                <div class="group relative">
                    <InputLabel for="email" value="Email"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300 group-focus-within:text-indigo-600" />

                    <div class="relative">
                        <TextInput id="email" type="email" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg 
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
                                   dark:bg-gray-700 dark:text-gray-200 
                                   transition-all duration-300 ease-in-out
                                   group-focus-within:shadow-md" v-model="form.email" required
                            autocomplete="username" />
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400 dark:text-gray-500 
                                   transition-all duration-300 group-focus-within:text-indigo-500 
                                   group-focus-within:-rotate-6 group-focus-within:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <InputError class="mt-2 text-sm" :message="form.errors.email" />
                </div>

                <div v-if="mustVerifyEmail && user.email_verified_at === null"
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-300">
                        Su dirección de correo electrónico no está verificada.
                        <Link :href="route('verification.send')" method="post" as="button"
                            class="ml-2 text-yellow-600 dark:text-yellow-400 hover:underline font-semibold transition-colors">
                        Reenviar correo de verificación
                        </Link>
                    </p>

                    <div v-show="status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-600 dark:text-green-400 animate-bounce">
                        Se ha enviado un nuevo enlace de verificación.
                    </div>
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
                            ✓ Cambios guardados exitosamente
                        </p>
                    </Transition>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
/* Additional custom styles can be added here if needed */
</style>