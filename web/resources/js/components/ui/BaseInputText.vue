<template>
    <BaseFormField :for-id="id" :error="error" :hint="hint" :label="label">
        <FloatLabel
            variant="on"
            :pt="{ label: { style: 'background-color: transparent' } }"
        >
            <InputText
                :id="id"
                v-model="value"
                :disabled="disabled"
                :class="[
                    'w-full',
                    {
                        'p-inputtext-sm': dense,
                        'p-invalid': error,
                    },
                ]"
            />
            <label :for="id">{{ label }}</label>
        </FloatLabel>
    </BaseFormField>
</template>

<script setup>
import { computed } from "vue";
import InputText from "primevue/inputtext";
import BaseFormField from "./BaseFormField.vue";
import FloatLabel from "primevue/floatlabel";

const props = defineProps({
    modelValue: String,
    label: String,
    id: {
        type: String,
        default: () => `input-${Math.random().toString(36).slice(2)}`,
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
