<template>
    <div class="min-h-screen flex">
        <!-- LADO ESQUERDO (FIXO) -->
        <div
            class="hidden md:flex w-1/2 bg-black text-white items-center justify-center"
        >
            <div class="max-w-md text-center">
                <h1 class="text-3xl font-bold mb-4">CRM - Teste API</h1>
                <p class="text-gray-300">Sistema Adminstrativo</p>
            </div>
        </div>

        <!-- LADO DIREITO (DINÂMICO) -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>
    </div>
</template>
