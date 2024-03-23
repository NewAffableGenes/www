<?php

function createDefaultTree($mysqli, $userId, $media_path) {
    // Do the tree
    $data = array(
        "created_by" => $userId,
        "aspect_ratio" => 1.414,
        "title" => "Your title here",
        "line_thickness" => 4,
        "connecting_R" => 0,
        "connecting_G" => 128,
        "connecting_B" => 128,
        "outline_thickness" => 4,
        "watermark_media_id" => 4,
        "box_outline" => true,
        "outline_R" => 128,
        "outline_G" => 64,
        "outline_B" => 0,
        "min_indi_H" => 40,
        "min_indi_W" => 60,
        "line_height" => 15,
        "sibling_gap" => 20,
        "marriage_gap" => 20,
        "thumbnail_H" => 100,
        "thumbnail_W" => 100,
        "zoom" => 1.0);
    $treeId = create_assoc($mysqli, "tree", $data);
        
    // Title font
    $data = array(
        "tree_id" => $treeId,
        "style" => "helvetica",
        "size" => 48,
        "font_R" => 0,
        "font_G" => 0,
        "font_B" => 0,
        "background_R" => 255,
        "background_G" => 255,
        "background_B" => 255,
        "oblique" => false,
        "bold" => true,
        "underline" => true,
        "opaque_background" => false);
    $titleFont = create_assoc($mysqli, "font", $data);

    // Originator font
    $data = array(
        "tree_id" => $treeId,
        "style" => "courier",
        "size" => 14,
        "font_R" => 0,
        "font_G" => 0,
        "font_B" => 0,
        "background_R" => 255,
        "background_G" => 255,
        "background_B" => 255,
        "oblique" => false,
        "bold" => false,
        "underline" => false,
        "opaque_background" => false);
    $originatorFont = create_assoc($mysqli, "font", $data);

    // First line font
    $data = array(
        "tree_id" => $treeId,
        "style" => "helvetica",
        "size" => 14,
        "font_R" => 0,
        "font_G" => 0,
        "font_B" => 0,
        "background_R" => 128,
        "background_G" => 192,
        "background_B" => 255,
        "oblique" => false,
        "bold" => true,
        "underline" => false,
        "opaque_background" => true);
    $firstLineFont = create_assoc($mysqli, "font", $data);

    // Other line font
    $data = array(
        "tree_id" => $treeId,
        "style" => "helvetica",
        "size" => 14,
        "font_R" => 0,
        "font_G" => 0,
        "font_B" => 0,
        "background_R" => 230,
        "background_G" => 230,
        "background_B" => 230,
        "oblique" => false,
        "bold" => false,
        "underline" => false,
        "opaque_background" => true);
    $otherLineFont = create_assoc($mysqli, "font", $data);

    // Store links to the fonts
    $data = read_assoc($mysqli, "tree", $treeId);
    $data['title_font'] = $titleFont;
    $data['originator_font'] = $originatorFont;
    $data['first_line_font'] = $firstLineFont;
    $data['other_line_font'] = $otherLineFont;
    update_assoc($mysqli, "tree", $treeId, $data);

    // Make a place for the media if it doesn't exist
    if(!file_exists($media_path)) {
        mkdir($media_path, 0777);
    }
    return $treeId;
}

function createDefaultIndividual($mysqli, $treeId, $label) {
    $data = array(
        "label" => $label,
        "tree_id" => $treeId,
        "sex" => "u",
        "show_me" => "t",
        "name1" => "",
        "name2" => "",
        "name3" => "",
        "place_in_family_sibling_list" => 99,
        "living" => "u",
        "box_text" => "Your text here");
    return create_assoc($mysqli, "individual", $data);
}

function createDefaultFamily($mysqli, $treeId, $label) {
    $data = array(
        "label" => $label,
        "tree_id" => $treeId,
        "pref_type" => "w",
        "nchild" => 0);
    return create_assoc($mysqli, "family", $data);
}

