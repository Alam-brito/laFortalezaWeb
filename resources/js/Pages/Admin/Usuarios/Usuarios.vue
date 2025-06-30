<template>
    <AdminLayout>
        <ListaUsuarios>
            
        </ListaUsuarios>  
        <p>Total de visitas: {{ count }}</p>  
    </AdminLayout>

</template>

<script setup>
import ListaUsuarios from './ListaUsuarios.vue';
import AdminLayout from '../Components/AdminLayout.vue';
defineProps(['count']);

</script>