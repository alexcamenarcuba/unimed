<template>
  <div class="flex flex-col gap-4">
    <BaseInputText
      v-model="form.name"
      label="Nome do endpoint"
      placeholder="Ex: Login"
    />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <BaseSelect
        v-model="form.method"
        label="Método"
        :options="methods"
        optionLabel="label"
        optionValue="value"
      />

      <div class="md:col-span-2">
        <BaseInputText
          v-model="form.path"
          label="Path"
          placeholder="/contratante/login"
        />
      </div>
    </div>

    <BaseCheckbox hint="Quando habilitado, este endpoint usa autenticação do ambiente durante a execução.">
      <div class="flex items-center gap-2">
        <Checkbox
          inputId="requires-auth"
          v-model="form.requires_auth"
          binary
        />
        <label for="requires-auth" class="text-sm">Requer autenticação Bearer</label>
      </div>
    </BaseCheckbox>

    <div class="border rounded-md p-4 flex flex-col gap-3">
      <div class="flex items-center justify-between">
        <p class="text-sm font-medium">Variáveis do endpoint por tenant</p>
        <Button
          label="Adicionar variável"
          icon="pi pi-plus"
          size="small"
          severity="secondary"
          @click="addVariable"
        />
      </div>

      <div
        v-for="(variable, index) in form.variables"
        :key="index"
        class="border rounded-md p-3 flex flex-col gap-3"
      >
        <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-center">
          <div class="md:col-span-5">
            <BaseInputText
              v-model="variable.key"
              label="Chave"
              placeholder="complemento"
            />
          </div>
          <div class="md:col-span-4">
            <BaseSelect
              v-model="variable.type"
              label="Tipo"
              :options="variableTypes"
              optionLabel="label"
              optionValue="value"
              @update:modelValue="onVariableTypeChange(variable)"
            />
          </div>
          <div class="md:col-span-3 pt-5 flex justify-end">
            <Button
              icon="pi pi-trash"
              text
              severity="danger"
              @click="removeVariable(index)"
            />
          </div>
        </div>

        <div
          v-for="env in targetEnvironments"
          :key="`${index}-${env.id}`"
          class="border border-gray-200 rounded p-3"
        >
          <p class="text-xs text-gray-500 mb-2">{{ env.name }}</p>

          <BaseInputText
            v-if="variable.type === 'simple'"
            v-model="variable.values[env.id]"
            label="Valor"
            placeholder="aqui vamos inserir o complemento"
          />

          <div v-else-if="variable.type === 'array'">
            <label class="block mb-2 text-sm font-medium">Valor JSON</label>
            <Textarea
              v-model="variable.valuesText[env.id]"
              rows="4"
              class="w-full font-mono"
              placeholder='{"campo": "valor"}'
            />
          </div>

          <div v-else>
            <label class="block mb-2 text-sm font-medium">Arquivo</label>
            <input
              type="file"
              class="w-full text-sm"
              @change="onFileSelected($event, variable, env.id)"
            >
            <p v-if="variable.fileNames[env.id]" class="text-xs text-gray-500 mt-2">
              Arquivo atual: {{ variable.fileNames[env.id] }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="flex justify-between w-full mt-6">
    <Button label="Cancelar" severity="secondary" @click="emit('cancel')" />
    <Button
      :label="endpoint ? 'Salvar Endpoint' : 'Criar Endpoint'"
      icon="pi pi-check"
      @click="save"
    />
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, ref } from 'vue'
import Button from 'primevue/button'
import Checkbox from 'primevue/checkbox'
import Textarea from 'primevue/textarea'

