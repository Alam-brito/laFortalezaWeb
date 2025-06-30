<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

import { onMounted } from 'vue';
import { initFlowbite } from 'flowbite';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('check.user.role'), {
        onFinish: () => form.reset('password'),
        onSuccess: () => {
            Swal.fire({
                title: '¡Acceso correcto!',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                timer: 3000,
                timerProgressBar: true,
            });
        },
    });
};

onMounted(() => {
    initFlowbite();
});
</script>

<template>
    <section class="relative min-h-screen flex items-center justify-center bg-cover bg-center"
        style="background-image: url('https://www.seedprod.com/wp-content/uploads/2021/09/welcome-login-page-template.jpg');">

        <!-- Capa de superposición para mejorar la legibilidad -->
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>

        <div
            class="relative flex flex-col md:flex-row items-center w-full max-w-5xl mx-auto p-4 md:p-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg">

            <!-- Sección de bienvenida para ambos formatos -->
            <div class="w-full md:w-1/2 px-2 md:px-6 mb-4 md:mb-0 text-black dark:text-white">
                <!-- En versión móvil, usamos un div con altura fija -->
                <div class="block md:hidden w-full h-40 rounded-lg bg-cover bg-center"
                    style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQkglX7StajfhQytzUb9xjrBjfNcTzLtlMK_w&s');">
                </div>

                <!-- En versión desktop, usamos la imagen completa como antes -->
                <img class="hidden md:block w-full h-auto object-cover rounded-lg"
                    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQkglX7StajfhQytzUb9xjrBjfNcTzLtlMK_w&s"
                    alt="Imagen de bienvenida">
            </div>

            <!-- Formulario de Login -->
            <div class="w-full md:w-1/2 p-4 md:p-8 bg-white dark:bg-gray-800 rounded-lg shadow-md 
            transition-transform transform hover:scale-105 
            shadow-lg hover:shadow-2xl duration-300 ease-in-out">
                <div class="flex items-center mb-6 text-xl md:text-2xl font-semibold text-gray-800 dark:text-white">
                    <img class="w-10 h-10 md:w-14 md:h-14 mr-2" src="assets/images/logo.png" alt="logo">
                    La Fortaleza
                </div>

                <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white">
                    Inicia sesión con tu cuenta
                </h2>

                <form @submit.prevent="submit" class="mt-4 space-y-4">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Email
                        </label>
                        <TextInput type="email" name="email" id="email"
                            class="block w-full px-4 py-2 bg-gray-200 dark:bg-gray-900 text-gray-800 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500 dark:placeholder-gray-500"
                            placeholder="tucorreo@ejemplo.com" v-model="form.email" required autofocus
                            autocomplete="username" />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-400">Contraseña</label>
                        <TextInput type="password" name="password" id="password"
                            class="block w-full px-4 py-2 bg-gray-200 dark:bg-gray-900 text-gray-800 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500 dark:placeholder-gray-500"
                            placeholder="••••••••" v-model="form.password" required autocomplete="current-password" />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <!-- Recordar contraseña y enlace de recuperación -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center mb-2 sm:mb-0">
                            <Checkbox name="remember" v-model:checked="form.remember"
                                class="w-4 h-4 border-gray-300 dark:border-gray-700 bg-gray-200 dark:bg-gray-900 focus:ring-3 focus:ring-blue-500" />
                            <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                Recordar contraseña
                            </label>
                        </div>
                        <Link v-if="canResetPassword" :href="route('password.request')"
                            class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        ¿Olvidaste tu contraseña?
                        </Link>
                    </div>
                    <!-- Botón de Iniciar Sesión -->
                    <PrimaryButton class="w-full justify-center bg-orange-600 hover:bg-orange-700 text-white py-2 rounded-md 
            focus:outline-none focus:ring-4 focus:ring-orange-500 dark:focus:ring-green-400 
            dark:bg-green-500 dark:text-white" :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing">
                        Iniciar sesión
                    </PrimaryButton>
                </form>
                <p class="mt-2 text-sm font-light text-center text-gray-500 dark:text-gray-400">
                    ¿No tienes una cuenta?
                    <Link :href="route('register')"
                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                    Regístrate
                    </Link>
                </p>
            </div>
        </div>
    </section>
</template>