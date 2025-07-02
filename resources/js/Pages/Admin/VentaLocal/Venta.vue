<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import axios from "axios";

// Estados reactivos
const searchQuery = ref("");
const selectedCategory = ref("");
const selectedWarehouse = ref("");
const products = ref([]);
const categories = ref([]);
const warehouses = ref([]);
const cart = reactive([]);
const customerName = ref("");
const paymentType = ref("efectivo", "QR");
const showInvoice = ref(false);
const invoiceData = ref(null);
const isLoading = ref(false);
const errorMessage = ref("");
const isCartAnimating = ref(false);
const saleDescription = ref(""); // Nuevo estado para la descripción de la venta
const showQRModal = ref(false); // Nuevo estado para mostrar el QR modal

// Computed para verificar si se debe mostrar el modal QR
const shouldShowQRModal = computed(() => {
    return paymentType.value === "QR" && cart.length > 0 && customerName.value.trim();
});

//Watcher para mostrar/ocultar modal QR automáticamente
const handlePaymentTypeChange = () => {
    if (paymentType.value === "QR" && cart.length > 0 && customerName.value.trim()) {
        showQRModal.value = true;
    } else {
        showQRModal.value = false;
    }
};



// Obtener datos iniciales
const props = defineProps({
    initialProducts: Array,
    categorias: Array,
    almacenes: Array,
});

// Inicialización con datos de props y carga dinámica
onMounted(async () => {
    // Cargar categorías desde el endpoint
    try {
        const categoriasResponse = await axios.get("/admin/ruta/categorias");
        categories.value = categoriasResponse.data;
    } catch (error) {
        console.error("Error cargando categorías:", error);
        // Usar las categorías de props como fallback
        categories.value = props.categorias || [];
    }

    // Cargar almacenes desde el endpoint
    try {
        const almacenesResponse = await axios.get("/admin/ruta/almacenes");
        warehouses.value = almacenesResponse.data;

        // Establecer un almacén por defecto si hay almacenes disponibles
        if (warehouses.value.length) {
            selectedWarehouse.value = warehouses.value[0].id;
        }
    } catch (error) {
        console.error("Error cargando almacenes:", error);
        // Usar los almacenes de props como fallback
        warehouses.value = props.almacenes || [];
        if (warehouses.value.length) {
            selectedWarehouse.value = warehouses.value[0].id;
        }
    }

    // Inicializar productos con los datos iniciales
    if (props.initialProducts && props.initialProducts.length) {
        products.value = props.initialProducts;
    }

    // Realizar una búsqueda inicial para cargar productos
    searchProducts();
});

// Computed properties
const total = computed(() => {
    return cart.reduce((sum, item) => sum + item.precioFinal * item.quantity, 0);
});

const cartItemCount = computed(() => {
    return cart.reduce((sum, item) => sum + item.quantity, 0);
});
//Productos
const filteredProducts = computed(() => {
    return products.value.filter((product) => {
        // Si no hay productos, retornamos array vacío
        if (!product) return false;

        const matchesSearch =
            !searchQuery.value ||
            product.nombre?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            product.descripcion
                ?.toLowerCase()
                .includes(searchQuery.value.toLowerCase());

        const matchesCategory =
            !selectedCategory.value || product.id_categoria == selectedCategory.value;

        return matchesSearch && matchesCategory;
    });
});

// Función para formatear la ruta de la imagen
const getImageUrl = (imagePath) => {
    // Si la imagen comienza con http o https, se asume que es una URL completa
    if (
        imagePath &&
        (imagePath.startsWith("http://") || imagePath.startsWith("https://"))
    ) {
        return imagePath;
    }

    // De lo contrario, asume que es una ruta relativa al directorio de almacenamiento
    return imagePath ? `/storage/${imagePath}` : "/img/no-image.png";
};

// Métodos
const searchProducts = async () => {
    try {
        isLoading.value = true;
        errorMessage.value = "";

        // Usando la ruta original de búsqueda con los parámetros actualizados
        const response = await axios.get("/admin/ventaLocal/buscar", {
            params: {
                query: searchQuery.value,
                categoria: selectedCategory.value,
                almacen: selectedWarehouse.value,
            },
        });

        products.value = response.data;
        console.log("Productos cargados:", products.value);
    } catch (error) {
        console.error("Error searching products:", error);
        errorMessage.value =
            "Error al buscar productos: " +
            (error.response?.data?.message || error.message);
    } finally {
        isLoading.value = false;
    }
};

