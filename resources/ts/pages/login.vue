<script setup lang="ts">
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { VForm } from 'vuetify/components/VForm'

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const isPasswordVisible = ref(false)
const loading = ref(false)

const route = useRoute()
const router = useRouter()
const ability = useAbility()

const errors = ref<Record<string, string | undefined>>({
  email: undefined,
  password: undefined,
})

const refVForm = ref<VForm>()

const credentials = ref({
  email: 'support@lab.local',
  password: 'changeme123',
})

const rememberMe = ref(false)

const login = async () => {
  loading.value = true
  errors.value = { email: undefined, password: undefined }
  try {
    const res = await $api('/auth/login', {
      method: 'POST',
      body: {
        email: credentials.value.email,
        password: credentials.value.password,
      },
      onResponseError({ response }) {
        errors.value = response._data?.errors || { email: response._data?.message || 'خطأ في بيانات الدخول' }
      },
    })

    const { accessToken, userData, userAbilityRules } = res

    useCookie('userAbilityRules').value = userAbilityRules
    ability.update(userAbilityRules)

    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken

    await nextTick(() => {
      router.replace(route.query.to ? String(route.query.to) : '/')
    })
  }
  catch (err) {
    console.error(err)
  }
  finally {
    loading.value = false
  }
}

const onSubmit = () => {
  refVForm.value?.validate()
    .then(({ valid: isValid }) => {
      if (isValid)
        login()
    })
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4 bg-background">
    <VCard
      class="auth-card pa-6"
      max-width="440"
      width="100%"
      elevation="8"
      rounded="lg"
    >
      <VCardItem class="justify-center pb-4">
        <VCardTitle>
          <div class="app-logo d-flex align-center gap-x-3">
            <VNodeRenderer :nodes="themeConfig.app.logo" />
            <h1 class="app-logo-title font-weight-bold text-h4">
              {{ themeConfig.app.title }}
            </h1>
          </div>
        </VCardTitle>
      </VCardItem>

      <VCardText class="text-center pb-6">
        <h4 class="text-h5 font-weight-bold mb-1">
          تسجيل الدخول للنظام 👋🏻
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          يرجى إدخال البريد الإلكتروني وكلمة المرور للمتابعة
        </p>
      </VCardText>

      <VCardText>
        <VForm
          ref="refVForm"
          @submit.prevent="onSubmit"
        >
          <VRow>
            <!-- email -->
            <VCol cols="12">
              <AppTextField
                v-model="credentials.email"
                label="البريد الإلكتروني"
                placeholder="name@example.com"
                type="email"
                autofocus
                dir="ltr"
                :rules="[requiredValidator, emailValidator]"
                :error-messages="errors.email"
              />
            </VCol>

            <!-- password -->
            <VCol cols="12">
              <AppTextField
                v-model="credentials.password"
                label="كلمة المرور"
                placeholder="············"
                :rules="[requiredValidator]"
                :type="isPasswordVisible ? 'text' : 'password'"
                autocomplete="password"
                dir="ltr"
                :error-messages="errors.password"
                :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
              />
            </VCol>

            <!-- remember me -->
            <VCol cols="12" class="pt-2 pb-4">
              <VCheckbox
                v-model="rememberMe"
                label="تذكرني على هذا الجهاز"
              />
            </VCol>

            <!-- login button -->
            <VCol cols="12">
              <VBtn
                block
                type="submit"
                color="primary"
                size="large"
                :loading="loading"
              >
                تسجيل الدخول
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";

.auth-wrapper {
  min-height: 100vh;
}
</style>
