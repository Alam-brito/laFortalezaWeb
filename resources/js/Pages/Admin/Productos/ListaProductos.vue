<script setup>
import { usePage, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import Swal from 'sweetalert2'; // Importar SweetAlert2
import { UploadFilled } from '@element-plus/icons-vue'

const productos = computed(() => usePage().props.productos);
const inventarios = usePage().props.inventarios;
const promociones = usePage().props.promociones;

const form = useForm({
    nombre: '',
    id_categoria: '',
    id_almacen: '',
    descripcion: '',
    precio: '',
    id_promocion: '',
    stock: '0', // Inicializar con '0' en lugar de ''
    imagen: null,
    visible: true,
});

const categorias = ref([]); // Lista de categorías
const almacenes = ref([]); // Lista de almacenes
const stock = ref(0); // Stock dinámico basado en la categoría y el almacén

// Cargar categorías y almacenes al montar el componente
const fetchInitialData = async () => {
    try {
        categorias.value = await axios.get(route('ruta.categorias')).then(res => res.data);
        almacenes.value = await axios.get(route('ruta.almacenes')).then(res => res.data);
    } catch (error) {
        console.error('Error al cargar datos iniciales:', error);
        // Mostrar mensaje de error con SweetAlert2
        Swal.fire({
            title: 'Error',
            text: 'No se pudieron cargar las categorías o almacenes',
            icon: 'error',
            confirmButtonText: 'Ok'
        });
    }
};

// Calcular el stock dinámico
const calculateStock = async () => {
    if (form.id_categoria && form.id_almacen) {
        try {
            const response = await axios.post(route('ruta.calcular-stock'), {
                id_categoria: form.id_categoria,
                id_almacen: form.id_almacen,
            });
            stock.value = response.data.stock;
            // Asignar al formulario también
            form.stock = stock.value.toString();
        } catch (error) {
            console.error('Error al calcular el stock:', error);
            stock.value = 0;
            form.stock = '0';
        }
    } else {
        stock.value = 0;
        form.stock = '0';
    }
};

// Observa cambios en categoría y almacén para calcular el stock
watch([() => form.id_categoria, () => form.id_almacen], () => {
    calculateStock();
});

// Inicializar datos
fetchInitialData();

// Función personalizada para manejar la subida del archivo
const customUpload = ({ file }) => {
    form.imagen = file; // Asigna el archivo al formulario
    console.log('Archivo subido:', file);

    // Mostrar vista previa de la imagen
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    console.log('Datos que se enviarán:', form);
    form.post(route('admin.productos.store'), {
        onSuccess: () => {
            // Mostrar mensaje de éxito con SweetAlert2
            Swal.fire({
                title: 'Éxito',
                text: 'Producto añadido correctamente',
                icon: 'success',
                confirmButtonText: 'Ok'
            });
            form.reset();
            dialogVisible.value = false;
            //Inertia.reload(); // Actualiza la lista de productos
        },
        onError: (errors) => {
            console.error('Errores en el envío:', errors);
            // Mostrar errores con SweetAlert2
            Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al añadir el producto',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        },
        forceFormData: true // Forzar FormData para la subida de archivos
    });
};

const clearForm = () => {
    form.nombre = '';
    form.descripcion = '';
    form.id_categoria = '';
    form.id_almacen = '';
    form.id_promocion = '';
    form.precio = '';
    form.stock = '0';
    form.imagen = null;
    form.visible = true; // Reiniciar a visible por defecto
    previewImage.value = '';
};

const isAddProduct = ref(false);
const dialogVisible = ref(false);
const editMode = ref(false);

// Cerrar modal
const handleClose = (done) => {
    clearForm();
    done();
};

// Abrir modal para añadir
const openAddModel = () => {
    clearForm();
    isAddProduct.value = true;
    dialogVisible.value = true;
    editMode.value = false;
};

const previewImage = ref(''); // Variable reactiva para la previsualización de la imagen

// Editar producto
// Editar producto - DEBUG COMPLETO
// Editar producto - SOLUCIÓN FINAL
const editModel = async (producto) => {
    editMode.value = true;
    isAddProduct.value = false;
    dialogVisible.value = true;

    // Limpiar primero todo el formulario
    clearForm();
    await nextTick();

    // Asignar los valores básicos del producto al formulario
    form.id = producto.id;
    form.nombre = producto.producto_nombre || '';
    form.descripcion = producto.descripcion || '';
    form.precio = producto.precio || '';
    form.stock = producto.stock ? producto.stock.toString() : '0';
    form.visible = producto.visible !== undefined ? producto.visible : true;
    previewImage.value = producto.imagen ? `/storage/${producto.imagen}` : '';
    form.imagen = null;

    setTimeout(() => {
        // Buscar categoría por nombre
        if (producto.categoria_nombre) {
            const categoria = categorias.value.find(c => c.nombre === producto.categoria_nombre);
            if (categoria) {
                form.id_categoria = categoria.id;
                console.log('Categoría encontrada:', categoria.nombre, 'ID:', categoria.id);
            } else {
                console.log('Categoría no encontrada:', producto.categoria_nombre);
            }
        }

        // Buscar almacén por nombre
        if (producto.almacen_nombre) {
            const almacen = almacenes.value.find(a => a.nombre === producto.almacen_nombre);
            if (almacen) {
                form.id_almacen = almacen.id;
                console.log('Almacén encontrado:', almacen.nombre, 'ID:', almacen.id);
            } else {
                console.log('Almacén no encontrado:', producto.almacen_nombre);
            }
        }

        // Buscar promoción por descuento (si tiene descuento > 0)
        if (producto.promocion_descuento && producto.promocion_descuento > 0) {
            const promocion = promociones.find(p => p.descuento == producto.promocion_descuento);
            if (promocion) {
                form.id_promocion = promocion.id;
            } else {
                form.id_promocion = ''; // Asegurarse de que esté vacío si no se encuentra
            }
        } else {
            // Si no hay descuento o es 0, dejar vacío (sin promoción)
            form.id_promocion = '';
        }
    }, 100);
};

const updateProduct = () => {
    // Confirmar con SweetAlert2 antes de editar
    dialogVisible.value = false;
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas editar este producto?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, editar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.post(route('admin.productos.edit', form.id), {
                onSuccess: () => {
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Producto editado correctamente',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    });
                    dialogVisible.value = false;
                    //Inertia.reload(); // Recarga los datos
                },
                onError: (errors) => {
                    console.error('Error al editar:', errors);
                    // Mostrar error
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al editar el producto',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                },
                forceFormData: true // Importante para archivos
            });
        }
    });
};

