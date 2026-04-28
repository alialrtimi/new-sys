import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp,router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
// import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
// أمثلة على ايقونات
import { faUser, faHome, faEdit, faTrash,faBars,faEllipsisVertical   } from '@fortawesome/free-solid-svg-icons'

library.add(faUser, faHome, faEdit, faTrash,faBars,faEllipsisVertical  )
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(ElementPlus)
            .component('font-awesome-icon', FontAwesomeIcon)
            
            .mount(el);
    },
    progress: {
        color: '#4B5563',
        showSpinner: true, 
        delay: 100000, 
    },
});
router.on('start', () => {
    const buttons = document.getElementsByTagName('button');

    // Loop through each button and add a class
    for (let button of buttons) {
        button.classList.add('disabled'); // Replace 'your-class-name' with your desired class
    }

});


router.on('finish', () => {
    const buttons = document.querySelectorAll('button');

    // Remove the class from each button
    buttons.forEach(button => {
        button.classList.remove('disabled'); // Replace 'your-class-name' with the class you want to remove
    });
});