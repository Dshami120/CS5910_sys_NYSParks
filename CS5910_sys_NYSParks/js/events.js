<<<<<<< HEAD
document.addEventListener('DOMContentLoaded', function() {
    let currentDate = new Date(2026, 3, 1); 
    let selectedPark = 'all';

    const currentMonthDisplay = document.getElementById('current-month');
    const prevButton = document.getElementById('prev-month');
    const nextButton = document.getElementById('next-month');
    const calendarGrid = document.querySelector('.calendar-grid');
    const filterButtons = document.querySelectorAll('.filter');
    const eventCards = document.querySelectorAll('.event-card');

    // Initialize
    renderCalendar();
    updateMonthDisplay();
    filterEvents();

    // Event listeners
    prevButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
        updateMonthDisplay();
    });

    nextButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
        updateMonthDisplay();
    });

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');
            // Update selected park
            selectedPark = button.dataset.park;
            // Filter events
            filterEvents();
        });
    });

    function renderCalendar() {
        const existingDates = calendarGrid.querySelectorAll('.date');
        existingDates.forEach(date => date.remove());

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());

        // Generate 42 days (6 weeks × 7 days)
        for (let i = 0; i < 42; i++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);

            const dateDiv = document.createElement('div');
            dateDiv.className = 'date';

            if (date.getMonth() === month) {
                dateDiv.textContent = date.getDate();

                // Check if today
                const today = new Date();
                if (date.toDateString() === today.toDateString()) {
                    dateDiv.classList.add('today');
                }
            } else {
                // Empty cell for days outside current month
                dateDiv.classList.add('empty');
            }

            calendarGrid.appendChild(dateDiv);
        }
    }

    function updateMonthDisplay() {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
        currentMonthDisplay.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
    }

    function filterEvents() {
        eventCards.forEach(card => {
            const cardPark = card.dataset.park;
            if (selectedPark === 'all' || cardPark === selectedPark) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
=======
document.addEventListener('DOMContentLoaded', function() {
    let currentDate = new Date(2026, 3, 1); 
    let selectedPark = 'all';

    const currentMonthDisplay = document.getElementById('current-month');
    const prevButton = document.getElementById('prev-month');
    const nextButton = document.getElementById('next-month');
    const calendarGrid = document.querySelector('.calendar-grid');
    const filterButtons = document.querySelectorAll('.filter');
    const eventCards = document.querySelectorAll('.event-card');

    // Initialize
    renderCalendar();
    updateMonthDisplay();
    filterEvents();

    // Event listeners
    prevButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
        updateMonthDisplay();
    });

    nextButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
        updateMonthDisplay();
    });

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');
            // Update selected park
            selectedPark = button.dataset.park;
            // Filter events
            filterEvents();
        });
    });

    function renderCalendar() {
        const existingDates = calendarGrid.querySelectorAll('.date');
        existingDates.forEach(date => date.remove());

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());

        // Generate 42 days (6 weeks × 7 days)
        for (let i = 0; i < 42; i++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);

            const dateDiv = document.createElement('div');
            dateDiv.className = 'date';

            if (date.getMonth() === month) {
                dateDiv.textContent = date.getDate();

                // Check if today
                const today = new Date();
                if (date.toDateString() === today.toDateString()) {
                    dateDiv.classList.add('today');
                }
            } else {
                // Empty cell for days outside current month
                dateDiv.classList.add('empty');
            }

            calendarGrid.appendChild(dateDiv);
        }
    }

    function updateMonthDisplay() {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
        currentMonthDisplay.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
    }

    function filterEvents() {
        eventCards.forEach(card => {
            const cardPark = card.dataset.park;
            if (selectedPark === 'all' || cardPark === selectedPark) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
>>>>>>> c4d9c7e43ed83e0c3eba450b220d5bab494fef50
});