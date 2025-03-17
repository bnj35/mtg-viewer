<script setup>
import { onMounted, ref } from 'vue';
import { fetchAllCards } from '../services/cardService';

const cards = ref([]);
const loadingCards = ref(true);
const currentPage = ref(1);

async function loadCards(page = 1) {
    loadingCards.value = true;
    cards.value = await fetchAllCards(page);
    loadingCards.value = false;
}

function nextPage() {
    currentPage.value += 1;
    loadCards(currentPage.value);
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value -= 1;
        loadCards(currentPage.value);
    }
}

onMounted(() => {
    loadCards();
});
</script>

<template>
    <div>
        <h1>Toutes les cartes</h1>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else>
            <div class="pagination">
                <button type="button" @click="prevPage" :disabled="currentPage === 1">Previous</button>
                <span>Page {{ currentPage }}</span>
                <button type="button" @click="nextPage">Next</button>
            </div>
            <div class="card-result" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} <span>({{ card.uuid }})</span>
                </router-link>
            </div>
        </div>
    </div>
</template>
