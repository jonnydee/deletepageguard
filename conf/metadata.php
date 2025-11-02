<?php
/**
 * Configuration metadata for the Delete Page Guard plugin
 *
 * The Configuration Manager uses this metadata to render appropriate
 * input controls and perform simple validation. For details see the
 * DokuWiki developer documentation.
 */

// patterns: use the default (empty) class to render a textarea control. This
// allows multiple regular expressions to be entered on separate lines. Each
// line should contain one Perl‑compatible regular expression. The
// Configuration Manager treats an empty setting class as a multiline
// textarea for free text input.
$meta['patterns'] = array('');

// match_target: single choice from a list of options. Choices are
// 'id' (match against page ID) and 'filepath' (match against relative
// filesystem path). The order of choices determines display order.
$meta['match_target'] = array('multichoice', '_choices' => array('id','filepath'));

// exempt_groups: comma separated list. Use 'string' for free text input.
$meta['exempt_groups'] = array('string');

// trim_mode: boolean on/off. Use 'onoff' to present a checkbox.
$meta['trim_mode'] = array('onoff');