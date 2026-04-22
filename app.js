// =========================================================
// NYS PARKS & RECREATION - SHARED JAVASCRIPT
// ---------------------------------------------------------
// This file keeps the demo data and a few small UI behaviors
// in one place so the HTML files stay simple.
//
// Later, when PHP / SQL is added, these arrays can be replaced
// with values coming from the database.
// =========================================================

// ----------------------------------------------------------
// DEMO PARK DATA
// ----------------------------------------------------------
const parks = [
  {
    name: "Jones Beach State Park",
    region: "Long Island",
    type: "Beach",
    image: "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80",
    description: "Oceanfront boardwalk, summer concerts, family activities, and wide sandy beaches."
  },
  {
    name: "Letchworth State Park",
    region: "Western New York",
    type: "Waterfalls",
    image: "https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80",
    description: "Dramatic gorge views, trails, scenic overlooks, and some of the state's most iconic waterfalls."
  },
  {
    name: "Niagara Falls State Park",
    region: "Western New York",
    type: "Landmark",
    image: "https://images.unsplash.com/photo-1528909514045-2fa4ac7a08ba?auto=format&fit=crop&w=1200&q=80",
    description: "World-famous waterfalls, observation decks, and unforgettable sightseeing experiences."
  },
  {
    name: "Watkins Glen State Park",
    region: "Finger Lakes",
    type: "Hiking",
    image: "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80",
    description: "Stone bridges, layered waterfalls, gorge trails, and one of New York's signature hikes."
  },
  {
    name: "Montauk Point State Park",
    region: "Long Island",
    type: "Coastal",
    image: "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80",
    description: "Clifftop views, striped lighthouse scenery, fishing spots, and dramatic coastal landscapes."
  },
  {
    name: "Minnewaska State Park Preserve",
    region: "Hudson Valley",
    type: "Trails",
    image: "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80",
    description: "Ridge-top views, lakes, cliffs, carriage roads, and peaceful hiking routes."
  }
];

// ----------------------------------------------------------
// DEMO EVENT DATA
// ----------------------------------------------------------
const events = [
  {
    title: "Summer Concert Series: Rock the Beach",
    category: "Music",
    park: "Jones Beach State Park",
    region: "Long Island",
    dateLabel: "JUL",
    dateDay: "15",
    price: "$45",
    time: "7:00 PM",
    image: "https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1200&q=80"
  },
  {
    title: "Sunrise Yoga by the Lighthouse",
    category: "Wellness",
    park: "Montauk Point State Park",
    region: "Long Island",
    dateLabel: "JUN",
    dateDay: "20",
    price: "$15",
    time: "6:00 AM",
    image: "https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1200&q=80"
  },
  {
    title: "Food Truck Festival",
    category: "Food",
    park: "Eisenhower Park",
    region: "Long Island",
    dateLabel: "AUG",
    dateDay: "05",
    price: "$5",
    time: "11:00 AM",
    image: "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1200&q=80"
  },
  {
    title: "Cross Country 5K",
    category: "Sports",
    park: "Sunken Meadow State Park",
    region: "Long Island",
    dateLabel: "SEP",
    dateDay: "10",
    price: "$30",
    time: "8:30 AM",
    image: "https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=1200&q=80"
  },
  {
    title: "Autumn Leaves Guided Walk",
    category: "Nature",
    park: "Letchworth State Park",
    region: "Western New York",
    dateLabel: "OCT",
    dateDay: "12",
    price: "Free",
    time: "2:00 PM",
    image: "https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80"
  },
  {
    title: "Family Stargazing Night",
    category: "Family",
    park: "Watkins Glen State Park",
    region: "Finger Lakes",
    dateLabel: "AUG",
    dateDay: "18",
    price: "$12",
    time: "8:00 PM",
    image: "https://images.unsplash.com/photo-1532968961962-8a0cb3a2d4f5?auto=format&fit=crop&w=1200&q=80"
  }
];

// ----------------------------------------------------------
// SMALL HELPER: EVENT CATEGORY CLASS
// Used to normalize classes later if needed.
// ----------------------------------------------------------
function normalizeText(value) {
  return String(value).toLowerCase().replace(/\s+/g, "-");
}

// ----------------------------------------------------------
// RENDER FEATURED PARKS
// If a page contains an element with the ID "parks-grid",
// we fill it with demo park cards automatically.
// ----------------------------------------------------------
function renderParks() {
  const grid = document.getElementById("parks-grid");
  if (!grid) return;

  grid.innerHTML = parks.map((park) => `
    <article class="col-md-6 col-xl-4">
      <figure class="image-card h-100 mb-0">
        <img src="${park.image}" alt="${park.name}" class="image-cover-md" />
        <figcaption class="p-4">
          <p class="section-kicker mb-2">${park.region} · ${park.type}</p>
          <h3 class="h5 fw-bold mb-2">${park.name}</h3>
          <p class="text-muted mb-0">${park.description}</p>
        </figcaption>
      </figure>
    </article>
  `).join("");
}

