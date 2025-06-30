<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6 max-w-xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-3xl p-8 transition-all duration-500 
                    hover:shadow-2xl hover:scale-[1.01] transform will-change-transform">
            <header class="mb-8">
                <div class="flex items-center mb-5 animate-fade-in-down">
                    <svg class="w-12 h-12 text-red-500 mr-5 animate-pulse" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h2 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight">
                        Eliminar cuenta
                    </h2>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed animate-fade-in">
                    Una vez que se elimine su cuenta, todos sus recursos y datos serán eliminados permanentemente.
                    Antes de eliminar su cuenta, por favor descargue cualquier dato o información que desee conservar.
                </p>
            </header>

            <div class="text-center">
                <DangerButton @click="confirmUserDeletion" class="flex items-center justify-center mx-auto space-x-3 group transition-all duration-300 
                           hover:scale-105 active:scale-95 transform will-change-transform">
                    <svg class="w-6 h-6 text-white group-hover:animate-wiggle" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="font-semibold">Eliminar cuenta</span>
                </DangerButton>
            </div>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8 space-y-6 animate-fade-in-up">
                <div class="text-center">
                    <div class="flex justify-center mb-6">
                        <svg class="w-24 h-24 text-red-500 animate-bounce-slow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4 tracking-tight">
                        Confirmar eliminación de cuenta
                    </h2>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                        Una vez que se elimina su cuenta, todos sus recursos y datos serán eliminados permanentemente.
                        Por favor ingrese su contraseña para confirmar.
                    </p>

                    <div class="mt-6">
                        <InputLabel for="password" value="Password" class="sr-only" />

                        <TextInput id="password" ref="passwordInput" v-model="form.password" type="password" class="mt-1 block w-full transition-all duration-300 
                                   focus:ring-2 focus:ring-red-500 focus:border-red-500 
                                   rounded-xl shadow-sm" placeholder="Password" @keyup.enter="deleteUser" />

                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-center space-x-4">
                        <SecondaryButton @click="closeModal" class="px-6 py-3 transition-all duration-300 
                                   hover:scale-105 active:scale-95 transform">
                            Cancelar
                        </SecondaryButton>

                        <DangerButton class="px-6 py-3 transition-all duration-300 
                                   hover:scale-105 active:scale-95 transform"
                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="deleteUser">
                            Eliminar cuenta
                        </DangerButton>
                    </div>
                </div>
            </div>
        </Modal>
    </section>
</template>

<style>
@keyframes fade-in-down {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@keyframes fade-in-up {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes bounce-slow {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-20px);
    }
}

@keyframes wiggle {

    0%,
    100% {
        transform: rotate(0deg);
    }

    25% {
        transform: rotate(-10deg);
    }

    75% {
        transform: rotate(10deg);
    }
}

.animate-fade-in-down {
    animation: fade-in-down 0.6s ease-out;
}

.animate-fade-in {
    animation: fade-in 0.8s ease-out;
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out;
}

.animate-bounce-slow {
    animation: bounce-slow 2s infinite;
}

.animate-wiggle {
    animation: wiggle 0.5s;
}
</style>