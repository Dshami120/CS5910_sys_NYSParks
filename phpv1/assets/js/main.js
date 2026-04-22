/*
|--------------------------------------------------------------------------
| Shared front-end starter file
|--------------------------------------------------------------------------
| This file holds shared behaviors that can be reused across pages.
| We keep the JavaScript simple and heavily commented for the capstone.
*/

document.addEventListener('DOMContentLoaded', () => {
    console.log('NYS Parks shared JavaScript loaded.');

    initializeParkFilters();
    initializeParkQuickViewModal();
    initializeEventFilters();
    initializeEventPreviewModal();
    initializeStateMap();
    initializeDonateForm();
    initializeFaqFilters();
    initializeNewsFilters();
    initializeAIGuideChat();
    initializeClientEventForm();
    initializeAdminDashboardCharts();
});

/*
|--------------------------------------------------------------------------
| Parks page filtering
|--------------------------------------------------------------------------
| If the parks page elements are present, allow the user to:
| 1. Search by keyword
| 2. Filter by region
| 3. Filter by park type
| 4. Reset the filters
*/
function initializeParkFilters() {
    const searchInput = document.getElementById('parkSearch');
    const regionFilter = document.getElementById('regionFilter');
    const typeFilter = document.getElementById('typeFilter');
    const resetButton = document.getElementById('resetParksFilters');
    const parkItems = document.querySelectorAll('.park-item');
    const noParksMessage = document.getElementById('noParksMessage');
    const parkCount = document.getElementById('parkCount');
    const parksResultsText = document.getElementById('parksResultsText');

    // Exit early if we are not on parks.php.
    if (!searchInput || !regionFilter || !typeFilter || !resetButton || parkItems.length === 0) {
        return;
    }

    function applyFilters() {
        const keyword = searchInput.value.trim().toLowerCase();
        const selectedRegion = regionFilter.value;
        const selectedType = typeFilter.value;
        let visibleCount = 0;

        parkItems.forEach((item) => {
            const itemSearch = (item.dataset.search || '').toLowerCase();
            const itemRegion = item.dataset.region || '';
            const itemType = item.dataset.type || '';

            const matchesKeyword = keyword === '' || itemSearch.includes(keyword);
            const matchesRegion = selectedRegion === 'all' || itemRegion === selectedRegion;
            const matchesType = selectedType === 'all' || itemType === selectedType;

            if (matchesKeyword && matchesRegion && matchesType) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        // Update small live counters so the page feels more dynamic.
        if (parkCount) {
            parkCount.textContent = visibleCount;
        }

        if (parksResultsText) {
            parksResultsText.textContent = `Showing ${visibleCount} park${visibleCount === 1 ? '' : 's'}`;
        }

        if (noParksMessage) {
            noParksMessage.classList.toggle('d-none', visibleCount !== 0);
        }
    }

    searchInput.addEventListener('input', applyFilters);
    regionFilter.addEventListener('change', applyFilters);
    typeFilter.addEventListener('change', applyFilters);

    resetButton.addEventListener('click', () => {
        searchInput.value = '';
        regionFilter.value = 'all';
        typeFilter.value = 'all';
        applyFilters();
    });

    applyFilters();
}

/*
|--------------------------------------------------------------------------
| Parks page quick-view modal
|--------------------------------------------------------------------------
| Populates the Bootstrap modal with data from the clicked park card.
*/
function initializeParkQuickViewModal() {
    const modal = document.getElementById('parkPreviewModal');
    if (!modal) {
        return;
    }

    modal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        if (!button) {
            return;
        }

        document.getElementById('parkPreviewModalLabel').textContent = button.getAttribute('data-park-name') || 'Park';
        document.getElementById('modalParkRegion').textContent = button.getAttribute('data-park-region') || 'Region';
        document.getElementById('modalParkAddress').textContent = button.getAttribute('data-park-address') || 'Address not available';
        document.getElementById('modalParkHours').textContent = button.getAttribute('data-park-hours') || 'Hours not available';
        document.getElementById('modalParkAmenities').textContent = button.getAttribute('data-park-amenities') || 'Amenities not available';
        document.getElementById('modalParkSummary').textContent = button.getAttribute('data-park-summary') || 'No summary available.';
    });
}

