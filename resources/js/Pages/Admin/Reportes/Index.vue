<template>
    <AdminLayout>
        <Reporte>

        </Reporte>
        <p>Total de visitas: {{ count }}</p>
    </AdminLayout>
</template>
<script setup>
defineProps(['count']);
import AdminLayout from '../Components/AdminLayout.vue';
import Reporte from './Reporte.vue';
</script>