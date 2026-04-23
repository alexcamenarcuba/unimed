import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

import PrimeVue from "primevue/config";
import MyTheme from "./theme/style";

import ToastService from "primevue/toastservice";
import Toast from "primevue/toast";

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./features/**/*.vue");

    const page = Object.keys(pages).find(path =>
        path.endsWith(`${name}.vue`)
    );
    if (!page) {
        throw new Error(`Página não encontrada: ${name}`);
    }

    return pages[page]();
    },

    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(PrimeVue, {
                theme: {
                    preset: MyTheme,
                    options: {
                        prefix: "p",
                        darkModeSelector: ".dark",
                        cssLayer: false,
                    },
                },
            });

        // Registrar componentes base globalmente
        const baseComponents = import.meta.glob("./components/ui/Base*.vue", {
            eager: true,
        });

        for (const [path, module] of Object.entries(baseComponents)) {
            const componentName = path.split("/").pop().replace(".vue", "");
            app.component(componentName, module.default);
        }

        app.use(ToastService);
        app.component("Toast", Toast);

        app.mount(el);
    },
});
