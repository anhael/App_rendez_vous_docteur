<template>
    <div v-if="open && property" class="details-backdrop" @click.self="$emit('close')">
        <div class="details-modal">
            <button type="button" class="details-close" @click="$emit('close')" aria-label="Fermer">×</button>

            <img :src="property.imageUrl" :alt="property.title" class="details-image">

            <div class="details-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <span class="property-city">{{ property.city }}</span>
                        <h3 class="h4 mb-1">{{ property.title }}</h3>
                    </div>
                    <span class="property-rating">★ {{ property.rating }}</span>
                </div>

                <p class="text-muted mb-3">{{ property.tagLine }}</p>
                <p class="mb-3">{{ property.description || 'Description indisponible.' }}</p>

                <div class="property-amenities mb-3">
                    <span v-for="amenity in property.amenities" :key="amenity" class="amenity-pill">
                        {{ amenity }}
                    </span>
                </div>

                <div class="details-meta mb-3">
                    <span>{{ property.guests }} voyageurs</span>
                    <span>•</span>
                    <span>{{ property.nightLabel }} / nuit</span>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary" type="button" @click="$emit('book', property.id)">
                        Réserver ce logement
                    </button>
                    <button class="btn btn-light" type="button" @click="$emit('close')">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    property: {
        type: Object,
        default: null,
    },
})
</script>