function createDefaultNote($mysqli, $treeId, $label) {
    $data = array(
        "label" => $label,
        "tree_id" => $treeId,
        "note" => "");
    return create_assoc($mysqli, "note", $data);
}

function createDefaultSource($mysqli, $treeId, $label) {
    $data = array(
        "label" => $label,
        "tree_id" => $treeId,
        "filed_by_entry" => "",
        "originator" => "",
        "publication_facts" => "",
        "text" => "",
        "title" => "");
    return create_assoc($mysqli, "source", $data);
}

function createDefaultMedia($mysqli, $treeId, $label) {
    $data = array(
        "label" => $label,
        "tree_id" => $treeId,
        "title" => "",
        "mime_type" => "",
        "format" => "",
        "content" => null);
    return create_assoc($mysqli, "media", $data);
}

function createDefaultSubmitter($mysqli, $treeId, $label) {
    $data = array(
        "label" => $label,
        "tree_id" => $treeId,
        "address_id" => createDefaultAddress($mysqli, $treeId),
        "name" => "");
    return create_assoc($mysqli, "submitter", $data);
}

function createDefaultNoteLink($mysqli, $treeId, $type, $id, $note) {
    $data = array(
        "tree_id" => $treeId,
        "belongs_to_class" => $type,
        "belongs_to_id" => $id,
        "note_id" => $note);
    return create_assoc($mysqli, "note_link", $data);
}

function createDefaultMediaLink($mysqli, $treeId, $type, $id, $media) {
    $data = array(
        "tree_id" => $treeId,
        "belongs_to_class" => $type,
        "belongs_to_id" => $id,
        "media_id" => $media);
    return create_assoc($mysqli, "media_link", $data);
}

function createDefaultCitation($mysqli, $treeId, $type, $id) {
    $data = array(
        "tree_id" => $treeId,
        "belongs_to_class" => $type,
        "belongs_to_id" => $id,
        "where_within_source" => "",
        "role_in_event" => "",
        "text_from_source" => "",
        "certainty_assessment" => "",
        "event_type" => "");
    return create_assoc($mysqli, "citation", $data);
}

function createDefaultSubmitterLink($mysqli, $treeId, $type, $id, $submitter) {
    $data = array(
        "tree_id" => $treeId,
        "belongs_to_class" => $type,
        "belongs_to_id" => $id,
        "submitter_id" => $submitter);
    return create_assoc($mysqli, "submitter_link", $data);
}

function createDefaultEvent($mysqli, $treeId, $type, $id) { 
    $data = array(
        "tree_id" => $treeId,
        "belongs_to_class" => $type,
        "belongs_to_id" => $id,
        "address_id" => createDefaultAddress($mysqli, $treeId),
        "place_id" => createDefaultPlace($mysqli, $treeId),
        "type" => "",
        "adopted_by_which_parent" => "",
        "age_at_event" => "",
        "argument" => "",
        "cause_of_event" => "",
        "event_descriptor" => "",
        "husband_age" => "",
        "wife_age" => "",
        "responsible_agency" => "");
    return create_assoc($mysqli, "event", $data);
}

function createDefaultPlace($mysqli, $treeId) {
    $data = array(
        "tree_id" => $treeId,
        "place_hierarchy" => "",
        "place_value" => "");
    return create_assoc($mysqli, "place", $data);
}

function createDefaultAddress($mysqli, $treeId) {
    $data = array(
        "tree_id" => $treeId,
        "city" => "",
        "country" => "",
        "line1" => "",
        "line2" => "",
        "line3" => "",
        "phone" => "",
        "postal_code" => "",
        "state" => "");
    return create_assoc($mysqli, "address", $data);
}

function createDefaultComplexDate($mysqli, $treeId) {
    $data = array(
        "tree_id" => $treeId,
        "structure" => 10, // Interpreted
        "day1" => -1,
        "month1" => -1,
        "year1" => -1,
        "day2" => -1,
        "month2" => -1,
        "year2" => -1,
        "interpreted_string" => "");
    return create_assoc($mysqli, "complex_date", $data);
}
