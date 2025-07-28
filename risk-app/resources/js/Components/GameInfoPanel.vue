<script setup>
import { computed } from 'vue';

const props = defineProps({
    game: Object,
    reinforcements: Number,
    authPlayer: Object,
    errors: Object,
    flash: Object,
    battleLog: Array,
});

const isMyTurn = computed(() => props.game.status === 'playing' && props.game.current_turn_player_id === props.authPlayer?.id);
</script>

<template>
    <div class="bg-white p-4 shadow-md z-10">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">{{ game.name }}</h1>
                <p class="text-gray-600">
                    Turn: <span class="font-semibold">{{ game.current_turn_player?.user.name ?? 'N/A' }}</span> |
                    Phase: <span class="font-semibold capitalize">{{ game.turn_phase ?? 'Finished' }}</span>
                </p>
            </div>
            <div v-if="isMyTurn && game.turn_phase === 'reinforce'" class="text-right">
                <p class="text-lg">Reinforcements: <span class="font-bold text-blue-600">{{ reinforcements }}</span></p>
            </div>
        </div>

        <!-- Battle Log -->
        <div v-if="battleLog && battleLog.length > 0" class="mt-4 space-y-2">
            <h4 class="font-bold text-sm">Battle Report:</h4>
            <ul class="list-disc list-inside bg-gray-100 p-2 rounded-md text-sm max-h-32 overflow-y-auto">
                <li v-for="(log, index) in battleLog" :key="index">{{ log }}</li>
            </ul>
        </div>

        <!-- Flash Messages -->
        <div v-if="flash && flash.success" class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded transition-opacity duration-500" role="alert">
            {{ flash.success }}
        </div>
        <div v-if="flash && flash.error" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded transition-opacity duration-500" role="alert">
            {{ flash.error }}
        </div>

        <!-- Validation Errors -->
        <div v-if="errors && Object.keys(errors).length > 0" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
            <ul>
                <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
            </ul>
        </div>
    </div>
</template>
