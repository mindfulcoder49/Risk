<script setup>
import {
    LMap,
    LTileLayer,
    LGeoJson,
    LMarker,
    LTooltip,
} from "@vue-leaflet/vue-leaflet";
import "leaflet/dist/leaflet.css";
import { computed } from "vue";
import { divIcon } from "leaflet";
import MapLegend from './MapLegend.vue';

const props = defineProps({
    mapData: Object,
    territoryState: Map,
    players: Array,
});

const markersToRender = computed(() => {
    if (!props.mapData?.territories) {
        return [];
    }
    // Render a marker for every territory that has coordinates, regardless of claim status.
    const markers = props.mapData.territories.filter(territory =>
        territory.geo_data?.lat && territory.geo_data?.lng
    );
    console.log(`--- [GameMap.vue] Calculated ${markers.length} total markers to render.`);
    return markers;
});

const getArmyIcon = (territoryState, territoryName) => {
    const armyCount = territoryState ? territoryState.armies : 0;
    const color = territoryState ? territoryState.player.color : '#CCCCCC'; // Grey for unclaimed

    const armyCountDisplay = `<div class="army-count-container" style="background-color: ${color};">${armyCount}</div>`;

    return divIcon({
        html: `
            <div class="army-marker-content">
                ${armyCountDisplay}
                <div class="territory-name-label">${territoryName}</div>
            </div>
        `,
        className: 'army-marker',
        iconSize: [80, 45],
        iconAnchor: [40, 45],
    });
};

const geoJsonOptions = computed(() => ({
    style: (feature) => {
        const territoryId = feature.properties.id;
        const state = props.territoryState.get(territoryId);
        const ownerColor = state ? state.player.color : '#CCCCCC'; // Default grey

        return {
            fillColor: ownerColor,
            weight: 1,
            opacity: 1,
            color: '#333',
            fillOpacity: 0.7,
        };
    },
    onEachFeature: (feature, layer) => {
        // Bind a tooltip to each territory layer
        layer.bindTooltip(feature.properties.name, {
            permanent: false,
            sticky: true,
        });
    }
}));

</script>

<template>
    <l-map
        :center="[20, 0]"
        :zoom="2"
        :min-zoom="2"
        :max-bounds="[[-90, -180], [90, 180]]"
        style="height: 100%; width: 100%; background-color: #A6CDE4;"
    >
        <l-tile-layer
            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            layer-type="base"
            name="OpenStreetMap"
            attribution="&copy; OpenStreetMap contributors"
        ></l-tile-layer>

        <MapLegend :players="players" />

        <template v-for="territory in markersToRender" :key="territory.id">
             <l-marker
                :lat-lng="[territory.geo_data.lat, territory.geo_data.lng]"
                :icon="getArmyIcon(
                    territoryState.get(territory.id),
                    territory.name
                )"
            >
            </l-marker>
        </template>
    </l-map>
</template>

<style>
.army-marker {
    background: none;
    border: none;
}
.army-marker-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.army-count-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    color: white;
    font-weight: bold;
    font-size: 16px;
    border: 2px solid white;
    box-shadow: 0 0 5px rgba(0,0,0,0.5);
    pointer-events: none; /* Allow clicks to pass through to the map */
    /* Add a small margin to separate it from the name label */
    margin-bottom: 2px;
}
.territory-name-label {
    margin-top: 4px;
    padding: 1px 5px;
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 4px;
    color: #333;
    font-size: 10px;
    font-weight: bold;
    white-space: nowrap;
    pointer-events: none;
}
</style>
