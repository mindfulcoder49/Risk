<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome to MapWars.Online" />
    <div class="bg-gray-900 text-gray-200 min-h-screen flex flex-col font-sans">
        <!-- Header -->
        <header class="container mx-auto px-6 py-4">
            <nav class="flex justify-between items-center">
                <h1 class="text-2xl font-bold tracking-wider text-white">MapWars.Online</h1>
                <div v-if="canLogin" class="space-x-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="text-gray-300 hover:text-yellow-400 transition duration-300"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="text-gray-300 hover:text-yellow-400 transition duration-300"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-2 px-4 rounded-lg transition duration-300"
                        >
                            Register
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            <!-- Hero Section -->
            <section class="text-center py-20 lg:py-32 bg-gray-900">
                <div class="container mx-auto px-6">
                    <h2 class="text-4xl lg:text-6xl font-extrabold text-white leading-tight mb-4">
                        Conquer the World, One Territory at a Time.
                    </h2>
                    <p class="text-lg lg:text-xl text-gray-400 max-w-3xl mx-auto mb-8">
                        A classic game of global domination. Challenge your friends, forge alliances, and betray them all to claim victory.
                    </p>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition duration-300 inline-block"
                    >
                        Get Started for Free
                    </Link>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-20 bg-gray-800">
                <div class="container mx-auto px-6">
                    <h3 class="text-3xl font-bold text-center text-white mb-12">Why You'll Love MapWars</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Feature 1 -->
                        <div class="bg-gray-900 p-8 rounded-lg text-center">
                            <h4 class="text-xl font-bold text-yellow-400 mb-2">Strategic Gameplay</h4>
                            <p class="text-gray-400">
                                Experience the classic rules of Risk. Manage your armies, plan your attacks, and fortify your borders to outwit your opponents.
                            </p>
                        </div>
                        <!-- Feature 2 -->
                        <div class="bg-gray-900 p-8 rounded-lg text-center">
                            <h4 class="text-xl font-bold text-yellow-400 mb-2">Online Multiplayer</h4>
                            <p class="text-gray-400">
                                Create a game and invite up to 5 of your friends for an epic battle of wits. Who will emerge as the ultimate conqueror?
                            </p>
                        </div>
                        <!-- Feature 3 -->
                        <div class="bg-gray-900 p-8 rounded-lg text-center">
                            <h4 class="text-xl font-bold text-yellow-400 mb-2">Claim Your Empire</h4>
                            <p class="text-gray-400">
                                No random starting positions. Engage in a tense, turn-based claiming phase to strategically choose your starting territories.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final CTA -->
            <section class="py-20 bg-gray-900">
                <div class="container mx-auto px-6 text-center">
                    <h3 class="text-3xl font-bold text-white mb-4">Ready to Rule the World?</h3>
                    <p class="text-gray-400 mb-8">Your empire awaits. Sign up now and begin your campaign for global domination.</p>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-3 px-8 rounded-lg text-lg transition duration-300 inline-block"
                    >
                        Play Now
                    </Link>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 py-6">
            <div class="container mx-auto px-6 text-center text-gray-500">
                &copy; 2024 MapWars.Online. All rights reserved.
            </div>
        </footer>
    </div>
</template>