// Carga productos al cambiar la categoría utilizando el nuevo endpoint
const handleCategoryChange = async () => {
    if (selectedCategory.value) {
        try {
            isLoading.value = true;
            // Usamos el nuevo endpoint para obtener productos por categoría
            const productsResponse = await axios.get(
                `/admin/ruta/productos/${selectedCategory.value}`
            );

            // Cargar información detallada de los productos filtrados
            searchProducts();
        } catch (error) {
            console.error("Error cargando productos por categoría:", error);
            errorMessage.value = "Error al cargar productos por categoría";
        } finally {
            isLoading.value = false;
        }
    } else {
        // Si no hay categoría seleccionada, cargamos todos los productos
        searchProducts();
    }
};

const handleWarehouseChange = async () => {
    // Al cambiar el almacén, recargamos los productos
    searchProducts();

    // Si tenemos una categoría seleccionada, también podemos calcular el stock
    if (selectedCategory.value && selectedWarehouse.value) {
        try {
            const stockResponse = await axios.post("/admin/calcular-stock", {
                id_categoria: selectedCategory.value,
                id_almacen: selectedWarehouse.value,
            });
            console.log("Stock disponible:", stockResponse.data.stock);
        } catch (error) {
            console.error("Error calculando stock:", error);
        }
    }
};

const addToCart = (product) => {
    if (product.stock <= 0) {
        alert("No hay stock disponible para este producto");
        return;
    }

    const existingItem = cart.find((item) => item.id === product.id);
    // CORRECCIÓN: precioFinal debe ser el precio unitario (con descuento si aplica)
    const precioUnitario = product.descuento
        ? product.precio * (1 - product.descuento / 100)
        : product.precio;

    if (existingItem) {
        if (existingItem.quantity >= product.stock) {
            alert("No hay suficiente stock disponible");
            return;
        }
        // CORRECCIÓN: Solo incrementar la cantidad, no recalcular el precio unitario
        existingItem.quantity++;
    } else {
        cart.push({
            ...product,
            precioFinal: precioUnitario, // Este es el precio unitario
            quantity: 1,
        });
    }

    isCartAnimating.value = true;
    setTimeout(() => {
        isCartAnimating.value = false;
    }, 800);
};

const removeFromCart = (index) => {
    cart.splice(index, 1);
};

const updateQuantity = (item, newQuantity) => {
    if (newQuantity < 1) {
        return;
    }
    if (newQuantity > item.stock) {
        alert("No hay suficiente stock");
        return;
    }
    // CORRECCIÓN: Solo actualizar la cantidad, no recalcular el precio
    item.quantity = newQuantity;
};

const processSale = async () => {
    if (cart.length === 0) {
        alert("El carrito está vacío");
        return;
    }

    if (!customerName.value.trim()) {
        alert("Por favor ingrese el nombre del cliente");
        return;
    }

    if (!selectedWarehouse.value) {
        alert("Por favor seleccione un almacén");
        return;
    }

    try {
        isLoading.value = true;

        const response = await axios.post("/admin/ventaLocal/procesar", {
            nombreCliente: customerName.value.trim(),
            almacenId: selectedWarehouse.value,
            productos: cart.map((item) => ({
                id: item.id,
                cantidad: item.quantity,
                descripcion: saleDescription.value,
                precioFinal: item.precioFinal, // Asegúrate de enviar el precio final
            })),
            tipoPago: paymentType.value,
            total: total.value,
        });

        if (response.data.success) {
            showQRModal.value = false;
            const invoiceResponse = await axios.get(
                `/admin/ventaLocal/factura/${response.data.venta_id}`
            );
            invoiceData.value = invoiceResponse.data;
            showInvoice.value = true;
            // Limpiar carrito después de procesar la venta
            cart.splice(0, cart.length);
            // Limpiar nombre del cliente
            customerName.value = "";
            saleDescription.value = ""; // ← LIMPIAR DESCRIPCIÓN DE LA VENTA
            // Actualizar productos para reflejar el nuevo stock
            paymentType.value = "efectivo"; // Reset payment type
            searchProducts();
        }
    } catch (error) {
        console.error("Error processing sale:", error);
        alert(
            "Error al procesar la venta: " +
            (error.response?.data?.message || error.message)
        );
    } finally {
        isLoading.value = false;
    }
};

//Función para cerrar modal QR
const closeQRModal = () => {
    showQRModal.value = false;
    paymentType.value = "efectivo"; // Opcional: volver a efectivo
};

