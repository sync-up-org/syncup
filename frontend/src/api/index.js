const BASE = '/api'

function token() {
  return localStorage.getItem('auth_token')
}

async function request(path, options = {}) {
  const headers = { 'Accept': 'application/json', ...options.headers }
  const t = token()
  if (t) headers['Authorization'] = `Bearer ${t}`

  if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
    options.body = JSON.stringify(options.body)
  }

  const res = await fetch(`${BASE}${path}`, { ...options, headers })

  let data
  try {
    data = await res.json()
  } catch {
    try {
      const text = await res.text()
      data = text ? { message: text.slice(0, 200) } : {}
    } catch {
      data = {}
    }
  }

  if (!res.ok) {
    const msg = data.message || data.error || `Request failed (${res.status})`
    const err = new Error(msg)
    err.status = res.status
    err.data = data
    throw err
  }

  return data
}

export const api = {
  // Auth
  register: (payload) => request('/register', { method: 'POST', body: payload }),
  login: (payload) => request('/login', { method: 'POST', body: payload }),

  // Users
  getProfile: () => request('/v1/users/me'),
  updateProfile: (payload) => request('/v1/users/update', { method: 'PATCH', body: payload }),
  deleteUser: (id) => request(`/v1/users/delete/${id}`, { method: 'DELETE' }),

  // Tasks
  getTasks: (params = {}) => {
    const qs = new URLSearchParams()
    if (params.status) qs.set('status', params.status)
    if (params.search) qs.set('search', params.search)
    const query = qs.toString()
    return request(`/v1/tasks/get${query ? '?' + query : ''}`)
  },
  createTask: (payload) => request('/v1/tasks/create', { method: 'POST', body: payload }),
  updateTask: (id, payload) => request(`/v1/tasks/update/${id}`, { method: 'PATCH', body: payload }),
  deleteTask: (id) => request(`/v1/tasks/delete/${id}`, { method: 'DELETE' }),
}
