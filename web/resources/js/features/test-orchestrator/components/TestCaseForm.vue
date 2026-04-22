<template>
    <div class="flex flex-col gap-4">
        <BaseInputText
            v-model="form.name"
            label="Nome do teste"
            placeholder="Ex: Login com credenciais validas"
        />

        <!-- MÉTODO + ENDPOINT + STATUS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <BaseSelect
                v-model="form.method"
                label="Método"
                :options="methods"
                optionLabel="label"
                optionValue="value"
                placeholder="Selecione o método"
            />

            <div class="md:col-span-2">
                <BaseInputText
                    v-model="form.endpoint"
                    label="Endpoint"
                    placeholder="/contratante/login"
                />
            </div>

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
import { ref, watch, onMounted } from "vue";
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
});

const emit = defineEmits(["saved", "cancel"]);

const methods = [
    { label: "GET", value: "GET" },
    { label: "POST", value: "POST" },
    { label: "PUT", value: "PUT" },
    { label: "DELETE", value: "DELETE" },
];

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
    method: "POST",
    endpoint: "",
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
            method: props.testCase.method,
            endpoint: props.testCase.endpoint,
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
        method: "POST",
        endpoint: "",
        expected_status: 200,
        request_json: "",
        expected_json: "",
    };
}

async function save() {
    try {
        const payload = {
            name: form.value.name,
            method: form.value.method,
            endpoint: form.value.endpoint,
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
