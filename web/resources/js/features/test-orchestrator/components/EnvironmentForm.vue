<template>
  <div class="flex flex-col gap-4">
    <BaseInputText
      v-model="form.name"
      label="Nome do ambiente"
      placeholder="Filial SP"
    />

    <BaseInputText
      v-model="form.base_url"
      label="Base URL"
      placeholder="https://api.filial-sp.com"
    />

    <BaseCheckbox hint="Ambientes inativos não entram na execução da suite.">
      <div class="flex items-center gap-2">
        <Checkbox
          inputId="environment-active"
          v-model="form.is_active"
          binary
        />
        <label for="environment-active" class="text-sm">Ambiente ativo</label>
      </div>
    </BaseCheckbox>

    <BaseCheckbox hint="Quando habilitado, endpoints com autenticação usarão token desse ambiente.">
      <div class="flex items-center gap-2">
        <Checkbox
          inputId="environment-auth"
          v-model="form.requires_auth"
          binary
        />
        <label for="environment-auth" class="text-sm">Usa autenticação Bearer</label>
      </div>
    </BaseCheckbox>

    <div v-if="form.requires_auth" class="border rounded-md p-4 flex flex-col gap-4">
      <BaseInputText
        v-model="form.bearer_token"
        label="Bearer Token inicial (opcional)"
        placeholder="Cole aqui para sobrescrever token"
      />

      <p v-if="environment?.has_bearer_token" class="text-xs text-gray-500">
        Este ambiente já possui token salvo. Se deixar vazio, ele será mantido.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="md:col-span-2">
          <BaseInputText
            v-model="form.auth_login_path"
            label="Path do login"
            placeholder="/auth/login"
          />
        </div>

        <BaseSelect
          v-model="form.auth_login_method"
          label="Método login"
          :options="httpMethods"
          optionLabel="label"
          optionValue="value"
        />
      </div>

      <div>
        <label class="block mb-2 text-sm font-medium">Payload do login (JSON)</label>
        <Textarea
          v-model="form.auth_payload_text"
          rows="6"
          class="w-full font-mono"
          placeholder='{"email":"qa@crm.com","password":"123"}'
        />
      </div>

      <BaseInputText
        v-model="form.auth_token_path"
        label="Caminho do token na resposta"
        placeholder="data.token"
      />

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="md:col-span-2">
          <BaseInputText
            v-model="form.auth_validate_path"
            label="Path validação token (opcional)"
            placeholder="/auth/me"
          />
        </div>

        <BaseSelect
          v-model="form.auth_validate_method"
          label="Método validação"
          :options="httpMethods"
          optionLabel="label"
          optionValue="value"
        />
      </div>

      <BaseInputText
        v-model="form.auth_validate_status"
        label="Status esperado validação"
        placeholder="200"
      />
    </div>

  </div>

  <div class="flex justify-between w-full mt-6">
    <Button label="Cancelar" severity="secondary" @click="emit('cancel')" />
    <Button
      :label="environment ? 'Salvar Ambiente' : 'Criar Ambiente'"
      icon="pi pi-check"
      @click="save"
    />
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref } from 'vue'
import Button from 'primevue/button'
import Checkbox from 'primevue/checkbox'
import Textarea from 'primevue/textarea'

const props = defineProps({
  suiteId: {
    type: [String, Number],
    required: true,
  },
  environment: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['saved', 'cancel'])

const httpMethods = [
  { label: 'GET', value: 'GET' },
  { label: 'POST', value: 'POST' },
  { label: 'PUT', value: 'PUT' },
  { label: 'PATCH', value: 'PATCH' },
  { label: 'DELETE', value: 'DELETE' },
]

const form = ref({
  name: props.environment?.name ?? '',
  base_url: props.environment?.base_url ?? '',
  is_active: props.environment?.is_active ?? true,
  requires_auth: props.environment?.requires_auth ?? false,
  bearer_token: '',
  auth_login_path: props.environment?.auth_login_path ?? '',
  auth_login_method: props.environment?.auth_login_method ?? 'POST',
  auth_payload_text: JSON.stringify(props.environment?.auth_payload ?? {}, null, 2),
  auth_token_path: props.environment?.auth_token_path ?? 'token',
  auth_validate_path: props.environment?.auth_validate_path ?? '',
  auth_validate_method: props.environment?.auth_validate_method ?? 'GET',
  auth_validate_status: String(props.environment?.auth_validate_status ?? 200),
})

async function save() {
  try {
    const payload = {
      name: form.value.name,
      base_url: form.value.base_url,
      is_active: form.value.is_active,
      requires_auth: form.value.requires_auth,
      bearer_token: form.value.bearer_token || null,
      auth_login_path: form.value.auth_login_path || null,
      auth_login_method: form.value.auth_login_method,
      auth_payload: form.value.auth_payload_text
        ? JSON.parse(form.value.auth_payload_text)
        : {},
      auth_token_path: form.value.auth_token_path || 'token',
      auth_validate_path: form.value.auth_validate_path || null,
      auth_validate_method: form.value.auth_validate_method,
      auth_validate_status: Number(form.value.auth_validate_status || 200),
    }

    if (props.environment?.id) {
      await axios.put(`/test-suites/${props.suiteId}/environments/${props.environment.id}`, payload)
      emit('saved', props.environment.id)
      return
    }

    const response = await axios.post(`/test-suites/${props.suiteId}/environments`, payload)
    emit('saved', response.data.environment_id)
  } catch (e) {
    alert('Erro ao salvar ambiente (verifique o JSON do login)')
  }
}
</script>
