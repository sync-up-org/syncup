import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../components/LoginView.vue'
import RegisterView from '../components/RegisterView.vue'
import AppHeader from '../components/AppHeader.vue'
import TaskCard from '../components/TaskCard.vue'

const mockFetch = vi.fn()
global.fetch = mockFetch

let router

beforeEach(() => {
  setActivePinia(createPinia())
  localStorage.clear()
  mockFetch.mockReset()

  router = createRouter({
    history: createWebHistory(),
    routes: [
      { path: '/login', name: 'Login', component: LoginView },
      { path: '/register', name: 'Register', component: RegisterView },
    ],
  })
})

describe('LoginView', () => {
  it('renders email and password inputs', () => {
    const wrapper = mount(LoginView, { global: { plugins: [router] } })
    expect(wrapper.find('input[type="email"]').exists()).toBe(true)
    expect(wrapper.find('input[type="password"]').exists()).toBe(true)
  })

  it('shows error when fields are empty on submit', async () => {
    const wrapper = mount(LoginView, { global: { plugins: [router] } })
    await wrapper.find('button').trigger('click')
    expect(wrapper.text()).toContain('Please fill in all fields.')
  })

  it('shows error on failed login', async () => {
    mockFetch.mockResolvedValue({
      ok: false,
      status: 422,
      json: () => Promise.resolve({ message: 'These credentials do not match our records.' }),
    })
    const wrapper = mount(LoginView, { global: { plugins: [router] } })
    await wrapper.find('input[type="email"]').setValue('a@b.com')
    await wrapper.find('input[type="password"]').setValue('wrong')
    await wrapper.find('button').trigger('click')

    // Wait for async error to render
    await new Promise(r => setTimeout(r, 100))
    expect(wrapper.text()).toContain('credentials do not match')
  })

  it('has a link to register page', () => {
    const wrapper = mount(LoginView, { global: { plugins: [router] } })
    expect(wrapper.text()).toContain('Sign up')
  })
})

describe('RegisterView', () => {
  it('renders username, email, and password inputs', () => {
    const wrapper = mount(RegisterView, { global: { plugins: [router] } })
    expect(wrapper.findAll('input')).toHaveLength(3)
  })

  it('shows error when fields are empty', async () => {
    const wrapper = mount(RegisterView, { global: { plugins: [router] } })
    await wrapper.find('button').trigger('click')
    expect(wrapper.text()).toContain('Please fill in all fields.')
  })
})

describe('AppHeader', () => {
  it('emits open-profile on profile button click', async () => {
    const wrapper = mount(AppHeader)
    const btns = wrapper.findAll('button')
    await btns[0].trigger('click')
    expect(wrapper.emitted('open-profile')).toBeTruthy()
  })

  it('emits open-search on search button click', async () => {
    const wrapper = mount(AppHeader)
    const btns = wrapper.findAll('button')
    await btns[1].trigger('click')
    expect(wrapper.emitted('open-search')).toBeTruthy()
  })

  it('emits open-add-task on add task button click', async () => {
    const wrapper = mount(AppHeader)
    const addBtn = wrapper.find('.btn-add-task')
    await addBtn.trigger('click')
    expect(wrapper.emitted('open-add-task')).toBeTruthy()
  })

  it('renders SyncUp logo', () => {
    const wrapper = mount(AppHeader)
    expect(wrapper.text()).toContain('SyncUp')
  })
})

describe('TaskCard', () => {
  it('renders task title and description', () => {
    const task = { id: 1, title: 'Test Task', description: 'A description', status: 'pending' }
    const wrapper = mount(TaskCard, {
      props: { task },
      global: { plugins: [createPinia()] },
    })
    expect(wrapper.text()).toContain('Test Task')
    expect(wrapper.text()).toContain('A description')
  })

  it('shows three status dots', () => {
    const task = { id: 1, title: 'T', status: 'pending' }
    const wrapper = mount(TaskCard, {
      props: { task },
      global: { plugins: [createPinia()] },
    })
    const dots = wrapper.findAll('.status-dot')
    expect(dots).toHaveLength(3)
  })

  it('shows active status dot for current status', () => {
    const task = { id: 1, title: 'T', status: 'completed' }
    const wrapper = mount(TaskCard, {
      props: { task },
      global: { plugins: [createPinia()] },
    })
    const activeDot = wrapper.find('.status-dot.active')
    expect(activeDot.exists()).toBe(true)
  })
})
