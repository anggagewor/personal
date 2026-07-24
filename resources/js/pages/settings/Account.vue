<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { get, put, upload, del } from '@purdia/http'
import { useAuthStore } from '@purdia/auth'
import { useProfile } from '@/composables/useProfile'
import { Camera, Trash2, Check } from '@lucide/vue'

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------

const auth = useAuthStore()
const { avatarUrl, setAvatar } = useProfile()

const name = ref('')
const email = ref('')

const currentPassword = ref('')
const newPassword = ref('')
const newPasswordConfirmation = ref('')

const profileLoading = ref(false)
const passwordLoading = ref(false)
const avatarLoading = ref(false)

const profileSuccess = ref('')
const passwordSuccess = ref('')

const profileErrors = ref<Record<string, string[]>>({})
const passwordErrors = ref<Record<string, string[]>>({})
const avatarError = ref('')

// ---------------------------------------------------------------------------
// Load profile
// ---------------------------------------------------------------------------

onMounted(async () => {
  try {
    const response = await get<{
      id: number
      name: string
      email: string
      avatar: string | null
    }>('/profile')
    name.value = response.data.name
    email.value = response.data.email
    setAvatar(response.data.avatar)
  } catch {
    // Fallback to auth store data
    name.value = auth.user?.name ?? ''
    email.value = auth.user?.email ?? ''
  }
})

// ---------------------------------------------------------------------------
// Update profile
// ---------------------------------------------------------------------------

async function handleUpdateProfile() {
  profileLoading.value = true
  profileSuccess.value = ''
  profileErrors.value = {}

  try {
    const response = await put<{
      id: number
      name: string
      email: string
      avatar: string | null
    }>('/profile', { name: name.value, email: email.value })

    profileSuccess.value = 'Profil berhasil diperbarui.'
    name.value = response.data.name
    email.value = response.data.email

    setTimeout(() => { profileSuccess.value = '' }, 3000)
  } catch (err: unknown) {
    const apiErr = err as { errors?: Record<string, string[]> }
    profileErrors.value = apiErr?.errors ?? {}
  } finally {
    profileLoading.value = false
  }
}

// ---------------------------------------------------------------------------
// Update password
// ---------------------------------------------------------------------------

async function handleUpdatePassword() {
  passwordLoading.value = true
  passwordSuccess.value = ''
  passwordErrors.value = {}

  try {
    await put('/profile/password', {
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: newPasswordConfirmation.value,
    })

    passwordSuccess.value = 'Password berhasil diubah.'
    currentPassword.value = ''
    newPassword.value = ''
    newPasswordConfirmation.value = ''

    setTimeout(() => { passwordSuccess.value = '' }, 3000)
  } catch (err: unknown) {
    const apiErr = err as { errors?: Record<string, string[]> }
    passwordErrors.value = apiErr?.errors ?? {}
  } finally {
    passwordLoading.value = false
  }
}

// ---------------------------------------------------------------------------
// Upload avatar
// ---------------------------------------------------------------------------

const fileInput = ref<HTMLInputElement | null>(null)

function triggerFileInput() {
  fileInput.value?.click()
}

async function handleAvatarUpload(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  avatarLoading.value = true
  avatarError.value = ''

  const formData = new FormData()
  formData.append('avatar', file)

  try {
    const response = await upload<{ avatar: string }>('/profile/avatar', formData)
    setAvatar(response.data.avatar)
  } catch (err: unknown) {
    const apiErr = err as { message?: string; errors?: Record<string, string[]> }
    avatarError.value = apiErr?.errors?.avatar?.[0] ?? apiErr?.message ?? 'Gagal upload avatar.'
  } finally {
    avatarLoading.value = false
    // Reset input so same file can be re-selected
    if (fileInput.value) fileInput.value.value = ''
  }
}

async function handleRemoveAvatar() {
  avatarLoading.value = true
  avatarError.value = ''

  try {
    await del('/profile/avatar')
    setAvatar(null)
  } catch {
    avatarError.value = 'Gagal menghapus avatar.'
  } finally {
    avatarLoading.value = false
  }
}

