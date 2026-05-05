<template>
    <AdminLayout contentFullWidth>
        <!-- BG SECTION -->
        <div class="bg-gradient-to-b from-emerald-50/40 to-transparent pt-6 pb-8 px-3 sm:px-4 md:px-6">
            <div class="w-full">
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
                            label="Limpar seleção"
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
                    {{ selectedCaseIds.length }} de {{ suite.cases?.length ?? 0 }} cenários selecionados para execução.
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

    <div class="px-3 pb-4 sm:px-4 md:px-6">
        <!-- TABELA -->
        <div class="border rounded-lg">
            <button
                type="button"
                class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition"
                @click="showCases = !showCases"
            >
                <div class="text-left">
                    <h2 class="text-base font-semibold">Cenários de Teste</h2>
                    <p class="text-xs text-gray-400">{{ suite.cases?.length ?? 0 }} cenário(s) cadastrado(s).</p>
                </div>
                <i :class="showCases ? 'pi pi-chevron-up' : 'pi pi-chevron-down'" class="text-gray-400 text-sm" />
            </button>

            <div v-if="showCases" class="border-t overflow-x-auto">
                <TestCaseTable
                    :cases="suite.cases"
                    :environments="environments"
                    v-model:selectedCaseIds="selectedCaseIds"
                    @edit="openEdit"
                    @duplicate="duplicateCase"
                    @edit-endpoint="openEditEndpoint"
                />
            </div>
        </div>

        <div class="mt-6 border rounded-lg">
            <button
                type="button"
                class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition"
                @click="showGroups = !showGroups"
            >
                <div class="text-left">
                    <h2 class="text-base font-semibold">Grupos de Cenário</h2>
                    <p class="text-xs text-gray-400">Gerencie grupos reutilizáveis para organizar os cenários.</p>
                </div>
                <i :class="showGroups ? 'pi pi-chevron-up' : 'pi pi-chevron-down'" class="text-gray-400 text-sm" />
            </button>
            <div v-if="showGroups" class="p-4 border-t flex flex-col gap-4">

            <div class="flex flex-col md:flex-row gap-3 md:items-end">
                <div class="flex-1">
                    <BaseInputText
                        v-model="newGroupName"
                        label="Nome do grupo"
                    />
                </div>

                <Button
                    label="Cadastrar Grupo"
                    icon="pi pi-plus"
                    :loading="savingGroup"
                    @click="createGroup"
                />
            </div>

                <DataTable :value="groups" stripedRows size="small">
                <Column field="name" header="Grupo" />
                <Column header="Ações" style="width: 90px">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="deleteGroup(slotProps.data)"
                        />
                    </template>
                </Column>

                <template #empty>
                    <p class="text-center text-gray-400 py-4 text-sm">Nenhum grupo cadastrado.</p>
                </template>
                </DataTable>
            </div>
        </div>

        <div class="mt-6 border rounded-lg">
            <button
                type="button"
                class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition"
                @click="showEnvironments = !showEnvironments"
            >
                <div class="text-left">
                    <h2 class="text-base font-semibold">Ambientes</h2>
                    <p class="text-xs text-gray-400">{{ environments.length }} ambiente(s) configurado(s) para esta suite.</p>
                </div>
                <i :class="showEnvironments ? 'pi pi-chevron-up' : 'pi pi-chevron-down'" class="text-gray-400 text-sm" />
            </button>
            <div v-if="showEnvironments" class="border-t">
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
                        <div class="flex items-center gap-1">
                            <Button
                                :label="slotProps.data.is_active ? 'Desativar' : 'Ativar'"
                                :icon="slotProps.data.is_active ? 'pi pi-ban' : 'pi pi-check'"
                                :severity="slotProps.data.is_active ? 'danger' : 'success'"
                                text
                                @click="toggleEnvironmentStatus(slotProps.data)"
                            />
                            <Button
                                icon="pi pi-pencil"
                                text
                                @click="goToEditEnvironment(slotProps.data.id)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
            </div>
        </div>
    </div>
    </AdminLayout>
</template>