/*
|--------------------------------------------------------------------------
| Events page filtering
|--------------------------------------------------------------------------
| If the events page elements are present, allow the user to:
| 1. Search by keyword
| 2. Filter by park
| 3. Filter by month
| 4. Filter by category
| 5. Reset the filters
*/
function initializeEventFilters() {
    const searchInput = document.getElementById('eventSearch');
    const parkFilter = document.getElementById('eventParkFilter');
    const monthFilter = document.getElementById('eventMonthFilter');
    const categoryFilter = document.getElementById('eventCategoryFilter');
    const resetButton = document.getElementById('resetEventFilters');
    const eventItems = document.querySelectorAll('.event-item');
    const noEventsMessage = document.getElementById('noEventsMessage');
    const eventCount = document.getElementById('eventCount');
    const eventsResultsText = document.getElementById('eventsResultsText');

    // Exit early if we are not on events.php.
    if (!searchInput || !parkFilter || !monthFilter || !categoryFilter || !resetButton || eventItems.length === 0) {
        return;
    }

    function applyFilters() {
        const keyword = searchInput.value.trim().toLowerCase();
        const selectedPark = parkFilter.value;
        const selectedMonth = monthFilter.value;
        const selectedCategory = categoryFilter.value;
        let visibleCount = 0;

        eventItems.forEach((item) => {
            const itemSearch = (item.dataset.search || '').toLowerCase();
            const itemPark = item.dataset.park || '';
            const itemMonth = item.dataset.month || '';
            const itemCategory = item.dataset.category || '';

            const matchesKeyword = keyword === '' || itemSearch.includes(keyword);
            const matchesPark = selectedPark === 'all' || itemPark === selectedPark;
            const matchesMonth = selectedMonth === 'all' || itemMonth === selectedMonth;
            const matchesCategory = selectedCategory === 'all' || itemCategory === selectedCategory;

            if (matchesKeyword && matchesPark && matchesMonth && matchesCategory) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (eventCount) {
            eventCount.textContent = visibleCount;
        }

        if (eventsResultsText) {
            eventsResultsText.textContent = `Showing ${visibleCount} event${visibleCount === 1 ? '' : 's'}`;
        }

        if (noEventsMessage) {
            noEventsMessage.classList.toggle('d-none', visibleCount !== 0);
        }
    }

    searchInput.addEventListener('input', applyFilters);
    parkFilter.addEventListener('change', applyFilters);
    monthFilter.addEventListener('change', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);

    resetButton.addEventListener('click', () => {
        searchInput.value = '';
        parkFilter.value = 'all';
        monthFilter.value = 'all';
        categoryFilter.value = 'all';
        applyFilters();
    });

    applyFilters();
}

/*
|--------------------------------------------------------------------------
| Events page quick-view modal
|--------------------------------------------------------------------------
| Populates the Bootstrap modal with data from the clicked event card.
*/
function initializeEventPreviewModal() {
    const modal = document.getElementById('eventPreviewModal');
    if (!modal) {
        return;
    }

    modal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        if (!button) {
            return;
        }

        document.getElementById('eventPreviewModalLabel').textContent = button.getAttribute('data-event-title') || 'Event';
        document.getElementById('modalEventCategory').textContent = button.getAttribute('data-event-category') || 'Category';
        document.getElementById('modalEventDescription').textContent = button.getAttribute('data-event-description') || 'No description available.';
        document.getElementById('modalEventPark').textContent = button.getAttribute('data-event-park') || 'Park not available';
        document.getElementById('modalEventDate').textContent = button.getAttribute('data-event-date') || 'Date not available';
        document.getElementById('modalEventTime').textContent = button.getAttribute('data-event-time') || 'Time not available';
        document.getElementById('modalEventLocation').textContent = button.getAttribute('data-event-location') || 'Location not available';
        document.getElementById('modalEventType').textContent = button.getAttribute('data-event-type') || 'Type not available';
        document.getElementById('modalEventStatus').textContent = button.getAttribute('data-event-status') || 'Status not available';
    });
}