const initials = computed(() => {
  const n = name.value || auth.user?.name || ''
  return n.split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase()
})
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Akun</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola profil, foto, dan password kamu.</p>

    <div class="mt-8 max-w-2xl space-y-8">
      <!-- Avatar section -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Foto Profil</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">JPG, PNG, atau WebP. Maksimal 2MB.</p>

        <div class="mt-5 flex items-center gap-5">
          <!-- Avatar preview -->
          <div class="relative">
            <div
              class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300"
            >
              <img
                v-if="avatarUrl"
                :src="avatarUrl"
                alt="Avatar"
                class="h-full w-full object-cover"
              />
              <span v-else class="text-xl font-semibold">{{ initials }}</span>
            </div>

            <!-- Loading overlay -->
            <div
              v-if="avatarLoading"
              class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40"
            >
              <div class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent" />
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col gap-2">
            <div class="flex gap-2">
              <button
                class="flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50"
                :disabled="avatarLoading"
                @click="triggerFileInput"
              >
                <Camera :size="16" />
                Ganti Foto
              </button>
              <button
                v-if="avatarUrl"
                class="flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                :disabled="avatarLoading"
                @click="handleRemoveAvatar"
              >
                <Trash2 :size="16" />
                Hapus
              </button>
            </div>
            <p v-if="avatarError" class="text-xs text-red-600 dark:text-red-400">{{ avatarError }}</p>
          </div>

          <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="hidden"
            @change="handleAvatarUpload"
          />
        </div>
      </section>

      <!-- Profile section -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Profil</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui nama dan email kamu.</p>

        <form class="mt-5 space-y-4" @submit.prevent="handleUpdateProfile">
          <!-- Name -->
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
            <input
              v-model="name"
              type="text"
              required
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :class="profileErrors.name ? 'border-red-500 dark:border-red-500' : ''"
            />
            <p v-if="profileErrors.name" class="mt-1 text-xs text-red-600 dark:text-red-400">
              {{ profileErrors.name[0] }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input
              v-model="email"
              type="email"
              required
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :class="profileErrors.email ? 'border-red-500 dark:border-red-500' : ''"
            />
            <p v-if="profileErrors.email" class="mt-1 text-xs text-red-600 dark:text-red-400">
              {{ profileErrors.email[0] }}
            </p>
          </div>

          <!-- Submit -->
          <div class="flex items-center gap-3">
            <button
              type="submit"
              :disabled="profileLoading"
              class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50"
            >
              {{ profileLoading ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <span v-if="profileSuccess" class="flex items-center gap-1 text-sm text-emerald-600 dark:text-emerald-400">
              <Check :size="16" />
              {{ profileSuccess }}
            </span>
          </div>
        </form>
      </section>

      <!-- Password section -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Ubah Password</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gunakan password yang kuat dan unik.</p>

        <form class="mt-5 space-y-4" @submit.prevent="handleUpdatePassword">
          <!-- Current password -->
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Password Saat Ini</label>
            <input
              v-model="currentPassword"
              type="password"
              required
              autocomplete="current-password"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :class="passwordErrors.current_password ? 'border-red-500 dark:border-red-500' : ''"
            />
            <p v-if="passwordErrors.current_password" class="mt-1 text-xs text-red-600 dark:text-red-400">
              {{ passwordErrors.current_password[0] }}
            </p>
          </div>

          <!-- New password -->
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
            <input
              v-model="newPassword"
              type="password"
              required
              autocomplete="new-password"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :class="passwordErrors.password ? 'border-red-500 dark:border-red-500' : ''"
              placeholder="Minimal 8 karakter"
            />
            <p v-if="passwordErrors.password" class="mt-1 text-xs text-red-600 dark:text-red-400">
              {{ passwordErrors.password[0] }}
            </p>
          </div>

          <!-- Confirm new password -->
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password Baru</label>
            <input
              v-model="newPasswordConfirmation"
              type="password"
              required
              autocomplete="new-password"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Ulangi password baru"
            />
          </div>

          <!-- Submit -->
          <div class="flex items-center gap-3">
            <button
              type="submit"
              :disabled="passwordLoading"
              class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50"
            >
              {{ passwordLoading ? 'Menyimpan...' : 'Ubah Password' }}
            </button>
            <span v-if="passwordSuccess" class="flex items-center gap-1 text-sm text-emerald-600 dark:text-emerald-400">
              <Check :size="16" />
              {{ passwordSuccess }}
            </span>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>
