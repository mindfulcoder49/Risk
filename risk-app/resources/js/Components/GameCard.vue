<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    game: Object,
});

const isMyTurn = computed(() => {
    const { auth } = usePage().props;
    if (!props.game.current_turn_player || !auth.user) {
        return false;
    }
    return props.game.current_turn_player.user_id === auth.user.id;
});
</script>

<template>
    <div class="p-4 border rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow flex justify-between items-center"
         :class="{ 'border-l-4 border-yellow-400': game.status === 'playing' && isMyTurn }">
        <div>
            <h3 class="font-bold text-lg">{{ game.name }}</h3>
            <p class="text-sm text-gray-600">Players: {{ game.players.length }} / 6</p>
            <p v-if="game.status === 'playing' && game.current_turn_player" class="text-sm text-gray-600 mt-1">
                Turn: <span class="font-semibold">{{ game.current_turn_player.user.name }}</span>
            </p>
             <p v-if="game.status === 'playing' && isMyTurn" class="text-sm font-bold text-yellow-600 mt-1">
                It's your turn!
            </p>
        </div>
        <Link :href="route('games.show', game.id)" class="btn btn-primary">Enter Game</Link>
    </div>
</template>

<script>
// This is needed to use usePage in the template script setup block
import { usePage } from '@inertiajs/vue3';
</script>
