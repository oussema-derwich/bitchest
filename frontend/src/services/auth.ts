import { ref } from 'vue'
import api from './api'

export interface User {
  id: number
  name: string
  email: string
  role: string
  balance_eur: number
  is_active: boolean
}

export const currentUser = ref<User | null>(null)
export const isAuthenticated = ref(false)
export const token = ref<string | null>(null)

export function loadUserFromStorage() {
  const storedToken = localStorage.getItem('token')
  const storedUser = localStorage.getItem('user')
  
  if (storedToken && storedUser) {
    try {
      token.value = storedToken
      currentUser.value = JSON.parse(storedUser)
      isAuthenticated.value = true
      return true
    } catch (e) {
      console.error('Failed to load user from storage:', e)
      clearAuth()
      return false
    }
  }
  return false
}

export function setUser(user: User, authToken: string) {
  currentUser.value = user
  token.value = authToken
  isAuthenticated.value = true
  localStorage.setItem('token', authToken)
  localStorage.setItem('user', JSON.stringify(user))
}

export function clearAuth() {
  currentUser.value = null
  token.value = null
  isAuthenticated.value = false
  localStorage.removeItem('token')
  localStorage.removeItem('user')
}

export async function fetchUserProfile() {
  try {
    const res = await api.get('/auth/profile')
    if (res.data.user) {
      const user = res.data.user
      localStorage.setItem('user', JSON.stringify(user))
      currentUser.value = user
      return user
    }
  } catch (e) {
    console.error('Failed to fetch user profile:', e)
    clearAuth()
  }
}
