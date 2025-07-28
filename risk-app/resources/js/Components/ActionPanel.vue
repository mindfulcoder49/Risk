<script setup>
import { computed, watch, reactive } from 'vue';

const props = defineProps({
    gameStatus: String,
    isMyTurn: Boolean,
    phase: String,
    authPlayer: Object,
    totalReinforcements: Number,
    myTerritories: Array,
    territoryStateMap: Map,
});

const emit = defineEmits([
    'startGame',
    'reinforce',
    'attack',
    'endAttackPhase',
    'fortify',
    'endTurn',
]);

// --- Local State for Forms ---
const reinforcementForm = reactive({});
const attackForm = reactive({ from: null, to: null, armies: 1 });
const fortifyForm = reactive({ from: null, to: null, armies: 1 });

function resetForms(fullReset = true) {
    if (fullReset) {
        Object.keys(reinforcementForm).forEach(key => delete reinforcementForm[key]);
        attackForm.from = null;
        fortifyForm.from = null;
    }
    attackForm.to = null;
    attackForm.armies = 1;
    fortifyForm.to = null;
    fortifyForm.armies = 1;
}

defineExpose({
    resetForms
});

// --- Event Handlers ---
function handleReinforce() {
    const payload = Object.entries(reinforcementForm)
        .filter(([, armies]) => parseInt(armies) > 0)
        .map(([territory_id, armies]) => ({
            territory_id: parseInt(territory_id),
            armies: parseInt(armies)
        }));
    console.log('[ActionPanel] Emitting reinforce:', payload);
    emit('reinforce', payload);
}

function handleAttack() {
    console.log('[ActionPanel] Emitting attack:', attackForm);
    emit('attack', { ...attackForm });
}

function handleFortify() {
    console.log('[ActionPanel] Emitting fortify:', fortifyForm);
    emit('fortify', { ...fortifyForm });
}

// --- Reinforcement ---
const placedReinforcements = computed(() => {
    return Object.values(reinforcementForm).reduce((sum, count) => sum + (parseInt(count) || 0), 0);
});
const canReinforce = computed(() => {
    return placedReinforcements.value === props.totalReinforcements && props.totalReinforcements > 0;
});

// --- Attack ---
const attackFromOptions = computed(() => props.myTerritories.filter(t => t.armies > 1));
const attackToOptions = computed(() => {
    if (!attackForm.from) return [];
    const fromTerritory = props.territoryStateMap.get(attackForm.from);
    if (!fromTerritory || !fromTerritory.territory.adjacencies) return [];

    const adjacentIds = fromTerritory.territory.adjacencies.map(adj => adj.id);

    return adjacentIds
        .map(id => props.territoryStateMap.get(id))
        .filter(t => t && t.player.id !== props.authPlayer.id);
});
const maxAttackArmies = computed(() => {
    if (!attackForm.from) return 1;
    const fromTerritory = props.territoryStateMap.get(attackForm.from);
    return Math.min(3, fromTerritory.armies - 1);
});
watch(() => attackForm.from, () => {
    attackForm.to = null; // Reset target when source changes
    attackForm.armies = 1;
});

// --- Fortify ---
const fortifyFromOptions = computed(() => props.myTerritories.filter(t => t.armies > 1));
const fortifyToOptions = computed(() => {
    if (!fortifyForm.from) return [];

    const fromTerritory = props.territoryStateMap.get(fortifyForm.from);
    if (!fromTerritory || !fromTerritory.territory.adjacencies) {
        return [];
    }

    const adjacentIds = fromTerritory.territory.adjacencies.map(adj => adj.id);

    // Filter adjacent territories to only those also owned by the player.
    return adjacentIds
        .map(id => props.territoryStateMap.get(id))
        .filter(t => t && t.player.id === props.authPlayer.id);
});
const maxFortifyArmies = computed(() => {
    if (!fortifyForm.from) return 1;
    const fromTerritory = props.territoryStateMap.get(fortifyForm.from);
    return fromTerritory.armies - 1;
});
watch(() => fortifyForm.from, () => {
    fortifyForm.to = null; // Reset target when source changes
    fortifyForm.armies = 1;
});

</script>

