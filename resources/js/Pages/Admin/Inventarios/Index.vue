<template>
    <AdminLayout>
        <VistaInventario>
            
        </VistaInventario>
        <p>Total de visitas: {{ count }}</p>  
    </AdminLayout>


</template>


<script setup>
import AdminLayout from '../Components/AdminLayout.vue';
import VistaInventario from './VistaInventario.vue';
defineProps(['count']);
</script>