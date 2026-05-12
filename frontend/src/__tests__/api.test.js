import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'

const mockFetch = vi.fn()
global.fetch = mockFetch

beforeEach(() => {
  localStorage.clear()
  mockFetch.mockReset()
})

afterEach(() => {
  vi.restoreAllMocks()
})

// Dynamically import so fetch mock is in place
async function getApi() {
  return (await import('../api/index.js')).api
}

describe('api', () => {
  it('register sends POST to /api/register', async () => {
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ success: true }) })
    const api = await getApi()
    const res = await api.register({ username: 'test', email: 'test@t.com', password: 'pass' })
    expect(res.success).toBe(true)
    expect(mockFetch).toHaveBeenCalledWith('/api/register', expect.objectContaining({ method: 'POST' }))
  })

  it('login sends POST to /api/login', async () => {
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ token: 'abc' }) })
    const api = await getApi()
    const res = await api.login({ email: 'test@t.com', password: 'pass' })
    expect(res.token).toBe('abc')
  })

  it('includes auth token in Authorization header when set', async () => {
    localStorage.setItem('auth_token', 'my-token')
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ id: 1 }) })
    const api = await getApi()
    await api.getProfile()
    const callHeaders = mockFetch.mock.calls[0][1].headers
    expect(callHeaders['Authorization']).toBe('Bearer my-token')
  })

  it('getTasks appends query params', async () => {
    localStorage.setItem('auth_token', 't')
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ data: [] }) })
    const api = await getApi()
    await api.getTasks({ status: 'pending', search: 'hello' })
    const url = mockFetch.mock.calls[0][0]
    expect(url).toContain('status=pending')
    expect(url).toContain('search=hello')
  })

  it('createTask sends POST with JSON body', async () => {
    localStorage.setItem('auth_token', 't')
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ data: { id: 1 } }) })
    const api = await getApi()
    await api.createTask({ title: 'New Task' })
    expect(mockFetch).toHaveBeenCalledWith('/api/v1/tasks/create', expect.objectContaining({ method: 'POST' }))
    const body = JSON.parse(mockFetch.mock.calls[0][1].body)
    expect(body.title).toBe('New Task')
  })

  it('updateTask sends PATCH', async () => {
    localStorage.setItem('auth_token', 't')
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ data: {} }) })
    const api = await getApi()
    await api.updateTask(5, { status: 'completed' })
    expect(mockFetch).toHaveBeenCalledWith('/api/v1/tasks/update/5', expect.objectContaining({ method: 'PATCH' }))
  })

  it('deleteTask sends DELETE', async () => {
    localStorage.setItem('auth_token', 't')
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ message: 'Deleted' }) })
    const api = await getApi()
    await api.deleteTask(3)
    expect(mockFetch).toHaveBeenCalledWith('/api/v1/tasks/delete/3', expect.objectContaining({ method: 'DELETE' }))
  })

  it('throws on non-ok response', async () => {
    localStorage.setItem('auth_token', 't')
    mockFetch.mockResolvedValue({ ok: false, status: 422, json: () => Promise.resolve({ message: 'Validation failed' }) })
    const api = await getApi()
    await expect(api.createTask({})).rejects.toThrow('Validation failed')
  })

  it('updateProfile sends PATCH to /api/v1/users/update', async () => {
    localStorage.setItem('auth_token', 't')
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ data: { username: 'new' } }) })
    const api = await getApi()
    await api.updateProfile({ username: 'new' })
    expect(mockFetch).toHaveBeenCalledWith('/api/v1/users/update', expect.objectContaining({ method: 'PATCH' }))
  })

  it('deleteUser sends DELETE to /api/v1/users/delete/{id}', async () => {
    localStorage.setItem('auth_token', 't')
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ message: 'Deleted' }) })
    const api = await getApi()
    await api.deleteUser(7)
    expect(mockFetch).toHaveBeenCalledWith('/api/v1/users/delete/7', expect.objectContaining({ method: 'DELETE' }))
  })
})
