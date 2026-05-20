const BASE = '/api'

async function request(method, path, body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } }
  if (body) opts.body = JSON.stringify(body)
  const res = await fetch(BASE + path, opts)
  if (!res.ok) throw new Error(`HTTP ${res.status}`)
  return res.json()
}

export const api = {
  stability: {
    list: () => request('GET', '/stability'),
    get: (id) => request('GET', `/stability/${id}`),
    create: (data) => request('POST', '/stability', data),
    update: (id, data) => request('PUT', `/stability/${id}`, data),
    delete: (id) => request('DELETE', `/stability/${id}`),
  },
  dissolution: {
    list: () => request('GET', '/dissolution'),
    get: (id) => request('GET', `/dissolution/${id}`),
    create: (data) => request('POST', '/dissolution', data),
    update: (id, data) => request('PUT', `/dissolution/${id}`, data),
    delete: (id) => request('DELETE', `/dissolution/${id}`),
  },
  qualityCheck: {
    list: () => request('GET', '/quality_check'),
    get: (id) => request('GET', `/quality_check/${id}`),
    create: (data) => request('POST', '/quality_check', data),
    update: (id, data) => request('PUT', `/quality_check/${id}`, data),
    delete: (id) => request('DELETE', `/quality_check/${id}`),
  },
}
