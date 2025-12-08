export async function getUsers() {
  try {
    const response = await fetch("http://127.0.0.1:8000/users", {
      credentials: 'include'
    })

    // Si el servidor responde con error, emitir evento global
    if (!response.ok) {
      const event = new CustomEvent('auth:required', { detail: { status: response.status } })
      window.dispatchEvent(event)
      window.dispatchEvent(new CustomEvent('app:error', { detail: { message: 'HTTP Error ' + response.status, status: response.status } }))
      throw new Error('HTTP Error ' + response.status)
    }

    // Verificar content-type antes de parsear JSON
    const contentType = response.headers.get('content-type') || ''
    if (!contentType.includes('application/json')) {
      // Probablemente una página HTML (login), emitir evento
      const event = new CustomEvent('auth:required', { detail: { status: response.status, contentType } })
      window.dispatchEvent(event)
      window.dispatchEvent(new CustomEvent('app:error', { detail: { message: 'Respuesta no JSON (content-type: ' + contentType + ')' } }))
      throw new Error('Invalid JSON response')
    }

    const datos = await response.json()
    return datos.data
  } catch (e) {
    console.error('No se pudo hacer el fetch de los datos ', e)
    try {
      window.dispatchEvent(new CustomEvent('app:error', { detail: { message: e.message || String(e) } }))
    } catch (er) {
      // ignore if window not available
    }
    return []
  }
}