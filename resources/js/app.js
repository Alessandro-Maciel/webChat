require('./bootstrap');

// Import modules...

import { createApp, h } from 'vue';
import { App as InertiaApp, plugin as InertiaPlugin } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import moment from 'moment';
import { store } from './store';

//store.dispatch('userStateAction');

const el = document.getElementById('app');

moment.locale("pt-br");

createApp({
    render: () =>
        h(InertiaApp, {
            initialPage: JSON.parse(el.dataset.page),
            resolveComponent: (name) => require(`./Pages/${name}`).default,
        }),
})
    .use(store)
    .mixin({ methods: { route } })
    .use(InertiaPlugin)
    .mount(el);


InertiaProgress.init({ color: '#4B5563' });



