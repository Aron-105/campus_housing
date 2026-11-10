<?php
// Small display helpers; no framework or dependencies.
const DEFAULT_LISTING_PHOTO = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c';

function escape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function featured_homes(array $rows) {
    $featured = [];
    $used = [];
    $selections = [
        'Popular' => ['review_count', 'average_rating', 'listing_created_at'],
        'Best Value' => ['average_value_rating', 'review_count', 'listing_created_at'],
        'New' => ['listing_created_at', 'listing_id'],
    ];
    foreach ($selections as $badge => $fields) {
        $ranked = $rows;
        usort($ranked, function ($a, $b) use ($fields) {
            foreach ($fields as $field) {
                $comparison = ($b[$field] ?? 0) <=> ($a[$field] ?? 0);
                if ($comparison !== 0) { return $comparison; }
            }
            return ($a['listing_id'] ?? 0) <=> ($b['listing_id'] ?? 0);
        });
        foreach ($ranked as $row) {
            $id = (int) ($row['property_id'] ?? 0);
            if ($id < 1 || isset($used[$id])) { continue; }
            $row['badge'] = $badge;
            if (($badge === 'Popular' && empty($row['review_count'])) ||
                ($badge === 'Best Value' && empty($row['average_value_rating']))) {
                $row['badge'] = 'Featured';
            }
            $featured[] = $row;
            $used[$id] = true;
            break;
        }
    }
    return $featured;
}

function home_specs(array $home) {
    $type = strtolower((string) ($home['unit_type'] ?? ''));
    $parts = [];
    if (strpos($type, 'private room') !== false) {
        $parts[] = 'Private room';
    } elseif (strpos($type, 'studio') !== false ||
              (isset($home['bedrooms']) && (float) $home['bedrooms'] === 0.0)) {
        $parts[] = 'Studio';
    } else {
        if (isset($home['bedrooms'])) { $parts[] = (float) $home['bedrooms'] . ' bd'; }
        if (isset($home['bathrooms'])) { $parts[] = (float) $home['bathrooms'] . ' ba'; }
    }
    if (isset($home['usc_distance_meters']) && is_numeric($home['usc_distance_meters'])) {
        $miles = (float) $home['usc_distance_meters'] / 1609.344;
        $parts[] = rtrim(rtrim(number_format($miles, 1), '0'), '.') . ' mi';
    }
    return implode(' · ', $parts);
}

function photo_source($url) {
    if (!is_string($url) || trim($url) === '') { return ''; }
    $url = trim($url);
    if (filter_var($url, FILTER_VALIDATE_URL) && parse_url($url, PHP_URL_SCHEME) === 'https') {
        if (in_array(parse_url($url, PHP_URL_HOST), ['example.com', 'www.example.com'], true)) { return ''; }
        return $url;
    }
    if (preg_match('~\A(?!/)(?!.*\.\.)[a-zA-Z0-9_./-]+\z~', $url)) {
        return $url;
    }
    return '';
}

function request_text(array $input, $key, $default = '') {
    return isset($input[$key]) && is_string($input[$key])
        ? substr(trim($input[$key]), 0, 160) : $default;
}