<script setup>
import axios from "axios";
import { computed, ref, watch } from "vue";
import AdminLayout from "../../../layouts/AdminLayout.vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import TestCaseTable from "../components/TestCaseTable.vue";
import TestRunnerButton from "../components/TestRunnerButton.vue";

const props = defineProps({
    suite: Object,
    groups: {
        type: Array,
        default: () => []
    },
    environments: {
        type: Array,
        default: () => []
    }
})

const selectedCaseIds = ref([])
const allCaseIds = computed(() => (props.suite?.cases ?? []).map((testCase) => testCase.id))
const newGroupName = ref('')
const savingGroup = ref(false)
const showCases = ref(true)
const showGroups = ref(false)
const showEnvironments = ref(false)

const toast = useToast()
const confirm = useConfirm()

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

function duplicateCase(testCase) {
    router.visit(`/test-suites/${props.suite.id}/cases/create?clone_from=${testCase.id}`);
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

function askConfirmation(message, header = 'Confirmacao') {
    return new Promise((resolve) => {
        confirm.require({
            message,
            header,
            icon: 'pi pi-exclamation-triangle',
            rejectProps: {
                label: 'Cancelar',
                severity: 'secondary',
                text: true,
            },
            acceptProps: {
                label: 'Confirmar',
            },
            accept: () => resolve(true),
            reject: () => resolve(false),
        })
    })
}

async function toggleEnvironmentStatus(environment) {
    const shouldActivate = !environment.is_active
    const actionLabel = shouldActivate ? 'ativar' : 'desativar'

    const confirmed = await askConfirmation(
        `Deseja ${actionLabel} o ambiente "${environment.name}"?`,
        'Confirmar alteracao'
    )

    if (!confirmed) {
        return
    }

    try {
        await axios.patch(`/test-suites/${props.suite.id}/environments/${environment.id}/status`, {
            is_active: shouldActivate,
        })

        toast.add({
            severity: 'success',
            summary: 'Ambiente atualizado',
            detail: `Ambiente ${environment.name} ${shouldActivate ? 'ativado' : 'desativado'} com sucesso.`,
            life: 3000,
        })

        router.reload({
            only: ['environments'],
            preserveScroll: true,
        })
    } catch (error) {
        const message = error?.response?.data?.errors?.is_active?.[0]
            ?? `Nao foi possivel ${actionLabel} o ambiente.`

        toast.add({
            severity: 'error',
            summary: 'Falha ao atualizar ambiente',
            detail: message,
            life: 4500,
        })
    }
}

function goToEditSuite() {
    router.visit(`/test-suites/${props.suite.id}/edit`);
}

async function createGroup() {
    if (!newGroupName.value.trim()) {
        toast.add({
            severity: 'warn',
            summary: 'Nome obrigatorio',
            detail: 'Informe um nome para o grupo.',
            life: 3000,
        })
        return
    }

    savingGroup.value = true

    try {
        await axios.post(`/test-suites/${props.suite.id}/case-groups`, {
            name: newGroupName.value,
        })

        newGroupName.value = ''

        toast.add({
            severity: 'success',
            summary: 'Grupo cadastrado',
            detail: 'Grupo criado com sucesso.',
            life: 3000,
        })

        router.reload({
            only: ['groups'],
            preserveScroll: true,
        })
    } catch (error) {
        const message = error?.response?.data?.errors?.name?.[0] ?? 'Nao foi possivel cadastrar o grupo.'

        toast.add({
            severity: 'error',
            summary: 'Falha ao cadastrar grupo',
            detail: message,
            life: 4500,
        })
    } finally {
        savingGroup.value = false
    }
}

async function deleteGroup(group) {
    const confirmed = await askConfirmation(
        `Deseja remover o grupo "${group.name}"?`,
        'Remover grupo'
    )

    if (!confirmed) {
        return
    }

    try {
        await axios.delete(`/test-suites/${props.suite.id}/case-groups/${group.id}`)

        toast.add({
            severity: 'success',
            summary: 'Grupo removido',
            detail: `Grupo ${group.name} removido com sucesso.`,
            life: 3000,
        })

        router.reload({
            only: ['groups'],
            preserveScroll: true,
        })
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Falha ao remover grupo',
            detail: 'Nao foi possivel remover o grupo.',
            life: 4500,
        })
    }
}
</script>
