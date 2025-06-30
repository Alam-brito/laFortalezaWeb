<script setup>
import UserLayouts from '../User/Layouts/UserLayouts.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    count: {
        type: Number,
        default: 0
    }
});
</script>

<template>

    <Head title="Perfil" />

    <UserLayouts>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow-lg sm:rounded-2xl sm:p-8 dark:bg-gray-800 
                           transition-all duration-500 hover:shadow-2xl transform hover:scale-[1.015]">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl animate-fade-in" />
                </div>

                <div class="bg-white p-4 shadow-lg sm:rounded-2xl sm:p-8 dark:bg-gray-800 
                           transition-all duration-500 hover:shadow-2xl transform hover:scale-[1.015]">
                    <UpdatePasswordForm class="max-w-xl animate-fade-in-up" />
                </div>

                <div class="bg-white p-4 shadow-lg sm:rounded-2xl sm:p-8 dark:bg-gray-800 
                           transition-all duration-500 hover:shadow-2xl transform hover:scale-[1.015]">
                    <DeleteUserForm class="max-w-xl animate-fade-in-down" />
                </div>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 animate-fade-in-right">
                Total de visitas:
                <span class="font-bold text-gray-800 dark:text-gray-200 animate-bounce-slow">
                    {{ count }}
                </span>
            </div>
        </div>
    </UserLayouts>
</template>

<style>
@keyframes fade-in-left {
    0% {
        opacity: 0;
        transform: translateX(-20px);
    }

    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fade-in-right {
    0% {
        opacity: 0;
        transform: translateX(20px);
    }

    100% {
        opacity: 1;
        transform: translateX(0);
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

@keyframes bounce-slow {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.1);
    }
}

.animate-fade-in-left {
    animation: fade-in-left 0.6s ease-out;
}

.animate-fade-in-right {
    animation: fade-in-right 0.6s ease-out;
}

.animate-fade-in {
    animation: fade-in 0.8s ease-out;
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out;
}

.animate-fade-in-down {
    animation: fade-in-down 0.6s ease-out;
}

.animate-bounce-slow {
    animation: bounce-slow 1s infinite;
}
</style>