/*
|--------------------------------------------------------------------------
| Statewide map page
|--------------------------------------------------------------------------
| Uses Leaflet and the park data prepared in map.php.
| Users can:
| 1. Search parks by keyword
| 2. Filter by region
| 3. Click a sidebar item to zoom the map
| 4. Click a marker to open a popup
*/
function initializeStateMap() {
    const mapElement = document.getElementById('statewideMap');
    const searchInput = document.getElementById('mapSearch');
    const regionFilter = document.getElementById('mapRegionFilter');
    const resetButton = document.getElementById('resetMapFilters');
    const resultsList = document.getElementById('mapResultsList');
    const countBadge = document.getElementById('mapCountBadge');
    const emptyMessage = document.getElementById('mapEmptyMessage');

    if (!mapElement || typeof L === 'undefined' || !Array.isArray(window.nysMapParksData)) {
        return;
    }

    const parks = window.nysMapParksData;
    const markers = [];

    const map = L.map('statewideMap', {
        scrollWheelZoom: false
    }).setView([42.9, -75.5], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const bounds = L.latLngBounds([]);

    parks.forEach((park, index) => {
        const lat = parseFloat(park.latitude);
        const lng = parseFloat(park.longitude);

        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return;
        }

        const popupHtml = `
            <div class="map-popup-content">
                <h3>${escapeHtml(park.park_name || 'Park')}</h3>
                <p><strong>Region:</strong> ${escapeHtml(park.region || 'Unknown')}</p>
                <p><strong>Location:</strong> ${escapeHtml((park.city || '') + ', ' + (park.state || 'NY'))}</p>
                <p><strong>Hours:</strong> ${escapeHtml(park.hours || 'See park page')}</p>
                <p><strong>Amenities:</strong> ${escapeHtml(park.amenities || 'Outdoor recreation')}</p>
            </div>
        `;

        const marker = L.marker([lat, lng]).addTo(map).bindPopup(popupHtml);
        marker.parkIndex = index;
        marker.parkRegion = park.region || '';
        marker.parkSearch = `${park.park_name || ''} ${park.city || ''} ${park.region || ''} ${park.amenities || ''}`.toLowerCase();

        markers.push(marker);
        bounds.extend([lat, lng]);
    });

    if (bounds.isValid()) {
        map.fitBounds(bounds, { padding: [30, 30] });
    }

    function applyMapFilters() {
        const keyword = (searchInput?.value || '').trim().toLowerCase();
        const selectedRegion = regionFilter?.value || 'all';
        let visibleCount = 0;
        const visibleBounds = L.latLngBounds([]);

        document.querySelectorAll('.map-result-item').forEach((button) => {
            const itemSearch = (button.dataset.search || '').toLowerCase();
            const itemRegion = button.dataset.region || '';
            const mapIndex = Number(button.dataset.mapIndex);
            const marker = markers.find((m) => m.parkIndex === mapIndex);

            const matchesKeyword = keyword === '' || itemSearch.includes(keyword);
            const matchesRegion = selectedRegion === 'all' || itemRegion === selectedRegion;
            const isVisible = matchesKeyword && matchesRegion;

            button.classList.toggle('d-none', !isVisible);

            if (marker) {
                if (isVisible) {
                    marker.addTo(map);
                    visibleBounds.extend(marker.getLatLng());
                    visibleCount++;
                } else {
                    map.removeLayer(marker);
                }
            }
        });

        if (countBadge) {
            countBadge.textContent = String(visibleCount);
        }

        if (emptyMessage) {
            emptyMessage.classList.toggle('d-none', visibleCount !== 0);
        }

        if (visibleCount > 0 && visibleBounds.isValid()) {
            map.fitBounds(visibleBounds, { padding: [30, 30] });
        }
    }

    if (resultsList) {
        resultsList.addEventListener('click', (event) => {
            const button = event.target.closest('.map-result-item');
            if (!button) {
                return;
            }

            const index = Number(button.dataset.mapIndex);
            const marker = markers.find((m) => m.parkIndex === index);
            if (!marker) {
                return;
            }

            map.setView(marker.getLatLng(), 10, { animate: true });
            marker.openPopup();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyMapFilters);
    }

    if (regionFilter) {
        regionFilter.addEventListener('change', applyMapFilters);
    }

    if (resetButton) {
        resetButton.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (regionFilter) regionFilter.value = 'all';
            applyMapFilters();
        });
    }

    applyMapFilters();

    // Fix sizing if the map loads inside a layout that changes after render.
    setTimeout(() => map.invalidateSize(), 250);
}

/*
|--------------------------------------------------------------------------
| Tiny HTML escape helper for safe popup text
|--------------------------------------------------------------------------
*/
function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}


