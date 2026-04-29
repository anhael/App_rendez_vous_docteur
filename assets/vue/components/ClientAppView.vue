<template>
    <div class="medical-shell">
        <header class="medical-topbar">
            <div>
                <span class="kicker">MediLine</span>
                <h1>Prise de rendez-vous medicale</h1>
                <p>Selectionnez une clinique, choisissez un medecin, puis reservez un creneau.</p>
            </div>
            <div class="topbar-actions">
                <a class="btn btn-outline-dark" href="/admin/login">Espace admin</a>
            </div>
        </header>

        <section class="auth-card">
            <h2>{{ currentUser ? 'Mon espace patient' : 'Connexion patient' }}</h2>
            <div v-if="feedback" class="alert" :class="feedbackType === 'error' ? 'alert-danger' : 'alert-success'">{{ feedback }}</div>

            <div v-if="currentUser" class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <strong>{{ currentUser.firstname }} {{ currentUser.name }}</strong>
                    <div class="text-muted small">{{ currentUser.email }}</div>
                </div>
                <button class="btn btn-outline-secondary" type="button" @click="logout">Deconnexion</button>
            </div>

            <div v-else class="row g-2">
                <div class="col-12 col-md-6">
                    <input v-model.trim="auth.email" type="email" class="form-control" placeholder="Email">
                </div>
                <div class="col-12 col-md-6">
                    <input v-model="auth.password" type="password" class="form-control" placeholder="Mot de passe">
                </div>
                <div class="col-12 col-md-4">
                    <input v-model.trim="auth.firstname" type="text" class="form-control" placeholder="Prenom (inscription)">
                </div>
                <div class="col-12 col-md-4">
                    <input v-model.trim="auth.name" type="text" class="form-control" placeholder="Nom (inscription)">
                </div>
                <div class="col-12 col-md-4">
                    <input v-model.trim="auth.phone" type="text" class="form-control" placeholder="Telephone (optionnel)">
                </div>
                <div class="col-12 col-md-4">
                    <input v-model="auth.dateOfBirth" type="date" class="form-control">
                </div>
                <div class="col-12 col-md-8 d-flex gap-2">
                    <button class="btn btn-dark" type="button" :disabled="authLoading" @click="login">Se connecter</button>
                    <button class="btn btn-outline-dark" type="button" :disabled="authLoading" @click="register">S'inscrire</button>
                </div>
            </div>
        </section>

        <section class="main-grid">
            <article class="panel">
                <h2>Parcours de reservation</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Clinique</label>
                        <select v-model.number="selectedClinicId" class="form-select">
                            <option :value="0" disabled>Choisir une clinique</option>
                            <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Medecin</label>
                        <select v-model.number="selectedDoctorId" class="form-select" :disabled="!selectedClinic">
                            <option :value="0" disabled>Choisir un medecin</option>
                            <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                                {{ doctor.fullName }} - {{ doctor.specialty }}
                            </option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="availability-box" v-if="selectedDoctor">
                            <h3>Disponibilites</h3>
                            <ul>
                                <li v-for="slot in selectedDoctor.availabilities" :key="slot.id">
                                    {{ weekDays[slot.weekday] }}: {{ slot.startTime }} - {{ slot.endTime }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Date</label>
                        <input v-model="booking.date" type="date" class="form-control">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Heure</label>
                        <input v-model="booking.time" type="time" class="form-control">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Duree (min)</label>
                        <select v-model.number="booking.duration" class="form-select">
                            <option :value="15">15</option>
                            <option :value="30">30</option>
                            <option :value="45">45</option>
                            <option :value="60">60</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Motif (optionnel)</label>
                        <textarea v-model.trim="booking.note" class="form-control" rows="2" placeholder="Ex: consultation de suivi"></textarea>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary" type="button" :disabled="submitting" @click="createNewAppointment">Reserver ce creneau</button>
                    </div>
                    <div v-if="bookingError" class="col-12 alert alert-danger">{{ bookingError }}</div>
                    <div v-if="bookingSuccess" class="col-12 alert alert-success">{{ bookingSuccess }}</div>
                </div>
            </article>

            <article class="panel">
                <div class="calendar-head">
                    <h2>Mon calendrier</h2>
                    <input v-model="calendarMonth" type="month" class="form-control" @change="loadCalendar">
                </div>
                <div class="calendar-grid">
                    <div v-for="day in calendarDays" :key="day.iso" class="calendar-cell" :class="{ muted: !day.inMonth }">
                        <div class="day-number">{{ day.day }}</div>
                        <ul>
                            <li v-for="event in day.events" :key="event.id">{{ event.time }} - {{ event.doctor }}</li>
                        </ul>
                    </div>
                </div>
            </article>
        </section>

        <section class="panel">
            <h2>Mes rendez-vous</h2>
            <div v-if="loadingAppointments" class="text-muted">Chargement...</div>
            <div v-else-if="appointments.length === 0" class="text-muted">Aucun rendez-vous pour le moment.</div>
            <div v-else class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Medecin</th>
                            <th>Clinique</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="appointment in appointments" :key="appointment.id">
                            <td>{{ appointment.date }}</td>
                            <td>{{ appointment.time }}</td>
                            <td>{{ appointment.doctor }}</td>
                            <td>{{ appointment.clinic }}</td>
                            <td><span class="badge text-bg-light">{{ appointment.status }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import {
    createAppointment,
    fetchClinics,
    fetchMyAppointments,
    fetchMyCalendar,
    fetchMyProfile,
    loginClient,
    registerClient,
} from '../../api/properties.api'

const weekDays = {
    1: 'Lundi',
    2: 'Mardi',
    3: 'Mercredi',
    4: 'Jeudi',
    5: 'Vendredi',
    6: 'Samedi',
    7: 'Dimanche',
}

const authToken = ref(localStorage.getItem('medical_token') || '')
const currentUser = ref(null)
const authLoading = ref(false)
const feedback = ref('')
const feedbackType = ref('success')

const auth = ref({
    email: '',
    password: '',
    firstname: '',
    name: '',
    phone: '',
    dateOfBirth: '',
})

const clinics = ref([])
const selectedClinicId = ref(0)
const selectedDoctorId = ref(0)

const booking = ref({
    date: '',
    time: '',
    duration: 30,
    note: '',
})
const submitting = ref(false)
const bookingError = ref('')
const bookingSuccess = ref('')

const appointments = ref([])
const loadingAppointments = ref(false)
const calendarMonth = ref(new Date().toISOString().slice(0, 7))
const calendarEvents = ref([])

const selectedClinic = computed(() => clinics.value.find((clinic) => clinic.id === selectedClinicId.value) || null)
const doctors = computed(() => selectedClinic.value?.doctors || [])
const selectedDoctor = computed(() => doctors.value.find((doctor) => doctor.id === selectedDoctorId.value) || null)

const calendarDays = computed(() => {
    const [year, month] = calendarMonth.value.split('-').map((value) => Number(value))
    const firstDay = new Date(year, month - 1, 1)
    const startOffset = (firstDay.getDay() + 6) % 7
    const cursor = new Date(firstDay)
    cursor.setDate(cursor.getDate() - startOffset)

    const eventsByDay = calendarEvents.value.reduce((carry, event) => {
        if (!carry[event.date]) {
            carry[event.date] = []
        }
        carry[event.date].push(event)
        return carry
    }, {})

    const days = []
    for (let i = 0; i < 42; i += 1) {
        const iso = cursor.toISOString().slice(0, 10)
        days.push({
            iso,
            day: cursor.getDate(),
            inMonth: cursor.getMonth() === (month - 1),
            events: eventsByDay[iso] || [],
        })
        cursor.setDate(cursor.getDate() + 1)
    }

    return days
})

const loadClinics = async () => {
    clinics.value = await fetchClinics()
    if (clinics.value.length > 0) {
        selectedClinicId.value = clinics.value[0].id
        selectedDoctorId.value = clinics.value[0].doctors?.[0]?.id || 0
    }
}

const loadProfile = async () => {
    if (!authToken.value) {
        currentUser.value = null
        return
    }

    const result = await fetchMyProfile(authToken.value)
    if (result.unauthorized) {
        authToken.value = ''
        localStorage.removeItem('medical_token')
        currentUser.value = null
        return
    }

    currentUser.value = result.profile
}

const loadAppointments = async () => {
    if (!authToken.value) {
        appointments.value = []
        return
    }

    loadingAppointments.value = true
    try {
        const result = await fetchMyAppointments(authToken.value)
        if (result.unauthorized) {
            appointments.value = []
            return
        }
        appointments.value = result.appointments || []
    } finally {
        loadingAppointments.value = false
    }
}

const loadCalendar = async () => {
    if (!authToken.value) {
        calendarEvents.value = []
        return
    }

    const result = await fetchMyCalendar(authToken.value, calendarMonth.value)
    if (result.unauthorized) {
        calendarEvents.value = []
        return
    }

    calendarEvents.value = result.events || []
}

const login = async () => {
    authLoading.value = true
    feedback.value = ''

    try {
        const payload = await loginClient({ email: auth.value.email, password: auth.value.password })
        authToken.value = payload.token
        localStorage.setItem('medical_token', payload.token)
        feedbackType.value = 'success'
        feedback.value = 'Connexion reussie'
        await loadProfile()
        await loadAppointments()
        await loadCalendar()
    } catch (error) {
        feedbackType.value = 'error'
        feedback.value = error.message
    } finally {
        authLoading.value = false
    }
}

const register = async () => {
    authLoading.value = true
    feedback.value = ''

    try {
        await registerClient({
            email: auth.value.email,
            password: auth.value.password,
            firstname: auth.value.firstname,
            name: auth.value.name,
            phone: auth.value.phone,
            dateOfBirth: auth.value.dateOfBirth,
        })
        await login()
        feedbackType.value = 'success'
        feedback.value = 'Inscription terminee'
    } catch (error) {
        feedbackType.value = 'error'
        feedback.value = error.message
    } finally {
        authLoading.value = false
    }
}

const logout = () => {
    authToken.value = ''
    currentUser.value = null
    appointments.value = []
    calendarEvents.value = []
    localStorage.removeItem('medical_token')
    feedbackType.value = 'success'
    feedback.value = 'Deconnexion effectuee'
}

const createNewAppointment = async () => {
    bookingError.value = ''
    bookingSuccess.value = ''

    if (!authToken.value) {
        bookingError.value = 'Connectez-vous pour reserver.'
        return
    }

    if (!selectedDoctor.value) {
        bookingError.value = 'Choisissez un medecin.'
        return
    }

    submitting.value = true
    try {
        await createAppointment({
            doctorId: selectedDoctor.value.id,
            date: booking.value.date,
            time: booking.value.time,
            duration: booking.value.duration,
            note: booking.value.note,
            token: authToken.value,
        })

        bookingSuccess.value = 'Rendez-vous reserve avec succes.'
        await loadAppointments()
        await loadCalendar()
    } catch (error) {
        bookingError.value = error.message
    } finally {
        submitting.value = false
    }
}

onMounted(async () => {
    await loadClinics()
    await loadProfile()
    await loadAppointments()
    await loadCalendar()
})
</script>
