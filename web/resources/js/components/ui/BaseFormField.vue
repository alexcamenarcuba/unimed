<template>
    <div class="flex flex-col gap-1 w-full">
        <slot />

        <small v-if="hint && !error" class="text-gray-500">
            {{ hint }}
        </small>

        <small v-if="error" class="text-red-500 text-sm">
            {{ error }}
        </small>
    </div>
</template>

<script setup>
defineProps({
    forId: String,
    error: String,
    hint: String,
});
</script>
