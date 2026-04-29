const buildAuthHeaders = (token) => token ? { Authorization: `Bearer ${token}` } : {}

export const fetchClinics = async () => {
    const response = await fetch('/api/clinics')
    if (!response.ok) {
        throw new Error('Impossible de charger les cliniques')
    }
    return await response.json()
}

export const loginClient = async ({ email, password }) => {
    const response = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
    })

    const payload = await response.json().catch(() => ({}))
    if (!response.ok) {
        throw new Error(payload.message || 'Connexion impossible')
    }
    return payload
}

export const registerClient = async ({ email, password, name, firstname, phone, dateOfBirth }) => {
    const response = await fetch('/api/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            email,
            password,
            name,
            firstname,
            phone,
            date_of_birth: dateOfBirth,
        }),
    })

    const payload = await response.json().catch(() => ({}))
    if (!response.ok) {
        throw new Error(payload.error || 'Inscription impossible')
    }
    return payload
}

export const fetchMyProfile = async (token) => {
    const response = await fetch('/api/me', {
        headers: { ...buildAuthHeaders(token) },
    })

    if (response.status === 401) {
        return { unauthorized: true, profile: null }
    }

    if (!response.ok) {
        throw new Error('Impossible de charger le profil')
    }

    return { profile: await response.json() }
}

export const fetchMyAppointments = async (token) => {
    const response = await fetch('/api/appointments/me', {
        headers: { ...buildAuthHeaders(token) },
    })

    if (response.status === 401) {
        return { unauthorized: true, appointments: [] }
    }

    if (!response.ok) {
        throw new Error('Impossible de charger les rendez-vous')
    }

    return { appointments: await response.json() }
}

export const fetchMyCalendar = async (token, month) => {
    const response = await fetch(`/api/appointments/calendar?month=${encodeURIComponent(month)}`, {
        headers: { ...buildAuthHeaders(token) },
    })

    if (response.status === 401) {
        return { unauthorized: true, events: [] }
    }

    if (!response.ok) {
        throw new Error('Impossible de charger le calendrier')
    }

    return { events: await response.json() }
}

export const createAppointment = async ({ doctorId, date, time, duration, note, token }) => {
    const response = await fetch('/api/appointments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            ...buildAuthHeaders(token),
        },
        body: JSON.stringify({
            doctor_id: doctorId,
            date,
            time,
            duration,
            note,
        }),
    })

    const payload = await response.json().catch(() => ({}))
    if (!response.ok) {
        throw new Error(payload.error || 'Le rendez-vous a echoue')
    }
    return payload
}