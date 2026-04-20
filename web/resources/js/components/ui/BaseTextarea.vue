<template>
    <BaseFormField :label="label" :for-id="id" :error="error" :hint="hint">
        <Textarea :id="id" v-model="value" :rows="rows" class="w-full" />
    </BaseFormField>
</template>

<script setup>
import { computed } from "vue";
import Textarea from "primevue/textarea";
import BaseFormField from "./BaseFormField.vue";

const props = defineProps({
    modelValue: String,
    label: String,
    id: {
        type: String,
        default: () => `textarea-${Math.random().toString(36).slice(2)}`,
    },
    rows: {
        type: Number,
        default: 3,
    },
    error: String,
    hint: String,
});

const emit = defineEmits(["update:modelValue"]);

const value = computed({
    get: () => props.modelValue,
    set: (val) => emit("update:modelValue", val),
});
</script>
