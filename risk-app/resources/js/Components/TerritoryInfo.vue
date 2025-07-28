<script setup>
defineProps({
    title: String,
    territory: Object,
});
</script>

<template>
    <div v-if="territory" class="p-3 border rounded-lg bg-gray-100 text-sm">
        <h5 class="font-bold text-md mb-1">{{ title }}</h5>
        <div class="grid grid-cols-2 gap-x-2">
            <span><strong>Territory:</strong></span> <span>{{ territory.territory.name }}</span>
            <span><strong>Continent:</strong></span> <span>{{ territory.territory.continent.name }}</span>
            <span><strong>Owner:</strong></span> <span>{{ territory.player.user.name }}</span>
            <span><strong>Armies:</strong></span> <span class="font-bold">{{ territory.armies }}</span>
        </div>
    </div>
</template>
