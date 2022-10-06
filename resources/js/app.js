require('./bootstrap');

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Northlink Scraper';

import { SetupCalendar, Calendar, DatePicker } from 'v-calendar';

// Setup plugin for defaults or `$screens` (optional)
// app.use(SetupCalendar, {})
// // Use the components
// app.component('Calendar', Calendar)
// app.component('DatePicker', DatePicker)

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => require(`./Pages/${name}.vue`),
    setup({ el, app, props, plugin }) {
        return createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(SetupCalendar)
            .mixin({ methods: { route } })
            .mixin({
                components: {
                    InertiaProgress,
                    Calendar, DatePicker
                }
            })
            .mount(el);
    },
});

InertiaProgress.init({ color: '#4B5563' });