const deleteProduct = (id) => {
    // Confirmar con SweetAlert2 antes de eliminar
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas eliminar este producto? Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('admin.productos.delete', id), {
                onSuccess: () => {
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'Producto eliminado correctamente',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    });
                    //Inertia.reload(); // Recargar la lista
                },
                onError: (error) => {
                    console.error('Error al eliminar:', error);
                    // Mostrar error
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al eliminar el producto',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                }
            });
        }
    });
};

// Buscador
const search = ref("");
watch(search, (newValue) => {
    const url = new URL(route('admin.productos.index'));
    url.searchParams.set('search', newValue);

    router.visit(url.toString(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
});

// Resaltar el resultado de la búsqueda
const highlightMatch = (text, searchTerm) => {
    if (!searchTerm || !text) return text;
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    return text.replace(regex, '<span class="bg-yellow-400">$1</span>');
};

// Estado para ver detalles en modo móvil
const selectedProduct = ref(null);
const showMobileDetails = ref(false);

// Función para mostrar detalles en móvil
const viewProductDetails = (producto) => {
    selectedProduct.value = producto;
    showMobileDetails.value = true;
};

// Cerrar detalles en móvil
const closeMobileDetails = () => {
    showMobileDetails.value = false;
    selectedProduct.value = null;
};

// Detectar si estamos en un dispositivo móvil
const isMobile = ref(false);

// Comprobar tamaño de pantalla al cargar
const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
};

// Ejecutar al montar el componente y añadir listener para cambios de tamaño
onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

// Limpiar al desmontar
onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});
</script>


