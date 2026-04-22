<template>
    <AdminLayout>
    <div class="p-4">
        <!-- HEADER -->
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold">{{ suite.name }}</h1>
                <p class="text-sm text-gray-500">
                    Base URL: {{ suite.base_url }}
                </p>
            </div>

            <TestRunnerButton :suiteId="suite.id" />
        </div>

        <!-- AÇÃO -->
        <div class="mb-4 flex gap-2">
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

        <!-- TABELA -->
        <TestCaseTable :cases="suite.cases" @edit="openEdit" />

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

function openEdit(testCase) {
        router.visit(`/test-suites/${props.suite.id}/cases/${testCase.id}/edit`);
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
