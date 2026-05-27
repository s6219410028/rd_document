const BASE = '/api'

async function request(method, path, body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } }
  if (body) opts.body = JSON.stringify(body)
  const res = await fetch(BASE + path, opts)
  if (!res.ok) throw new Error(`HTTP ${res.status}`)
  return res.json()
}

async function upload(formType, formId, paramKey, paramLabel, file) {
  const fd = new FormData()
  fd.append('form_type', formType)
  fd.append('form_id', String(formId))
  fd.append('param_key', paramKey)
  fd.append('param_label', paramLabel)
  fd.append('file', file)
  const res = await fetch(BASE + '/upload', { method: 'POST', body: fd })
  if (!res.ok) throw new Error(`HTTP ${res.status}`)
  return res.json()
}

export const api = {
  nextRunNumber: (year, month) => {
    const q = year && month ? `?year=${year}&month=${month}` : ''
    return request('GET', `/next_run_number${q}`)
  },
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
    patch: (id, data) => request('PATCH', `/dissolution/${id}`, data),
    delete: (id) => request('DELETE', `/dissolution/${id}`),
  },
  uploads: {
    list: (formType, formId) => request('GET', `/upload?form_type=${formType}&form_id=${formId}`),
    upload: (formType, formId, paramKey, paramLabel, file) => upload(formType, formId, paramKey, paramLabel, file),
    delete: (id) => request('DELETE', `/upload/${id}`),
    fileUrl: (filename) => `${BASE}/uploads/${filename}`,
  },
  qualityCheck: {
    list: () => request('GET', '/quality_check'),
    get: (id) => request('GET', `/quality_check/${id}`),
    create: (data) => request('POST', '/quality_check', data),
    update: (id, data) => request('PUT', `/quality_check/${id}`, data),
    patch: (id, data) => request('PATCH', `/quality_check/${id}`, data),
    delete: (id) => request('DELETE', `/quality_check/${id}`),
    lock: (id) => request('PATCH', `/quality_check/${id}`, { locked: true }),
  },
}
