import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore, useTaskStore } from '../store/index.js'

const mockFetch = vi.fn()
global.fetch = mockFetch

beforeEach(() => {
  setActivePinia(createPinia())
  localStorage.clear()
  mockFetch.mockReset()
})

afterEach(() => {
  vi.restoreAllMocks()
})

function mockOk(data) {
  return { ok: true, json: () => Promise.resolve(data) }
}

function mockErr(status, message) {
  return { ok: false, status, json: () => Promise.resolve({ message }) }
}

describe('useAuthStore', () => {
  it('starts unauthenticated', () => {
    const auth = useAuthStore()
    expect(auth.isAuthenticated).toBe(false)
    expect(auth.user).toBeNull()
    expect(auth.token).toBeNull()
  })

  it('login sets token and calls localStorage', async () => {
    mockFetch.mockResolvedValue(mockOk({ token: 'tok-123' }))
    const auth = useAuthStore()
    await auth.login('a@b.com', 'secret')
    expect(auth.token).toBe('tok-123')
    expect(auth.isAuthenticated).toBe(true)
    expect(localStorage.getItem('auth_token')).toBe('tok-123')
  })

  it('login throws on failure', async () => {
    mockFetch.mockResolvedValue(mockErr(422, 'These credentials do not match our records.'))
    const auth = useAuthStore()
    await expect(auth.login('a@b.com', 'wrong')).rejects.toThrow('Invalid credentials')
    expect(auth.isAuthenticated).toBe(false)
  })

  it('fetchProfile sets user', async () => {
    const userData = { id: 1, username: 'test', email: 'a@b.com' }
    mockFetch.mockResolvedValue(mockOk(userData))
    const auth = useAuthStore()
    auth.token = 'tok'
    localStorage.setItem('auth_token', 'tok')
    await auth.fetchProfile()
    expect(auth.user).toEqual(userData)
  })

  it('logout clears user, token, and localStorage', () => {
    localStorage.setItem('auth_token', 'tok')
    const auth = useAuthStore()
    auth.token = 'tok'
    auth.user = { username: 'test' }
    auth.logout()
    expect(auth.token).toBeNull()
    expect(auth.user).toBeNull()
    expect(auth.isAuthenticated).toBe(false)
    expect(localStorage.getItem('auth_token')).toBeNull()
  })

  it('restores token from localStorage on init', () => {
    localStorage.setItem('auth_token', 'restored-token')
    const auth = useAuthStore()
    expect(auth.token).toBe('restored-token')
    expect(auth.isAuthenticated).toBe(true)
  })

  it('register calls api.register', async () => {
    mockFetch.mockResolvedValue(mockOk({ success: true, user_id: 5 }))
    const auth = useAuthStore()
    const res = await auth.register('user', 'u@b.com', 'pass')
    expect(res.success).toBe(true)
    expect(res.user_id).toBe(5)
  })

  it('updateProfile calls api.updateProfile and updates user', async () => {
    const existing = { id: 1, username: 'old', email: 'a@b.com' }
    const updated = { data: { id: 1, username: 'new', email: 'a@b.com' } }
    mockFetch.mockResolvedValue(mockOk(updated))
    const auth = useAuthStore()
    auth.user = existing
    await auth.updateProfile({ username: 'new' })
    expect(auth.user.username).toBe('new')
  })

  it('deleteUser calls api.deleteUser', async () => {
    mockFetch.mockResolvedValue(mockOk({ message: 'Deleted' }))
    const auth = useAuthStore()
    const res = await auth.deleteUser(7)
    expect(res.message).toBe('Deleted')
  })
})

describe('useTaskStore', () => {
  it('starts with empty tasks', () => {
    const ts = useTaskStore()
    expect(ts.tasks).toEqual([])
    expect(ts.loading).toBe(false)
  })

  it('fetchTasks sets tasks and loading state', async () => {
    const taskData = { data: [{ id: 1, title: 'Task 1', status: 'pending' }] }
    mockFetch.mockResolvedValue(mockOk(taskData))
    const ts = useTaskStore()
    const fetchPromise = ts.fetchTasks()
    expect(ts.loading).toBe(true)
    await fetchPromise
    expect(ts.tasks).toEqual(taskData.data)
    expect(ts.loading).toBe(false)
  })

  it('addTask prepends the new task', async () => {
    mockFetch.mockResolvedValue(mockOk({ data: { id: 2, title: 'New', status: 'pending' } }))
    const ts = useTaskStore()
    ts.tasks = [{ id: 1, title: 'Old', status: 'pending' }]
    await ts.addTask({ title: 'New' })
    expect(ts.tasks).toHaveLength(2)
    expect(ts.tasks[0].title).toBe('New')
  })

  it('updateTask replaces the task in the list', async () => {
    mockFetch.mockResolvedValue(mockOk({ data: { id: 1, title: 'Updated', status: 'completed' } }))
    const ts = useTaskStore()
    ts.tasks = [{ id: 1, title: 'Original', status: 'pending' }]
    await ts.updateTask(1, { status: 'completed', title: 'Updated' })
    expect(ts.tasks[0].status).toBe('completed')
    expect(ts.tasks[0].title).toBe('Updated')
  })

  it('deleteTask removes the task from the list', async () => {
    mockFetch.mockResolvedValue(mockOk({ message: 'Deleted' }))
    const ts = useTaskStore()
    ts.tasks = [
      { id: 1, title: 'A' },
      { id: 2, title: 'B' },
    ]
    await ts.deleteTask(1)
    expect(ts.tasks).toHaveLength(1)
    expect(ts.tasks[0].id).toBe(2)
  })

  it('setSearch updates searchQuery', () => {
    const ts = useTaskStore()
    ts.setSearch('hello')
    expect(ts.searchQuery).toBe('hello')
  })
})
