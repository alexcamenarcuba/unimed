<template>
    <BaseFormField :error="error" :hint="hint">
        <FloatLabel variant="on">
            <Select
                :id="id"
                v-model="value"
                :options="options"
                :optionLabel="optionLabel"
                :optionValue="optionValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :class="{ 'p-inputtext-sm': dense }"
                class="w-full"
            />
            <label v-if="label" :for="id">{{ label }}</label>
        </FloatLabel>
    </BaseFormField>
</template>

<script setup>
import { computed } from "vue";
import Select from "primevue/select";
import FloatLabel from "primevue/floatlabel";
import BaseFormField from "./BaseFormField.vue";

const props = defineProps({
    modelValue: [String, Number, Object],
    label: String,
    options: {
        type: Array,
        default: () => [],
    },
    optionLabel: {
        type: String,
        default: "label",
    },
    optionValue: {
        type: String,
        default: "value",
    },
    placeholder: String,
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).slice(2)}`,
    },
    dense: Boolean,
    disabled: Boolean,
    error: String,
    hint: String,
});

const emit = defineEmits(["update:modelValue"]);

const value = computed({
    get: () => props.modelValue,
    set: (val) => emit("update:modelValue", val),
});
</script>
