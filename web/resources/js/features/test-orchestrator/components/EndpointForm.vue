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

    <BaseCheckbox hint="Quando habilitado, este endpoint será executado com Authorization: Bearer {token do próprio endpoint}.">
      <div class="flex items-center gap-2">
        <Checkbox
          inputId="requires-auth"
          v-model="form.requires_auth"
          binary
        />
        <label for="requires-auth" class="text-sm">Requer autenticação Bearer</label>
      </div>
    </BaseCheckbox>

    <div v-if="form.requires_auth" class="border rounded-md p-4 flex flex-col gap-4">
      <p class="text-sm text-gray-600">
        Configure token inicial e/ou login para renovação automática.
      </p>

      <BaseInputText
        v-model="form.bearer_token"
        label="Bearer Token inicial (opcional)"
        placeholder="Cole aqui apenas se quiser sobrescrever"
      />

      <p v-if="endpoint?.has_bearer_token" class="text-xs text-gray-500">
        Este endpoint já possui token salvo. Se deixar o campo acima vazio, ele será mantido.
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
          :options="authMethods"
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
        placeholder="token"
      />

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="md:col-span-2">
          <BaseInputText
            v-model="form.auth_validate_path"
            label="Path de validação do token (opcional)"
            placeholder="/auth/me"
          />
        </div>

        <BaseSelect
          v-model="form.auth_validate_method"
          label="Método validação"
          :options="authMethods"
          optionLabel="label"
          optionValue="value"
        />
      </div>

      <BaseInputText
        v-model="form.auth_validate_status"
        label="Status esperado na validação"
        placeholder="200"
      />
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
import { ref } from 'vue'
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
})

const emit = defineEmits(['saved', 'cancel'])

const methods = [
  { label: 'GET', value: 'GET' },
  { label: 'POST', value: 'POST' },
  { label: 'PUT', value: 'PUT' },
  { label: 'PATCH', value: 'PATCH' },
  { label: 'DELETE', value: 'DELETE' },
]

const authMethods = [
  { label: 'GET', value: 'GET' },
  { label: 'POST', value: 'POST' },
  { label: 'PUT', value: 'PUT' },
  { label: 'PATCH', value: 'PATCH' },
  { label: 'DELETE', value: 'DELETE' },
]

const form = ref({
  name: props.endpoint?.name ?? '',
  method: props.endpoint?.method ?? 'GET',
  path: props.endpoint?.path ?? '',
  requires_auth: props.endpoint?.requires_auth ?? false,
  bearer_token: '',
  auth_login_path: props.endpoint?.auth_login_path ?? '',
  auth_login_method: props.endpoint?.auth_login_method ?? 'POST',
  auth_payload_text: JSON.stringify(props.endpoint?.auth_payload ?? {}, null, 2),
  auth_token_path: props.endpoint?.auth_token_path ?? 'token',
  auth_validate_path: props.endpoint?.auth_validate_path ?? '',
  auth_validate_method: props.endpoint?.auth_validate_method ?? 'GET',
  auth_validate_status: String(props.endpoint?.auth_validate_status ?? 200),
})

async function save() {
  try {
    const authPayload = form.value.auth_payload_text
      ? JSON.parse(form.value.auth_payload_text)
      : {}

    const payload = {
      name: form.value.name,
      method: form.value.method,
      path: form.value.path,
      requires_auth: form.value.requires_auth,
      bearer_token: form.value.bearer_token || null,
      auth_login_path: form.value.auth_login_path || null,
      auth_login_method: form.value.auth_login_method,
      auth_payload: authPayload,
      auth_token_path: form.value.auth_token_path || 'token',
      auth_validate_path: form.value.auth_validate_path || null,
      auth_validate_method: form.value.auth_validate_method,
      auth_validate_status: Number(form.value.auth_validate_status || 200),
    }

    if (props.endpoint?.id) {
      await axios.put(`/test-suites/${props.suiteId}/endpoints/${props.endpoint.id}`, payload)
      emit('saved', props.endpoint.id)
      return
    }

    const response = await axios.post(`/test-suites/${props.suiteId}/endpoints`, payload)
    emit('saved', response.data.endpoint_id)
  } catch (e) {
    alert('Erro ao salvar endpoint (verifique JSON de autenticação)')
  }
}
</script>
