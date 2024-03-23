<?php

// This function checks all variables for validity as they are read from and
// written to the SQL database - This addresses a typing weakness in PHP / MySQL

function check_type($type, &$data) {
    static $definitions = [
        'log' => [
            'id' => 'int',
            'log_text' => 'nullstr',
            'create_time' => 'nullstr',
            'written_by' => 'nullint'],
        'rights' => [
            'id' => 'int',
            'rights_giver' => 'int',
            'rights_receiver' => 'nullint',
            'write_allowed' => 'bool',
            'export_pdf_allowed' => 'bool',
            'download_allowed' => 'bool',
            'rights_accepted' => 'bool'],
        'address' => [
            'id' => 'int',
            'line1' => 'nullstr',
            'line2' => 'nullstr',
            'line3' => 'nullstr',
            'city' => 'nullstr',
            'state' => 'nullstr',
            'postal_code' => 'nullstr',
            'country' => 'nullstr',
            'phone' => 'nullstr',
            'line1' => 'nullstr',
            'tree_id' => 'int'],
        'chunk' => [
            'id' => 'int',
            'user_id' => 'int',
            'count' => 'nullint',
            'content' => 'nullstr' // Not a blob because it is now the file name
        ],
        'citation' => [
            'id' => 'int',
            'source_id' => 'nullint',
            'where_within_source' => 'nullstr',
            'role_in_event' => 'nullstr',
            'text_from_source' => 'nullstr',
            'certainty_assessment' => 'nullstr',
            'event_type' => 'nullstr',
            'entry_recording_date_id' => 'nullint',
            'tree_id' => 'int',
            'belongs_to_class' => 'str',
            'belongs_to_id' => 'int'],
        'complex_date' => [
            'id' => 'int',
            'structure' => 'nullint',
            'day1' => 'nullint',
            'month1' => 'nullint',
            'year1' => 'nullint',
            'day2' => 'nullint',
            'month2' => 'nullint',
            'year2' => 'nullint',
            'interpreted_string' => 'nullstr',
            'tree_id' => 'int'],
        'submitter_link' => [
            'id' => 'int',
            'submitter_id' => 'int',
            'tree_id' => 'int',
            'belongs_to_class' => 'str',
            'belongs_to_id' => 'int'],
        'note_link' => [
            'id' => 'int',
            'note_id' => 'int',
            'tree_id' => 'int',
            'belongs_to_class' => 'str',
            'belongs_to_id' => 'int'],
        'media_link' => [
            'id' => 'int',
            'media_id' => 'int',
            'tree_id' => 'int',
            'belongs_to_class' => 'str',
            'belongs_to_id' => 'int'],
        'event' => [
            'id' => 'int',
            'type' => 'nullstr',
            'argument' => 'nullstr',
            'event_descriptor' => 'nullstr',
            'event_date_id' => 'nullint',
            'place_id' => 'int',
            'address_id' => 'int',
            'age_at_event' => 'nullstr',
            'responsible_agency' => 'nullstr',
            'cause_of_event' => 'nullstr',
            'husband_age' => 'nullstr',
            'wife_age' => 'nullstr',
            'adoptive_family_id' => 'nullint',
            'tree_id' => 'int',
            'adopted_by_which_parent' => 'nullstr',
            'belongs_to_class' => 'str',
            'belongs_to_id' => 'int'],
        'user' => [
            'id' => 'int',
            'n_fail' => 'int',
            'tree_id' => 'nullint',
            'upload_filesize' => 'nullint',
            'upload_numchunk' => 'nullint',
            'upload_lastchunk' => 'nullint',
            'email' => 'str',
            'password' => 'str',
            'create_time' => 'str',
            'last_attempt' => 'str',
            'username' => 'str',
            'subscribed_until' => 'nullstr',
            'session_id' => 'nullstr',
            'upload_filename' => 'nullstr',
            'upload_hash' => 'nullstr',
            'usergroup' => 'str',
            'email_confirmed' => 'bool'],
        'font' => [
            'id' => 'int',
            'size' => 'int',
            'tree_id' => 'int',
            'font_R' => 'int',
            'font_G' => 'int',
            'font_B' => 'int',
            'background_R' => 'int',
            'background_G' => 'int',
            'background_B' => 'int',
            'style' => 'str',
            'opaque_background' => 'bool',
            'bold' => 'bool',
            'underline' => 'bool',
            'oblique' => 'bool'],
        'tree' => [
            'id' => 'int',
            'created_by' => 'int',
            'marriage_gap' => 'int',
            'line_height' => 'int',
            'sibling_gap' => 'int',
            'min_indi_H' => 'int',
            'min_indi_W' => 'int',
            'line_thickness' => 'int',
            'outline_thickness' => 'int',
            'connecting_R' => 'int',
            'connecting_G' => 'int',
            'connecting_B' => 'int',
            'outline_R' => 'int',
            'outline_G' => 'int',
            'outline_B' => 'int',
            'thumbnail_W' => 'int',
            'thumbnail_H' => 'int',
            'scroll_X' => 'int',
            'scroll_Y' => 'int',
            'aspect_ratio' => 'float',
            'zoom' => 'float',
            'author' => 'nullint',
            'watermark_media_id' => 'nullint',
            'root' => 'nullint',
            'first_line_font' => 'nullint',
            'other_line_font' => 'nullint',
            'title_font' => 'nullint',
            'originator_font' => 'nullint',
            'title' => 'nullstr',
            'when_dropped_by_user' => 'nullstr',
            'box_outline' => 'bool'],
        'individual' => [
            'id' => 'int',
            'tree_id' => 'int',
            'place_in_family_sibling_list' => 'int',
            'label' => 'str',
            'sex' => 'str',
            'show_me' => 'str',
            'living' => 'str',
            'name1' => 'str',
            'name2' => 'str',
            'name3' => 'str',
            'child_in_family' => 'nullint',
            'lspouse' => 'nullint',
            'rspouse' => 'nullint',
            'l_media_id' => 'nullint',
            'r_media_id' => 'nullint',
            'tt_index' => 'nullint',
            'box_text' => 'str'],
        'family' => [
            'id' => 'int',
            'tree_id' => 'int',
            'nchild' => 'int',
            'label' => 'str',
            'pref_type' => 'str',
            'wife' => 'nullint',
            'husband' => 'nullint',
            'tt_index' => 'nullint'],
        'note' => [
            'id' => 'int',
            'tree_id' => 'int',
            'label' => 'str',
            'note' => 'str'],
        'source' => [
            'id' => 'int',
            'tree_id' => 'int',
            'label' => 'str',
            'filed_by_entry' => 'str',
            'originator' => 'str',
            'title' => 'str',
            'publication_facts' => 'str',
            'text' => 'str'],
        'submitter' => [
            'id' => 'int',
            'tree_id' => 'int',
            'label' => 'str',
            'name' => 'str',
            'registered_RFN' => 'str',
            'address_id' => 'nullint'],
        'media' => [
            'id' => 'int',
            'tree_id' => 'int',
            'label' => 'str',
            'format' => 'str',
            'title' => 'str',
            'mime_type' => 'str',
            'content' => 'str'],
        'place' => [
            'id' => 'int',
            'tree_id' => 'int',
            'place_hierarchy' => 'nullstr',
            'place_value' => 'str']
    ];

    if (array_key_exists($type, $definitions)) {
        $defs = $definitions[$type];
        if(!is_array($data)) {
            echo "Data variable has not been passed into db_typing:<BR>";
            var_dump($data);
            var_dump($definitions);
            debug_print_backtrace();
            die();
        }
        foreach ($data as $key => &$value) {
            if (array_key_exists($key, $defs)) {
                switch ($defs[$key]) {
                    case 'int':
                        $value = intval($value);
                        break;
                    case 'nullint':
                        if ($value != null) {
                            $value = intval($value);
                        }
                        break;
                    case 'str':
                        $value = strval($value);
                        break;
                    case 'nullstr':
                        if ($value != null) {
                            $value = strval($value);
                        }
                        break;
                    case 'bool':
                        $value = boolval($value);
                        break;
                    case 'float':
                        $value = floatval($value);
                        break;
                    default:
                        echo "Type check on unknown subtype: " . $defs[$key] . ", in key: " . $key . ", in type: " . $type;
                        die();
                        break;
                }
            } else {
                echo "Type check on unknown key: " . $key . ", in type: " . $type;
                die();
            }
        }
    } else {
        echo "Type check on unknown type: " . $type;
        die();
    }
}
