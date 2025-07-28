<script setup>
import { LControl } from '@vue-leaflet/vue-leaflet';

defineProps({
    players: Array,
});
</script>

<template>
    <l-control position="bottomright">
        <div class="p-2 bg-white bg-opacity-80 rounded-md shadow-lg">
            <h4 class="font-bold text-sm mb-1 text-center">Players</h4>
            <ul>
                <li
                    v-for="player in players"
                    :key="player.id"
                    class="flex items-center text-xs py-0.5"
                    :class="{ 'opacity-50 line-through': player.status === 'eliminated' }"
                >
                    <span
                        class="w-3 h-3 rounded-full mr-2 border border-gray-600"
                        :style="{ backgroundColor: player.color }"
                    ></span>
                    <span>{{ player.user.name }}</span>
                </li>
            </ul>
        </div>
    </l-control>
</template>
