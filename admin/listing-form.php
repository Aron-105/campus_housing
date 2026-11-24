<?php
// $listing can be set before including this file to fill in the fields
if (!isset($listing)) {
    $listing = [];
}

function field($listing, $key) {
    if (isset($listing[$key])) {
        return htmlspecialchars($listing[$key]);
    }
    return '';
}
?>

<form class="listing-form" method="post" action="">

    <fieldset>
        <legend>Property</legend>

        <label for="name">Property name</label>
        <input type="text" id="name" name="name" value="<?php echo field($listing, 'name'); ?>" required>

        <label for="address">Street address</label>
        <input type="text" id="address" name="address" value="<?php echo field($listing, 'address'); ?>" required>

        <div class="row">
            <div>
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="Los Angeles">
            </div>
            <div>
                <label for="state">State</label>
                <input type="text" id="state" name="state" value="CA" maxlength="2">
            </div>
            <div>
                <label for="zip">Zip code</label>
                <input type="text" id="zip" name="zip" maxlength="5">
            </div>
        </div>

        <label for="neighborhood">Neighborhood</label>
        <select id="neighborhood" name="neighborhood">
            <option value="">Choose a neighborhood</option>
            <?php
            $neighborhoods = ['North University Park', 'Figueroa Corridor', 'Exposition Park', 'West Adams', 'Downtown LA', 'Koreatown'];
            foreach ($neighborhoods as $n) {
                $selected = (field($listing, 'neighborhood') == $n) ? 'selected' : '';
                echo "<option $selected>$n</option>";
            }
            ?>
        </select>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"></textarea>
    </fieldset>

    <fieldset>
        <legend>Unit</legend>

        <div class="row">
            <div>
                <label for="unit_type">Unit type</label>
                <select id="unit_type" name="unit_type">
                    <option>Private room</option>
                    <option>Studio</option>
                    <option>Apartment</option>
                    <option>House</option>
                </select>
            </div>
            <div>
                <label for="bedrooms">Bedrooms</label>
                <input type="number" id="bedrooms" name="bedrooms" min="0" max="10" value="<?php echo field($listing, 'bedrooms'); ?>">
            </div>
            <div>
                <label for="bathrooms">Bathrooms</label>
                <input type="number" id="bathrooms" name="bathrooms" min="0" max="10" step="0.5">
            </div>
        </div>

        <div class="row">
            <div>
                <label for="rent">Monthly rent ($)</label>
                <input type="number" id="rent" name="rent" min="0" value="<?php echo field($listing, 'rent'); ?>" required>
            </div>
            <div>
                <label for="rent_basis">Rent is</label>
                <select id="rent_basis" name="rent_basis">
                    <option value="per_person">Per person</option>
                    <option value="per_unit">For the whole unit</option>
                </select>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Listing</legend>

        <div class="row">
            <div>
                <label for="available_from">Available from</label>
                <input type="date" id="available_from" name="available_from">
            </div>
            <div>
                <label for="available_until">Available until</label>
                <input type="date" id="available_until" name="available_until">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php
                    $statuses = ['active', 'leased', 'expired'];
                    foreach ($statuses as $s) {
                        $selected = (field($listing, 'status') == $s) ? 'selected' : '';
                        echo "<option value=\"$s\" $selected>" . ucfirst($s) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div>
                <label for="deposit">Security deposit ($)</label>
                <input type="number" id="deposit" name="deposit" min="0">
            </div>
            <div>
                <label for="app_fee">Application fee ($)</label>
                <input type="number" id="app_fee" name="app_fee" min="0">
            </div>
        </div>

        <label class="checkbox"><input type="checkbox" name="utilities_included" value="1"> Utilities included</label>

        <label for="listing_url">Original listing link</label>
        <input type="url" id="listing_url" name="listing_url" placeholder="https://">
    </fieldset>

    <button type="submit" class="btn"><?php echo $buttonText; ?></button>
    <a class="btn btn-outline" href="index.php">Cancel</a>
</form>