<template>
    <section class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 p-4">
        <div class="max-w-7xl mx-auto">
            <!-- Encabezado con efecto neumórfico -->
            <div
                class="flex flex-col md:flex-row items-center justify-between mb-8 p-4 md:p-6 bg-white rounded-2xl shadow-soft-xl dark:bg-gray-800">
                <h1
                    class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-4 md:mb-0 text-center md:text-left">
                    Gestión de Productos
                    <span class="text-green-600 dark:text-green-400">|</span>
                    <span class="text-sm md:text-lg text-gray-500 dark:text-gray-300">Panel administrativo</span>
                </h1>
                <button @click="openAddModel"
                    class="flex items-center space-x-2 bg-green-600 hover:bg-lime-600 text-white px-4 md:px-6 py-2 md:py-3 rounded-lg 
                               transition-all duration-300 transform hover:scale-105 shadow-lg w-full md:w-auto justify-center md:justify-start">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Añadir Producto</span>
                </button>
            </div>

            <!-- Barra de búsqueda mejorada -->
            <div class="mb-6 relative group">
                <div
                    class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input v-model="search" type="text" class="w-full pl-12 pr-4 py-3 rounded-xl border-0 bg-white dark:bg-gray-800 
                              text-gray-900 dark:text-white shadow-lg focus:ring-2 focus:ring-blue-500 
                              transition-all duration-300" placeholder="Buscar productos...">
            </div>

            <!-- Vista para escritorio - Tabla original con mejoras de scroll horizontal -->
            <div class="overflow-hidden rounded-2xl shadow-xl bg-white dark:bg-gray-800 hidden md:block">
                <div class="overflow-x-auto"> <!-- Contenedor con scroll horizontal -->
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-lime-500 to-blue-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Producto</th>
                                <th class="px-6 py-4 text-left font-semibold">Categoría</th>
                                <th class="px-6 py-4 text-left font-semibold">Precio</th>
                                <th class="px-6 py-4 text-left font-semibold">Stock</th>
                                <th class="px-6 py-4 text-left font-semibold">Promoción</th>
                                <th class="px-6 py-4 text-left font-semibold">Visibilidad</th>
                                <th class="px-6 py-4 text-left font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(producto, index) in productos" :key="producto.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                                :style="{ animation: `fadeIn 0.3s ease ${index * 0.05}s forwards`, opacity: 0 }">
                                <!-- Contenido de la tabla mejorado -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden shadow-sm">
                                            <img v-if="producto.imagen" :src="`/storage/${producto.imagen}`"
                                                class="w-full h-full object-cover transform hover:scale-110 transition-transform">
                                            <div v-else
                                                class="w-full h-full bg-gray-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white"
                                                v-html="highlightMatch(producto.producto_nombre, search)"></div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                                {{ producto.descripcion }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 
                                            text-sm font-medium dark:bg-blue-900 dark:text-blue-200">
                                        {{ producto.categoria_nombre }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    <div class="flex items-center">
                                        <span class="text-lg">{{ producto.precio }} Bs.</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-green-500 transition-all duration-500"
                                                :style="{ width: `${Math.min((producto.stock / 100) * 100, 100)}%` }">
                                            </div>
                                        </div>
                                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ producto.stock }} unidades
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="producto.promocion_descuento" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm 
                                            dark:bg-green-900 dark:text-green-200">
                                        -{{ producto.promocion_descuento }}%
                                    </span>
                                    <span v-else class="text-gray-400 text-sm">Sin promoción</span>
                                </td>

                                <td class="px-6 py-4">
                                    <span v-if="producto.visible"
                                        class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm dark:bg-green-900 dark:text-green-200">
                                        Visible
                                    </span>
                                    <span v-else
                                        class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm dark:bg-gray-700 dark:text-gray-300">
                                        Oculto
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <button @click="editModel(producto)" class="p-2 hover:bg-blue-100 rounded-lg transition-colors 
                                                  dark:hover:bg-gray-600 tooltip" data-tip="Editar">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button @click="deleteProduct(producto.id)" class="p-2 hover:bg-red-100 rounded-lg transition-colors 
                                                  dark:hover:bg-gray-600 tooltip" data-tip="Eliminar">
                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vista para móvil - Lista de tarjetas -->
            <div class="md:hidden space-y-4">
                <div v-for="(producto, index) in productos" :key="producto.id"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden"
                    :style="{ animation: `fadeIn 0.3s ease ${index * 0.05}s forwards`, opacity: 0 }">
                    <div class="p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0">
                                <img v-if="producto.imagen" :src="`/storage/${producto.imagen}`"
                                    class="w-full h-full object-cover">
                                <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-medium text-gray-900 dark:text-white truncate"
                                    v-html="highlightMatch(producto.producto_nombre, search)"></h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{ producto.descripcion
                                    }}</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-base font-medium text-gray-900 dark:text-white">{{ producto.precio
                                        }}
                                        Bs.</span>
                                    <span v-if="producto.promocion_descuento"
                                        class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs dark:bg-green-900 dark:text-green-200">
                                        -{{ producto.promocion_descuento }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mt-3">
                            <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Categoría</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{
                                    producto.categoria_nombre }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Stock</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ producto.stock }}
                                    unidades</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <span v-if="producto.visible"
                                class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs dark:bg-green-900 dark:text-green-200">
                                Visible
                            </span>
                            <span v-else
                                class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs dark:bg-gray-700 dark:text-gray-300">
                                Oculto
                            </span>

                            <div class="flex space-x-2">
                                <button @click="editModel(producto)"
                                    class="p-2 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors dark:bg-gray-700 dark:hover:bg-gray-600">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button @click="deleteProduct(producto.id)"
                                    class="p-2 bg-red-50 hover:bg-red-100 rounded-lg transition-colors dark:bg-gray-700 dark:hover:bg-gray-600">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Inicio de ventana-->
        <el-dialog v-model="dialogVisible" :title="editMode ? 'Editar Producto' : 'Añadir Producto'" width="500"
            :before-close="handleClose" class="dark:bg-gray-900 dark:text-white">
            <!--Inicio de formulario-->
            <form @submit.prevent="editMode ? updateProduct() : submit()" enctype="multipart/form-data"
                class="max-w-md mx-auto dark:bg-gray-900">
                <div class="relative z-0 w-full mb-5 group">
                    <input type="text" v-model="form.nombre" id="nombre"
                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                        placeholder=" " required />
                    <label for="nombre"
                        class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-200 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-100 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Nombre
                        del producto</label>
                </div>

                <!-- Categoría -->
                <div>
                    <label for="id_categoria"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Categoría</label>
                    <select v-model="form.id_categoria" id="id_categoria" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="">Seleccione una categoría</option>
                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                            {{ categoria.nombre }}
                        </option>
                    </select>
                </div>

                <!-- Almacén -->
                <div class="mt-4">
                    <label for="id_almacen"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Almacén</label>
                    <select v-model="form.id_almacen" id="id_almacen" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="">Seleccione un almacén</option>
                        <option v-for="almacen in almacenes" :key="almacen.id" :value="almacen.id">
                            {{ almacen.nombre }}
                        </option>
                    </select>
                </div>

                <!-- Descripción -->
                <div class="mt-4">
                    <label for="descripcion"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Descripción</label>
                    <textarea v-model="form.descripcion" id="descripcion" rows="3"
                        class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="Descripción del producto..."></textarea>
                </div>

                <!-- Precio -->
                <div class="mt-4">
                    <label for="precio" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Precio
                        (Bs.)</label>
                    <input type="number" v-model="form.precio" id="precio" step="0.01" min="0" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="0.00">
                </div>

                <!-- Promoción -->
                <div class="mt-4">
                    <label for="id_promocion"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Promoción</label>
                    <select v-model="form.id_promocion" id="id_promocion"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="">Elegir promoción</option>
                        <option v-for="promocion in promociones" :key="promocion.id" :value="promocion.id">
                            {{ promocion.descuento }}% de descuento
                        </option>
                    </select>
                </div>

                <!-- Stock 
                <div class="mt-4">
                    <label for="stock"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Stock</label>
                    <input type="number" v-model="form.stock" id="stock" min="0" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="0">
                </div>
                -->

                <!-- Visibilidad -->
                <div class="flex items-center mt-4">
                    <input id="visible" type="checkbox" v-model="form.visible"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="visible" class="ms-2 text-sm font-medium text-gray-900 dark:text-white">Producto visible
                        en la tienda</label>
                </div>

                <!-- Imagen -->
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Imagen del
                        producto</label>
                    <el-upload class="upload-demo" :http-request="customUpload" accept="image/*" :show-file-list="false"
                        :auto-upload="true">
                        <el-button type="primary" :icon="UploadFilled">Seleccionar imagen</el-button>
                        <template #tip>
                            <div class="el-upload__tip">
                                Solo archivos JPG/PNG/GIF con tamaño menor a 2MB
                            </div>
                        </template>
                    </el-upload>
                    <!-- Vista previa de la imagen -->
                    <div v-if="previewImage" class="mt-3 relative">
                        <img :src="previewImage"
                            class="max-h-64 rounded-lg border border-gray-200 dark:border-gray-700">
                        <button @click="previewImage = ''; form.imagen = null" type="button"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="dialogVisible = false" type="button"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        {{ editMode ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
            <!--Fin de formulario-->
        </el-dialog>
        <!--Fin de ventana-->
    </section>
</template>

<style>
/* Animación de fadeIn para los elementos de la lista */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Tooltip personalizado */
.tooltip {
    position: relative;
}

.tooltip::after {
    content: attr(data-tip);
    position: absolute;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s, visibility 0.3s;
}

.tooltip:hover::after {
    visibility: visible;
    opacity: 1;
}

/* Sombra neumórfica suave */
.shadow-soft-xl {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
}

/* Efecto de línea truncada */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>