<?php
function admin_housing_value(string $value, array $field): ?string {
    [$label, $type, $limit] = $field;
    if ($value === '') {
        if ($field[3] ?? false) { throw new InvalidArgumentException($label . ' is required.'); }
        return null;
    }
    $min = $field[4] ?? 0;
    $scale = $field[5] ?? 0;
    $valid = match ($type) {
        'select' => array_key_exists($value, $limit),
        'zip' => preg_match('/\A[0-9]{5}(?:-[0-9]{4})?\z/', $value),
        'state' => preg_match('/\A[A-Za-z]{2}\z/', $value),
        'decimal' => preg_match('/\A-?[0-9]+(?:\.[0-9]{1,' . $scale . '})?\z/', $value)
            && $value >= $min && $value <= $limit,
        'integer' => preg_match('/\A-?[0-9]+\z/', $value) && $value >= $min && $value <= $limit,
        'textarea' => strlen($value) <= $limit && preg_match('//u', $value),
        default => ($length = preg_match_all('/./us', $value)) !== false && $length <= $limit,
    };
    if (!$valid) {
        $hint = match ($type) {
            'zip' => 'Use a five-digit ZIP code or ZIP+4.',
            'state' => 'Use a two-letter state abbreviation.',
            'select' => 'Choose an option from the list.',
            'decimal' => "Enter a number from $min to $limit with up to $scale decimal places.",
            'integer' => "Enter a whole number from $min to $limit.",
            'textarea' => "Enter valid text up to $limit bytes.",
            default => "Enter valid text up to $limit characters.",
        };
        throw new InvalidArgumentException($label . '. ' . $hint);
    }
    return $type === 'state' ? strtoupper($value) : $value;
}

function admin_render_housing_field(string $column, array $field, string $value): void {
    [$label, $type, $limit] = $field;
    $required = ($field[3] ?? false) ? ' required' : '';
    ?>
    <label for="<?= escape($column) ?>"><?= escape($label) ?></label>
    <?php if ($type === 'select') { ?>
        <select id="<?= escape($column) ?>" name="<?= escape($column) ?>"<?= $required ?>>
            <option value=""><?= $required ? 'Choose an option' : 'Unspecified' ?></option>
            <?php foreach ($limit as $key => $caption) { ?>
                <option value="<?= escape($key) ?>"<?= $value === (string)$key ? ' selected' : '' ?>><?= escape($caption) ?></option>
            <?php } ?>
        </select>
    <?php } elseif ($type === 'textarea') { ?>
        <textarea id="<?= escape($column) ?>" name="<?= escape($column) ?>" rows="5" maxlength="<?= (int)$limit ?>"><?= escape($value) ?></textarea>
    <?php } elseif (in_array($type, ['decimal', 'integer'], true)) { ?>
        <input type="number" id="<?= escape($column) ?>" name="<?= escape($column) ?>" value="<?= escape($value) ?>"
            min="<?= escape($field[4] ?? 0) ?>" max="<?= escape($limit) ?>" step="<?= escape(10 ** -($field[5] ?? 0)) ?>"<?= $required ?>>
    <?php } else { ?>
        <input type="text" id="<?= escape($column) ?>" name="<?= escape($column) ?>"
            value="<?= escape($value) ?>" maxlength="<?= (int)$limit ?>"<?= $required ?>>
    <?php }
}
