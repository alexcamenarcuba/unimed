<template>
    <Toast />
    <ConfirmDialog />
    <div class="min-h-screen flex w-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">
        <div
            v-if="isMobile && sidebarMobileOpen"
            class="fixed inset-0 z-40 bg-slate-900/45 backdrop-blur-[2px]"
            @click="sidebarMobileOpen = false"
        ></div>

        <aside
            :class="[
                'fixed md:sticky md:top-0 md:self-start z-50 h-screen border-r border-slate-200/70 bg-white/90 dark:border-slate-800 dark:bg-slate-900/95 backdrop-blur-xl transition-all duration-300',
                isMobile
                    ? sidebarMobileOpen
                        ? 'translate-x-0 w-72'
                        : '-translate-x-full w-72'
                    : sidebarOpen
                      ? 'w-72'
                      : 'w-24',
            ]"
        >
            <div
                class="h-16 px-4 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-linear-to-br from-cyan-500 to-emerald-500 text-white flex items-center justify-center shadow-lg shadow-cyan-600/20"
                    >
                        <i class="pi pi-bolt text-lg"></i>
                    </div>
                    <div v-if="sidebarOpen || isMobile" class="leading-tight">
                        <p
                            class="text-sm font-semibold tracking-wide text-slate-800 dark:text-slate-100"
                        >
                            CRM - API Testes
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400"></p>
                    </div>
                </div>

                <button
                    @click="toggleSidebar"
                    class="h-9 w-9 rounded-lg text-slate-500 dark:text-slate-300 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    type="button"
                >
                    <i
                        :class="
                            sidebarOpen || (isMobile && sidebarMobileOpen)
                                ? 'pi pi-angle-left'
                                : 'pi pi-angle-right'
                        "
                    ></i>
                </button>
            </div>

            <nav class="p-3 space-y-1">
                <Link
                    v-for="item in menu"
                    :key="item.label"
                    :href="item.href"
                    class="relative group flex items-center w-full rounded-xl p-3 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/90 dark:hover:bg-slate-800/90 transition-all"
                    :class="[
                        sidebarOpen || isMobile
                            ? 'gap-3 justify-start'
                            : 'justify-center',
                    ]"
                >
                    <span
                        class="h-9 w-9 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700 flex items-center justify-center text-base"
                    >
                        <i :class="item.icon"></i>
                    </span>

                    <span
                        v-if="sidebarOpen || isMobile"
                        class="text-sm font-medium"
                    >
                        {{ item.label }}
                    </span>

                    <span
                        v-if="!sidebarOpen && !isMobile"
                        class="absolute left-full ml-2 px-2 py-1 text-xs bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 rounded-md opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none"
                    >
                        {{ item.label }}
                    </span>
                </Link>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header
                class="relative z-40 h-16 bg-white dark:bg-slate-900 border-b border-slate-200/70 dark:border-slate-800 px-3 sm:px-4 md:px-6"
            >
                <div
                    :class="[
                        'h-full flex items-center w-full',
                        props.contentFullWidth ? '' : 'mx-auto max-w-7xl',
                    ]"
                >
                    <button
                        v-if="isMobile"
                        @click="toggleSidebar"
                        class="mr-3 h-9 w-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200"
                        type="button"
                    >
                        <i class="pi pi-bars"></i>
                    </button>

                    <div class="ml-auto flex items-center gap-4 md:gap-5">
                        <button
                            type="button"
                            class="h-9 w-9 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            @click="toggleTheme"
                            :title="isDark ? 'Ativar tema claro' : 'Ativar tema escuro'"
                        >
                            <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'"></i>
                        </button>
                        <i
                            class="pi pi-search cursor-pointer text-slate-500 dark:text-slate-300 hover:text-slate-700 dark:hover:text-white transition"
                        ></i>
                        <i
                            class="pi pi-bell cursor-pointer text-slate-500 dark:text-slate-300 hover:text-slate-700 dark:hover:text-white transition"
                        ></i>

                        <div class="relative user-menu">
                            <div
                                class="flex items-center gap-2 cursor-pointer"
                                @click="toggleUserMenu"
                            >
                                <img
                                    src="https://i.pravatar.cc/40"
                                    class="w-8 h-8 rounded-full"
                                    alt="Avatar do usuario"
                                />
                                <span class="hidden sm:inline">Admin</span>
                                <i
                                    :class="[
                                        userMenuOpen
                                            ? 'pi pi-chevron-up'
                                            : 'pi pi-chevron-down',
                                        'text-xs transition-all duration-200',
                                    ]"
                                ></i>
                            </div>

                            <div
                                v-if="userMenuOpen"
                                class="absolute right-0 mt-4 w-72 bg-white dark:bg-slate-900 shadow-md p-4 z-999 rounded-xl border border-slate-100 dark:border-slate-700"
                            >
                                <div class="mb-3">
                                    <p class="font-semibold">Admin User</p>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">
                                        admin@email.com
                                    </p>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <button
                                        class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 dark:hover:bg-slate-800"
                                        type="button"
                                    >
                                        <i class="pi pi-user"></i>
                                        <span>Edit profile</span>
                                    </button>

                                    <button
                                        class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 dark:hover:bg-slate-800"
                                        type="button"
                                    >
                                        <i class="pi pi-cog"></i>
                                        <span>Account settings</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main
                :class="[
                    'flex-1',
                    props.contentFullWidth ? 'p-0' : 'p-3 sm:p-4 md:p-6',
                ]"
            >
                <div
                    :class="[
                        'motion-safe:animate-[fade-in_280ms_ease-out]',
                        props.contentFullWidth ? 'w-full' : 'mx-auto max-w-7xl',
                    ]"
                >
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    contentFullWidth: {
        type: Boolean,
        default: false,
    },
});

const sidebarOpen = ref(true);
const sidebarMobileOpen = ref(false);
const isMobile = ref(false);
const isDark = ref(false);

const userMenuOpen = ref(false);
const menu = [
    { label: "Dashboard",           icon: "pi pi-home",       href: "/" },
    { label: "Cenários de Testes",  icon: "pi pi-list-check", href: "/test-suites" },
    { label: "Histórico de Runs",   icon: "pi pi-chart-line", href: "/test-runs" },
    { label: "Chamados",            icon: "pi pi-ticket", href: "/tickets" },
    { label: "Requirements",        icon: "pi pi-sitemap", href: "/tickets/requirements" },
];

const checkScreen = () => {
    isMobile.value = window.innerWidth < 768;

    // Mantem o estado da sidebar coerente ao trocar de breakpoint.
    if (!isMobile.value) {
        sidebarMobileOpen.value = false;
    }
};

const toggleSidebar = () => {
    if (isMobile.value) {
        sidebarMobileOpen.value = !sidebarMobileOpen.value;
    } else {
        sidebarOpen.value = !sidebarOpen.value;
    }
};

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value;
};

const applyTheme = (darkEnabled) => {
    isDark.value = darkEnabled;
    document.documentElement.classList.toggle("dark", darkEnabled);
    window.localStorage.setItem("theme", darkEnabled ? "dark" : "light");
};

const toggleTheme = () => {
    applyTheme(!isDark.value);
};

// fechar ao clicar fora
const handleClickOutside = (e) => {
    if (!e.target.closest(".user-menu")) {
        userMenuOpen.value = false;
    }
};

onMounted(() => {
    const savedTheme = window.localStorage.getItem("theme");
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    applyTheme(savedTheme ? savedTheme === "dark" : prefersDark);

    checkScreen();
    window.addEventListener("resize", checkScreen);
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", checkScreen);
    document.removeEventListener("click", handleClickOutside);
});
</script>
