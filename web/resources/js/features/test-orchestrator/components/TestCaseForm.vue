<template>
    <div class="flex flex-col gap-4">
        <BaseInputText
            v-model="form.name"
            label="Nome do teste"
            placeholder="Ex: Login com credenciais validas"
        />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <BaseSelect
                v-model="form.endpoint_id"
                label="Endpoint"
                :options="endpointOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Selecione o endpoint"
            />

            <div>
                <BaseSelect
                    v-model="form.expected_status"
                    label="HttpCode Esperado"
                    :options="httpStatusOptions"
                    optionLabel="label"
                    optionValue="value"
                />
            </div>
        </div>

        <div
            v-if="selectedEndpointVariables.length"
            class="border rounded-md p-3 flex flex-col gap-2"
        >
            <p class="text-sm font-medium">Overrides de variáveis para este cenário</p>
            <p class="text-xs text-gray-500">
                O valor padrao vale para todos os ambientes. Se preencher um ambiente especifico, ele sobrescreve apenas naquele sistema.
            </p>

            <div class="flex flex-col gap-3">
                <div
                    v-for="environment in targetEnvironments"
                    :key="`override-env-${environment.id}`"
                    class="border border-gray-200 rounded p-3 flex flex-col gap-3"
                >
                    <p class="text-xs text-gray-500">{{ environment.name }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <template v-for="variable in selectedEndpointVariables" :key="`override-${environment.id}-${variable.key}`">
                            <BaseInputText
                                v-if="variable.type === 'simple'"
                                v-model="form.variable_overrides[environment.id][variable.key]"
                                :label="`Variável ${variable.key}`"
                                placeholder="Valor para este cenário"
                            />

                            <div v-else-if="variable.type === 'array'" class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Variável {{ variable.key }}</label>
                                <Textarea
                                    v-model="form.variable_override_json[environment.id][variable.key]"
                                    rows="5"
                                    class="w-full font-mono"
                                    placeholder='{"campo": "valor"}'
                                />
                                <p class="text-xs text-gray-500">
                                    Informe um JSON para sobrescrever este array/objeto neste ambiente.
                                </p>
                            </div>

                            <div v-else class="flex flex-col justify-center">
                                <p class="text-sm font-medium">Variável {{ variable.key }}</p>
                                <p class="text-xs text-gray-500">
                                    Override de arquivo por cenário ainda nao esta habilitado.
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <TabView>
            <!-- REQUEST -->
            <TabPanel header="Request">
                <div
                    v-if="selectedEndpointVariableKeys.length"
                    class="mb-3 border rounded-md p-3 flex flex-col gap-2"
                >
                    <p class="text-sm font-medium">Variáveis disponíveis do endpoint</p>
                    <p class="text-xs text-gray-500">
                        Clique para inserir no JSON como placeholder no formato
                        <code v-pre>{{chave}}</code>.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-for="variableKey in selectedEndpointVariableKeys"
                            :key="variableKey"
                            size="small"
                            severity="secondary"
                            :label="'{{' + variableKey + '}}'"
                            @click="insertVariablePlaceholder(variableKey)"
                        />
                    </div>
                </div>

                <Textarea
                    v-model="form.request_json"
                    rows="8"
                    class="w-full font-mono"
                    placeholder=''
                />
            </TabPanel>
        </TabView>
    </div>

    <div class="flex justify-between w-full">
        <Button label="Cancelar" severity="secondary" @click="close" />

        <Button label="Salvar" icon="pi pi-check" @click="save" />
    </div>
</template>

<script setup>
import axios from "axios";
import { computed, ref, onMounted, watch } from "vue";
import Textarea from "primevue/textarea";
import Button from "primevue/button";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";

const EMPTY_OBJECT_MARKER = "__crmqa_empty_object__";

onMounted(() => {
    hydrateForm();
});

const props = defineProps({
    suiteId: [Number, String],
    testCase: Object,
    environments: {
        type: Array,
        default: () => [],
    },
    endpoints: {
        type: Array,
        default: () => [],
    },
    initialEndpointId: {
        type: [String, Number],
        default: null,
    },
});

const emit = defineEmits(["saved", "cancel"]);

const targetEnvironments = computed(() => {
    const environments = [
        { id: "__default", name: "Padrao para todos os ambientes" },
        ...props.environments,
    ];

    if (environments.length > 1) {
        return environments;
    }

    return [{ id: "__default", name: "Padrao" }];
});

const endpointOptions = computed(() => {
    return props.endpoints.map((endpoint) => ({
        label: `${endpoint.method} ${endpoint.path} - ${endpoint.name}`,
        value: endpoint.id,
    }));
});

const form = ref({
    name: "",
    endpoint_id: null,
    expected_status: 200,
    request_json: "",
    expected_json: "",
    variable_overrides: {},
    variable_override_json: {},
});

const selectedEndpoint = computed(() => {
    return props.endpoints.find((item) => item.id === form.value.endpoint_id) ?? null;
});

const selectedEndpointVariables = computed(() => {
    return (selectedEndpoint.value?.variables ?? []).filter((item) => Boolean(item?.key));
});

const selectedEndpointVariableKeys = computed(() => {
    return selectedEndpointVariables.value
        .map((item) => item?.key)
        .filter((key) => Boolean(key));
});

watch(selectedEndpointVariables, () => {
    syncOverrideState();
}, { deep: true });

const httpStatusOptions = [
    { label: "200", value: 200 },
    { label: "201", value: 201 },
    { label: "400", value: 400 },
    { label: "401", value: 401 },
    { label: "404", value: 404 },
    { label: "500", value: 500 },
];

/**
 * Preenche form (edição ou novo)
 */
function hydrateForm() {
    if (props.testCase) {
        form.value = {
            name: props.testCase.name,
            endpoint_id: props.testCase.endpoint_id,
            expected_status: props.testCase.expected_status ?? 200,
            request_json: JSON.stringify(
                props.testCase.request_payload ?? {},
                null,
                2,
            ),
            expected_json: JSON.stringify(
                props.testCase.expected_response ?? {},
                null,
                2,
            ),
            variable_overrides: normalizeVariableOverrides(props.testCase.variable_overrides ?? {}),
            variable_override_json: {},
        };

        syncOverrideState();
    } else {
        resetForm();
    }
}

function resetForm() {
    form.value = {
        name: "",
        endpoint_id: props.initialEndpointId,
        expected_status: 200,
        request_json: "",
        expected_json: "",
        variable_overrides: {},
        variable_override_json: {},
    };

    syncOverrideState();
}

function normalizeVariableOverrides(overrides) {
    if (!overrides || typeof overrides !== "object" || Array.isArray(overrides)) {
        return {};
    }

    if (isFlatOverrideMap(overrides)) {
        return {
            __default: overrides,
        };
    }

    return overrides;
}

function stringifyOverrideValue(value) {
    const normalizedValue = unmarkEmptyObjects(value);

    if (normalizedValue === null || normalizedValue === undefined || normalizedValue === "") {
        return "";
    }

    if (typeof normalizedValue === "string") {
        return normalizedValue;
    }

    return JSON.stringify(normalizedValue, null, 2);
}

function unmarkEmptyObjects(value) {
    if (!Array.isArray(value) && (!value || typeof value !== "object")) {
        return value;
    }

    if (
        value
        && !Array.isArray(value)
        && Object.keys(value).length === 1
        && value[EMPTY_OBJECT_MARKER] === true
    ) {
        return {};
    }

    if (Array.isArray(value)) {
        return value.map((item) => unmarkEmptyObjects(item));
    }

    return Object.fromEntries(Object.entries(value).map(([key, item]) => [key, unmarkEmptyObjects(item)]));
}

function isFlatOverrideMap(overrides) {
    return Object.values(overrides).some((value) => !Array.isArray(value) && (typeof value !== "object" || value === null));
}

function syncOverrideState() {
    const normalizedOverrides = normalizeVariableOverrides(form.value.variable_overrides ?? {});
    const nextOverrides = {};
    const nextOverrideJson = {};

    for (const environment of targetEnvironments.value) {
        const environmentOverrides = normalizedOverrides[environment.id] ?? {};
        nextOverrides[environment.id] = {};
        nextOverrideJson[environment.id] = {};

        for (const variable of selectedEndpointVariables.value) {
            const currentValue = environmentOverrides[variable.key];

            if (variable.type === "array") {
                nextOverrides[environment.id][variable.key] = currentValue ?? null;
                nextOverrideJson[environment.id][variable.key] = stringifyOverrideValue(currentValue);
                continue;
            }

            nextOverrides[environment.id][variable.key] = currentValue ?? "";
        }
    }

    form.value.variable_overrides = nextOverrides;
    form.value.variable_override_json = nextOverrideJson;
}

function buildFilteredOverrides() {
    const overrides = {};

    for (const environment of targetEnvironments.value) {
        const environmentOverrides = {};

        for (const variable of selectedEndpointVariables.value) {
            if (variable.type === "array") {
                const rawValue = form.value.variable_override_json?.[environment.id]?.[variable.key] ?? "";

                if (rawValue.trim() === "") {
                    continue;
                }

                environmentOverrides[variable.key] = markEmptyObjects(JSON.parse(rawValue));
                continue;
            }

            const value = form.value.variable_overrides?.[environment.id]?.[variable.key];

            if (value === null || value === undefined) {
                continue;
            }

            if (typeof value === "string" && value.trim() === "") {
                continue;
            }

            environmentOverrides[variable.key] = value;
        }

        if (Object.keys(environmentOverrides).length > 0) {
            overrides[environment.id] = environmentOverrides;
        }
    }

    return overrides;
}

function markEmptyObjects(value) {
    if (Array.isArray(value)) {
        return value.map((item) => markEmptyObjects(item));
    }

    if (value && typeof value === "object") {
        const entries = Object.entries(value);

        if (entries.length === 0) {
            return {
                [EMPTY_OBJECT_MARKER]: true,
            };
        }

        return Object.fromEntries(entries.map(([key, item]) => [key, markEmptyObjects(item)]));
    }

    return value;
}

function insertVariablePlaceholder(variableKey) {
    const placeholder = `{{${variableKey}}}`;

    try {
        const requestObject = JSON.parse(form.value.request_json || "{}");

        if (!requestObject || Array.isArray(requestObject) || typeof requestObject !== "object") {
            alert("O request precisa ser um JSON objeto para inserir placeholders automaticamente.");
            return;
        }

        requestObject[variableKey] = placeholder;
        form.value.request_json = JSON.stringify(requestObject, null, 2);
    } catch (e) {
        alert("JSON do request inválido. Corrija o JSON para inserir placeholders automaticamente.");
    }
}

async function save() {
    try {
        const filteredOverrides = buildFilteredOverrides();

        const payload = {
            name: form.value.name,
            endpoint_id: form.value.endpoint_id,
            expected_status: form.value.expected_status,
            request_json: JSON.parse(form.value.request_json || "{}"),
            expected_json: JSON.parse(form.value.expected_json || "{}"),
            variable_overrides: filteredOverrides,
        };

        if (props.testCase) {
            await axios.put( `/test-suites/${props.suiteId}/cases/${props.testCase.id}`, payload);
        } else {
            await axios.post(`/test-suites/${props.suiteId}/cases`, payload);
        }

        emit("saved");
    } catch (e) {
        alert("JSON inválido ou erro ao salvar");
    }
}

function close() {
    emit("cancel");
}
</script>
