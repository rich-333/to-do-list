export async function getUsers() {
  try {
    const response = await fetch("http://127.0.0.1:8000/users")

    const datos = await response.json()

    return datos.data
  } catch (e) {
    console.error("No se pudo hacer el fetch de los datos ", e)
    return []
  }
}