const props = defineProps({
  suiteId: {
    type: [String, Number],
    required: true,
  },
  endpoint: {
    type: Object,
    default: null,
  },
  environments: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['saved', 'cancel'])

const methods = [
  { label: 'GET', value: 'GET' },
  { label: 'POST', value: 'POST' },
  { label: 'PUT', value: 'PUT' },
  { label: 'PATCH', value: 'PATCH' },
  { label: 'DELETE', value: 'DELETE' },
]

const variableTypes = [
  { label: 'Simples', value: 'simple' },
  { label: 'Array/Objeto', value: 'array' },
  { label: 'Arquivo', value: 'file' },
]

const targetEnvironments = computed(() => {
  if (props.environments.length > 0) {
    return props.environments
  }

  return [{ id: '__default', name: 'Padrao' }]
})

function normalizeVariables(variables) {
  return (variables || []).map((item) => {
    const values = item.values || {}
    const valuesText = {}
    const fileNames = {}

    for (const env of targetEnvironments.value) {
      const envValue = values[env.id] ?? null

      if (item.type === 'array') {
        valuesText[env.id] = JSON.stringify(envValue ?? [], null, 2)
      }

      if (item.type === 'file' && envValue?.name) {
        fileNames[env.id] = envValue.name
      }
    }

    return {
      key: item.key || '',
      type: item.type || 'simple',
      values,
      valuesText,
      fileNames,
    }
  })
}

const form = ref({
  name: props.endpoint?.name ?? '',
  method: props.endpoint?.method ?? 'GET',
  path: props.endpoint?.path ?? '',
  requires_auth: props.endpoint?.requires_auth ?? false,
  variables: normalizeVariables(props.endpoint?.variables),
})

function addVariable() {
  const values = {}
  const valuesText = {}
  const fileNames = {}

  for (const env of targetEnvironments.value) {
    values[env.id] = ''
    valuesText[env.id] = '[]'
    fileNames[env.id] = ''
  }

  form.value.variables.push({
    key: '',
    type: 'simple',
    values,
    valuesText,
    fileNames,
  })
}

function removeVariable(index) {
  form.value.variables.splice(index, 1)
}

function onVariableTypeChange(variable) {
  for (const env of targetEnvironments.value) {
    if (variable.type === 'simple') {
      variable.values[env.id] = ''
      variable.valuesText[env.id] = '[]'
      variable.fileNames[env.id] = ''
      continue
    }

    if (variable.type === 'array') {
      variable.values[env.id] = []
      variable.valuesText[env.id] = '[]'
      variable.fileNames[env.id] = ''
      continue
    }

    variable.values[env.id] = null
    variable.valuesText[env.id] = '[]'
    variable.fileNames[env.id] = ''
  }
}

async function onFileSelected(event, variable, environmentId) {
  const file = event.target.files?.[0]

  if (!file) {
    return
  }

  const contentBase64 = await readFileAsBase64(file)

  variable.values[environmentId] = {
    name: file.name,
    mime_type: file.type || 'application/octet-stream',
    content_base64: contentBase64,
  }
  variable.fileNames[environmentId] = file.name
}

function readFileAsBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.onload = () => {
      const result = String(reader.result || '')
      const parts = result.split(',')
      resolve(parts.length > 1 ? parts[1] : result)
    }

    reader.onerror = () => {
      reject(new Error('Falha ao ler arquivo'))
    }

    reader.readAsDataURL(file)
  })
}

async function save() {
  try {
    const variables = form.value.variables
      .filter((item) => item.key)
      .map((item) => {
        const values = {}

        for (const env of targetEnvironments.value) {
          const envId = env.id

          if (item.type === 'array') {
            values[envId] = item.valuesText[envId]
              ? JSON.parse(item.valuesText[envId])
              : []
            continue
          }

          if (item.type === 'file') {
            values[envId] = item.values[envId] || null
            continue
          }

          values[envId] = item.values[envId] ?? ''
        }

        return {
          key: item.key,
          type: item.type,
          values,
        }
      })

    const payload = {
      name: form.value.name,
      method: form.value.method,
      path: form.value.path,
      requires_auth: form.value.requires_auth,
      variables,
    }

    if (props.endpoint?.id) {
      await axios.put(`/test-suites/${props.suiteId}/endpoints/${props.endpoint.id}`, payload)
      emit('saved', props.endpoint.id)
      return
    }

    const response = await axios.post(`/test-suites/${props.suiteId}/endpoints`, payload)
    emit('saved', response.data.endpoint_id)
  } catch (e) {
    alert('Erro ao salvar endpoint')
  }
}
</script>
