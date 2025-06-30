<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Mensajes de error personalizados
const passwordErrors = ref([]);
const confirmPasswordError = ref('');
const showPassword = ref(false); // Estado para mostrar/ocultar contraseña
const showConfirmPassword = ref(false); // Estado para la confirmación

// Función para validar la contraseña en tiempo real
const validatePassword = () => {
    passwordErrors.value = [];

    const password = form.password;

    if (password.length < 8 || password.length > 20) {
        passwordErrors.value.push("La contraseña debe tener entre 8 y 20 caracteres.");
    }
    if (!/[A-Z]/.test(password)) {
        passwordErrors.value.push("Debe contener al menos una letra mayúscula.");
    }
    if (!/[a-z]/.test(password)) {
        passwordErrors.value.push("Debe contener al menos una letra minúscula.");
    }
    if (!/[0-9]/.test(password)) {
        passwordErrors.value.push("Debe contener al menos un número.");
    }
    if (!/[@$!%*?&]/.test(password)) {
        passwordErrors.value.push("Debe contener al menos un carácter especial (@$!%*?&).");
    }

    // Validar si la confirmación coincide
    validateConfirmPassword();
};

// Validar que ambas contraseñas sean iguales
const validateConfirmPassword = () => {
    if (form.password !== form.password_confirmation) {
        confirmPasswordError.value = "Las contraseñas no coinciden.";
    } else {
        confirmPasswordError.value = "";
    }
};

// Validar antes de enviar el formulario
const submit = () => {
    validatePassword();

    if (passwordErrors.value.length > 0 || confirmPasswordError.value) {
        return;
    }

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Registro" />

        <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-800 md:text-2xl dark:text-white">
            Registra un nuevo usuario para acceder a la plataforma
        </h1>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Nombre -->
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    Nombre completo
                </label>
                <TextInput id="name" type="text"
                    class="block w-full px-3 py-2 bg-gray-200 dark:bg-gray-900 text-gray-800 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500 dark:placeholder-gray-500"
                    placeholder="Ingresa tu nombre" v-model="form.name" required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    Correo Electrónico
                </label>
                <TextInput id="email" type="email"
                    class="block w-full px-3 py-2 bg-gray-200 dark:bg-gray-900 text-gray-800 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500 dark:placeholder-gray-500"
                    placeholder="tucorreo@ejemplo.com" v-model="form.email" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Contraseña -->
            <div class="relative">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    Contraseña
                </label>
                <div class="relative">
                    <TextInput :type="showPassword ? 'text' : 'password'" id="password" v-model="form.password" required
                        @input="validatePassword"
                        class="block w-full px-3 py-2 bg-gray-200 dark:bg-gray-900 text-gray-800 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500 dark:placeholder-gray-500"
                        placeholder="••••••••" autocomplete="new-password" />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-600 dark:text-gray-400">
                        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </button>
                </div>
                <!-- Mostrar errores en tiempo real -->
                <p v-for="error in passwordErrors" :key="error" class="mt-1 text-sm text-red-500">
                    {{ error }}
                </p>
            </div>

            <!-- Confirmación de contraseña -->
            <div class="relative">
                <label for="password_confirmation"
                    class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    Confirmar Contraseña
                </label>
                <div class="relative">
                    <TextInput :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation"
                        v-model="form.password_confirmation" required @input="validateConfirmPassword"
                        class="block w-full px-3 py-2 bg-gray-200 dark:bg-gray-900 text-gray-800 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500 dark:placeholder-gray-500"
                        placeholder="••••••••" autocomplete="new-password" />
                    <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-600 dark:text-gray-400">
                        <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </button>
                </div>
                <p v-if="confirmPasswordError" class="mt-1 text-sm text-red-500">
                    {{ confirmPasswordError }}
                </p>
            </div>

            <!-- Botón de registro -->
            <div class="flex items-center justify-between">
                <Link :href="route('login')"
                    class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">
                ¿Ya estás registrado?
                </Link>
                <PrimaryButton
                    class="bg-blue-600 hover:bg-blue-700 dark:bg-green-700 dark:text-white dark:hover:bg-lime-600 text-white py-2 px-4 rounded-lg focus:ring-4 focus:ring-lime-500"
                    :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                    Registrarse
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
