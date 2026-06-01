import { ref, computed, readonly } from 'vue'

const TOKEN_KEY      = 'rd_auth_token'
const USER_KEY       = 'rd_auth_user'
const ACTIVE_ROLE_KEY = 'rd_active_role'

// Module-level shared state — one instance across all components
const user       = ref(null)
const activeRole = ref(null)

function initFromStorage() {
  try {
    const stored = localStorage.getItem(USER_KEY)
    if (stored) {
      user.value = JSON.parse(stored)
      activeRole.value = user.value.role === 'admin'
        ? (localStorage.getItem(ACTIVE_ROLE_KEY) || 'sender')
        : user.value.role
    }
  } catch {
    user.value = null
    activeRole.value = null
  }
}

initFromStorage()

// "role" is the view-role: for admin it's the selected acting role; for sender/tester it's fixed
const role = computed(() => {
  if (!user.value) return null
  if (user.value.role === 'admin') return activeRole.value || 'sender'
  return user.value.role
})

export function useAuth() {
  const isLoggedIn = computed(() => !!user.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')

  function getToken() {
    return localStorage.getItem(TOKEN_KEY)
  }

  function setActiveRole(newRole) {
    if (user.value?.role !== 'admin') return
    activeRole.value = newRole
    localStorage.setItem(ACTIVE_ROLE_KEY, newRole)
  }

  async function login(username, password) {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Login failed')

    localStorage.setItem(TOKEN_KEY, data.token)
    localStorage.setItem(USER_KEY, JSON.stringify(data.user))
    user.value = data.user

    if (data.user.role === 'admin') {
      activeRole.value = localStorage.getItem(ACTIVE_ROLE_KEY) || 'sender'
    } else {
      activeRole.value = data.user.role
      localStorage.setItem(ACTIVE_ROLE_KEY, data.user.role)
    }
    return data.user
  }

  async function logout() {
    const token = getToken()
    if (token) {
      try {
        await fetch('/api/auth/logout', {
          method: 'POST',
          headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
        })
      } catch {}
    }
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
    user.value      = null
    activeRole.value = null
  }

  async function verifyToken() {
    const token = getToken()
    if (!token) {
      user.value = null
      activeRole.value = null
      return false
    }
    try {
      const res = await fetch('/api/auth/me', {
        headers: { Authorization: `Bearer ${token}` },
      })
      if (!res.ok) {
        localStorage.removeItem(TOKEN_KEY)
        localStorage.removeItem(USER_KEY)
        user.value = null
        activeRole.value = null
        return false
      }
      const userData = await res.json()
      user.value = userData
      localStorage.setItem(USER_KEY, JSON.stringify(userData))
      activeRole.value = userData.role === 'admin'
        ? (localStorage.getItem(ACTIVE_ROLE_KEY) || 'sender')
        : userData.role
      return true
    } catch {
      user.value = null
      activeRole.value = null
      return false
    }
  }

  return {
    user:        readonly(user),
    role:        readonly(role),
    activeRole:  readonly(activeRole),
    isLoggedIn,
    isAdmin,
    login,
    logout,
    verifyToken,
    setActiveRole,
    getToken,
  }
}
