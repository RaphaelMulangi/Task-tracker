import client from './client'

export const register = (payload) => client.post('/register', payload).then((r) => r.data)

export const login = (payload) => client.post('/login', payload).then((r) => r.data)

export const logout = () => client.post('/logout').then((r) => r.data)

export const me = () => client.get('/user').then((r) => r.data)

export const updateProfile = (payload) => client.put('/profile', payload).then((r) => r.data)