<template>
    <div class="p-4 border rounded-lg bg-gray-50 h-full flex flex-col">
        <h3 class="text-xl font-bold mb-4 text-center">Actions</h3>

        <!-- WAITING / FINISHED -->
        <div v-if="gameStatus === 'waiting'">
            <p class="text-center mb-4">Waiting for players to join...</p>
            <button v-if="authPlayer && authPlayer.turn_order === 1" @click="$emit('startGame')" class="btn btn-primary w-full">Start Game</button>
        </div>
        <div v-else-if="gameStatus === 'finished'">
            <h4 class="text-lg font-semibold text-center">Game Over!</h4>
            <!-- Note: game.winner is not a prop here, so this needs to be passed or handled differently if needed -->
        </div>

        <!-- PLAYING -->
        <div v-else-if="isMyTurn" class="flex-grow flex flex-col">
            <!-- CLAIM -->
            <div v-if="phase === 'claim'" class="space-y-4">
                <h4 class="font-bold">Claim Phase</h4>
                <p>Click on an unowned territory on the map to claim it.</p>
            </div>

            <!-- SETUP REINFORCE -->
            <div v-else-if="phase === 'setup_reinforce'" class="space-y-4">
                <h4 class="font-bold">Placement Phase</h4>
                <p>Place your remaining armies on your territories.</p>
                <!-- @todo: Implement setup reinforcement form -->
            </div>

            <!-- REINFORCE -->
            <div v-else-if="phase === 'reinforce'" class="space-y-4">
                <h4 class="font-bold">Reinforcement Phase</h4>
                <p>You have <span class="font-bold">{{ totalReinforcements }}</span> reinforcements to place.</p>
                <p>Placed: {{ placedReinforcements }} / {{ totalReinforcements }}</p>
                <div class="space-y-2 max-h-64 overflow-y-auto p-2 border rounded">
                    <div v-for="territory in myTerritories" :key="territory.id" class="form-control">
                         <label class="label py-1">
                            <span class="label-text">{{ territory.territory.name }} ({{ territory.armies }})</span>
                        </label>
                        <input type="number" min="0" v-model.number="reinforcementForm[territory.territory_id]" placeholder="0" class="input input-bordered input-sm w-full" />
                    </div>
                </div>
                <button @click="handleReinforce" :disabled="!canReinforce" class="btn btn-primary w-full">Confirm Reinforcements</button>
            </div>

            <!-- ATTACK -->
            <div v-else-if="phase === 'attack'" class="space-y-4">
                <h4 class="font-bold">Attack Phase</h4>
                <!-- From -->
                <div class="form-control">
                    <label class="label"><span class="label-text">Attack From:</span></label>
                    <select v-model="attackForm.from" class="select select-bordered">
                        <option :value="null" disabled>Select Territory</option>
                        <option v-for="t in attackFromOptions" :key="t.territory_id" :value="t.territory_id">
                            {{ t.territory.name }} ({{ t.armies }} armies)
                        </option>
                    </select>
                </div>
                <!-- To -->
                <div class="form-control">
                    <label class="label"><span class="label-text">Attack To:</span></label>
                    <select v-model="attackForm.to" class="select select-bordered" :disabled="!attackForm.from">
                        <option :value="null" disabled>Select Target</option>
                        <option v-for="t in attackToOptions" :key="t.territory_id" :value="t.territory_id">
                            {{ t.territory.name }} ({{ t.armies }} armies)
                        </option>
                    </select>
                </div>
                <!-- Armies -->
                <div class="form-control">
                     <label class="label"><span class="label-text">Armies to attack with: ({{ attackForm.armies }})</span></label>
                     <input type="range" :min="1" :max="maxAttackArmies" v-model.number="attackForm.armies" class="range range-primary" :disabled="!attackForm.from" />
                </div>
                <button @click="handleAttack" :disabled="!attackForm.from || !attackForm.to" class="btn btn-error w-full">Attack!</button>
                <button @click="$emit('endAttackPhase')" class="btn btn-primary w-full">End Attack Phase</button>
            </div>

            <!-- FORTIFY -->
            <div v-else-if="phase === 'fortify'" class="space-y-4">
                <h4 class="font-bold">Fortify Phase</h4>
                <p class="text-sm">You may move armies once before ending your turn.</p>
                 <!-- From -->
                <div class="form-control">
                    <label class="label"><span class="label-text">Move From:</span></label>
                    <select v-model="fortifyForm.from" class="select select-bordered">
                        <option :value="null" disabled>Select Territory</option>
                        <option v-for="t in fortifyFromOptions" :key="t.territory_id" :value="t.territory_id">
                            {{ t.territory.name }} ({{ t.armies }} armies)
                        </option>
                    </select>
                </div>
                <!-- To -->
                <div class="form-control">
                    <label class="label"><span class="label-text">Move To:</span></label>
                    <select v-model="fortifyForm.to" class="select select-bordered" :disabled="!fortifyForm.from">
                        <option :value="null" disabled>Select Target</option>
                         <option v-for="t in fortifyToOptions" :key="t.territory_id" :value="t.territory_id">
                            {{ t.territory.name }}
                        </option>
                    </select>
                </div>
                 <!-- Armies -->
                <div class="form-control">
                     <label class="label"><span class="label-text">Armies to move: ({{ fortifyForm.armies }})</span></label>
                     <input type="range" :min="1" :max="maxFortifyArmies" v-model.number="fortifyForm.armies" class="range range-primary" :disabled="!fortifyForm.from" />
                </div>
                <button @click="handleFortify" :disabled="!fortifyForm.from || !fortifyForm.to" class="btn btn-accent w-full">Fortify & End Turn</button>
                <button @click="$emit('endTurn')" class="btn btn-primary w-full">Skip Fortify & End Turn</button>
            </div>
        </div>

        <!-- NOT MY TURN -->
        <div v-else>
            <p class="text-center">It's not your turn.</p>
            <p v-if="phase === 'claim'" class="text-center text-sm text-gray-500">Waiting for other players to claim territories.</p>
        </div>
    </div>
</template>
