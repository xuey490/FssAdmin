<!-- 登录页面 -->
<template>
  <div class="flex w-full h-screen">
    <LoginLeftView />

    <div class="relative flex-1">
      <AuthTopBar />

      <div class="auth-right-wrap">
        <div class="form">
          <h3 class="title">{{ $t('login.title') }}</h3>
          <p class="sub-title">{{ $t('login.subTitle') }}</p>
          <ElForm
            ref="formRef"
            :model="formData"
            :rules="rules"
            :key="formKey"
            @keyup.enter="handleSubmit"
            style="margin-top: 25px"
          >
            <ElFormItem prop="username">
              <ElInput
                class="custom-height"
                :placeholder="$t('login.placeholder.username')"
                v-model.trim="formData.username"
              />
            </ElFormItem>
            <ElFormItem prop="tenant_id">
              <ElSelect
                class="w-full custom-height"
                :placeholder="tenantList.length > 0 ? '请选择租户' : '请先输入用户名'"
                v-model="formData.tenant_id"
                :loading="loadingTenants"
                :disabled="tenantList.length === 0"
              >
                <ElOption
                  v-for="tenant in tenantList"
                  :key="tenant.id"
                  :label="tenant.name"
                  :value="tenant.id"
                >
                  <span>{{ tenant.name }}</span>
                  <span v-if="tenant.is_default" style="color: var(--el-color-primary); margin-left: 8px;">(默认)</span>
                </ElOption>
              </ElSelect>
            </ElFormItem>
            <ElFormItem prop="password">
              <ElInput
                class="custom-height"
                :placeholder="$t('login.placeholder.password')"
                v-model.trim="formData.password"
                type="password"
                autocomplete="off"
                show-password
              />
            </ElFormItem>
            <ElFormItem prop="code">
              <ElInput
                class="custom-height"
                :placeholder="$t('login.placeholder.code')"
                v-model.trim="formData.code"
                type="text"
                autocomplete="off"
              >
                <template #append>
                  <img
                    :src="captcha"
                    style="height: 36px; cursor: pointer"
                    @click="refreshCaptcha"
                  />
                </template>
              </ElInput>
              <b>测试环境，验证码随便填</b>
            </ElFormItem>

            <div class="flex-cb mt-2 text-sm">
              <ElCheckbox v-model="formData.rememberPassword">{{
                $t('login.rememberPwd')
              }}</ElCheckbox>
              <!-- <RouterLink class="text-theme" :to="{ name: 'ForgetPassword' }">{{
                $t('login.forgetPwd')
              }}</RouterLink> -->
            </div>

            <div style="margin-top: 30px">
              <ElButton
                class="w-full custom-height"
                type="primary"
                @click="handleSubmit"
                :loading="loading"
                v-ripple
              >
                {{ $t('login.btnText') }}
              </ElButton>
            </div>

            <!-- <div class="mt-5 text-sm text-gray-600">
              <span>{{ $t('login.noAccount') }}</span>
              <RouterLink class="text-theme" :to="{ name: 'Register' }">{{
                $t('login.register')
              }}</RouterLink>
            </div> -->
          </ElForm>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import AppConfig from '@/config'
  import { useUserStore } from '@/store/modules/user'
  import { useI18n } from 'vue-i18n'
  import { HttpError } from '@/utils/http/error'
  import { fetchCaptcha, fetchLogin, fetchGetUserInfo, fetchTenantsByUsername } from '@/api/auth'
  import { ElNotification, type FormInstance, type FormRules } from 'element-plus'

  defineOptions({ name: 'Login' })

  const { t, locale } = useI18n()
  const formKey = ref(0)

  // 监听语言切换，重置表单
  watch(locale, () => {
    formKey.value++
  })

  const userStore = useUserStore()
  const router = useRouter()

  const captcha = ref(
    'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
  )

  const systemName = AppConfig.systemInfo.name
  const formRef = ref<FormInstance>()

  const formData = reactive({
    username: 'admin',
    password: '123456',
    code: '',
    uuid: '',
    tenant_id: undefined as number | undefined,
    rememberPassword: true
  })

  const tenantList = ref<Api.Auth.TenantItem[]>([])
  const loadingTenants = ref(false)

  const rules = computed<FormRules>(() => ({
    username: [{ required: true, message: t('login.placeholder.username'), trigger: 'blur' }],
    password: [{ required: true, message: t('login.placeholder.password'), trigger: 'blur' }],
    code: [{ required: true, message: t('login.placeholder.code'), trigger: 'blur' }],
    tenant_id: [{ required: true, message: '请选择租户', trigger: 'change' }]
  }))

  const loading = ref(false)

  onMounted(() => {
    refreshCaptcha()
    // 如果有默认用户名，自动加载租户列表
    if (formData.username) {
      loadTenantList()
    }
  })

  // 监听用户名变化，加载租户列表
  watch(() => formData.username, (newUsername) => {
    if (newUsername && newUsername.trim()) {
      loadTenantList()
    } else {
      tenantList.value = []
      formData.tenant_id = undefined
    }
  })

  // 加载租户列表
  const loadTenantList = async () => {
    if (!formData.username || !formData.username.trim()) {
      return
    }

    try {
      loadingTenants.value = true
      const list = await fetchTenantsByUsername(formData.username.trim())
      tenantList.value = list || []
      
      // 如果只有一个租户，自动选中
      if (tenantList.value.length === 1) {
        formData.tenant_id = tenantList.value[0].id
      } else if (tenantList.value.length > 0) {
        // 如果有默认租户，自动选中
        const defaultTenant = tenantList.value.find(t => t.is_default)
        if (defaultTenant) {
          formData.tenant_id = defaultTenant.id
        }
      }
    } catch (error) {
      console.error('[Login] 加载租户列表失败:', error)
      tenantList.value = []
    } finally {
      loadingTenants.value = false
    }
  }

  // 登录
  const handleSubmit = async () => {
    if (!formRef.value) return

    try {
      // 表单验证
      const valid = await formRef.value.validate()
      if (!valid) return

      loading.value = true

      // 登录请求
      const { access_token, refresh_token } = await fetchLogin({
        username: formData.username,
        password: formData.password,
        code: formData.code,
        uuid: formData.uuid,
        tenant_id: formData.tenant_id
      })

      // 验证token
      if (!access_token) {
        throw new Error('Login failed - no token received')
      }

      // 存储token和用户信息
      userStore.setToken(access_token, refresh_token)
      const userInfo = await fetchGetUserInfo()
      userStore.setUserInfo(userInfo)
      userStore.setLoginStatus(true)

      // 登录成功处理
      showLoginSuccessNotice()
      router.push('/')
    } catch (error) {
      // 处理 HttpError
      if (error instanceof HttpError) {
        // console.log(error.code)
      } else {
        // 处理非 HttpError
        // ElMessage.error('登录失败，请稍后重试')
        console.error('[Login] Unexpected error:', error)
      }
    } finally {
      refreshCaptcha()
      loading.value = false
    }
  }

  // 获取验证码
  const refreshCaptcha = async () => {
    fetchCaptcha().then((res) => {
      formData.uuid = res.uuid
      captcha.value = res.image
    })
  }

  // 登录成功提示
  const showLoginSuccessNotice = () => {
    setTimeout(() => {
      ElNotification({
        title: t('login.success.title'),
        type: 'success',
        duration: 2500,
        zIndex: 10000,
        //message: `${t('login.success.message')}, ${systemName}!`
        message: `${t('login.success.message')}, ${formData.username}!`
      })
    }, 150)
  }
</script>

<style scoped>
  @import './style.css';
</style>

<style lang="scss" scoped>
  :deep(.el-select__wrapper) {
    height: 40px !important;
  }
</style>
