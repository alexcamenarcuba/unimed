<template>
    <BaseFormField
        :label="label"
        :for-id="id"
        :error="error"
        :hint="hint"
        hide-label
    >
        <div class="float-container" :class="{ active: isActive }">
            <Dropdown
                :inputId="id"
                v-model="value"
                :options="options"
                :optionLabel="optionLabel"
                :optionValue="optionValue"
                class="w-full"
                @focus="focused = true"
                @blur="focused = false"
            />

            <label :for="id">{{ label }}</label>
        </div>
    </BaseFormField>
</template>

<script setup>
import { computed, ref } from "vue";
import Dropdown from "primevue/dropdown";
import BaseFormField from "./BaseFormField.vue";

const props = defineProps({
    modelValue: [String, Number, Object],
    label: String,
    options: Array,
    optionLabel: String,
    optionValue: String,
    id: String,
    error: String,
    hint: String,
});

const emit = defineEmits(["update:modelValue"]);

const focused = ref(false);

const value = computed({
    get: () => props.modelValue,
    set: (val) => emit("update:modelValue", val),
});

const isActive = computed(() => {
    return focused.value || !!value.value;
});
</script>

<style scoped>
.float-container {
    position: relative;
}

.float-container label {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    padding: 0 4px;
    color: #6b7280;
    font-size: 14px;
    pointer-events: none;
    transition: all 0.2s ease;
}

/* estado ativo (igual input bonito) */
.float-container.active label {
    top: -2px;
    font-size: 12px;
    color: #10b981; /* verde igual seu exemplo */
}
</style>
