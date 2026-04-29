<template>
    <div class="medical-shell admin-shell">
        <header class="medical-topbar">
            <div>
                <span class="kicker">Admin</span>
                <h1>Gestion des cliniques et rendez-vous</h1>
                <p>Pilotez les cliniques, les medecins et le statut des rendez-vous.</p>
            </div>
            <div class="topbar-actions">
                <a href="/admin/patients" class="btn btn-outline-primary">Patients & rendez-vous</a>
                <a href="/" class="btn btn-outline-dark">Vue patient</a>
                <a href="/admin/logout" class="btn btn-dark">Deconnexion</a>
            </div>
        </header>

        <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
        <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

        <section class="kpi-grid">
            <article class="kpi-card">
                <span>Cliniques</span>
                <strong>{{ stats.clinics }}</strong>
            </article>
            <article class="kpi-card">
                <span>Medecins</span>
                <strong>{{ stats.doctors }}</strong>
            </article>
            <article class="kpi-card">
                <span>Rendez-vous</span>
                <strong>{{ stats.appointments }}</strong>
            </article>
            <article class="kpi-card">
                <span>Patients</span>
                <strong>{{ stats.patients }}</strong>
            </article>
        </section>

        <section class="panel">
            <h2>Creer une clinique</h2>
            <form class="row g-2" @submit.prevent="saveClinic">
                <div class="col-12 col-lg-4"><input v-model.trim="clinicForm.name" class="form-control"
                        placeholder="Nom" required></div>
                <div class="col-12 col-lg-4"><input v-model.trim="clinicForm.address" class="form-control"
                        placeholder="Adresse" required></div>
                <div class="col-12 col-lg-3"><input v-model.trim="clinicForm.phone" class="form-control"
                        placeholder="Telephone" required></div>
                <div class="col-12 col-lg-1 d-grid"><button class="btn btn-primary" type="submit">Ajouter</button></div>
            </form>
        </section>

        <section class="panel">
            <h2>Creer un medecin</h2>
            <form class="row g-2" @submit.prevent="saveDoctor">
                <div class="col-12 col-lg-3"><input v-model.trim="doctorForm.fullName" class="form-control"
                        placeholder="Nom complet" required></div>
                <div class="col-12 col-lg-3"><input v-model.trim="doctorForm.specialty" class="form-control"
                        placeholder="Specialite" required></div>
                <div class="col-12 col-lg-2"><input v-model.trim="doctorForm.phone" class="form-control"
                        placeholder="Telephone" required></div>
                <div class="col-12 col-lg-3">
                    <select v-model.number="doctorForm.clinic_id" class="form-select" required>
                        <option :value="0" disabled>Clinique</option>
                        <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
                    </select>
                </div>
                <div class="col-12 col-lg-1 d-grid"><button class="btn btn-primary" type="submit">Ajouter</button></div>
            </form>
        </section>

        <section class="panel">
            <h2>Ajouter une disponibilite</h2>
            <form class="row g-2" @submit.prevent="saveAvailability">
                <div class="col-12 col-lg-3">
                    <select v-model.number="availabilityForm.doctor_id" class="form-select" required>
                        <option :value="0" disabled>Medecin</option>
                        <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">{{ doctor.fullName }}
                        </option>
                    </select>
                </div>
                <div class="col-12 col-lg-2">
                    <select v-model.number="availabilityForm.weekday" class="form-select" required>
                        <option :value="1">Lundi</option>
                        <option :value="2">Mardi</option>
                        <option :value="3">Mercredi</option>
                        <option :value="4">Jeudi</option>
                        <option :value="5">Vendredi</option>
                        <option :value="6">Samedi</option>
                        <option :value="7">Dimanche</option>
                    </select>
                </div>
                <div class="col-12 col-lg-2"><input v-model="availabilityForm.startTime" type="time"
                        class="form-control" required></div>
                <div class="col-12 col-lg-2"><input v-model="availabilityForm.endTime" type="time" class="form-control"
                        required></div>
                <div class="col-12 col-lg-3 d-grid"><button class="btn btn-primary" type="submit">Ajouter</button></div>
            </form>
        </section>

        <section class="panel">
            <h2>Rendez-vous recents</h2>
            <div class="table-responsive">
                <table class="table">
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
                        <tr v-for="appointment in appointments" :key="appointment.id">
                            <td>{{ appointment.date }} {{ appointment.time }}</td>
                            <td>{{ appointment.patient.firstname }} {{ appointment.patient.name }}</td>
                            <td>{{ appointment.doctor.fullName }}</td>
                            <td>{{ appointment.doctor.clinic }}</td>
                            <td>
                                <select :value="appointment.status" class="form-select form-select-sm"
                                    @change="onStatusChange(appointment.id, $event)">
                                    <option value="pending">pending</option>
                                    <option value="confirmed">confirmed</option>
                                    <option value="cancelled">cancelled</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import {
    createAdminAvailability,
    createAdminClinic,
    createAdminDoctor,
    fetchAdminDashboard,
    updateAdminAppointmentStatus,
} from '../../api/admin.api'

const stats = ref({ clinics: 0, doctors: 0, appointments: 0, patients: 0 })
const clinics = ref([])
const doctors = ref([])
const appointments = ref([])

const clinicForm = ref({ name: '', address: '', phone: '' })
const doctorForm = ref({ fullName: '', specialty: '', phone: '', clinic_id: 0 })
const availabilityForm = ref({ doctor_id: 0, weekday: 1, startTime: '09:00', endTime: '12:00' })

const errorMessage = ref('')
const successMessage = ref('')

const loadDashboard = async () => {
    errorMessage.value = ''
    const data = await fetchAdminDashboard()
    stats.value = data.stats || stats.value
    clinics.value = data.clinics || []
    doctors.value = data.doctors || []
    appointments.value = data.appointments || []

    if (doctorForm.value.clinic_id === 0 && clinics.value.length > 0) {
        doctorForm.value.clinic_id = clinics.value[0].id
    }

    if (availabilityForm.value.doctor_id === 0 && doctors.value.length > 0) {
        availabilityForm.value.doctor_id = doctors.value[0].id
    }
}

const saveClinic = async () => {
    try {
        await createAdminClinic(clinicForm.value)
        clinicForm.value = { name: '', address: '', phone: '' }
        successMessage.value = 'Clinique ajoutee'
        await loadDashboard()
    } catch (error) {
        errorMessage.value = error.message
    }
}

const saveDoctor = async () => {
    try {
        await createAdminDoctor(doctorForm.value)
        doctorForm.value = {
            fullName: '',
            specialty: '',
            phone: '',
            clinic_id: clinics.value[0]?.id || 0,
        }
        successMessage.value = 'Medecin ajoute'
        await loadDashboard()
    } catch (error) {
        errorMessage.value = error.message
    }
}

const saveAvailability = async () => {
    try {
        await createAdminAvailability(availabilityForm.value)
        successMessage.value = 'Disponibilite ajoutee'
        await loadDashboard()
    } catch (error) {
        errorMessage.value = error.message
    }
}

const onStatusChange = async (appointmentId, event) => {
    try {
        await updateAdminAppointmentStatus(appointmentId, event.target.value)
        successMessage.value = 'Statut modifie'
        await loadDashboard()
    } catch (error) {
        errorMessage.value = error.message
    }
}

onMounted(async () => {
    await loadDashboard()
})
</script>