const printInvoice = () => {
    window.print();
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 p-4 md:p-8">
        <!-- Encabezado y Filtros -->
        <div class="mb-6 space-y-4">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
                Punto de Venta
            </h1>

            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar producto</label>
                    <div class="relative">
                        <input v-model="searchQuery" @input="searchProducts"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Nombre o descripción..." />
                        <div v-if="isLoading" class="absolute right-3 top-1/2 transform -translate.y-1/2">
                            <div
                                class="animate-spin h-5 w-5 border-2 border-blue-500 rounded-full border-t-transparent">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                    <select v-model="selectedCategory" @change="handleCategoryChange"
                        class="mt-1 block rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todas</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.nombre }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Almacén</label>
                    <select v-model="selectedWarehouse" @change="handleWarehouseChange"
                        class="mt-1 block rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                            {{ warehouse.nombre }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Mensaje de error -->
            <div v-if="errorMessage"
                class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4">
                <p>{{ errorMessage }}</p>
            </div>
        </div>

        <!-- Layout principal: Productos (izquierda) y Carrito (derecha) -->
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sección de Productos (izquierda, 2/3 del espacio) -->
            <div class="lg:w-2/3">
                <div v-if="isLoading" class="flex justify-center items-center my-12">
                    <div class="animate-spin h-12 w-12 border-4 border-blue-500 rounded-full border-t-transparent">
                    </div>
                </div>

                <div v-else-if="filteredProducts.length === 0"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 text-center my-6">
                    <p class="text-xl text-gray-600 dark:text-gray-300">
                        No se encontraron productos que coincidan con los filtros aplicados.
                    </p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div v-for="product in filteredProducts" :key="product.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 transition-transform duration-200 hover:scale-105">
                        <img :src="getImageUrl(product.imagen)" :alt="product.nombre"
                            class="w-full h-40 object-cover mb-3 rounded-lg" />

                        <div class="space-y-2">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                {{ product.nombre }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ product.descripcion }}
                            </p>

                            <!-- En el componente Vue -->
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                        <span v-if="product.enPromocion" class="line-through text-red-500 text-sm">
                                            Bs{{ Number(product.precio).toFixed(2) }}
                                        </span>
                                        Bs{{ Number(product.precioFinal).toFixed(2) }}
                                    </span>
                                </div>
                                <span :class="[
                                    'text-sm',
                                    product.stock > 0
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400',
                                ]">
                                    {{ product.stock > 0 ? `${product.stock} disp.` : "Agotado" }}
                                </span>
                            </div>
                            <!-- Añadir un badge de descuento si el producto tiene promoción -->
                            <div v-if="product.enPromocion"
                                class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                -{{ product.descuento }}%
                            </div>

                            <button @click="addToCart(product)" :disabled="product.stock <= 0"
                                class="w-full mt-2 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors dark:bg-blue-700 dark:hover:bg-blue-600">
                                Añadir al carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carrito (derecha, 1/3 del espacio, fijo en pantallas grandes) -->
            <div class="lg:w-1/3 sticky top-0 self-start">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-5">
                    <h3 class="flex items-center font-bold mb-4 text-gray-800 dark:text-white">
                        Carrito de Compras
                        <div class="relative ml-2" :class="{ 'cart-animation': isCartAnimating }">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                            <span v-if="cartItemCount > 0"
                                class="cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center transition-all duration-300 transform scale-100">
                                {{ cartItemCount }}
                            </span>
                        </div>
                    </h3>

                    <div v-if="cart.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-8">
                        El carrito está vacío
                    </div>

                    <div v-else class="space-y-3 max-h-[50vh] overflow-y-auto">
                        <div v-for="(item, index) in cart" :key="index"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center space-x-1">
                                    <button @click="updateQuantity(item, item.quantity - 1)"
                                        class="p-1 bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                                            class="size-4">
                                            <path d="M3.75 7.25a.75.75 0 0 0 0 1.5h8.5a.75.75 0 0 0 0-1.5h-8.5Z" />
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center dark:text-white">{{
                                        item.quantity
                                    }}</span>
                                    <button @click="updateQuantity(item, item.quantity + 1)"
                                        class="p-1 bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                                            class="size-4">
                                            <path
                                                d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                                        </svg>
                                    </button>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 dark:text-white text-sm">
                                        {{ item.nombre }}
                                    </h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-300">
                                        Bs.{{ item.precioFinal }} c/u
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <span class="font-semibold dark:text-white">Bs.{{ (item.precioFinal *
                                    item.quantity).toFixed(2) }}</span>
                                <button @click="removeFromCart(index)"
                                    class="text-red-500 hover:text-red-700 dark:text-red-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-semibold dark:text-white">Total:</span>
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">Bs{{ total.toFixed(2)
                            }}</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre
                                    del Cliente</label>
                                <input v-model="customerName"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Ingrese el nombre del cliente" />
                            </div>

                            <!--Campo para la descripción-->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Descripcion
                                    de la venta</label>
                                <textarea v-model="saleDescription" rows="4"
                                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Escribe una descripción de la venta"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Método de Pago
                                </label>
                                <select v-model="paymentType" @change="handlePaymentTypeChange"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="QR">QR</option>
                                </select>
                            </div>

                            <button v-if="paymentType !== 'QR'" @click="processSale"
                                :disabled="isLoading || cart.length === 0 || !customerName.trim()"
                                class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed dark:bg-green-700 dark:hover:bg-green-600">
                                {{ isLoading ? "Procesando..." : "Confirmar Venta" }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal QR -->
        <div v-if="showQRModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full">
                <!-- Header del modal -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold dark:text-white">Pago con QR</h2>
                    <button @click="closeQRModal"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white text-2xl">
                        ×
                    </button>
                </div>

                <!-- Información de la venta -->
                <div class="text-center mb-6">
                    <p class="text-lg font-semibold dark:text-white mb-2">
                        Total a pagar: <span class="text-green-600">Bs{{ total.toFixed(2) }}</span>
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Cliente: {{ customerName }}
                    </p>
                </div>

                <!-- ← AQUÍ VA TU IMAGEN QR -->
                <div class="flex justify-center mb-6">
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <img src="https://res.cloudinary.com/dcdhuwp0y/image/upload/v1751327910/Qr_ubdklx.jpg" alt="Código QR para pago" class="w-48 h-48 object-contain"
                            @error="$event.target.src = '/images/qr-placeholder.png'" />
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Escanea el código QR con tu aplicación de banca móvil para realizar el pago
                    </p>
                </div>

                <!-- ← NUEVO: Botón confirmar venta (aparece aquí cuando es QR) -->
                <div class="space-y-3">
                    <button @click="processSale" :disabled="isLoading || cart.length === 0 || !customerName.trim()"
                        class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed dark:bg-green-700 dark:hover:bg-green-600">
                        {{ isLoading ? "Procesando..." : "Confirmar Pago QR" }}
                    </button>

                    <button @click="closeQRModal"
                        class="w-full bg-gray-200 dark:bg-gray-600 dark:text-white py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Factura -->
        <div v-if="showInvoice" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div id="invoice-print" class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-2xl w-full">
                <div class="flex justify-between items-start mb-6">
                    <h2 class="text-2xl font-bold dark:text-white">
                        Factura #{{ invoiceData.id }}
                    </h2>
                    <button @click="showInvoice = false"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">
                        ×
                    </button>
                </div>

                <div class="flex items-center mb-6 text-xl md:text-2xl font-semibold text-gray-800 dark:text-white">
                    <img class="w-10 h-10 md:w-14 md:h-14 mr-2" src="/assets/images/logo.png" alt="logo" />
                    La Fortaleza
                </div>

                <div class="space-y-4 mb-6">
                    <div class="dark:text-gray-200">
                        <p class="font-semibold">
                            Fecha: {{ new Date().toLocaleDateString("es-BO") }}
                        </p>
                        <p class="font-semibold">
                            Hora: {{ new Date().toLocaleTimeString("es-BO") }}
                        </p>
                        <p>Cliente: {{ invoiceData.cliente.nombre }}</p>
                        <p v-if="invoiceData.cliente.email">
                            Email: {{ invoiceData.cliente.email }}
                        </p>
                    </div>

                    <div class="border-y border-gray-200 dark:border-gray-700 py-4">
                        <div v-for="(item, index) in invoiceData.items" :key="index"
                            class="flex justify-between items-center mb-2">
                            <div>
                                <p class="font-semibold dark:text-white">{{ item.producto }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ item.almacen }} (x{{ item.cantidad }})
                                </p>
                            </div>
                            <span class="dark:text-white">Bs.{{ item.subtotal.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div class="text-right text-xl font-bold dark:text-white">
                        <p>Método de pago: {{ invoiceData.tipoPago }}</p>
                        <p>Total: Bs.{{ Number(invoiceData.total).toFixed(2) }}</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button @click="printInvoice"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 dark:bg-blue-700">
                        Imprimir
                    </button>
                    <button @click="showInvoice = false"
                        class="bg-gray-200 dark:bg-gray-600 dark:text-white px-6 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }

    #invoice-print,
    #invoice-print * {
        visibility: visible;
    }

    #invoice-print {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}

@media print {
    body * {
        visibility: hidden;
    }

    #invoice-print,
    #invoice-print * {
        visibility: visible;
    }

    #invoice-print {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}

/* Estilos adicionales para el modal QR */
.modal-overlay {
    backdrop-filter: blur(2px);
}

.qr-container {
    animation: fadeInScale 0.3s ease-out;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.9);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Estilos para el carrito */
.cart-animation {
    animation: cart-bounce 0.8s ease;
}

.cart-badge {
    box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
    animation: pulse-badge 1.5s infinite;
}

@keyframes cart-bounce {

    0%,
    20%,
    50%,
    80%,
    100% {
        transform: translateY(0);
    }

    40% {
        transform: translateY(-10px);
    }

    60% {
        transform: translateY(-5px);
    }
}

@keyframes pulse-badge {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
    }

    70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px rgba(220, 38, 38, 0);
    }

    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
    }
}
</style>
