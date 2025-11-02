<?php
/**
 * Configuration metadata for the Delete Guard plugin
 *
 * The Configuration Manager uses this metadata to render appropriate
 * input controls and perform simple validation. For details see the
 * DokuWiki developer documentation.
 */

// patterns: use the default 'string' class to allow free text. You may
// specify multiple patterns separated by newlines. There is no built‑in
// textarea class, but the Configuration Manager will provide a multi‑line
// input for long strings when the `_type` is not specified.
$meta['patterns'] = array('string');

// match_target: single choice from a list of options. Choices are
// 'id' (match against page ID) and 'filepath' (match against relative
// filesystem path). The order of choices determines display order.
$meta['match_target'] = array('multichoice', '_choices' => array('id','filepath'));

// exempt_groups: comma separated list. Use 'string' for free text input.
$meta['exempt_groups'] = array('string');

// trim_mode: boolean on/off. Use 'onoff' to present a checkbox.
$meta['trim_mode'] = array('onoff');