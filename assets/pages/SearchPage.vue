<script setup>
import { ref, onMounted, watch } from 'vue';
import { fetchCardByName, fetchSetCodes } from '../services/cardService';

const cards = ref([]);
const loadingCards = ref(false);
const search = ref('');
const setCode = ref('');
const setCodes = ref([]);

async function searchCards(name, edition) {
    if (name.length < 3) {
        cards.value = [];
        return;
    }
    loadingCards.value = true;
    cards.value = await fetchCardByName(name, edition);
    loadingCards.value = false;
}

async function loadSetCodes() {
    setCodes.value = await fetchSetCodes();
}

watch([search, setCode], ([newSearch, newSetCode]) => {
    searchCards(newSearch, newSetCode);
});

onMounted(() => {
    searchCards('', '');
    loadSetCodes();
});
</script>

<template>
    <div>
        <h1>Rechercher une Carte</h1>
        <form>
            <label for="search">Rechercher une carte</label>
            <input id="search" type="text" v-model="search" placeholder="Nom de la carte" />
            <br>
            <label for="setCode">Filtrer par édition</label>
            <select id="setCode" v-model="setCode">
                <option value="">All</option>
                <option v-for="code in setCodes" :key="code.setCode" :value="code.setCode">{{ code.setCode }}</option>
            </select>
        </form>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else>
            <div class="card" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }"> {{ card.name }} - {{ card.uuid }} </router-link>
            </div>
        </div>
    </div>
</template>