// ----------------------------------------------------------
// RENDER EVENTS
// This works on the Events page and also on the homepage
// if those containers exist.
// ----------------------------------------------------------
function renderEvents(filteredEvents = events) {
  const grid = document.getElementById("events-grid");
  if (!grid) return;

  grid.innerHTML = filteredEvents.map((event) => `
    <article class="col-md-6 col-xl-4">
      <article class="event-card h-100">
        <img src="${event.image}" alt="${event.title}" class="image-cover-event" />
        <section class="p-3 p-lg-4">
          <section class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <section class="event-date-badge text-center py-2 px-1">
              <p class="small text-uppercase text-muted fw-bold mb-1">${event.dateLabel}</p>
              <h3 class="h5 fw-bold mb-0">${event.dateDay}</h3>
            </section>
            <section class="text-end">
              <p class="category-badge mb-1">${event.category}</p>
              <p class="small text-muted mb-0">From ${event.price}</p>
            </section>
          </section>

          <h3 class="h5 fw-bold mb-2">${event.title}</h3>
          <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>${event.park}, ${event.region}</p>
          <p class="small text-muted mb-3"><i class="bi bi-clock me-1"></i>${event.time}</p>

          <a href="client-create-event.html" class="btn btn-success w-100 rounded-pill fw-semibold">
            View Details & Book
          </a>
        </section>
      </article>
    </article>
  `).join("");
}

// ----------------------------------------------------------
// SIMPLE EVENT FILTERING
// The Events page contains buttons with data-category.
// Clicking them re-renders the event list.
// ----------------------------------------------------------
function setupEventFilters() {
  const buttons = document.querySelectorAll("[data-category]");
  if (!buttons.length) return;

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      buttons.forEach((item) => item.classList.remove("active-link"));
      button.classList.add("active-link");

      const category = button.getAttribute("data-category");
      if (category === "All") {
        renderEvents(events);
      } else {
        renderEvents(events.filter((event) => event.category === category));
      }
    });
  });
}

// ----------------------------------------------------------
// ACTIVE NAV LINK HELPER
// Adds the highlight style to the current page automatically
// when a link has a matching data-page attribute.
// ----------------------------------------------------------
function setupActivePage() {
  const pageName = document.body.getAttribute("data-page");
  if (!pageName) return;

  document.querySelectorAll("[data-page-link]").forEach((link) => {
    if (link.getAttribute("data-page-link") === pageName) {
      link.classList.add("active-link");
    }
  });
}

// ----------------------------------------------------------
// PAGE INITIALIZATION
// Run all the small page features after the DOM is loaded.
// ----------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
  setupActivePage();
  renderParks();
  renderEvents(events.slice(0, 3)); // homepage default if container exists

  if (document.getElementById("events-grid")) {
    renderEvents(events);
  }

  setupEventFilters();
  renderNews();
});


// ----------------------------------------------------------
// DEMO NEWS DATA
// ----------------------------------------------------------
const newsItems = [
  {
    title: "Summer trail restoration projects begin statewide",
    category: "Operations",
    date: "June 21, 2026",
    image: "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80",
    excerpt: "Crews are improving trail signage, drainage, and accessibility features at major park destinations."
  },
  {
    title: "Family adventure weekends return to select parks",
    category: "Programs",
    date: "June 18, 2026",
    image: "https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80",
    excerpt: "New seasonal programming includes guided hikes, outdoor skills workshops, and kid-friendly discovery events."
  },
  {
    title: "New waterfront safety updates announced for peak season",
    category: "Safety",
    date: "June 12, 2026",
    image: "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80",
    excerpt: "Updated swim hours, weather alert messaging, and beach operations plans are now available for visitors."
  }
];

// ----------------------------------------------------------
// RENDER NEWS CARDS
// ----------------------------------------------------------
function renderNews() {
  const container = document.getElementById("news-grid");
  if (!container) return;

  container.innerHTML = newsItems.map((item) => `
    <article class="col-md-6 col-xl-4">
      <article class="news-card">
        <img src="${item.image}" alt="${item.title}" />
        <section class="p-4">
          <section class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <span class="news-tag"><i class="bi bi-newspaper"></i>${item.category}</span>
            <span class="small text-muted">${item.date}</span>
          </section>
          <h2 class="h5 fw-bold mb-2">${item.title}</h2>
          <p class="text-muted mb-3">${item.excerpt}</p>
          <a href="events.html" class="map-link text-decoration-none">Read more</a>
        </section>
      </article>
    </article>
  `).join("");
}


// ----------------------------------------------------------
// DEMO PORTAL DATA
// ----------------------------------------------------------
const portalMetrics = {
  adminBookingsByMonth: [42, 56, 64, 78, 88, 71],
  adminSiteTraffic: [1800, 2200, 2600, 3100, 3400, 2900],
  clientBookings: [
    { title: "Sunset Wellness Series", park: "Jones Beach", status: "Approved" },
    { title: "Community Food Fair", park: "Niagara Falls", status: "Pending" },
    { title: "Youth Sports Day", park: "Letchworth", status: "Approved" }
  ],
  employeeShifts: [
    { park: "Jones Beach State Park", date: "06/14/2026", hours: "8:00 AM – 4:00 PM" },
    { park: "Jones Beach State Park", date: "06/15/2026", hours: "9:00 AM – 5:00 PM" },
    { park: "Letchworth State Park", date: "06/18/2026", hours: "7:30 AM – 3:30 PM" }
  ]
};
