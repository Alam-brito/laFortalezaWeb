<template>
    <AdminLayout>
        <ListaProductos>

        </ListaProductos>
        <p>Total de visitas: {{ count }}</p>
    </AdminLayout>

</template>

<script setup>
import AdminLayout from '../Components/AdminLayout.vue';
import ListaProductos from './ListaProductos.vue';
defineProps(['count']);
</script>