/*
|--------------------------------------------------------------------------
| Donate page interactions
|--------------------------------------------------------------------------
| Keeps the custom amount field and pill/radio styles in sync.
*/
function initializeDonateForm() {
    const donateForm = document.getElementById('donateForm');
    if (!donateForm) {
        return;
    }

    const amountInputs = donateForm.querySelectorAll('.amount-choice-input');
    const customAmountInput = document.getElementById('custom_amount');
    const pillOptions = donateForm.querySelectorAll('.pill-option');

    function updateAmountCards() {
        amountInputs.forEach((input) => {
            const card = input.closest('.amount-choice-card');
            if (!card) return;

            card.classList.toggle('selected', input.checked);
        });

        if (customAmountInput) {
            const customSelected = donateForm.querySelector('.amount-choice-input[value="custom"]')?.checked;
            customAmountInput.disabled = !customSelected;
            customAmountInput.required = !!customSelected;

            if (customSelected) {
                customAmountInput.focus();
            }
        }
    }

    function updatePills() {
        pillOptions.forEach((option) => {
            const input = option.querySelector('input');
            option.classList.toggle('selected', !!input?.checked);
        });
    }

    amountInputs.forEach((input) => {
        input.addEventListener('change', updateAmountCards);
    });

    pillOptions.forEach((option) => {
        const input = option.querySelector('input');
        if (input) {
            input.addEventListener('change', updatePills);
        }
    });

    updateAmountCards();
    updatePills();
}


