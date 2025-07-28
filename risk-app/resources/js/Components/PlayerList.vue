<script setup>
defineProps({
    players: Array,
    currentTurnPlayerId: Number,
});
</script>

<template>
    <div class="p-4 border rounded-lg bg-gray-50">
        <h3 class="text-xl font-bold mb-2">Players</h3>
        <ul>
            <li
                v-for="player in players"
                :key="player.id"
                class="flex items-center justify-between p-2 rounded"
                :class="{
                    'bg-yellow-200 font-bold': player.id === currentTurnPlayerId,
                    'opacity-50': player.status === 'eliminated'
                }"
            >
                <div class="flex items-center">
                    <span
                        class="w-4 h-4 rounded-full mr-3 border border-gray-400"
                        :style="{ backgroundColor: player.color }"
                    ></span>
                    <span>{{ player.user.name }}</span>
                </div>
                <span v-if="player.status === 'eliminated'" class="text-sm text-red-600">Eliminated</span>
            </li>
        </ul>
    </div>
</template>
