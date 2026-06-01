const BASE      = '/api'
const TOKEN_KEY = 'rd_auth_token'

function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

async function request(method, path, body = null) {
  const token   = getToken()
  const headers = { 'Content-Type': 'application/json' }
  if (token) headers['Authorization'] = `Bearer ${token}`
  // IIS blocks PUT/PATCH/DELETE — tunnel them as POST with override header
  const tunnelled = ['PUT', 'PATCH', 'DELETE'].includes(method)
  if (tunnelled) headers['X-HTTP-Method-Override'] = method
  const opts = { method: tunnelled ? 'POST' : method, headers }
  if (body) opts.body = JSON.stringify(body)
  const res = await fetch(BASE + path, opts)
  if (!res.ok) {
    let msg = `HTTP ${res.status}`
    try { const j = await res.json(); if (j?.error) msg = j.error } catch {}
    throw new Error(msg)
  }
  return res.json()
}

async function upload(formType, formId, paramKey, paramLabel, file) {
  const token = getToken()
  const fd    = new FormData()
  fd.append('form_type', formType)
  fd.append('form_id', String(formId))
  fd.append('param_key', paramKey)
  fd.append('param_label', paramLabel)
  fd.append('file', file)
  const headers = {}
  if (token) headers['Authorization'] = `Bearer ${token}`
  const res = await fetch(BASE + '/upload', { method: 'POST', body: fd, headers })
  if (!res.ok) throw new Error(`HTTP ${res.status}`)
  return res.json()
}

export const api = {
  nextRunNumber: (year, month) => {
    const q = year && month ? `?year=${year}&month=${month}` : ''
    return request('GET', `/next_run_number${q}`)
  },
  auth: {
    login:  (username, password) => request('POST', '/auth/login', { username, password }),
    logout: ()                   => request('POST', '/auth/logout'),
    me:     ()                   => request('GET',  '/auth/me'),
  },
  users: {
    list:   ()           => request('GET',    '/users'),
    create: (data)       => request('POST',   '/users', data),
    update: (id, data)   => request('PUT',    `/users/${id}`, data),
    delete: (id)         => request('DELETE', `/users/${id}`),
  },
  stability: {
    list:   ()           => request('GET',    '/stability'),
    get:    (id)         => request('GET',    `/stability/${id}`),
    create: (data)       => request('POST',   '/stability', data),
    update: (id, data)   => request('PUT',    `/stability/${id}`, data),
    delete: (id)         => request('DELETE', `/stability/${id}`),
  },
  dissolution: {
    list:   ()           => request('GET',    '/dissolution'),
    get:    (id)         => request('GET',    `/dissolution/${id}`),
    create: (data)       => request('POST',   '/dissolution', data),
    update: (id, data)   => request('PUT',    `/dissolution/${id}`, data),
    patch:  (id, data)   => request('PATCH',  `/dissolution/${id}`, data),
    delete: (id)         => request('DELETE', `/dissolution/${id}`),
  },
  uploads: {
    list:    (formType, formId) => request('GET', `/upload?form_type=${formType}&form_id=${formId}`),
    upload:  (formType, formId, paramKey, paramLabel, file) =>
               upload(formType, formId, paramKey, paramLabel, file),
    delete:  (id)               => request('DELETE', `/upload/${id}`),
    fileUrl: (filename)         => `${BASE}/uploads/${filename}`,
  },
  qualityCheck: {
    list:   ()           => request('GET',    '/quality_check'),
    get:    (id)         => request('GET',    `/quality_check/${id}`),
    create: (data)       => request('POST',   '/quality_check', data),
    update: (id, data)   => request('PUT',    `/quality_check/${id}`, data),
    patch:  (id, data)   => request('PATCH',  `/quality_check/${id}`, data),
    delete: (id)         => request('DELETE', `/quality_check/${id}`),
    lock:   (id)         => request('PATCH',  `/quality_check/${id}`, { locked: true }),
  },
}
