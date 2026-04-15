<<<<<<< HEAD
        let map;
        let markers = [];
        let infoWindow;

        const parks = [
            {
                name: "Jones Beach State Park",
                type: "beach",
                lat: 40.6066,
                lng: -73.5117,
                description: "Beautiful beach park with boardwalk, playgrounds, and water activities.",
                address: "Jones Beach State Pkwy, Wantagh, NY 11793",
                amenities: ["Beach", "Boardwalk", "Playgrounds", "Fishing", "Camping"]
            },
            {
                name: "Bear Mountain State Park",
                type: "mountain",
                lat: 41.3165,
                lng: -73.9868,
                description: "Scenic mountain park with hiking trails, zoo, and Hudson River views.",
                address: "Bear Mountain State Park, Bear Mountain, NY 10911",
                amenities: ["Hiking", "Zoo", "Camping", "Fishing", "Picnic Areas"]
            },
            {
                name: "Robert Moses State Park",
                type: "beach",
                lat: 40.6368,
                lng: -73.2551,
                description: "Long Island beach park with camping, fishing, and water sports.",
                address: "Robert Moses State Park, Babylon, NY 11702",
                amenities: ["Beach", "Camping", "Fishing", "Boating", "Playgrounds"]
            },
            {
                name: "Letchworth State Park",
                type: "mountain",
                lat: 42.4347,
                lng: -78.0519,
                description: "Known as the 'Grand Canyon of the East' with stunning gorges and waterfalls.",
                address: "Letchworth State Park, Mount Morris, NY 14510",
                amenities: ["Hiking", "Camping", "Fishing", "Scenic Views", "Photography"]
            },
            {
                name: "Saratoga Spa State Park",
                type: "lake",
                lat: 43.0326,
                lng: -73.7672,
                description: "Mineral springs park with pools, golf course, and Saratoga Race Course nearby.",
                address: "Saratoga Spa State Park, Saratoga Springs, NY 12866",
                amenities: ["Swimming Pools", "Golf", "Camping", "Mineral Springs", "Pavilion"]
            },
            {
                name: "Stony Brook State Park",
                type: "lake",
                lat: 40.9126,
                lng: -73.1393,
                description: "Lake park with beaches, camping, and recreational activities.",
                address: "Stony Brook State Park, Stony Brook, NY 11790",
                amenities: ["Lake", "Beach", "Camping", "Fishing", "Boating"]
            },
            {
                name: "Washington's Headquarters State Historic Site",
                type: "historical",
                lat: 41.4031,
                lng: -73.6062,
                description: "Historic site where George Washington headquartered during Revolutionary War.",
                address: "Washington's Headquarters State Historic Site, Newburgh, NY 12550",
                amenities: ["Historic Buildings", "Museum", "Guided Tours", "Picnic Areas"]
            },
            {
                name: "Clinton House State Historic Site",
                type: "historical",
                lat: 42.6526,
                lng: -73.7572,
                description: "Home of DeWitt Clinton, featuring period furnishings and gardens.",
                address: "Clinton House State Historic Site, Poughkeepsie, NY 12601",
                amenities: ["Historic House", "Gardens", "Museum", "Educational Programs"]
            },
            {
                name: "Niagara Falls State Park",
                type: "historical",
                lat: 43.0962,
                lng: -79.0377,
                description: "Home to the famous Niagara Falls, with observation decks and historic sites.",
                address: "Niagara Falls State Park, Niagara Falls, NY 14303",
                amenities: ["Waterfalls", "Observation Decks", "Boat Tours", "Historic Sites"]
            },
            {
                name: "Watkins Glen State Park",
                type: "mountain",
                lat: 42.3759,
                lng: -76.8708,
                description: "Gorge with 19 waterfalls and stone bridges.",
                address: "Watkins Glen State Park, Watkins Glen, NY 14891",
                amenities: ["Hiking", "Waterfalls", "Photography", "Picnic Areas"]
            },
            {
                name: "Allegany State Park",
                type: "mountain",
                lat: 42.0906,
                lng: -78.8547,
                description: "Largest state park in NY, with mountains, lakes, and forests.",
                address: "Allegany State Park, Salamanca, NY 14779",
                amenities: ["Hiking", "Camping", "Fishing", "Boating", "Skiing"]
            },
            {
                name: "Minnewaska State Park Preserve",
                type: "mountain",
                lat: 41.7276,
                lng: -74.2394,
                description: "Shawangunk Ridge with lakes, cliffs, and hiking trails.",
                address: "Minnewaska State Park Preserve, Kerhonkson, NY 12446",
                amenities: ["Hiking", "Lake", "Cliffs", "Camping", "Scenic Views"]
            },
            {
                name: "Hudson Highlands State Park",
                type: "mountain",
                lat: 41.2445,
                lng: -73.9479,
                description: "Scenic park with hiking and Hudson River views.",
                address: "Hudson Highlands State Park, Cold Spring, NY 10516",
                amenities: ["Hiking", "Scenic Views", "Fishing", "Picnic Areas"]
            },
            {
                name: "Cayuga Lake State Park",
                type: "lake",
                lat: 42.4642,
                lng: -76.7022,
                description: "Beach and camping on Cayuga Lake.",
                address: "Cayuga Lake State Park, Union Springs, NY 13160",
                amenities: ["Lake", "Beach", "Camping", "Fishing", "Boating"]
            },
            {
                name: "Old Fort Niagara State Historic Site",
                type: "historical",
                lat: 43.2617,
                lng: -79.0631,
                description: "18th century fort with museum and tours.",
                address: "Old Fort Niagara State Historic Site, Youngstown, NY 14174",
                amenities: ["Historic Fort", "Museum", "Guided Tours", "Picnic Areas"]
            }
        ];

        function initMap() {
            // Center on New York State
            const nyCenter = { lat: 42.1497, lng: -74.9384 };

            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 7,
                center: nyCenter,
                styles: [
                    {
                        featureType: 'poi.park',
                        elementType: 'geometry',
                        stylers: [{ color: '#c8e6c9' }]
                    }
                ]
            });

            infoWindow = new google.maps.InfoWindow();

            // Add markers for all parks
            addParkMarkers('all');

            // Create park list
            createParkList();
        }

        function addParkMarkers(filterType) {
            // Clear existing markers
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            const filteredParks = filterType === 'all' ?
                parks :
                parks.filter(park => park.type === filterType);

            filteredParks.forEach(park => {
                const marker = new google.maps.Marker({
                    position: { lat: park.lat, lng: park.lng },
                    map: map,
                    title: park.name,
                    icon: {
                        url: getMarkerIcon(park.type),
                        scaledSize: new google.maps.Size(30, 30)
                    }
                });

                marker.addListener('click', () => {
                    showParkInfo(park);
                });

                markers.push(marker);
            });

            // Adjust map bounds to show all markers
            if (markers.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                markers.forEach(marker => bounds.extend(marker.getPosition()));
                map.fitBounds(bounds);
            }
        }

        function getMarkerIcon(type) {
            const icons = {
                beach: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="15" cy="15" r="14" fill="#FF6B35" stroke="white" stroke-width="2"/>
                        <path d="M8 20 Q15 10 22 20" stroke="white" stroke-width="3" fill="none"/>
                    </svg>`),
                mountain: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="15" cy="15" r="14" fill="#8B4513" stroke="white" stroke-width="2"/>
                        <polygon points="8,22 15,8 22,22" fill="white"/>
                    </svg>`),
                lake: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="15" cy="15" r="14" fill="#4682B4" stroke="white" stroke-width="2"/>
                        <ellipse cx="15" cy="18" rx="8" ry="4" fill="white"/>
                    </svg>`),
                historical: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="15" cy="15" r="14" fill="#8B0000" stroke="white" stroke-width="2"/>
                        <rect x="12" y="10" width="6" height="8" fill="white"/>
                        <polygon points="9,10 15,6 21,10" fill="white"/>
                    </svg>`)
            };
            return icons[type] || 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="15" cy="15" r="14" fill="#4CAF50" stroke="white" stroke-width="2"/>
                    <circle cx="15" cy="15" r="6" fill="white"/>
                </svg>`);
        }

        function showParkInfo(park) {
            const content = `
                <div style="max-width: 250px;">
                    <h3 style="margin: 0 0 10px 0; color: #2E7D32;">${park.name}</h3>
                    <p style="margin: 5px 0;"><strong>Type:</strong> ${park.type.charAt(0).toUpperCase() + park.type.slice(1)}</p>
                    <p style="margin: 5px 0;"><strong>Address:</strong> ${park.address}</p>
                    <p style="margin: 5px 0;">${park.description}</p>
                    <p style="margin: 5px 0;"><strong>Amenities:</strong> ${park.amenities.join(', ')}</p>
                </div>
            `;

            infoWindow.setContent(content);
            infoWindow.open(map, markers.find(m => m.title === park.name));
        }

        function createParkList() {
            const parkList = document.getElementById('park-list');

            parks.forEach(park => {
                const card = document.createElement('div');
                card.className = 'park-card';
                card.onclick = () => {
                    map.setCenter({ lat: park.lat, lng: park.lng });
                    map.setZoom(12);
                    showParkInfo(park);
                };

                card.innerHTML = `
                    <h3>${park.name}</h3>
                    <p><strong>Type:</strong> ${park.type.charAt(0).toUpperCase() + park.type.slice(1)}</p>
                    <p>${park.description.substring(0, 100)}...</p>
                    <p><strong>Amenities:</strong> ${park.amenities.slice(0, 3).join(', ')}${park.amenities.length > 3 ? '...' : ''}</p>
                `;

                parkList.appendChild(card);
            });
        }

        // Filter button event listeners
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                addParkMarkers(btn.dataset.filter);
            });
        });

        // Handle API loading error
=======
        let map;
        let markers = [];
        let infoWindow;

        const parks = [
            {
                name: "Jones Beach State Park",
                type: "beach",
                lat: 40.6066,
                lng: -73.5117,
                description: "Beautiful beach park with boardwalk, playgrounds, and water activities.",
                address: "Jones Beach State Pkwy, Wantagh, NY 11793",
                amenities: ["Beach", "Boardwalk", "Playgrounds", "Fishing", "Camping"]
            },
            {
                name: "Bear Mountain State Park",
                type: "mountain",
                lat: 41.3165,
                lng: -73.9868,
                description: "Scenic mountain park with hiking trails, zoo, and Hudson River views.",
                address: "Bear Mountain State Park, Bear Mountain, NY 10911",
                amenities: ["Hiking", "Zoo", "Camping", "Fishing", "Picnic Areas"]
            },
            {
                name: "Robert Moses State Park",
                type: "beach",
                lat: 40.6368,
                lng: -73.2551,
                description: "Long Island beach park with camping, fishing, and water sports.",
                address: "Robert Moses State Park, Babylon, NY 11702",
                amenities: ["Beach", "Camping", "Fishing", "Boating", "Playgrounds"]
            },
            {
                name: "Letchworth State Park",
                type: "mountain",
                lat: 42.4347,
                lng: -78.0519,
                description: "Known as the 'Grand Canyon of the East' with stunning gorges and waterfalls.",
                address: "Letchworth State Park, Mount Morris, NY 14510",
                amenities: ["Hiking", "Camping", "Fishing", "Scenic Views", "Photography"]
            },
            {
                name: "Saratoga Spa State Park",
                type: "lake",
                lat: 43.0326,
                lng: -73.7672,
                description: "Mineral springs park with pools, golf course, and Saratoga Race Course nearby.",
                address: "Saratoga Spa State Park, Saratoga Springs, NY 12866",
                amenities: ["Swimming Pools", "Golf", "Camping", "Mineral Springs", "Pavilion"]
            },
            {
                name: "Stony Brook State Park",
                type: "lake",
                lat: 40.9126,
                lng: -73.1393,
                description: "Lake park with beaches, camping, and recreational activities.",
                address: "Stony Brook State Park, Stony Brook, NY 11790",
                amenities: ["Lake", "Beach", "Camping", "Fishing", "Boating"]
            },
            {
                name: "Washington's Headquarters State Historic Site",
                type: "historical",
                lat: 41.4031,
                lng: -73.6062,
                description: "Historic site where George Washington headquartered during Revolutionary War.",
                address: "Washington's Headquarters State Historic Site, Newburgh, NY 12550",
                amenities: ["Historic Buildings", "Museum", "Guided Tours", "Picnic Areas"]
            },
            {
                name: "Clinton House State Historic Site",
                type: "historical",
                lat: 42.6526,
                lng: -73.7572,
                description: "Home of DeWitt Clinton, featuring period furnishings and gardens.",
                address: "Clinton House State Historic Site, Poughkeepsie, NY 12601",
                amenities: ["Historic House", "Gardens", "Museum", "Educational Programs"]
            },
            {
                name: "Niagara Falls State Park",
                type: "historical",
                lat: 43.0962,
                lng: -79.0377,
                description: "Home to the famous Niagara Falls, with observation decks and historic sites.",
                address: "Niagara Falls State Park, Niagara Falls, NY 14303",
                amenities: ["Waterfalls", "Observation Decks", "Boat Tours", "Historic Sites"]
            },
            {
                name: "Watkins Glen State Park",
                type: "mountain",
                lat: 42.3759,
                lng: -76.8708,
                description: "Gorge with 19 waterfalls and stone bridges.",
                address: "Watkins Glen State Park, Watkins Glen, NY 14891",
                amenities: ["Hiking", "Waterfalls", "Photography", "Picnic Areas"]
            },
            {
                name: "Allegany State Park",
                type: "mountain",
                lat: 42.0906,
                lng: -78.8547,
                description: "Largest state park in NY, with mountains, lakes, and forests.",
                address: "Allegany State Park, Salamanca, NY 14779",
                amenities: ["Hiking", "Camping", "Fishing", "Boating", "Skiing"]
            },
            {
                name: "Minnewaska State Park Preserve",
                type: "mountain",
                lat: 41.7276,
                lng: -74.2394,
                description: "Shawangunk Ridge with lakes, cliffs, and hiking trails.",
                address: "Minnewaska State Park Preserve, Kerhonkson, NY 12446",
                amenities: ["Hiking", "Lake", "Cliffs", "Camping", "Scenic Views"]
            },
            {
                name: "Hudson Highlands State Park",
                type: "mountain",
                lat: 41.2445,
                lng: -73.9479,
                description: "Scenic park with hiking and Hudson River views.",
                address: "Hudson Highlands State Park, Cold Spring, NY 10516",
                amenities: ["Hiking", "Scenic Views", "Fishing", "Picnic Areas"]
            },
            {
                name: "Cayuga Lake State Park",
                type: "lake",
                lat: 42.4642,
                lng: -76.7022,
                description: "Beach and camping on Cayuga Lake.",
                address: "Cayuga Lake State Park, Union Springs, NY 13160",
                amenities: ["Lake", "Beach", "Camping", "Fishing", "Boating"]
            },
            {
                name: "Old Fort Niagara State Historic Site",
                type: "historical",
                lat: 43.2617,
                lng: -79.0631,
                description: "18th century fort with museum and tours.",
                address: "Old Fort Niagara State Historic Site, Youngstown, NY 14174",
                amenities: ["Historic Fort", "Museum", "Guided Tours", "Picnic Areas"]
            }
        ];

        function initMap() {
            // Center on New York State
            const nyCenter = { lat: 42.1497, lng: -74.9384 };

            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 7,
                center: nyCenter,
                styles: [
                    {
                        featureType: 'poi.park',
                        elementType: 'geometry',
                        stylers: [{ color: '#c8e6c9' }]
                    }
                ]
            });

            infoWindow = new google.maps.InfoWindow();

            // Add markers for all parks
            addParkMarkers('all');

            // Create park list
            createParkList();
        }

        function addParkMarkers(filterType) {
            // Clear existing markers
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            const filteredParks = filterType === 'all' ?
                parks :
                parks.filter(park => park.type === filterType);

            filteredParks.forEach(park => {
                const marker = new google.maps.Marker({
                    position: { lat: park.lat, lng: park.lng },
                    map: map,
                    title: park.name,
                    icon: {
                        url: getMarkerIcon(park.type),
                        scaledSize: new google.maps.Size(30, 30)
                    }
                });

                marker.addListener('click', () => {
                    showParkInfo(park);
                });

                markers.push(marker);
            });

            // Adjust map bounds to show all markers
            if (markers.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                markers.forEach(marker => bounds.extend(marker.getPosition()));
                map.fitBounds(bounds);
            }
        }

        function getMarkerIcon(type) {
            const icons = {
                beach: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="15" cy="15" r="14" fill="#FF6B35" stroke="white" stroke-width="2"/>
                        <path d="M8 20 Q15 10 22 20" stroke="white" stroke-width="3" fill="none"/>
                    </svg>`),
                mountain: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="15" cy="15" r="14" fill="#8B4513" stroke="white" stroke-width="2"/>
                        <polygon points="8,22 15,8 22,22" fill="white"/>
                    </svg>`),
                lake: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="15" cy="15" r="14" fill="#4682B4" stroke="white" stroke-width="2"/>
                        <ellipse cx="15" cy="18" rx="8" ry="4" fill="white"/>
                    </svg>`),
                historical: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="15" cy="15" r="14" fill="#8B0000" stroke="white" stroke-width="2"/>
                        <rect x="12" y="10" width="6" height="8" fill="white"/>
                        <polygon points="9,10 15,6 21,10" fill="white"/>
                    </svg>`)
            };
            return icons[type] || 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="15" cy="15" r="14" fill="#4CAF50" stroke="white" stroke-width="2"/>
                    <circle cx="15" cy="15" r="6" fill="white"/>
                </svg>`);
        }

        function showParkInfo(park) {
            const content = `
                <div style="max-width: 250px;">
                    <h3 style="margin: 0 0 10px 0; color: #2E7D32;">${park.name}</h3>
                    <p style="margin: 5px 0;"><strong>Type:</strong> ${park.type.charAt(0).toUpperCase() + park.type.slice(1)}</p>
                    <p style="margin: 5px 0;"><strong>Address:</strong> ${park.address}</p>
                    <p style="margin: 5px 0;">${park.description}</p>
                    <p style="margin: 5px 0;"><strong>Amenities:</strong> ${park.amenities.join(', ')}</p>
                </div>
            `;

            infoWindow.setContent(content);
            infoWindow.open(map, markers.find(m => m.title === park.name));
        }

        function createParkList() {
            const parkList = document.getElementById('park-list');

            parks.forEach(park => {
                const card = document.createElement('div');
                card.className = 'park-card';
                card.onclick = () => {
                    map.setCenter({ lat: park.lat, lng: park.lng });
                    map.setZoom(12);
                    showParkInfo(park);
                };

                card.innerHTML = `
                    <h3>${park.name}</h3>
                    <p><strong>Type:</strong> ${park.type.charAt(0).toUpperCase() + park.type.slice(1)}</p>
                    <p>${park.description.substring(0, 100)}...</p>
                    <p><strong>Amenities:</strong> ${park.amenities.slice(0, 3).join(', ')}${park.amenities.length > 3 ? '...' : ''}</p>
                `;

                parkList.appendChild(card);
            });
        }
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                addParkMarkers(btn.dataset.filter);
            });
        });

        // Handle API loading error
>>>>>>> c4d9c7e43ed83e0c3eba450b220d5bab494fef50
        window.initMap = initMap;