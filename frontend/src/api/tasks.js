import client from './client'

export const listTasks = (status) =>
  client.get('/tasks', { params: status && status !== 'all' ? { status } : {} }).then((r) => r.data)

export const getTask = (id) => client.get(`/tasks/${id}`).then((r) => r.data)

export const createTask = (payload) => client.post('/tasks', payload).then((r) => r.data)

export const updateTask = (id, payload) => client.put(`/tasks/${id}`, payload).then((r) => r.data)

export const deleteTask = (id) => client.delete(`/tasks/${id}`).then((r) => r.data)

export const toggleTaskComplete = (id) => client.patch(`/tasks/${id}/toggle`).then((r) => r.data)
