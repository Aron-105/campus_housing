const unit = document.getElementById('unit_id');
const amenities = document.getElementById('amenities');
const unitAmenities = JSON.parse(amenities.dataset.units);
const submittedAmenities = JSON.parse(amenities.dataset.submitted);
const options = amenities.querySelector('.amenity-options');
const checkboxes = options.querySelectorAll('input');

function showAmenities(submitted = null) {
    const selected = unitAmenities[unit.value] ?? {};
    amenities.disabled = !unit.value;
    options.hidden = !unit.value;
    document.getElementById('amenities-prompt').hidden = Boolean(unit.value);
    document.getElementById('amenities-unit-id').value = unit.value;
    const baseline = [];
    for (const checkbox of checkboxes) {
        const amenity = selected[checkbox.value];
        checkbox.disabled = amenity?.shared ?? false;
        if (amenity?.available && !checkbox.disabled) {
            baseline.push(checkbox.value);
        }
        checkbox.checked = submitted && !checkbox.disabled
            ? submitted.includes(checkbox.value)
            : amenity?.available ?? false;
    }
    document.getElementById('amenities-baseline').value = JSON.stringify(baseline);
}

unit.addEventListener('change', () => showAmenities());
showAmenities(submittedAmenities);
