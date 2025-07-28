<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import GameCard from '@/Components/GameCard.vue';

defineProps({
    activeGames: Array,
    waitingGames: Array,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-2xl font-bold mb-4">Welcome back, {{ $page.props.auth.user.name }}!</h3>
                        <p class="mb-4">Ready for your next move? You can find a new game or create your own in the lobby.</p>
                        <Link :href="route('games.index')" class="btn btn-primary btn-lg">Go to Game Lobby</Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Active Games -->
                    <div>
                        <h3 class="text-xl font-bold mb-4">My Active Games</h3>
                        <div v-if="activeGames.length > 0" class="space-y-4">
                            <GameCard v-for="game in activeGames" :key="game.id" :game="game" />
                        </div>
                        <p v-else class="text-gray-500">You have no active games.</p>
                    </div>

                    <!-- Waiting Games -->
                    <div>
                        <h3 class="text-xl font-bold mb-4">My Games in Lobby</h3>
                        <div v-if="waitingGames.length > 0" class="space-y-4">
                            <GameCard v-for="game in waitingGames" :key="game.id" :game="game" />
                        </div>
                        <p v-else class="text-gray-500">You are not in any games that are waiting for players.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
