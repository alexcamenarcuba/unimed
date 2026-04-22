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
    </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from "../../../layouts/AdminLayout.vue";
import { router } from "@inertiajs/vue3";
import Button from "primevue/button";
import TestCaseTable from "../components/TestCaseTable.vue";
import TestRunnerButton from "../components/TestRunnerButton.vue";

const props = defineProps({
  suite: Object
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
</script>