/*
|--------------------------------------------------------------------------
| FAQ page filtering
|--------------------------------------------------------------------------
| Lets users search questions by keyword and topic.
*/
function initializeFaqFilters() {
    const searchInput = document.getElementById('faqSearch');
    const topicButtons = document.querySelectorAll('.faq-topic-button');
    const resetButton = document.getElementById('resetFaqFilters');
    const faqItems = document.querySelectorAll('.faq-item');
    const noFaqMessage = document.getElementById('noFaqMessage');
    const faqCount = document.getElementById('faqCount');
    const faqResultsText = document.getElementById('faqResultsText');

    if (!searchInput || !topicButtons.length || !resetButton || !faqItems.length) {
        return;
    }

    let selectedTopic = 'all';

    function applyFilters() {
        const keyword = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        faqItems.forEach((item) => {
            const itemSearch = (item.dataset.search || '').toLowerCase();
            const itemTopic = item.dataset.topic || '';

            const matchesKeyword = keyword === '' || itemSearch.includes(keyword);
            const matchesTopic = selectedTopic === 'all' || itemTopic === selectedTopic;

            if (matchesKeyword && matchesTopic) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (faqCount) {
            faqCount.textContent = visibleCount;
        }

        if (faqResultsText) {
            faqResultsText.textContent = `Showing ${visibleCount} question${visibleCount === 1 ? '' : 's'}`;
        }

        if (noFaqMessage) {
            noFaqMessage.classList.toggle('d-none', visibleCount !== 0);
        }
    }

    topicButtons.forEach((button) => {
        button.addEventListener('click', () => {
            topicButtons.forEach((btn) => btn.classList.remove('active'));
            button.classList.add('active');
            selectedTopic = button.dataset.faqTopic || 'all';
            applyFilters();
        });
    });

    searchInput.addEventListener('input', applyFilters);

    resetButton.addEventListener('click', () => {
        searchInput.value = '';
        selectedTopic = 'all';
        topicButtons.forEach((btn) => btn.classList.remove('active'));
        const allButton = document.querySelector('.faq-topic-button[data-faq-topic="all"]');
        if (allButton) {
            allButton.classList.add('active');
        }
        applyFilters();
    });

    applyFilters();
}


/*
|--------------------------------------------------------------------------
| News page filtering
|--------------------------------------------------------------------------
| Lets users search news posts by keyword and topic.
*/
function initializeNewsFilters() {
    const searchInput = document.getElementById('newsSearch');
    const topicButtons = document.querySelectorAll('.news-topic-button');
    const resetButton = document.getElementById('resetNewsFilters');
    const newsItems = document.querySelectorAll('.news-item');
    const noNewsMessage = document.getElementById('noNewsMessage');
    const newsCount = document.getElementById('newsCount');
    const newsResultsText = document.getElementById('newsResultsText');

    if (!searchInput || !topicButtons.length || !resetButton || !newsItems.length) {
        return;
    }

    let selectedTopic = 'all';

    function applyFilters() {
        const keyword = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        newsItems.forEach((item) => {
            const itemSearch = (item.dataset.search || '').toLowerCase();
            const itemTopic = item.dataset.topic || '';

            const matchesKeyword = keyword === '' || itemSearch.includes(keyword);
            const matchesTopic = selectedTopic === 'all' || itemTopic === selectedTopic;

            if (matchesKeyword && matchesTopic) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (newsCount) {
            newsCount.textContent = visibleCount;
        }

        if (newsResultsText) {
            newsResultsText.textContent = `Showing ${visibleCount} update${visibleCount === 1 ? '' : 's'}`;
        }

        if (noNewsMessage) {
            noNewsMessage.classList.toggle('d-none', visibleCount !== 0);
        }
    }

    topicButtons.forEach((button) => {
        button.addEventListener('click', () => {
            topicButtons.forEach((btn) => btn.classList.remove('active'));
            button.classList.add('active');
            selectedTopic = button.dataset.newsTopic || 'all';
            applyFilters();
        });
    });

    searchInput.addEventListener('input', applyFilters);

    resetButton.addEventListener('click', () => {
        searchInput.value = '';
        selectedTopic = 'all';
        topicButtons.forEach((btn) => btn.classList.remove('active'));
        const allButton = document.querySelector('.news-topic-button[data-news-topic="all"]');
        if (allButton) {
            allButton.classList.add('active');
        }
        applyFilters();
    });

    applyFilters();
}


/*
|--------------------------------------------------------------------------
| AI Guide page chat
|--------------------------------------------------------------------------
| Sends messages from ai-guide.php to api/chatbot.php using fetch.
| The real OpenAI request happens on the server in PHP.
*/
function initializeAIGuideChat() {
    const chatForm = document.getElementById('aiChatForm');
    const chatInput = document.getElementById('aiChatInput');
    const chatMessages = document.getElementById('aiChatMessages');
    const chatStatus = document.getElementById('aiChatStatus');
    const clearButton = document.getElementById('clearAiChat');
    const promptChips = document.querySelectorAll('.ai-prompt-chip');

    if (!chatForm || !chatInput || !chatMessages) {
        return;
    }

    // Keep a tiny local history array so the assistant can remember a few recent turns.
    const conversationHistory = [];

    function addMessage(role, text, extraClass = '') {
        const wrapper = document.createElement('div');
        wrapper.className = `ai-message ai-message-${role} ${extraClass}`.trim();

        const label = document.createElement('div');
        label.className = 'ai-message-label';
        label.textContent = role === 'user' ? 'You' : 'AI Guide';

        const bubble = document.createElement('div');
        bubble.className = 'ai-message-bubble';
        bubble.textContent = text;

        wrapper.appendChild(label);
        wrapper.appendChild(bubble);
        chatMessages.appendChild(wrapper);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        return wrapper;
    }

    function setStatus(text, isError = false) {
        if (!chatStatus) {
            return;
        }

        chatStatus.textContent = text;
        chatStatus.classList.toggle('text-danger', isError);
        chatStatus.classList.toggle('text-muted', !isError);
    }

    promptChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            chatInput.value = chip.dataset.prompt || '';
            chatInput.focus();
        });
    });

    if (clearButton) {
        clearButton.addEventListener('click', () => {
            conversationHistory.length = 0;
            chatMessages.innerHTML = '';
            addMessage('assistant', 'Hi! I can help you explore New York State parks, compare destinations, and suggest ideas for hiking, family trips, beaches, waterfalls, and more.');
            setStatus('Chat cleared. Start a new question anytime.');
            chatInput.focus();
        });
    }

    chatForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const message = chatInput.value.trim();
        if (!message) {
            setStatus('Please enter a question before sending.', true);
            return;
        }

        addMessage('user', message);
        conversationHistory.push({ role: 'user', content: message });
        chatInput.value = '';
        chatInput.disabled = true;

        const loadingMessage = addMessage('assistant', 'Thinking...', 'ai-message-loading');
        setStatus('Sending your question to the chatbot...');

        try {
            const response = await fetch('api/chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message,
                    history: conversationHistory.slice(-6)
                })
            });

            const data = await response.json();
            loadingMessage.remove();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Chatbot request failed.');
            }

            addMessage('assistant', data.reply);
            conversationHistory.push({ role: 'assistant', content: data.reply });

            if (data.mode === 'live') {
                setStatus('Live OpenAI response received from the PHP backend.');
            } else {
                setStatus('Demo fallback response shown because the live API key is not configured yet.');
            }
        } catch (error) {
            loadingMessage.remove();
            addMessage('assistant', 'Sorry, something went wrong while contacting the chatbot. Please try again.');
            setStatus(error.message || 'Something went wrong.', true);
        } finally {
            chatInput.disabled = false;
            chatInput.focus();
        }
    });
}


