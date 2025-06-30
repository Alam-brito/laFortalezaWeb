<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

defineProps(
    ['count'],
    {
        canResetPassword: {
            type: Boolean,
        },
        status: {
            type: String,
        },
    }
);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('admin.login.post'), {
        onFinish: () => form.reset('password'),
        onSuccess: () => {
            Swal.fire({
                title: '¡BIENVENID@!',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                timer: 3000,
                timerProgressBar: true,
            });
        },
    });
};
</script>

<template>
    <div>
        <div class="min-h-screen flex items-center justify-center bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-200">

            <Head title="Iniciar sesión" />

            <div class="w-full max-w-md bg-gray-50 dark:bg-gray-800 rounded-lg shadow-lg p-6 space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-center">Inicia sesión</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        ¿Eres Administrador?, debes confirmar tus credenciales para acceder.
                    </p>
                </div>

                <div v-if="status" class="mb-4 text-sm font-medium text-green-600 dark:text-green-400">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel class="text-gray-900 dark:text-white" for="email" value="Correo Electrónico" />

                        <TextInput id="email" type="email"
                            class="mt-1 block w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="tucorreo@ejemplo.com" v-model="form.email" required autofocus
                            autocomplete="username" />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel class="text-gray-900 dark:text-white" for="password" value="Contraseña" />

                        <TextInput id="password" type="password"
                            class="mt-1 block w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="" v-model="form.password" required autocomplete="current-password" />

                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Recordar contraseña</span>
                        </label>

                        <Link v-if="canResetPassword" :href="route('password.request')"
                            class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <div class="flex items-center justify-center">
                        <PrimaryButton
                            class="w-40 justify-center bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-600 text-gray-100 py-2 rounded-lg transition duration-300 ease-in-out"
                            :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            Iniciar sesión
                        </PrimaryButton>
                    </div>
                </form>
                <p class="text-center text-gray-600 dark:text-gray-400">Total de visitas: {{ count }}</p>
            </div>
        </div>
    </div>
</template>
