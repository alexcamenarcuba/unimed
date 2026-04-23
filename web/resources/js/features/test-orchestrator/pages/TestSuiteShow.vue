<template>
    <AdminLayout>
        <!-- BG SECTION -->
        <div class="bg-gradient-to-b from-emerald-50/40 to-transparent pt-6 pb-8 -mx-4 px-4 sm:-mx-6 sm:px-6 md:-mx-8 md:px-8">
            <div class="max-w-7xl mx-auto">
                <!-- HEADER -->
                <div class="flex justify-between items-start gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ suite.name }}</h1>
                        <p class="text-sm text-gray-500">
                            Base URL: {{ suite.base_url }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap justify-end">
                        <Button
                            label="Selecionar todos"
                            severity="secondary"
                            text
                            @click="selectAllCases"
                        />
                        <Button
                            label="Limpar selecao"
                            severity="secondary"
                            text
                            @click="clearSelectedCases"
                        />
                        <TestRunnerButton
                            :suiteId="suite.id"
                            :testCaseIds="selectedCaseIds"
                            :disabled="selectedCaseIds.length === 0"
                        />
                    </div>
                </div>

                <p class="text-sm text-gray-500 mb-4">
                    {{ selectedCaseIds.length }} de {{ suite.cases?.length ?? 0 }} cenarios selecionados para execucao.
                </p>

                <!-- AÇÃO -->
                <div class="flex gap-2">
                    <Button
                        label="Editar Suite"
                        icon="pi pi-pencil"
                        severity="secondary"
                        @click="goToEditSuite"
                    />
                    <Button
                        label="Novo Ambiente"
                        icon="pi pi-server"
                        severity="secondary"
                        @click="goToCreateEnvironment"
                    />
                    <Button
                        label="Novo Endpoint"
                        icon="pi pi-link"
                        severity="secondary"
                        @click="goToCreateEndpoint"
                    />
                    <Button
                        label="Novo Cenário"
                        icon="pi pi-plus"
                        @click="goToCreate"
                    />
                </div>
            </div>
        </div>

    <div class="p-4">
        <!-- TABELA -->
        <div class="overflow-x-auto border rounded-lg">
            <TestCaseTable
                :cases="suite.cases"
                :environments="environments"
                v-model:selectedCaseIds="selectedCaseIds"
                @edit="openEdit"
                @edit-endpoint="openEditEndpoint"
            />
        </div>

        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-2">Ambientes</h2>
            <DataTable :value="environments" stripedRows>
                <Column field="name" header="Nome" />
                <Column field="base_url" header="Base URL" />
                <Column header="Auth">
                    <template #body="slotProps">
                        <Tag
                            :severity="slotProps.data.requires_auth ? 'info' : 'secondary'"
                            :value="slotProps.data.requires_auth ? 'Bearer' : 'Sem Auth'"
                        />
                    </template>
                </Column>
                <Column header="Ativo">
                    <template #body="slotProps">
                        <Tag
                            :severity="slotProps.data.is_active ? 'success' : 'danger'"
                            :value="slotProps.data.is_active ? 'Sim' : 'Nao'"
                        />
                    </template>
                </Column>
                <Column header="Acoes">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            text
                            @click="goToEditEnvironment(slotProps.data.id)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
    </AdminLayout>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import AdminLayout from "../../../layouts/AdminLayout.vue";
import { router } from "@inertiajs/vue3";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import TestCaseTable from "../components/TestCaseTable.vue";
import TestRunnerButton from "../components/TestRunnerButton.vue";

const props = defineProps({
    suite: Object,
    environments: {
        type: Array,
        default: () => []
    }
})

const selectedCaseIds = ref([])
const allCaseIds = computed(() => (props.suite?.cases ?? []).map((testCase) => testCase.id))

watch(allCaseIds, (caseIds) => {
    selectedCaseIds.value = [...caseIds]
}, { immediate: true })

function selectAllCases() {
    selectedCaseIds.value = [...allCaseIds.value]
}

function clearSelectedCases() {
    selectedCaseIds.value = []
}

function openEdit(testCase) {
        router.visit(`/test-suites/${props.suite.id}/cases/${testCase.id}/edit`);
}

function openEditEndpoint(endpointId) {
    router.visit(`/test-suites/${props.suite.id}/endpoints/${endpointId}/edit`);
}

function goToCreate() {
    router.visit(`/test-suites/${props.suite.id}/cases/create`);
}

function goToCreateEndpoint() {
    router.visit(`/test-suites/${props.suite.id}/endpoints/create`);
}

function goToCreateEnvironment() {
    router.visit(`/test-suites/${props.suite.id}/environments/create`);
}

function goToEditEnvironment(environmentId) {
    router.visit(`/test-suites/${props.suite.id}/environments/${environmentId}/edit`);
}

function goToEditSuite() {
    router.visit(`/test-suites/${props.suite.id}/edit`);
}
</script>
