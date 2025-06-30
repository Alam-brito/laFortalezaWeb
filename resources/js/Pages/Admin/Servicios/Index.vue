<template>
    <AdminLayout>
        <ListaServicios>
            
        </ListaServicios>
        <p>Total de visitas: {{ count }}</p>  
    </AdminLayout>
</template>

<script setup>
defineProps(['count']);
import AdminLayout from '../Components/AdminLayout.vue';
import ListaServicios from './ListaServicios.vue';
</script>