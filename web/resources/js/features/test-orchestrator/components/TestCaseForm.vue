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
import { computed, ref, onMounted } from "vue";
import Textarea from "primevue/textarea";
import Button from "primevue/button";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";

onMounted(() => {
    hydrateForm();
});

const props = defineProps({
    suiteId: [Number, String],
    testCase: Object,
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

const endpointOptions = computed(() => {
    return props.endpoints.map((endpoint) => ({
        label: `${endpoint.method} ${endpoint.path} - ${endpoint.name}`,
        value: endpoint.id,
    }));
});

const selectedEndpoint = computed(() => {
    return props.endpoints.find((item) => item.id === form.value.endpoint_id) ?? null;
});

const selectedEndpointVariableKeys = computed(() => {
    const variables = selectedEndpoint.value?.variables ?? [];

    return variables
        .map((item) => item?.key)
        .filter((key) => Boolean(key));
});

const httpStatusOptions = [
    { label: "200", value: 200 },
    { label: "201", value: 201 },
    { label: "400", value: 400 },
    { label: "401", value: 401 },
    { label: "404", value: 404 },
    { label: "500", value: 500 },
];

const form = ref({
    name: "",
    endpoint_id: null,
    expected_status: 200,
    request_json: "",
    expected_json: "",
});

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
        };
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
    };
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
        const payload = {
            name: form.value.name,
            endpoint_id: form.value.endpoint_id,
            expected_status: form.value.expected_status,
            request_json: JSON.parse(form.value.request_json || "{}"),
            expected_json: JSON.parse(form.value.expected_json || "{}"),
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
