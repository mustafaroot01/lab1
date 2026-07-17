import { ofetch } from 'ofetch'

export const $api = ofetch.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken) {
      options.headers = new Headers(options.headers as HeadersInit)
      options.headers.set('Authorization', `Bearer ${accessToken}`)
    }
  },
})
