import axios from 'axios'

const API_BASE_URL = '/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

export const getItems = async (status?: string) => {
  try {
    const params = status ? { status } : {}
    const response = await api.get('/items', { params })
    return response.data
  } catch (error) {
    console.error('Error fetching items:', error)
    throw error
  }
}

export const getItem = async (id: number) => {
  try {
    const response = await api.get(`/items/${id}`)
    return response.data
  } catch (error) {
    console.error('Error fetching item:', error)
    throw error
  }
}

export const createItem = async (itemData: {
  title: string
  description: string
  status: string
  image?: string
}) => {
  try {
    const response = await api.post('/items', itemData)
    return response.data
  } catch (error) {
    console.error('Error creating item:', error)
    throw error
  }
}

export const getComments = async (itemId: number) => {
  try {
    const response = await api.get(`/items/${itemId}/comments`)
    return response.data
  } catch (error) {
    console.error('Error fetching comments:', error)
    throw error
  }
}

export const createComment = async (itemId: number, content: string) => {
  try {
    const response = await api.post(`/items/${itemId}/comments`, { content })
    return response.data
  } catch (error) {
    console.error('Error creating comment:', error)
    throw error
  }
}
