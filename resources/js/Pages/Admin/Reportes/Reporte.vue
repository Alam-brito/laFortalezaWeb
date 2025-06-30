<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

const fechaInicio = ref("");
const fechaFin = ref("");
const usuarioSeleccionado = ref("todos"); // Nuevo: Estado para el usuario seleccionado
const usuarios = ref([]); // Nuevo: Lista de usuarios
const ventas = ref([]);
const cargando = ref(false);
const cargando2 = ref(false);

// Estados para la búsqueda de movimientos de productos
const productos = ref([]);
const almacenes = ref([]);
const movimientos = ref([]);
const productoSeleccionado = ref("todos");
const almacenSeleccionado = ref("todos");
const tipoMovimiento = ref("todos");

// Cargar lista de usuarios al montar el componente
onMounted(async () => {
  try {
    const response = await axios.get(route("obtener.usuarios"));
    usuarios.value = response.data;

    // Cargar lista de productos
    const productosResponse = await axios.get(route("obtener.productos"));
    productos.value = productosResponse.data;

    // Cargar lista de almacenes
    const almacenesResponse = await axios.get(route("obtener.almacenes"));
    almacenes.value = almacenesResponse.data;
  } catch (error) {
    console.error("Error al cargar datos:", error);
  }
});


// Función para buscar ventas
const buscarVentas = async () => {
  if (!fechaInicio.value || !fechaFin.value) {
    Swal.fire("Error", "Debe seleccionar ambas fechas", "error");
    return;
  }

  cargando.value = true;
  try {
    const response = await axios.post(route('buscar.ventas'), {
      fechaInicio: fechaInicio.value,
      fechaFin: fechaFin.value,
      id_user: usuarioSeleccionado.value, // Enviamos el usuario seleccionado
    });

    ventas.value = response.data;
    if (ventas.value.length === 0) {
      Swal.fire("Sin resultados", "No se encontraron ventas en este rango", "info");
    }
  } catch (error) {
    Swal.fire("Error", "No se pudo obtener los datos", "error");
  } finally {
    cargando.value = false;
  }
};

// Función para buscar movimientos de productos
const buscarMovimientos = async () => {
  console.log("Tipo de Movimiento seleccionado:", tipoMovimiento.value); // DEBUG
  cargando2.value = true;
  try {
    const response = await axios.post(route("buscar.movimientos"), {
      fechaInicio: fechaInicio.value,
      fechaFin: fechaFin.value,
      id_producto: productoSeleccionado.value,
      id_almacen: almacenSeleccionado.value,
      tipo_movimiento: tipoMovimiento.value.trim(), // Asegura que se envía sin espacios
    });

    movimientos.value = response.data;
    console.log("Resultados obtenidos:", movimientos.value); // DEBUG

    if (movimientos.value.length === 0) {
      Swal.fire("Sin resultados", "No se encontraron movimientos en este rango", "info");
    }
  } catch (error) {
    Swal.fire("Error", "No se pudo obtener los datos", "error");
  } finally {
    cargando2.value = false;
  }
};


const dialogVisible = ref(false);
const dialogVisible2 = ref(false);

//Abrir la ventana con los datos cargados para enviar correos
const openAddModel = () => {
  if (ventas.value.length === 0) {
    Swal.fire("Error", "No hay datos para enviar", "error");
    return;
  }
  // Obtener emails únicos
  emailTo.value = [...new Set(ventas.value.map(v => v.email))].join(", ");

  const tablaVentas = `
  <h2 style="text-align: center; font-family: Arial, sans-serif; color: #333; margin-bottom: 10px;">
    📊 <span style="color: #007bff;">Reporte de Ventas</span>
  </h2>
  <table role="presentation" style="width: 100%; border-collapse: collapse; border-spacing: 0; font-family: Arial, sans-serif; border: 1px solid #ccc; mso-table-lspace:0pt; mso-table-rspace:0pt;">
    <thead>
      <tr>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Cliente</th>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Email</th>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Fecha</th>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Monto (Bs.)</th>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Pago</th>
      </tr>
    </thead>
    <tbody>
      ${ventas.value.map(v => `
        <tr>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${v.nombre}</td>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">
            <a href="mailto:${v.email}" style="color: #007bff; text-decoration: none;">${v.email}</a>
          </td>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${v.fecha}</td>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${Number(v.total).toFixed(2)}</td>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${v.tipo_pago}</td>
        </tr>
      `).join('')}
    </tbody>
  </table>
`;

  message.value = tablaVentas; // Asignamos la tabla a la variable message
  dialogVisible.value = true;
}

