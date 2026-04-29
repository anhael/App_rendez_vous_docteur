<template>
    <article class="property-card" :class="{ selected }">
        <button class="property-select" type="button" @click="$emit('select', property.id)">
            <img :src="property.imageUrl" :alt="property.title" class="property-image">

            <div class="property-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <span class="property-city">{{ property.city }}</span>
                        <h3 class="property-title mb-1">{{ property.title }}</h3>
                    </div>
                    <span class="property-rating">★ {{ property.rating }}</span>
                </div>

                <p class="property-tagline mb-2">{{ property.tagLine }}</p>
                <p class="property-description mb-3">{{ property.description || defaultDescription }}</p>

                <div class="property-amenities mb-3">
                    <span v-for="amenity in property.amenities" :key="amenity" class="amenity-pill">
                        {{ amenity }}
                    </span>
                </div>

                <div class="property-footer">
                    <div>
                        <span class="price-value">{{ property.nightLabel }}</span>
                        <span class="price-suffix">/ nuit</span>
                    </div>
                    <div class="property-meta">
                        <span>{{ property.guests }} voyageurs</span>
                        <span>{{ selected ? 'Sélectionné' : 'Disponible' }}</span>
                    </div>
                </div>
            </div>
        </button>

        <div class="property-actions">
            <button class="btn btn-light btn-sm" type="button" @click="$emit('details', property.id)">
                Voir détails
            </button>
            <button class="btn btn-primary btn-sm" type="button" @click="$emit('book', property.id)">
                Réserver
            </button>
        </div>
    </article>
</template>

<script setup>
const defaultDescription = 'Séjour pensé pour mettre en avant une belle expérience utilisateur.'

defineProps({
    property: {
        type: Object,
        required: true,
    },
    selected: {
        type: Boolean,
        default: false,
    },
})
</script>