<template>
    <div class="medical-shell admin-shell">
        <header class="medical-topbar">
            <div>
                <span class="kicker">Admin</span>
                <h1>Résumé des patients et rendez-vous</h1>
                <p>Vue synthétique des comptes patients, de leurs rendez-vous et de leur statut.</p>
            </div>
            <div class="topbar-actions">
                <a href="/admin" class="btn btn-outline-primary">Retour dashboard</a>
                <a href="/admin/logout" class="btn btn-dark">Deconnexion</a>
            </div>
        </header>

        <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

        <section class="kpi-grid">
            <article class="kpi-card">
                <span>Patients actifs</span>
                <strong>{{ patientSummaries.length }}</strong>
            </article>
            <article class="kpi-card">
                <span>Rendez-vous total</span>
                <strong>{{ appointments.length }}</strong>
            </article>
            <article class="kpi-card">
                <span>Confirmes</span>
                <strong>{{ confirmedAppointments }}</strong>
            </article>
            <article class="kpi-card">
                <span>A venir</span>
                <strong>{{ upcomingAppointments }}</strong>
            </article>
        </section>

        <section class="panel">
            <h2>Patients par activite</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Rendez-vous</th>
                            <th>Prochain RDV</th>
                            <th>Dernier statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="patient in patientSummaries" :key="patient.id">
                            <td>
                                <strong>{{ patient.fullName }}</strong>
                                <div class="text-muted small">ID #{{ patient.id }}</div>
                            </td>
                            <td>
                                <div>{{ patient.email }}</div>
                                <div class="text-muted small">{{ patient.phone || 'Telephone non renseigne' }}</div>
                            </td>
                            <td>{{ patient.totalAppointments }}</td>
                            <td>
                                <template v-if="patient.nextAppointment">
                                    {{ patient.nextAppointment.date }} {{ patient.nextAppointment.time }}
                                    <div class="text-muted small">{{ patient.nextAppointment.doctor }}</div>
                                </template>
                                <span v-else>Aucun rendez-vous a venir</span>
                            </td>
                            <td>
                                <span class="badge text-bg-light text-uppercase">{{ patient.lastStatus }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h2>Rendez-vous recents</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Medecin</th>
                            <th>Clinique</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="appointment in recentAppointments" :key="appointment.id">
                            <td>{{ appointment.date }} {{ appointment.time }}</td>
                            <td>{{ appointment.patient.firstname }} {{ appointment.patient.name }}</td>
                            <td>{{ appointment.doctor.fullName }}</td>
                            <td>{{ appointment.doctor.clinic }}</td>
                            <td>
                                <span class="badge" :class="statusBadgeClass(appointment.status)">{{ appointment.status
                                    }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { fetchAdminDashboard } from '../../api/admin.api'

const users = ref([])
const appointments = ref([])
const errorMessage = ref('')

const loadData = async () => {
    try {
        errorMessage.value = ''
        const data = await fetchAdminDashboard()
        users.value = data.users || []
        appointments.value = data.appointments || []
    } catch (error) {
        errorMessage.value = error.message
    }
}

const appointmentSortKey = (appointment) => new Date(`${appointment.date}T${appointment.time}:00`).getTime()
const isFutureAppointment = (appointment) => appointmentSortKey(appointment) >= Date.now()

const patientSummaries = computed(() => {
    const patients = users.value.filter((user) => !user.roles?.includes('ROLE_ADMIN'))

    return patients.map((patient) => {
        const patientAppointments = appointments.value
            .filter((appointment) => appointment.patient.id === patient.id)
            .sort((left, right) => appointmentSortKey(left) - appointmentSortKey(right))

        const upcoming = patientAppointments.find((appointment) => isFutureAppointment(appointment))
        const latest = patientAppointments[patientAppointments.length - 1]

        return {
            id: patient.id,
            fullName: `${patient.firstname || ''} ${patient.name || ''}`.trim() || patient.email,
            email: patient.email,
            phone: patient.phone,
            totalAppointments: patientAppointments.length,
            nextAppointment: upcoming || null,
            lastStatus: latest?.status || 'aucun',
        }
    }).sort((left, right) => right.totalAppointments - left.totalAppointments)
})

const confirmedAppointments = computed(() => appointments.value.filter((appointment) => appointment.status === 'confirmed').length)
const upcomingAppointments = computed(() => appointments.value.filter((appointment) => isFutureAppointment(appointment)).length)
const recentAppointments = computed(() => [...appointments.value].sort((left, right) => appointmentSortKey(right) - appointmentSortKey(left)).slice(0, 12))

const statusBadgeClass = (status) => {
    if (status === 'confirmed') {
        return 'text-bg-success'
    }

    if (status === 'cancelled') {
        return 'text-bg-secondary'
    }

    return 'text-bg-warning'
}

onMounted(() => {
    loadData()
    document.title = 'Back-office | Patients'
})
</script>