//Para los movimientos del inventario
const editModel = () => {
  if (movimientos.value.length === 0) {
    Swal.fire("Error", "No hay datos para enviar", "error");
    return;
  }

  // Construir la tabla en HTML
  const tablaMovimientos = `
  <h2 style="text-align: center; font-family: Arial, sans-serif; color: #333; margin-bottom: 10px;">
    📦 <span style="color: #007bff;">Reporte de Movimientos de Inventario</span>
  </h2>
  <table role="presentation" style="width: 100%; border-collapse: collapse; border-spacing: 0; font-family: Arial, sans-serif; border: 1px solid #ccc; mso-table-lspace:0pt; mso-table-rspace:0pt;">
    <thead>
      <tr>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Producto</th>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Almacén</th>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Fecha</th>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Glosa</th> <!-- Nueva columna -->
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Tipo</th>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Cantidad</th>
        <th style="background-color: #007bff; color: white; padding: 5px; text-align: center; font-size: 14px; border: 1px solid #ccc;">Usuario</th>
      </tr>
    </thead>
    <tbody>
      ${movimientos.value.map(m => `
        <tr>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${m.producto}</td>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${m.almacen}</td>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${m.fecha}</td>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${m.glosa}</td> <!-- Nueva celda -->
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${m.tipo}</td>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">
            ${m.tipo === 'egreso' ? `- ${m.cantidad} uds.` : `+ ${m.cantidad} uds.`}
          </td>
          <td style="border: 1px solid #ccc; text-align: center; padding: 5px; font-size: 13px;">${m.usuario}</td>
        </tr>
      `).join('')}
    </tbody>
  </table>
`;
  message.value = tablaMovimientos; // Asignamos la tabla a la variable message
  dialogVisible2.value = true; // Mostramos el modal de movimientos de inventario
};


// Variables para el formulario de correo
const emailTo = ref("");
const subject = ref("");
const message = ref("");
const enviandoCorreo = ref(false); // Nueva variable para mostrar el spinner

// Enviar correo
const enviarCorreo = async () => {
  if (!emailTo.value || !message.value) {
    Swal.fire("Error", "Debe completar todos los campos", "error");
    return;
  }

  enviandoCorreo.value = true; // Activar la ruedita de carga

  try {
    subject.value = "Reporte de ventas";
    await axios.post(route('enviar.correo'), {
      to: emailTo.value,
      subject: subject.value,
      message: message.value,
    });

    // Confirmación con SweetAlert
    Swal.fire({
      title: "Éxito",
      text: "Correo enviado correctamente",
      icon: "success",
      timer: 2000,
      timerProgressBar: true,
    });

    dialogVisible.value = false; // Cerrar el modal
  } catch (error) {
    Swal.fire("Error", "No se pudo enviar el correo", "error");
    dialogVisible.value = false; // Cerrar el modal
  } finally {
    enviandoCorreo.value = false; // Desactivar la ruedita de carga
  }
};

const enviarReporteMovimientos = async () => {
  if (!emailTo.value || !message.value) {
    Swal.fire("Error", "No hay datos para enviar", "error");
    return;
  }

  enviandoCorreo.value = true; // Activar la ruedita de carga

  try {
    subject.value = "Reporte de Movimientos de Inventario";

    await axios.post(route('enviar.correo'), {
      to: emailTo.value,
      subject: subject.value,
      message: message.value,
    });

    // Confirmación con SweetAlert
    Swal.fire({
      title: "Éxito",
      text: "Correo enviado correctamente",
      icon: "success",
      timer: 2000,
      timerProgressBar: true,
    });

    dialogVisible2.value = false; // Cerrar el modal
  } catch (error) {
    Swal.fire("Error", "No se pudo enviar el correo", "error");
  } finally {
    enviandoCorreo.value = false; // Desactivar la ruedita de carga
  }
};

