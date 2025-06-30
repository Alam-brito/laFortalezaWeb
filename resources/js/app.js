import '../css/app.css';
import './bootstrap';

import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

//Element plus para las ventanas
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import 'flowbite/dist/flowbite.min.js';

//Sweet Alert para las alertas
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

//Para el botón de modo oscuro
import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()

createInertiaApp({
    title: (title) => `${title} La Fortaleza`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .component("Head", Head)
            .component("Link", Link)
            .use(ZiggyVue)
            .use(ElementPlus)
            .use(VueSweetalert2)
            .mount(el)
    },
    progress: {
        color: '#81BFDA',
        showSpinner: true,
    },
});
