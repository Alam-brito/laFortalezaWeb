<template>
    <AdminLayout>
        <Lista>

        </Lista>
        <p>Total de visitas: {{ count }}</p>  
    </AdminLayout>
</template>

<script setup>
import Lista from './Lista.vue';
import AdminLayout from '../Components/AdminLayout.vue';
defineProps(['count']);
</script>