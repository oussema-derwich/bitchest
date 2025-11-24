import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json'
  }
})

// Attach token if present
api.interceptors.request.use((config: any) => {
  const token = localStorage.getItem('token')
  if (token && config.headers) {
    config.headers.Authorization = `Bearer ${token}`
  } 
  return config
}, (error) => {
  return Promise.reject(error)
})

// Handle response
api.interceptors.response.use((response) => {
  return response
}, (error) => {
  if (error.response?.status === 401) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    window.location.href = '/login'
  }
  // Temporary: when server returns 5xx, save the full JSON payload for debugging
  try {
    const status = error.response?.status
    if (status && status >= 500) {
      const payload = error.response?.data || { message: error.message }
      try { localStorage.setItem('lastServerError', JSON.stringify(payload)) } catch (e) {}
      // also expose on window for quick access in dev console
      try { (window as any).__lastServerError = payload } catch (e) {}
      // print to console in a readable way (no blocking alert)
      console.error('Server 5xx error payload saved to localStorage:lastServerError', payload)
    }
  } catch (e) {
    // ignore errors while trying to log debug info
  }
  return Promise.reject(error)
})

export default api
