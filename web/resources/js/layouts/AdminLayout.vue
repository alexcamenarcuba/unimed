<template>
    <Toast />
    <div
        class="min-h-screen flex bg-[radial-gradient(circle_at_85%_-10%,rgba(16,185,129,0.14),transparent_42%),radial-gradient(circle_at_0%_100%,rgba(14,165,233,0.12),transparent_40%),linear-gradient(180deg,#f8fafc_0%,#f1f5f9_100%)]"
    >
        <div
            v-if="isMobile && sidebarMobileOpen"
            class="fixed inset-0 z-40 bg-slate-900/45 backdrop-blur-[2px]"
            @click="sidebarMobileOpen = false"
        ></div>

        <aside
            :class="[
                'fixed md:sticky md:top-0 md:self-start z-50 h-screen border-r border-slate-200/70 bg-white/90 backdrop-blur-xl transition-all duration-300',
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
                class="h-16 px-4 border-b border-slate-200/70 flex items-center justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-linear-to-br from-cyan-500 to-emerald-500 text-white flex items-center justify-center shadow-lg shadow-cyan-600/20"
                    >
                        <i class="pi pi-bolt text-lg"></i>
                    </div>
                    <div v-if="sidebarOpen || isMobile" class="leading-tight">
                        <p
                            class="text-sm font-semibold tracking-wide text-slate-800"
                        >
                            CRM - API Testes
                        </p>
                        <p class="text-xs text-slate-500"></p>
                    </div>
                </div>

                <button
                    @click="toggleSidebar"
                    class="h-9 w-9 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition"
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
                    class="relative group flex items-center w-full rounded-xl p-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100/90 transition-all"
                    :class="[
                        sidebarOpen || isMobile
                            ? 'gap-3 justify-start'
                            : 'justify-center',
                    ]"
                >
                    <span
                        class="h-9 w-9 rounded-lg bg-slate-50 border border-slate-200/70 flex items-center justify-center text-base"
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
                        class="absolute left-full ml-2 px-2 py-1 text-xs bg-slate-900 text-white rounded-md opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none"
                    >
                        {{ item.label }}
                    </span>
                </Link>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-h-screen md:pl-0">
            <header
                class="relative z-40 h-16 bg-white border-b border-slate-200/70 flex items-center px-4 md:px-5"
            >
                <button
                    v-if="isMobile"
                    @click="toggleSidebar"
                    class="mr-3 h-9 w-9 rounded-md border border-slate-200 text-slate-600"
                    type="button"
                >
                    <i class="pi pi-bars"></i>
                </button>

                <div class="ml-auto flex items-center gap-4 md:gap-5">
                    <i
                        class="pi pi-search cursor-pointer text-slate-500 hover:text-slate-700 transition"
                    ></i>
                    <i
                        class="pi pi-bell cursor-pointer text-slate-500 hover:text-slate-700 transition"
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
                            class="absolute right-0 mt-4 w-72 bg-white shadow-md p-4 z-999 rounded-xl border border-slate-100"
                        >
                            <div class="mb-3">
                                <p class="font-semibold">Admin User</p>
                                <p class="text-sm text-gray-500">
                                    admin@email.com
                                </p>
                            </div>

                            <div class="flex flex-col gap-1">
                                <button
                                    class="flex items-center gap-2 p-2 rounded hover:bg-gray-50"
                                    type="button"
                                >
                                    <i class="pi pi-user"></i>
                                    <span>Edit profile</span>
                                </button>

                                <button
                                    class="flex items-center gap-2 p-2 rounded hover:bg-gray-50"
                                    type="button"
                                >
                                    <i class="pi pi-cog"></i>
                                    <span>Account settings</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-3 sm:p-4 md:p-6">
                <div
                    class="mx-auto max-w-7xl motion-safe:animate-[fade-in_280ms_ease-out]"
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

const sidebarOpen = ref(true);
const sidebarMobileOpen = ref(false);
const isMobile = ref(false);

const userMenuOpen = ref(false);
const menu = [
    { label: "Dashboard",           icon: "pi pi-home",       href: "/" },
    { label: "Cenários de Testes",  icon: "pi pi-list-check", href: "/test-suites" },
    { label: "Histórico de Runs",   icon: "pi pi-chart-line", href: "/test-runs" },
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

// fechar ao clicar fora
const handleClickOutside = (e) => {
    if (!e.target.closest(".user-menu")) {
        userMenuOpen.value = false;
    }
};

onMounted(() => {
    checkScreen();
    window.addEventListener("resize", checkScreen);
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", checkScreen);
    document.removeEventListener("click", handleClickOutside);
});
</script>
