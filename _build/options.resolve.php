<?php
/** @var array $options */
$output = '<style>.msgiftcards-setup-options label{display:block;margin:6px 0;}</style>';
$output .= '<div class="msgiftcards-setup-options">';
$output .= '<h3>msGiftCards: перезапись чанков</h3>';
$output .= '<p>Отметьте чанки, которые нужно перезаписать содержимым из пакета. Неотмеченные чанки будут сохранены как есть.</p>';
$output .= '<label><input type="checkbox" name="msgiftcards_overwrite_chunk_field" value="1" /> Перезаписать чанк <b>msGiftCards.field</b></label>';
$output .= '<label><input type="checkbox" name="msgiftcards_overwrite_chunk_info" value="1" /> Перезаписать чанк <b>msGiftCards.info</b></label>';
$output .= '<label><input type="checkbox" name="msgiftcards_overwrite_chunk_certificate" value="1" /> Перезаписать чанк <b>msGiftCards.certificate</b></label>';
$output .= '</div>';
return $output;