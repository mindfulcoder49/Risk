<script setup>
import { computed, ref, onUnmounted, watch } from 'vue';
import { usePage, router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GameMap from '@/Components/GameMap.vue';
import ActionPanel from '@/Components/ActionPanel.vue';
import GameInfoPanel from '@/Components/GameInfoPanel.vue';
import PlayerList from '@/Components/PlayerList.vue';

const props = defineProps({
    game: Object,
    map: Object,
    reinforcements: Number,
    authPlayerData: Object,
    errors: Object,
    battleLog: Array,
});

const page = usePage();
const authPlayer = computed(() => props.authPlayerData.player);
const flash = computed(() => page.props.flash);
const actionPanelRef = ref(null);

// --- State ---
// Form state is now managed by ActionPanel.vue

// --- Computed Properties ---
const isMyTurn = computed(() => props.game.status === 'playing' && props.game.current_turn_player_id === authPlayer.value?.id);
const currentPhase = computed(() => props.game.turn_phase);

const territoryStateMap = computed(() => {
    const map = new Map();
    if (props.game && props.game.game_territories) {
        props.game.game_territories.forEach(gt => {
            // Store the full game territory object.
            // This ensures all data, including territory_id, is available.
            map.set(gt.territory_id, gt);
        });
    }
    return map;
});

const myTerritories = computed(() => {
    if (!authPlayer.value) return [];
    return props.game.game_territories.filter(gt => gt.player_id === authPlayer.value.id);
});

// --- Methods ---
function doClaimTerritory(territoryId) {
    if (props.game.turn_phase !== 'claim' || !isMyTurn.value) return;
    console.log('[Show.vue] Claiming territory:', territoryId);
    router.post(route('game.action.claim', props.game.id), {
        territory_id: territoryId,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (actionPanelRef.value) {
                actionPanelRef.value.resetForms();
            }
        },
    });
}

function doReinforce(reinforcementsPayload) {
    console.log('[Show.vue] Received reinforce event with payload:', reinforcementsPayload);
    router.post(route('game.action.reinforce', props.game.id), {
        reinforcements: reinforcementsPayload
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (actionPanelRef.value) {
                actionPanelRef.value.resetForms();
            }
        },
    });
}

function doAttack(attackPayload) {
    console.log('[Show.vue] Received attack event with payload:', attackPayload);
    if (!attackPayload.from || !attackPayload.to) return;
    router.post(route('game.action.attack', props.game.id), {
        from_territory_id: attackPayload.from,
        to_territory_id: attackPayload.to,
        armies: attackPayload.armies,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (actionPanelRef.value) {
                // Partial reset: only clear target and armies, not the source
                actionPanelRef.value.resetForms(false);
            }
        },
    });
}

function doEndAttackPhase() {
    router.post(route('game.action.endAttackPhase', props.game.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            if (actionPanelRef.value) {
                actionPanelRef.value.resetForms();
            }
        },
    });
}

function doFortify(fortifyPayload) {
    console.log('[Show.vue] Received fortify event with payload:', fortifyPayload);
    router.post(route('game.action.fortify', props.game.id), {
        from_territory_id: fortifyPayload.from,
        to_territory_id: fortifyPayload.to,
        armies: fortifyPayload.armies,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (actionPanelRef.value) {
                actionPanelRef.value.resetForms();
            }
        },
    });
}

function doEndTurn() {
    // Fortify route without params just ends the turn
    router.post(route('game.action.fortify', props.game.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            if (actionPanelRef.value) {
                actionPanelRef.value.resetForms();
            }
        },
    });
}

function doStartGame() {
    router.post(route('games.start', props.game.id));
}

// --- Polling ---
let pollInterval = null;
watch(() => [props.game.status, isMyTurn.value], ([status, myTurn]) => {
    clearInterval(pollInterval);
    if (status === 'playing' && !myTurn) {
        pollInterval = setInterval(() => {
            router.reload({ only: ['game', 'reinforcements', 'authPlayerData'], preserveScroll: true });
        }, 5000);
    }
}, { immediate: true });

onUnmounted(() => clearInterval(pollInterval));

</script>

<template>
    <Head :title="`Game: ${game.name}`" />
    <AuthenticatedLayout>
        <div class="flex flex-col lg:flex-row h-[calc(100vh-65px)]">
            <!-- Main Content -->
            <main class="flex-1 flex flex-col bg-gray-200">
                <GameInfoPanel :game="game" :reinforcements="reinforcements" :auth-player="authPlayer" :errors="errors" :flash="flash" :battle-log="battleLog" />
                <div class="flex-1 relative">
                    <GameMap
                        :map-data="map"
                        :territory-state="territoryStateMap"
                        :players="game.players"
                    />
                </div>
            </main>

            <!-- Side Panel -->
            <aside class="w-full lg:w-96 bg-white shadow-lg p-4 flex flex-col space-y-4 overflow-y-auto">
                <PlayerList :players="game.players" :current-turn-player-id="game.current_turn_player_id" />

                <ActionPanel
                    ref="actionPanelRef"
                    :game-status="game.status"
                    :is-my-turn="isMyTurn"
                    :phase="currentPhase"
                    :auth-player="authPlayer"
                    :total-reinforcements="reinforcements"
                    :my-territories="myTerritories"
                    :territory-state-map="territoryStateMap"
                    :map-data="map"
                    @start-game="doStartGame"
                    @claim-territory="doClaimTerritory"
                    @reinforce="doReinforce"
                    @attack="doAttack"
                    @end-attack-phase="doEndAttackPhase"
                    @fortify="doFortify"
                    @end-turn="doEndTurn"
                />
            </aside>
        </div>
    </AuthenticatedLayout>
</template>
