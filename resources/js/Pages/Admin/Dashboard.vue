<script setup>
import { ref, watchEffect, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import AdminLayout from "./Components/AdminLayout.vue";
import { use } from "echarts/core";
import { CanvasRenderer } from "echarts/renderers";
import { LineChart, BarChart, PieChart } from "echarts/charts";
import {
  GridComponent,
  TooltipComponent,
  LegendComponent,
  TitleComponent,
  ToolboxComponent,
  DataZoomComponent,
  VisualMapComponent,
  MarkLineComponent,
  MarkPointComponent
} from "echarts/components";
import VChart from "vue-echarts";

defineProps(['count']);

// Registrar módulos adicionales de ECharts para funcionalidades avanzadas
use([
  CanvasRenderer,
  LineChart,
  BarChart,
  PieChart,
  GridComponent,
  TooltipComponent,
  LegendComponent,
  TitleComponent,
  ToolboxComponent,
  DataZoomComponent,
  VisualMapComponent,
  MarkLineComponent,
  MarkPointComponent
]);

// Obtener datos de la página con Inertia
const { props } = usePage();
const { resumen, ventasMasAltas, productosMasVendidos, ingresosSemanales } = props;

// Paleta de colores profesional
const colorPalette = [
  '#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de',
  '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc', '#0096c7'
];

// Referencia para las animaciones
const chartAnimated = ref(false);

// Gráfico de Ingresos Semanales (Línea con área y gradiente)
const ingresosChart = ref({
  color: colorPalette,
  title: {
    text: "Ingresos Semanales",
    left: "center",
    textStyle: {
      fontWeight: 'bold',
      fontSize: 16
    },
    subtextStyle: {
      fontSize: 12
    }
  },
  tooltip: {
    trigger: "axis",
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    borderColor: '#ccc',
    borderWidth: 1,
    padding: 10,
    textStyle: {
      color: '#333'
    },
    formatter: (params) => {
      let index = params[0].dataIndex;
      let ingreso = ingresosSemanales[index];
      return `<div style="font-weight:bold;margin-bottom:5px;">${ingreso.fecha}</div>
              <span style="display:inline-block;margin-right:5px;border-radius:50%;width:10px;height:10px;background-color:${params[0].color};"></span>
              <span style="font-weight:bold">Ingresos:</span> Bs. ${ingreso.ingresos.toLocaleString()}`
    }
  },
  grid: {
    left: '5%',
    right: '5%',
    bottom: '15%',
    top: '20%',
    containLabel: true
  },
  toolbox: {
    feature: {
      saveAsImage: { title: 'Guardar' },
      dataZoom: { title: { zoom: 'Zoom', back: 'Restaurar' } },
      restore: { title: 'Reiniciar' }
    }
  },
  xAxis: {
    type: "category",
    data: [],
    axisLine: {
      lineStyle: {
        color: '#999'
      }
    },
    axisLabel: {
      rotate: 45,
      fontSize: 11,
      color: '#666',
      margin: 10
    },
    boundaryGap: false
  },
  yAxis: {
    type: "value",
    axisLabel: {
      formatter: value => `Bs. ${value}`,
      color: '#666'
    },
    splitLine: {
      lineStyle: {
        type: 'dashed',
        color: '#ddd'
      }
    }
  },
  dataZoom: [
    {
      type: 'inside',
      start: 0,
      end: 100
    },
    {
      type: 'slider',
      height: 20,
      bottom: 0,
      start: 0,
      end: 100
    }
  ],
  series: [
    {
      name: "Ingresos",
      type: "line",
      data: [],
      smooth: true,
      symbolSize: 8,
      lineStyle: {
        width: 3
      },
      areaStyle: {
        color: {
          type: 'linear',
          x: 0,
          y: 0,
          x2: 0,
          y2: 1,
          colorStops: [
            { offset: 0, color: 'rgba(84, 112, 198, 0.5)' },
            { offset: 1, color: 'rgba(84, 112, 198, 0.1)' }
          ]
        }
      },
      markPoint: {
        data: [
          { type: 'max', name: 'Máximo' },
          { type: 'min', name: 'Mínimo' }
        ]
      },
      markLine: {
        data: [
          { type: 'average', name: 'Promedio' }
        ],
        label: {
          formatter: 'Promedio: {c} Bs.'
        }
      },
      emphasis: {
        scale: true,
        focus: 'series'
      },
      // Animación
      animationDelay: function (idx) {
        return idx * 100;
      }
    }
  ],
  animation: true,
  animationDuration: 1500,
  animationEasing: 'cubicInOut'
});

// Gráfico de Ventas Más Altas (Barras con gradiente)
const ventasChart = ref({
  color: colorPalette,
  title: {
    text: "Ventas Más Altas",
    left: "center",
    textStyle: {
      fontWeight: 'bold',
      fontSize: 16
    }
  },
  tooltip: {
    trigger: "axis",
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    borderColor: '#ccc',
    borderWidth: 1,
    padding: 10,
    textStyle: {
      color: '#333'
    },
    formatter: (params) => {
      let index = params[0].dataIndex;
      let venta = ventasMasAltas[index];
      return `<div style="font-weight:bold;margin-bottom:5px;">${venta.cliente}</div>
              <div><span style="display:inline-block;margin-right:5px;border-radius:50%;width:10px;height:10px;background-color:${params[0].color};"></span>
              <span style="font-weight:bold">Total:</span> Bs. ${venta.total.toLocaleString()}</div>
              <div style="margin-top:5px;"><span style="font-weight:bold">Fecha:</span> ${venta.fecha}</div>`;
    },
  },
  grid: {
    left: '5%',
    right: '5%',
    bottom: '15%',
    top: '20%',
    containLabel: true
  },
  toolbox: {
    feature: {
      saveAsImage: { title: 'Guardar' },
      dataZoom: { title: { zoom: 'Zoom', back: 'Restaurar' } },
      restore: { title: 'Reiniciar' }
    }
  },
  xAxis: {
    type: "category",
    data: [],
    axisLabel: {
      rotate: 45,
      fontSize: 10,
      interval: 0,
      color: '#666',
      margin: 10
    },
    axisLine: {
      lineStyle: {
        color: '#999'
      }
    }
  },
  yAxis: {
    type: "value",
    axisLabel: {
      formatter: value => `Bs. ${value}`,
      color: '#666'
    },
    splitLine: {
      lineStyle: {
        type: 'dashed',
        color: '#ddd'
      }
    }
  },
  dataZoom: [
    {
      type: 'inside',
      start: 0,
      end: 100
    },
    {
      type: 'slider',
      height: 20,
      bottom: 0,
      start: 0,
      end: 100
    }
  ],
  series: [
    {
      name: "Total Venta",
      type: "bar",
      data: [],
      barWidth: '50%',
      itemStyle: {
        color: new Function('params', `
          return {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: '#91cc75' },
              { offset: 1, color: '#3ba272' }
            ]
          };
        `)
      },
      emphasis: {
        itemStyle: {
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowColor: 'rgba(0, 0, 0, 0.5)'
        }
      },
      // Animación
      animationDelay: function (idx) {
        return idx * 120;
      }
    }
  ],
  animation: true,
  animationDuration: 1500,
  animationEasing: 'elasticOut'
});

// Gráfico de Productos Más Vendidos (Pastel 3D con rosquilla)
const productosChart = ref({
  color: colorPalette,
  title: {
    text: "Productos Más Vendidos",
    left: "center",
    textStyle: {
      fontWeight: 'bold',
      fontSize: 16
    }
  },
  tooltip: {
    trigger: "item",
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    borderColor: '#ccc',
    borderWidth: 1,
    formatter: '{b}: <strong>{c}</strong> unidades (<b>{d}%</b>)'
  },
  legend: {
    type: 'scroll',
    orient: 'horizontal',
    bottom: '0%',
    left: 'center',
    textStyle: {
      fontSize: 11
    }
  },
  toolbox: {
    feature: {
      saveAsImage: { title: 'Guardar' },
      restore: { title: 'Reiniciar' }
    }
  },
  series: [
    {
      name: "Productos",
      type: "pie",
      radius: ['40%', '70%'],  // Formato de rosquilla
      center: ['50%', '50%'],
      avoidLabelOverlap: true,
      itemStyle: {
        borderRadius: 10,
        borderColor: '#fff',
        borderWidth: 2
      },
      label: {
        show: false,
        position: 'center',
        formatter: '{b}\n{c} ({d}%)'
      },
      emphasis: {
        label: {
          show: true,
          fontSize: 14,
          fontWeight: 'bold'
        },
        itemStyle: {
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowColor: "rgba(0, 0, 0, 0.5)"
        }
      },
      labelLine: {
        show: false
      },
      data: [],
      // Animación
      animationType: 'scale',
      animationEasing: 'elasticOut'
    }
  ],
  animation: true,
  animationDuration: 1800,
  animationDelay: index => index * 150
});

// Cargar datos en los gráficos
watchEffect(() => {
  if (ingresosSemanales && ingresosSemanales.length > 0) {
    ingresosChart.value.xAxis.data = ingresosSemanales.map((entry) => entry.fecha);
    ingresosChart.value.series[0].data = ingresosSemanales.map((entry) => entry.ingresos);

    // Agregar subtítulo con información del periodo
    if (ingresosSemanales.length >= 2) {
      const firstDate = ingresosSemanales[0].fecha;
      const lastDate = ingresosSemanales[ingresosSemanales.length - 1].fecha;
      ingresosChart.value.title.subtext = `Periodo: ${firstDate} al ${lastDate}`;
    }
  }

  if (ventasMasAltas && ventasMasAltas.length > 0) {
    ventasChart.value.xAxis.data = ventasMasAltas.map((venta) => venta.cliente);
    ventasChart.value.series[0].data = ventasMasAltas.map((venta) => venta.total);
  }

  if (productosMasVendidos && productosMasVendidos.length > 0) {
    productosChart.value.series[0].data = productosMasVendidos.map((producto) => ({
      name: producto.nombre,
      value: producto.total_vendido,
    }));
  }
});

// Asegurar que las animaciones se apliquen correctamente
onMounted(() => {
  setTimeout(() => {
    chartAnimated.value = true;
  }, 300);
});
</script>

<template>
  <AdminLayout>
    <!-- Resumen de Ventas con Contadores Animados -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 mt-6">
      <div class="summary-card flex items-center">
        <div class="icon-container">
          <i class="fas fa-chart-line text-3xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-2xl font-bold counter-value">{{ resumen.totalVentas }}</h3>
          <p class="text-sm opacity-90">Total de Ventas</p>
        </div>
      </div>
      <div class="summary-card2 flex items-center">
        <div class="icon-container">
          <i class="fas fa-box-open text-3xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-2xl font-bold counter-value">{{ resumen.totalProductosVendidos }}</h3>
          <p class="text-sm opacity-90">Total de Productos Vendidos</p>
        </div>
      </div>
    </div>

    <!-- Selector de Período (podría implementarse con la lógica existente) -->
    <div class="filter-container mb-6">
      <div class="text-center text-sm text-gray-600 dark:text-gray-300">
        <span>Visualizando datos del último periodo disponible</span>
      </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
      <div class="chart-container">
        <div class="chart-header">
          <i class="fas fa-line-chart text-blue-500"></i>
          <span>Tendencia de Ingresos</span>
        </div>
        <v-chart class="chart" :option="ingresosChart" autoresize />
      </div>
      <div class="chart-container">
        <div class="chart-header">
          <i class="fas fa-bars text-green-500"></i>
          <span>Mejores Ventas</span>
        </div>
        <v-chart class="chart" :option="ventasChart" autoresize />
      </div>
      <div class="chart-container">
        <div class="chart-header">
          <i class="fas fa-chart-pie text-orange-500"></i>
          <span>Popularidad de Productos</span>
        </div>
        <v-chart class="chart" :option="productosChart" autoresize />
      </div>
    </div>

    <div>
      <p class="dark:text-white text-center mt-6 mb-10 text-sm">Total de visitas: {{ count }}</p>
    </div>
  </AdminLayout>
</template>

<style scoped>
/* Estilos mejorados para los resúmenes */
.summary-card,
.summary-card2 {
  padding: 20px;
  border-radius: 12px;
  color: white;
  font-weight: bold;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.summary-card:hover,
.summary-card2:hover {
  transform: translateY(-5px);
  box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.15);
}

.summary-card {
  background: linear-gradient(135deg, #4caf50, #2e7d32);
}

.summary-card2 {
  background: linear-gradient(135deg, #ffb74d, #ff9800);
  color: #333;
}

.summary-card::before,
.summary-card2::before {
  content: '';
  position: absolute;
  top: -10%;
  right: -10%;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  pointer-events: none;
}

.icon-container {
  background: rgba(255, 255, 255, 0.2);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse 2s infinite;
}

/* Animación para los contadores */
.counter-value {
  display: inline-block;
  position: relative;
  animation: fadeInUp 1s ease-out;
}

/* Estilos para los gráficos */
.chart-container {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border: 1px solid #f0f0f0;
  position: relative;
}

.chart-container:hover {
  transform: translateY(-5px);
  box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.12);
}

.chart {
  width: 100%;
  height: 350px;
  transition: all 0.5s ease;
}

.chart-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 15px;
  font-weight: bold;
  color: #555;
  font-size: 14px;
}

.filter-container {
  background: rgba(240, 240, 240, 0.5);
  padding: 10px;
  border-radius: 10px;
  display: flex;
  justify-content: center;
}

/* Responsive fixes */
@media (max-width: 768px) {
  .chart-container {
    padding: 15px 10px;
  }

  .chart {
    height: 300px;
  }
}

/* Animaciones */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
  }

  70% {
    box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>