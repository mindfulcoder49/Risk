<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    games: Array,
});

const form = useForm({
    name: '',
});

const createGame = () => {
    form.post(route('games.store'), {
        onSuccess: () => form.reset('name'),
    });
};
</script>

<template>
    <Head title="Game Lobby" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Game Lobby</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Create Game Form -->
                        <form @submit.prevent="createGame" class="mb-8 p-4 border rounded">
                            <h3 class="text-lg font-bold mb-2">Create a New Game</h3>
                            <div class="flex items-center space-x-2">
                                <input v-model="form.name" type="text" placeholder="New Game Name" class="input input-bordered w-full max-w-xs" required />
                                <button class="btn btn-primary" :disabled="form.processing">Create Game</button>
                            </div>
                             <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </form>

                        <!-- Game List -->
                        <h3 class="text-lg font-bold mb-2">Available Games</h3>
                        <div class="space-y-4">
                            <div v-if="games.length === 0">
                                <p>No games are currently waiting for players. Why not create one?</p>
                            </div>
                            <div v-for="game in games" :key="game.id" class="p-4 border rounded-lg flex justify-between items-center">
                                <div>
                                    <h2 class="font-bold text-xl">{{ game.name }}</h2>
                                    <p>Players: {{ game.players.length }} / 6</p>
                                    <p class="text-sm text-gray-600">Host: {{ game.players[0]?.user.name }}</p>
                                </div>
                                <div class="flex space-x-2">
                                    <Link :href="route('games.join', game.id)" method="post" as="button" class="btn btn-secondary" :disabled="game.players.length >= 6">Join</Link>
                                    <Link :href="route('games.show', game.id)" class="btn">Spectate / Enter</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
