import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

import PrimeVue from "primevue/config";
import MyTheme from "./theme/style";

import ToastService from "primevue/toastservice";
import Toast from "primevue/toast";
import ConfirmationService from "primevue/confirmationservice";
import ConfirmDialog from "primevue/confirmdialog";

const savedTheme = window.localStorage.getItem("theme");
const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
const shouldUseDark = savedTheme ? savedTheme === "dark" : prefersDark;
document.documentElement.classList.toggle("dark", shouldUseDark);

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
        app.use(ConfirmationService);
        app.component("Toast", Toast);
        app.component("ConfirmDialog", ConfirmDialog);

        app.mount(el);
    },
});
