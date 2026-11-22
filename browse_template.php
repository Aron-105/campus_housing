<?php
$extraOpen = !$isResults || $filters['max_walk'] !== null ||
    $filters['bedrooms'] !== null || $filters['unit_type'] !== '';
$count = count($homes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Search student housing listings near USC by neighborhood, rent, and more.">
    <title><?= $isResults ? 'Browse listings' : 'Find a home' ?> — HomeSC</title>
    <link rel="icon" href="assets/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="browse.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <nav class="wrap header-inner" aria-label="Main navigation">
        <a class="brand" href="index.php" aria-label="HomeSC home"><img src="assets/logo-header.png" alt="HomeSC" width="110" height="34"></a>
        <div class="nav-links"><a href="index.php">Home</a><a href="search.php" <?= !$isResults ? 'aria-current="page"' : '' ?>>Search</a><a href="results.php" <?= $isResults ? 'aria-current="page"' : '' ?>>Browse listings</a><a href="login.php"><?= $userId ? 'Account' : 'Log in' ?></a></div>
    </nav>
</header>
<main id="main">
    <section class="search-intro">
        <div class="wrap">
            <p class="eyebrow">Homes near USC</p>
            <h1><?= $isResults ? 'Find your fit.' : 'Start with what matters.' ?></h1>
            <p class="intro-copy"><?= $isResults ? 'Adjust your search to see homes that fit your plans.' : 'Choose a neighborhood, move-in month, or budget. Add more details if you have them.' ?></p>
            <form class="search-panel" action="results.php" method="get" aria-label="Search homes">
                <div class="primary-filters">
                    <div class="field"><label for="location">Neighborhood</label><select id="location" name="location">
                        <option value="">Anywhere near USC</option>
                        <?php if ($filters['location'] !== '' && !in_array($filters['location'], $neighborhoods, true)) { ?><option value="<?= escape($filters['location']) ?>" selected><?= escape($filters['location']) ?></option><?php } ?>
                        <?php foreach ($neighborhoods as $neighborhood) { ?><option value="<?= escape($neighborhood) ?>" <?= $filters['location'] === $neighborhood ? 'selected' : '' ?>><?= escape($neighborhood) ?></option><?php } ?>
                    </select></div>
                    <div class="field"><label for="move-in">Move-in month</label><select id="move-in" name="move_in">
                        <option value="">Any time</option>
                        <?php for ($i = 0; $i < 24; $i++) {
                            $month = strtotime('first day of +' . $i . ' month');
                            $value = date('Y-m', $month); ?>
                            <option value="<?= $value ?>" <?= $filters['move_in'] === $value ? 'selected' : '' ?>><?= date('F Y', $month) ?></option>
                        <?php } ?>
                    </select></div>
                    <div class="field"><label for="max-rent">Maximum rent / month</label><div class="money-input"><span aria-hidden="true">$</span><input id="max-rent" name="max_rent" type="number" min="1" max="100000" step="1" inputmode="numeric" placeholder="Any budget" value="<?= escape($filters['max_rent'] ?? '') ?>"></div></div>
                    <button class="search-button" type="submit">Search homes <span aria-hidden="true">→</span></button>
                </div>
                <details class="more-filters" <?= $extraOpen ? 'open' : '' ?>>
                    <summary>More filters <span aria-hidden="true">⌄</span></summary>
                    <div class="extra-fields">
                        <div class="field"><label for="max-walk">Walk to campus</label><select id="max-walk" name="max_walk">
                            <option value="">Any distance</option>
                            <?php foreach ([10, 15, 20, 30] as $minutes) { ?><option value="<?= $minutes ?>" <?= $filters['max_walk'] === $minutes ? 'selected' : '' ?>>About <?= $minutes ?> minutes or less</option><?php } ?>
                        </select></div>
                        <div class="field"><label for="bedrooms">Bedrooms</label><select id="bedrooms" name="bedrooms">
                            <option value="">Any number</option>
                            <?php foreach ([1, 2, 3, 4] as $beds) { ?><option value="<?= $beds ?>" <?= $filters['bedrooms'] === $beds ? 'selected' : '' ?>><?= $beds ?>+ bedrooms</option><?php } ?>
                        </select></div>
                        <div class="field"><label for="unit-type">Home type</label><select id="unit-type" name="unit_type">
                            <option value="">Any type</option>
                            <?php if ($filters['unit_type'] !== '' && !in_array($filters['unit_type'], $unitTypes, true)) { ?><option value="<?= escape($filters['unit_type']) ?>" selected><?= escape($filters['unit_type']) ?></option><?php } ?>
                            <?php foreach ($unitTypes as $type) { ?><option value="<?= escape($type) ?>" <?= $filters['unit_type'] === $type ? 'selected' : '' ?>><?= escape($type) ?></option><?php } ?>
                        </select></div>
                    </div>
                    <p class="filter-note">Walking times are estimates based on distance to USC.</p>
                </details>
            </form>
        </div>
    </section>

    <?php if ($errorMessage) { ?><div class="wrap"><p class="status-message" role="alert"><?= escape($errorMessage) ?></p></div><?php } ?>
    <?php if ($isResults && !$errorMessage) { ?>
    <section class="results wrap" aria-labelledby="results-title">
        <div class="results-heading"><div><p class="eyebrow">Your search</p><h2 id="results-title"><?= $count ?> <?= $count === 1 ? 'home' : 'homes' ?> found</h2></div><a href="search.php">New search <span aria-hidden="true">→</span></a></div>
        <?php if ($count === 0) { ?>
            <div class="empty-state"><h3>No homes match these filters yet.</h3><p>Try a higher budget, a different neighborhood, or fewer filters.</p><a href="results.php">See all homes →</a></div>
        <?php } else { ?>
            <div id="map" class="listing-map" aria-label="Map of homes near USC"></div>
            <p class="map-note">Only homes with coordinates appear on the map.</p>
            <div class="home-grid">
                <?php foreach ($homes as $home) {
                    $photo = photo_source($home['photo_url'] ?? '');
                    $url = 'details.php?property_id=' . (int) $home['property_id'] . '&listing_id=' . (int) $home['listing_id'];
                    $name = $home['property_name'] ?? 'Home near USC'; ?>
                    <article class="home-card">
                        <a class="photo-link" href="<?= escape($url) ?>" aria-label="View <?= escape($name) ?>"><img src="<?= escape($photo ?: DEFAULT_LISTING_PHOTO) ?>" alt="<?= $photo ? escape($name . ' interior') : '' ?>" loading="lazy" width="900" height="506"></a>
                        <div class="card-content"><?php if (($home['listing_status'] ?? '') !== 'active' || (!empty($home['listing_available_until']) && $home['listing_available_until'] < date('Y-m-d'))) { ?><p class="availability">Not currently available</p><?php } ?><div class="card-title-row"><h3><a href="<?= escape($url) ?>"><?= escape($name) ?></a></h3><?php if (isset($home['monthly_rent'])) { ?><p class="price">$<?= number_format((float) $home['monthly_rent']) ?><small>/mo</small></p><?php } ?></div>
                        <p class="neighborhood"><?= escape($home['neighborhood'] ?? 'Location unavailable') ?></p>
                        <?php if (!empty($home['review_count']) && isset($home['average_rating'])) { ?><p class="rating"><?= escape(number_format((float)$home['average_rating'], 1)) ?> / 5 · <?= (int)$home['review_count'] ?> reviews</p><?php } ?>
                        <p class="specs"><?= escape(home_specs($home)) ?><?php if (!empty($home['rent_basis'])) { ?> <span>· <?= escape($home['rent_basis']) ?></span><?php } ?></p></div>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>
    </section>
    <?php } elseif (!$isResults) { ?><div class="wrap browse-link"><a href="results.php">Or browse every home <span aria-hidden="true">→</span></a></div><?php } ?>
</main>
<footer class="site-footer"><div class="wrap"><a href="index.php">HomeSC</a><p>Made for students in Los Angeles.</p></div></footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<script src="https://unpkg.com/@maplibre/maplibre-gl-leaflet@0.0.22/leaflet-maplibre-gl.js"></script>
<script nonce="<?= escape($nonce) ?>">
const form = document.querySelector('.search-panel');
let filterTimer;
form.addEventListener('change', () => form.requestSubmit());
form.querySelector('input').addEventListener('input', () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => { if (form.checkValidity()) form.requestSubmit(); }, 700);
});
const mapElement = document.getElementById('map');
function mapUnavailable() {
    if (mapElement) mapElement.innerHTML = '<p class="map-error" role="status">The map is unavailable. Browse the homes below.</p>';
}
if (mapElement && window.L && window.maplibregl && L.maplibreGL) {
  try {
    const map = L.map(mapElement, { minZoom: 1 }).setView([34.0224, -118.2851], 14);
    map.attributionControl.addAttribution('<a href="https://openfreemap.org/">OpenFreeMap</a> | &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> | Data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors');
    const layer = L.maplibreGL({ style: 'https://tiles.openfreemap.org/styles/positron', attributionControl: false }).addTo(map);
    const gl = layer.getMaplibreMap();
    let loaded = false;
    const loadTimeout = setTimeout(() => { if (!loaded) { map.remove(); mapUnavailable(); } }, 15000);
    gl.on('load', () => { loaded = true; clearTimeout(loadTimeout); });
    gl.on('error', event => { if (!loaded && event.error) { clearTimeout(loadTimeout); map.remove(); mapUnavailable(); } });
    L.marker([34.0224, -118.2851], { icon: L.divIcon({ className: 'campus-marker', html: '<span>USC</span>', iconSize: [46, 26], iconAnchor: [23, 13] }), interactive: false }).addTo(map);
    const homes = <?= json_encode(array_values(array_map(function ($home) { return ['lat'=>$home['latitude'], 'lng'=>$home['longitude'], 'name'=>$home['property_name'], 'rent'=>$home['monthly_rent'], 'specs'=>home_specs($home), 'property_id'=>(int)$home['property_id'], 'listing_id'=>(int)$home['listing_id']]; }, array_filter($homes, function ($home) { return is_numeric($home['latitude'] ?? null) && is_numeric($home['longitude'] ?? null); }))), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const properties = new Map();
    homes.forEach(home => {
        if (!properties.has(home.property_id)) properties.set(home.property_id, []);
        properties.get(home.property_id).push(home);
    });
    properties.forEach(listings => {
        const home = listings[0];
        const popup = document.createElement('div');
        const title = document.createElement('strong');
        title.textContent = home.name || 'Home near USC';
        popup.appendChild(title);
        listings.forEach((listing, index) => {
            const link = document.createElement('a');
            link.href = 'details.php?property_id=' + listing.property_id + '&listing_id=' + listing.listing_id;
            const price = listing.rent !== null && listing.rent !== '' ? '$' + Number(listing.rent).toLocaleString() + '/mo' : 'Rent unavailable';
            link.textContent = price + (listing.specs ? ' · ' + listing.specs : '') + ' — View listing';
            popup.appendChild(link);
        });
        const icon = L.divIcon({ className: 'house-marker', html: '<span></span>', iconSize: [28, 36], iconAnchor: [14, 36], popupAnchor: [0, -34] });
        L.marker([Number(home.lat), Number(home.lng)], { icon, title: home.name || 'View home' }).addTo(map).bindPopup(popup);
    });
  } catch (error) { mapUnavailable(); }
} else { mapUnavailable(); }
</script>
</body>
</html>
