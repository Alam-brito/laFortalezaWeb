<script setup>
import { Link, usePage, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';
// Definir la variable form correctamente
const form = useForm({});

const search = ref(usePage().props.search);

const canLogin = usePage().props.canLogin;
const canRegister = usePage().props.canRegister;
const auth = usePage().props.auth;

watch(search, (newValue) => {
    const url = new URL(route('user.home'));
    url.searchParams.set('search', newValue);

    // Actualiza la URL y envía la solicitud al backend
    router.visit(url.toString(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
});

const service = () => {
    form.get(route('servicio.index'))
};

</script>
<template>
    <nav
        class="bg-gradient-to-r from-teal-50 to-yellow-50 dark:from-slate-900 dark:to-slate-800 border-b border-teal-300 dark:border-gray-700">

        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            <Link :href="route('user.home')" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img src="assets/images/logo.png" class="h-8" />
            <span
                class="self-center font-semibold whitespace-nowrap text-gray-800 dark:text-gray-100">AgroVeterinaria</span>
            </Link>

            <div v-if="canLogin" class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">

                <button v-if="auth.user" type="button"
                    class="flex text-sm bg-gray-300 dark:bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-400 dark:focus:ring-gray-600"
                    id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown"
                    data-dropdown-placement="bottom">
                    <span class="sr-only">Open user menu</span>
                    <img class="w-8 h-8 rounded-full" src="https://cdn-icons-png.flaticon.com/512/9385/9385289.png"
                        alt="user photo">
                </button>

                <div v-else>
                    <Link :href="route('login')" type="button"
                        class="text-gray-800 bg-gray-300 hover:bg-gray-400 dark:text-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 font-medium rounded-lg text-sm px-5 py-2.5">
                    Iniciar Sesión
                    </Link>
                    <Link :href="route('register')" v-if="canRegister" type="button"
                        class="text-gray-800 bg-teal-600 hover:bg-teal-500 dark:text-gray-100 dark:bg-teal-700 dark:hover:bg-teal-600 font-medium rounded-lg text-sm px-5 py-2.5">
                    Registrarse
                    </Link>
                </div>

                <!-- Dropdown menu -->
                <div class="z-50 hidden my-4 text-base list-none bg-gray-300 divide-y divide-gray-700 rounded-lg shadow dark:bg-gray-900 dark:divide-gray-800"
                    id="user-dropdown">
                    <div class="px-4 py-3">
                        <span class="block text-sm text-gray-800 dark:text-gray-400">
                            Bienvenido {{ auth.user?.name || 'Usuario' }}
                        </span>
                        <span class="block text-sm text-gray-800 truncate dark:text-gray-500">
                            {{ auth.user?.email || 'Sin correo' }}
                        </span>

                    </div>

                    <ul class="py-2" aria-labelledby="user-menu-button">

                        <li>
                            <Link
                                class="w-full py-2 px-4 text-black hover:bg-lime-600 dark:hover:bg-lime-700 dark:hover:text-white dark:text-white flex items-center"
                                :href="route('profile.edit')" as="button" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 mr-3 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                                Mi Perfil
                            </Link>
                        </li>
                    </ul>

                    <ul class="py-2" aria-labelledby="user-menu-button">

                        <li>
                            <Link
                                class="w-full py-2 px-4 text-black hover:bg-sky-600 dark:hover:bg-sky-700 dark:hover:text-white dark:text-white flex items-center"
                                :href="route('historial.index')" as="button" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                            Mi Hsitorial</Link>
                        </li>
                    </ul>

                    <ul class="py-2" aria-labelledby="user-menu-button">


                        <li>
                            <Link :href="route('logout')" method="post" as="button"
                                class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 
                                               dark:text-red-400 dark:hover:bg-red-900/30 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Cerrar Sesión
                            </Link>
                        </li>
                    </ul>
                </div>
                <!--
                <button data-collapse-toggle="navbar-user" type="button"
                    class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-400 rounded-lg md:hidden hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600 dark:hover:bg-gray-800 dark:focus:ring-gray-500">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 17 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 1h15M1 7h15M1 13h15" />
                    </svg>
                </button>
                -->
            </div>
            <!--Barra de búsqueda-->
            <div class="w-full md:w-1/2 mt-2">
                <form class="flex items-center">
                    <label for="simple-search" class="sr-only">Search</label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input v-model="search" type="text" id="search"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Buscar">
                    </div>
                </form>
            </div>
            <!--Fin Barra de búsqueda-->
            <button type="button" class="flex items-center gap-x-2 text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 
           hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 
           dark:focus:ring-green-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-4 mt-4"
                @click="service">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                </svg>
                <p>NUESTROS SERVICIOS</p>
            </button>

        </div>
    </nav>
</template>