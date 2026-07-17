import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import type { App } from 'vue'
import { useCookie } from '@core/composable/useCookie'

window.Pusher = Pusher

export let echo: Echo | null = null

export function getEcho(): Echo {
  if (echo) return echo

  const appKey = import.meta.env.VITE_REVERB_APP_KEY || 'jykdgzalafvy6fj3nwtx'
  const wsHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname
  const wsPort = Number(import.meta.env.VITE_REVERB_PORT || 8080)
  const forceTLS = (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https'

  echo = new Echo({
    broadcaster: 'reverb',
    key: appKey,
    wsHost: wsHost,
    wsPort: wsPort,
    wssPort: wsPort,
    forceTLS: forceTLS,
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel: any) => {
      return {
        authorize: (socketId: string, callback: Function) => {
          const accessToken = useCookie('accessToken').value
          const baseUrl = import.meta.env.VITE_API_BASE_URL || '/api'
          const authUrl = baseUrl.replace(/\/api\/?$/, '') + '/api/broadcasting/auth'

          fetch(authUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              ...(accessToken ? { 'Authorization': `Bearer ${accessToken}` } : {}),
            },
            body: JSON.stringify({
              socket_id: socketId,
              channel_name: channel.name,
            }),
          })
            .then(response => {
              if (!response.ok) throw new Error(`Auth status: ${response.status}`)
              return response.json()
            })
            .then(data => {
              callback(false, data)
            })
            .catch(error => {
              console.error('[Echo] Private channel auth failed:', error)
              callback(true, error)
            })
        },
      }
    },
  })

  return echo
}

export default function (app: App) {
  if (typeof window !== 'undefined') {
    getEcho()
  }
}
