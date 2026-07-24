import { ref } from 'vue'

/**
 * Shared reactive profile state (avatar URL).
 * Updated by Account page after upload, read by Topbar.
 */
const avatarUrl = ref<string | null>(null)

export function useProfile() {
  function setAvatar(url: string | null) {
    avatarUrl.value = url
  }

  return {
    avatarUrl,
    setAvatar,
  }
}
