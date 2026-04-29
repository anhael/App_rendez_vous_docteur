const apiRequest = async (url, options = {}) => {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            ...(options.headers || {}),
        },
        ...options,
    })

    const payload = await response.json().catch(() => ({}))

    if (!response.ok) {
        throw new Error(payload.error || 'Admin API request failed')
    }

    return payload
}

export const fetchAdminDashboard = async () => apiRequest('/api/admin/dashboard')
export const createAdminClinic = async (data) => apiRequest('/api/admin/clinics', {
    method: 'POST',
    body: JSON.stringify(data),
})
export const updateAdminClinic = async (id, data) => apiRequest(`/api/admin/clinics/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
})
export const deleteAdminClinic = async (id) => apiRequest(`/api/admin/clinics/${id}`, {
    method: 'DELETE',
})

export const createAdminDoctor = async (data) => apiRequest('/api/admin/doctors', {
    method: 'POST',
    body: JSON.stringify(data),
})
export const updateAdminDoctor = async (id, data) => apiRequest(`/api/admin/doctors/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
})
export const deleteAdminDoctor = async (id) => apiRequest(`/api/admin/doctors/${id}`, {
    method: 'DELETE',
})

export const createAdminAvailability = async (data) => apiRequest('/api/admin/availabilities', {
    method: 'POST',
    body: JSON.stringify(data),
})

export const deleteAdminAvailability = async (id) => apiRequest(`/api/admin/availabilities/${id}`, {
    method: 'DELETE',
})

export const updateAdminAppointmentStatus = async (id, status) => apiRequest(`/api/admin/appointments/${id}/status`, {
    method: 'PUT',
    body: JSON.stringify({ status }),
})

export const fetchAdminUsers = async () => apiRequest('/api/admin/users')
