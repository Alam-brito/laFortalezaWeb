<template>
    <AdminLayout>
        <ListaPromo>

        </ListaPromo>
        <p>Total de visitas: {{ count }}</p>  
    </AdminLayout>
</template>

<script setup>
import ListaPromo from './ListaPromo.vue';
import AdminLayout from '../Components/AdminLayout.vue';
defineProps(['count']);
</script>