import { ofetch } from 'ofetch'

const getApiBaseUrl = () => {
  const raw = import.meta.env.VITE_API_BASE_URL || '/api'
  if (raw === '/api' || raw.endsWith('/api'))
    return raw
  return `${raw.replace(/\/+$/, '')}/api`
}

export const $api = ofetch.create({
  baseURL: getApiBaseUrl(),
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken) {
      options.headers = new Headers(options.headers as HeadersInit)
      options.headers.set('Authorization', `Bearer ${accessToken}`)
    }
  },
})
