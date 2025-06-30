<template>
    <AdminLayout>
        <Crear>

        </Crear>
        <div>
            <p>Total de visitas: {{ count }}</p>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '../Components/AdminLayout.vue';
import Crear from './Crear.vue';
defineProps(['count']);
</script>