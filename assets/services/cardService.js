export async function fetchAllCards() {
    const response = await fetch('/api/card/all');
    if (!response.ok) throw new Error('Failed to fetch cards');
    const result = await response.json();
    return result;
}

export async function fetchCard(uuid) {
    const response = await fetch(`/api/card/${uuid}`);
    if (response.status === 404) return null;
    if (!response.ok) throw new Error('Failed to fetch card');
    const card = await response.json();
    card.text = card.text.replaceAll('\\n', '\n');
    return card;
}

export async function fetchCardByName(name, setCode = '') {
    const response = await fetch(`/api/card/search/${name}?setCode=${setCode}`);
    if (response.status === 404) return [];
    if (!response.ok) throw new Error('Failed to fetch cards');
    const cards = await response.json();
    return cards;
}

export async function fetchSetCodes() {
    const response = await fetch('/api/card/setcodes');
    if (!response.ok) throw new Error('Failed to fetch set codes');
    const setCodes = await response.json();
    return setCodes;
}