</script>

<template>
  <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 shadow-lg rounded-lg p-14 mt-8">
    <!--Inicio de ventana-->
    <el-dialog v-model="dialogVisible" width="800px" :before-close="handleClose"
      class="dark:bg-gray-900 dark:text-white shadow-lg rounded-lg">
      <div class="p-8 bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white text-center mb-6">
          📧 Enviar Correo Electrónico
        </h2>
        <form action="">
          <!-- Destinatarios -->
          <div class="mb-5">
            <label for="to" class="block text-gray-700 dark:text-gray-300 font-medium mb-1 text-lg">📨
              Destinatarios:</label>
            <el-input v-model="emailTo" name="to" id="to" type="text" size="large"
              placeholder="ejemplo1@gmail.com, ejemplo2@gmail.com" required class="w-full" />
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Introduzca los emails separados por coma.
            </p>
          </div>

          <!-- Asunto -->
          <div class="mb-5">
            <label for="subject" class="block text-gray-700 dark:text-gray-300 font-medium mb-1 text-lg">✉️
              Asunto:</label>
            <el-input v-model="subject" name="subject" id="subject" type="text" size="large"
              placeholder="Introduzca el asunto" required class="w-full" />
          </div>

          <!-- Mensaje en HTML -->
          <div class="mb-5">
            <label for="message" class="block text-gray-700 dark:text-gray-300 font-medium mb-1 text-lg">📝
              Mensaje:</label>
            <div v-html="message"
              class="p-4 border rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white"></div>
          </div>

          <!-- Botón de Enviar -->
          <div class="flex justify-center mt-8">
            <el-button type="primary" native-type="submit" size="large" @click="enviarCorreo"
              :disabled="enviandoCorreo">
              <el-icon v-if="!enviandoCorreo"><i class="fas fa-paper-plane"></i></el-icon>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-6 h-6 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
              <span v-if="!enviandoCorreo">Enviar Reporte de ventas</span>

              <!-- Ruedita de carga -->
              <span v-if="enviandoCorreo" class="flex items-center">
                <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 16 0H4z"></path>
                </svg>
                Enviando...
              </span>
            </el-button>
          </div>
        </form>
      </div>
    </el-dialog>
    <!--Fin de ventana-->
    <!--Inicio de ventana2-->
    <!-- Modal para enviar Reporte de Movimientos de Inventario -->
    <el-dialog v-model="dialogVisible2" width="800px" :before-close="handleClose"
      class="dark:bg-gray-900 dark:text-white shadow-lg rounded-lg">
      <div class="p-8 bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white text-center mb-6">
          📧 Enviar Reporte de Movimientos de Inventario
        </h2>

        <form action="">
          <!-- Destinatarios -->
          <div class="mb-5">
            <label for="to" class="block text-gray-700 dark:text-gray-300 font-medium mb-1 text-lg">📨
              Destinatarios:</label>
            <el-input v-model="emailTo" name="to" id="to" type="text" size="large"
              placeholder="ejemplo1@gmail.com, ejemplo2@gmail.com" required class="w-full" />
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Introduzca los emails separados por coma.
            </p>
          </div>

          <!-- Asunto -->
          <div class="mb-5">
            <label for="subject" class="block text-gray-700 dark:text-gray-300 font-medium mb-1 text-lg">✉️
              Asunto:</label>
            <el-input v-model="subject" name="subject" id="subject" type="text" size="large"
              placeholder="Introduzca el asunto" required class="w-full" />
          </div>

          <!-- Mensaje en HTML -->
          <div class="mb-5">
            <label for="message" class="block text-gray-700 dark:text-gray-300 font-medium mb-1 text-lg">📝
              Mensaje:</label>
            <div v-html="message"
              class="p-4 border rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white"></div>
          </div>

          <!-- Botón de Enviar -->
          <div class="flex justify-center mt-8">
            <!-- Botón de Enviar en el Modal de Movimientos -->
            <el-button type="primary" native-type="submit" size="large" @click="enviarReporteMovimientos"
              :disabled="enviandoCorreo">
              <el-icon v-if="!enviandoCorreo"><i class="fas fa-paper-plane"></i></el-icon>
              <svg v-if="!enviandoCorreo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75" />
              </svg>
              <span v-if="!enviandoCorreo">Enviar Reporte de Movimientos</span>

              <!-- Ruedita de carga -->
              <span v-if="enviandoCorreo" class="flex items-center">
                <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 16 0H4z"></path>
                </svg>
                Enviando...
              </span>
            </el-button>
          </div>
        </form>
      </div>
    </el-dialog>
    <!--Fin de ventana2-->
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-4">Búsqueda de Ventas</h2>
    <!-- Formulario -->
    <form @submit.prevent="buscarVentas" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">📅 Fecha Inicio</label>
        <input type="date" v-model="fechaInicio"
          class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all dark:bg-gray-700 dark:text-white">
      </div>

      <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">📅 Fecha Fin</label>
        <input type="date" v-model="fechaFin"
          class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all dark:bg-gray-700 dark:text-white">
      </div>

      <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">👤 Usuario</label>
        <select v-model="usuarioSeleccionado"
          class="w-full px-4 py-3 bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all appearance-none">
          <option value="todos">Todos</option>
          <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id"
            class="hover:bg-blue-50 dark:hover:bg-gray-600">
            {{ usuario.name }}
          </option>
        </select>
      </div>
      <div class="flex items-end">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition w-full">
          Buscar
        </button>
      </div>
    </form>

    <!-- Cargando -->
    <div v-if="cargando" class="text-center py-4">
      <div class="inline-flex items-center text-blue-600 dark:text-blue-400">
        <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        Buscando ventas...
      </div>
    </div>

    <!-- Tabla de Ventas -->
    <div v-if="ventas.length > 0" class="overflow-x-auto mt-6">
      <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
        <thead class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
          <tr>
            <th class="border border-gray-300 p-2">Código</th>
            <th class="border border-gray-300 p-2">Nombre</th>
            <th class="border border-gray-300 p-2">Email</th>
            <th class="border border-gray-300 p-2">Fecha</th>
            <th class="border border-gray-300 p-2">Monto</th>
            <th class="border border-gray-300 p-2">Tipo de Pago</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="venta in ventas" :key="venta.id" class="text-gray-700 dark:text-gray-200 text-center">
            <td class="border border-gray-300 p-2">{{ venta.id }}</td>
            <td class="border border-gray-300 p-2">{{ venta.nombre }}</td>
            <td class="border border-gray-300 p-2">{{ venta.email }}</td>
            <td class="border border-gray-300 p-2">{{ venta.fecha }}</td>
            <td class="border border-gray-300 p-2">Bs. {{ Number(venta.total).toFixed(2) }}</td>
            <td class="border border-gray-300 p-2">{{ venta.tipo_pago }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Botón Enviar centrado -->
    <div class="flex justify-center mt-6">
      <button type="button" @click="openAddModel"
        class="text-white bg-[#1da1f2] hover:bg-[#1da1f2]/90 focus:ring-4 focus:outline-none focus:ring-[#1da1f2]/50 
               font-medium rounded-lg text-sm px-6 py-3 text-center inline-flex items-center dark:focus:ring-[#1da1f2]/55">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
        </svg>
        Cargar formulario
      </button>
    </div>

    <!-- Búsqueda de Movimientos de Productos -->
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mt-8 mb-4">Movimientos de Productos</h2>

    <form @submit.prevent="buscarMovimientos" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="text-gray-700 dark:text-gray-200">Producto:</label>
        <select v-model="productoSeleccionado" class="w-full px-4 py-2 border rounded-lg dark:text-gray-800">
          <option value="todos">Todos</option>
          <option v-for="producto in productos" :key="producto.id" :value="producto.id">
            {{ producto.nombre }}
          </option>
        </select>
      </div>
      <div>
        <label class="text-gray-700 dark:text-gray-200">Almacén:</label>
        <select v-model="almacenSeleccionado" class="w-full px-4 py-2 border rounded-lg dark:text-gray-800">
          <option value="todos">Todos</option>
          <option v-for="almacen in almacenes" :key="almacen.id" :value="almacen.id">
            {{ almacen.nombre }}
          </option>
        </select>
      </div>
      <div>
        <label class="text-gray-700 dark:text-gray-200">Tipo Movimiento:</label>
        <select v-model="tipoMovimiento" class="w-full px-4 py-2 border rounded-lg dark:text-gray-800">
          <option value="todos">Todos</option>
          <option value="ingreso">Ingreso</option>
          <option value="egreso">Egreso</option>
        </select>
      </div>
      <div class="flex items-end">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition w-full">
          Buscar
        </button>
      </div>
    </form>

    <!-- Cargando -->
    <div v-if="cargando2" class="text-center py-4">
      <div class="inline-flex items-center text-blue-600 dark:text-blue-400">
        <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        Buscando movimientos...
      </div>
    </div>

    <!-- Tabla de Movimientos de Inventario -->
    <div v-if="movimientos.length > 0" class="overflow-x-auto mt-6">
      <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
        <thead class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
          <tr>
            <th class="border border-gray-300 p-2">Producto</th>
            <th class="border border-gray-300 p-2">Almacén</th>
            <th class="border border-gray-300 p-2">Fecha</th>
            <th class="border border-gray-300 p-2">Glosa</th>
            <th class="border border-gray-300 p-2">Tipo de Movimiento</th>
            <th class="border border-gray-300 p-2">Cantidad</th>
            <th class="border border-gray-300 p-2">Usuario</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="movimiento in movimientos"
            :key="`${movimiento.id_producto}-${movimiento.id_almacen}-${movimiento.fecha}`"
            class="text-gray-700 dark:text-gray-200 text-center">
            <td class="border border-gray-300 p-2">{{ movimiento.producto }}</td>
            <td class="border border-gray-300 p-2">{{ movimiento.almacen }}</td>
            <td class="border border-gray-300 p-2">{{ movimiento.fecha }}</td>
            <td class="border border-gray-300 p-2">{{ movimiento.glosa }}</td>
            <td class="border border-gray-300 p-2"> {{ movimiento.tipo }}</td>
            <td class="border border-gray-300 p-2">
              <div v-if="movimiento.tipo == 'egreso'">
                -{{ movimiento.cantidad }}uds.
              </div>
              <div v-else>
                +{{ movimiento.cantidad }}uds.
              </div>
            </td>
            <td class="border border-gray-300 p-2">{{ movimiento.usuario }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Botón Enviar centrado -->
    <!-- Botón para abrir el formulario de movimientos de inventario -->
    <div class="flex justify-center mt-6">
      <button type="button" @click="editModel" class="text-black bg-[#F7BE38] hover:bg-[#F7BE38]/90 focus:ring-4 focus:outline-none focus:ring-[#F7BE38]/50 
           font-medium rounded-lg text-sm px-6 py-3 text-center inline-flex items-center dark:focus:ring-[#F7BE38]/55">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </svg>
        Cargar Reporte de Movimientos
      </button>
    </div>
  </div>
</template>

<style scoped>
@keyframes fade-in-up {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out forwards;
}

@keyframes slide-in {
  0% { opacity: 0; transform: translateY(-20px); }
  100% { opacity: 1; transform: translateY(0); }
}

.animate-slide-in {
  animation: slide-in 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.dark .modal-content {
  background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
  border: 1px solid #404040;
}

.dark input, .dark select {
  background-color: #262626;
  border-color: #404040;
}

.dark input:focus, .dark select:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}
</style>