/*
|--------------------------------------------------------------------------
| Client create event page
|--------------------------------------------------------------------------
| Filters the field dropdown so the client only sees fields for the selected
| park. This keeps the form easier to understand.
*/
function initializeClientEventForm() {
    const parkSelect = document.getElementById('park_id');
    const fieldSelect = document.getElementById('field_id');
    const capacityText = document.getElementById('fieldCapacityText');
    const guestInput = document.getElementById('guest_count');
    const fields = Array.isArray(window.clientPortalFields) ? window.clientPortalFields : [];

    if (!parkSelect || !fieldSelect) {
        return;
    }

    function renderFields() {
        const selectedParkId = parkSelect.value;
        const selectedFieldId = window.clientSelectedFieldId || '';
        fieldSelect.innerHTML = '<option value="">Choose a field</option>';

        const matchingFields = fields.filter((field) => String(field.park_id) === String(selectedParkId));

        matchingFields.forEach((field) => {
            const option = document.createElement('option');
            option.value = field.field_id;
            option.textContent = `${field.field_name} (${field.field_type || 'Field'})`;
            option.dataset.capacity = field.capacity || '';

            if (String(selectedFieldId) === String(field.field_id)) {
                option.selected = true;
            }

            fieldSelect.appendChild(option);
        });

        updateCapacityText();
    }

    function updateCapacityText() {
        const selectedOption = fieldSelect.options[fieldSelect.selectedIndex];
        const capacity = selectedOption ? selectedOption.dataset.capacity : '';

        if (capacity) {
            capacityText.textContent = `Selected field capacity: ${capacity} guests.`;
            if (guestInput) {
                guestInput.setAttribute('max', capacity);
            }
        } else {
            capacityText.textContent = 'Choose a park first to see matching fields.';
            if (guestInput) {
                guestInput.removeAttribute('max');
            }
        }
    }

    parkSelect.addEventListener('change', () => {
        window.clientSelectedFieldId = '';
        renderFields();
    });

    fieldSelect.addEventListener('change', updateCapacityText);
    renderFields();
}


/*
|--------------------------------------------------------------------------
| Admin dashboard charts
|--------------------------------------------------------------------------
| Uses Chart.js on the admin dashboard. The range dropdown swaps between
| 7 / 30 / 90 day demo analytics data without a page refresh.
*/
function initializeAdminDashboardCharts() {
    const rangeSelect = document.getElementById('adminAnalyticsRange');
    const siteCanvas = document.getElementById('adminSiteTrafficChart');
    const parkCanvas = document.getElementById('adminParkTrafficChart');
    const siteMetric = document.getElementById('siteTrafficMetric');
    const parkMetric = document.getElementById('parkTrafficMetric');
    const analytics = window.adminDashboardAnalytics || null;
    const defaultRange = window.adminDashboardDefaultRange || '30';

    if (!rangeSelect || !siteCanvas || !parkCanvas || typeof Chart === 'undefined' || !analytics) {
        return;
    }

    let siteChart = null;
    let parkChart = null;

    function formatNumber(value) {
        return Number(value || 0).toLocaleString();
    }

    function buildCharts(rangeKey) {
        const data = analytics[rangeKey] || analytics[defaultRange];
        if (!data) {
            return;
        }

        const totalSite = (data.siteTraffic || []).reduce((sum, current) => sum + Number(current || 0), 0);
        const totalPark = (data.parkTraffic || []).reduce((sum, current) => sum + Number(current.count || 0), 0);

        if (siteMetric) {
            siteMetric.textContent = formatNumber(totalSite);
        }
        if (parkMetric) {
            parkMetric.textContent = formatNumber(totalPark);
        }

        if (siteChart) {
            siteChart.destroy();
        }
        if (parkChart) {
            parkChart.destroy();
        }

        siteChart = new Chart(siteCanvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Site traffic',
                        data: data.siteTraffic,
                        borderWidth: 3,
                        tension: 0.35,
                        fill: false,
                    },
                    {
                        label: 'Event bookings',
                        data: data.bookings,
                        borderWidth: 3,
                        tension: 0.35,
                        fill: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });

        parkChart = new Chart(parkCanvas, {
            type: 'bar',
            data: {
                labels: data.parkTraffic.map(item => item.park),
                datasets: [
                    {
                        label: 'Park traffic',
                        data: data.parkTraffic.map(item => item.count),
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    }

    rangeSelect.addEventListener('change', () => buildCharts(rangeSelect.value));
    buildCharts(defaultRange);
}
