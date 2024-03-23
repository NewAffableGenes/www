<?php

function log_error($desc)
{
    error_log('Logged error: ' . $desc);
    $trace = debug_backtrace();
    foreach ($trace as $trl) {
        error_log('File: ' . $trl['file'] . ', Line: ' . $trl['line'] . ' (' . $trl['function'] . ')');
        // Could also show Args
        // error_log('Args: ' . print_r($trl['args'], true));
    }
}

include $path . "includes/defaults.php";
include $path . "includes/db_typing.php";
include $path . "includes/db_calls.php";

function check_tree($mysqli, $treeId)
{ // TODO Complete this function
    // After importing check data - e.g. nChild in Family data
}

function show_as_many_as_possible($mysqli, $treeId)
{

// THIS FUNCTION SEEMS TO BE ABLE TO REPEAR LSPOUSE & RSPOUSE
// Also does not correctly show Fred's daughter when there is no spouse!!!!!

    $maxStack = 6;
    // Record who is 'root'
    $temp = read_assoc($mysqli, 'tree', $treeId);
    $root = $temp['root'];

    // Get an array of all data for Individuals and Families in the tree - Just relationship data
    // This saves reading and writing multiple times as we go along
    // Collect lists of all the families in this tree and store them in a local assoc_array
    $temp = read_all_assoc($mysqli, 'family', $treeId);
    $families = [];
    foreach ($temp as $fam) {
        $families[$fam['id']] = [
            "id" => $fam['id'],
            "wife" => $fam['wife'],
            "husband" => $fam['husband'],
            "pref_type" => $fam['pref_type'],
            "children" => []
        ];
    }

    // Collect lists of all the people in this tree and add them to
    // the families list as children if appropriate

    $temp = read_all_assoc($mysqli, 'individual', $treeId);
    $people = [];
    foreach ($temp as &$indi) {
        $BirthDate = get_birth($mysqli, $indi['id'], $treeId); // returns null or CDateValue type Use member function RoughYear()
        if ($BirthDate == null)
        {
            $DoB = 0;
        } else {
            $DoB = $BirthDate->RoughDoB();
        }
        // echo $DoB . '<br>';
        $people[$indi['id']] = [
            "id" => $indi['id'],
            "sex" => $indi['sex'],
            "show_me" => 't', // Everybody starts at the top and not stacked
            "child_in_family" => $indi['child_in_family'],
            "was_lspouse" => $indi['lspouse'],
            "was_rspouse" => $indi['rspouse'],
            "lspouse" => null,
            "rspouse" => null, 
            "place" => 0, // intval($indi['place_in_family_sibling_list']),
            "spouses" => [],
            "DoB" => $DoB,
            "priority" => -1
        ]; // 'Priority records closeness' to root
        if ($indi['child_in_family'] != null) {
            array_push($families[$indi['child_in_family']]['children'], $indi['id']);
        }
    }

    $temp = null; // Release the memory

    // Sort the children in families from eldest to youngest
    foreach ($families as &$fam) {
        // but only bother if there's more than 1 child
        if (count($fam['children']) > 1) {
            $siblings = [];
            foreach ($fam['children'] as $child) {
                array_push($siblings, [$child, $people[$child]['DoB']]);
            }
            usort($siblings, function ($a, $b) {
                return $a[1] > $b[1];
            });
            $fam['children'] = [];
            foreach ($siblings as $child) {
                array_push($fam['children'], $child[0]);
            }
        }
    }

    // Add spouses to individuals and what family they come from
    foreach ($families as &$fam) {
        $w = $fam['wife'];
        $h = $fam['husband'];
        // Only record spouses if there are 2 in this family
        if (($w != null) && ($h != null)) {
            array_push($people[$w]['spouses'], [$h, count($fam['children'])]);
            array_push($people[$h]['spouses'], [$w, count($fam['children'])]);
        }
    }

    // For each individual sort their spouses by how many children they share
    foreach ($people as &$indi) {
        usort($indi['spouses'], function ($a, $b) {
            return $a[1] > $b[1];
        });
    }

    // Set priority for each Indi by going through the structure expanding out from the Root
    // Initially all priority numbers are -1 (see above where $people is initialised
    $pri = 0.0;
    $people[$root]['priority'] = $pri;

    $someDone = true;
    while ($someDone) {
        $someDone = false;
        foreach ($people as &$indi) {
            if ($indi['priority'] > $pri - 1) {
                if ($indi['child_in_family'] != null) {
                    $siblings = $families[$indi['child_in_family']]['children']; // Their siblings
                    $n = count($siblings);
                    $i = 0;
                    foreach ($siblings as $sib) {
                        if ($people[$sib]['priority'] == -1) {
                            $people[$sib]['priority'] = $pri + 0.1 + 0.1 * $i / $n;
                            $i = $i + 1;
                            $someDone = true;
                        }
                    }
                    $w = $families[$indi['child_in_family']]['wife']; // Their mother
                    if ($w != null) {
                        if ($people[$w]['priority'] == -1) {
                            $people[$w]['priority'] = $pri + 0.4;
                            $someDone = true;
                        }
                    }
                    $h = $families[$indi['child_in_family']]['husband']; // Their father
                    if ($h != null) {
                        if ($people[$h]['priority'] == -1) {
                            $people[$h]['priority'] = $pri + 0.5;
                            $someDone = true;
                        }
                    }
                }
                foreach ($families as &$fam) {
                    if (($fam['wife'] == $indi['id']) || ($fam['husband'] == $indi['id'])) {
                        $siblings = $fam['children']; // Their children
                        $n = count($siblings);
                        $i = 0;
                        foreach ($siblings as $sib) {
                            if ($people[$sib]['priority'] == -1) {
                                $people[$sib]['priority'] = $pri + 0.6 + 0.1 * $i / $n;
                                $i = $i + 1;
                                $someDone = true;
                            }
                        }
                        $w = $fam['wife']; // Their wife
                        if ($w != null) {
                            if ($people[$w]['priority'] == -1) {
                                $people[$w]['priority'] = $pri + 0.8;
                                $someDone = true;
                            }
                        }
                        $h = $fam['husband']; // Their husband
                        if ($h != null) {
                            if ($people[$h]['priority'] == -1) {
                                $people[$h]['priority'] = $pri + 0.9;
                                $someDone = true;
                            }
                        }
                    }
                }
            }
        }
        // Increment priority as we move outwards
        $pri++;
    }

    // If any individuals have a priority of -1 now then they are not linked to the Root!
    // So set all individuals with a priority of -1 to $pri which is now the next highest unused priority level
    foreach ($people as &$indi) {
        if ($indi['priority'] == -1) {
            $indi['priority'] = $pri;
        }
    }

    // Now assign each family with a priority which is min(priority mother, priority father, min(priority over
    // all children) + 0.1). Prioity and id are stored in a new matrix which will be sorted
    $ifamilies = [];
    foreach ($families as &$fam) {
        $fpri = $pri;
        if ($fam['wife'] != null) {
            $fpri = min($fpri, $people[$fam['wife']]['priority']);
        }
        if ($fam['husband'] != null) {
            $fpri = min($fpri, $people[$fam['husband']]['priority']);
        }
        $siblings = $fam['children']; // Their children
        foreach ($siblings as $sib) {
            $fpri = min($fpri, $people[$sib]['priority'] + 0.1);
        }
        array_push($ifamilies, [$fam['id'], $fpri]);
    }

    // Sort the families by their priority number
    usort($ifamilies, function ($a, $b) {
        return $a[1] > $b[1];
    });
    
    // Sort the children in families by priority
    foreach ($families as &$fam) {
        // but only bother if there's more than 1 child
        if (count($fam['children']) > 1) {
            $siblings = [];
            foreach ($fam['children'] as $child) {
                array_push($siblings, [$child, $people[$child]['priority']]);
            }
            usort($siblings, function ($a, $b) {
                return $a[1] > $b[1];
            });
            $fam['children'] = [];
            foreach ($siblings as $child) {
                array_push($fam['children'], $child[0]);
            }
        }
    }

    // Work through families in the prescribed order
    foreach ($ifamilies as $ifam) {
        $fam = $families[$ifam[0]];

        // Display the parents if we can
        $w = $fam['wife'];
        $h = $fam['husband'];
        // Only define position of spouses if there are 2 in this family
        if (($w != null) && ($h != null)) {
            // Check if these spouses are already shown together
            if (!(( ($people[$w]['lspouse'] == $h) && ($people[$h]['rspouse'] == $w) ) ||
                ( ($people[$w]['rspouse'] == $h) && ($people[$h]['lspouse'] = $w) ) )) {
                // Show the spouses if there is a slot
                if (($people[$w]['lspouse'] == null) && ($people[$h]['rspouse'] == null)) {
                    $people[$w]['lspouse'] = $h;
                    $people[$h]['rspouse'] = $w;
                } else if (($people[$w]['rspouse'] == null) && ($people[$h]['lspouse'] == null)) {
                    $people[$w]['rspouse'] = $h;
                    $people[$h]['lspouse'] = $w;
                }
            }
        }

        // Now for the children who are all draw_me = 't' and place = 0 by default
        // If there is more than 1 child we need to worry about what order they are drawn in
        // The following code could be made much more efficient !!
        if (count($fam['children']) > 1) {
            // Note: The children are already in priority order which is based firstly on their closeness
            // to the root individual and then their age
            $siblings = $fam['children'];
            $n = count($siblings);

            // The leftmost and rightmost child positions are privileged because spouses drawn 'outside' there can have their own
            // families shown in full. Create variables to store which child goes left and right       
            $lchild = null;
            $rchild = null;

            // We must respect any LSpouse and RSpouse already given because they must have come from a higher
            // priority family. So that determines the first choice for left and right child
            foreach ($siblings as $child) {
                if ($people[$child]['lspouse'] != null) {
                    if ($lchild == null) $lchild = $child;
                }
                if ($people[$child]['rspouse'] != null) {
                    if ($rchild == null) $rchild = $child;
                }
            }

            // Look for a deserving sibling to fill left or right child, starting with anyone who has spouse who is a child in a family
            foreach ($siblings as $child) {
                if (($lchild != $child) && ($rchild != $child)) { // Make sure we havent alread got them on left or right
                    foreach ($people[$child]['spouses'] as $spousedata) {
                        $spouse = $spousedata[0];
                        if (($people[$child]['lspouse'] != $spouse) && ($people[$child]['rspouse'] != $spouse)) {
                            if ($people[$spouse]['child_in_family'] != null) {
                                // If we've got here this child is a good candidate for left or right child because of this spouse
                                // Make sure they are not shown already together and there is room for them
                                // Try left and then right
                                if (($lchild == null) && 
                                        ($people[$child]['lspouse'] == null) && 
                                        ($people[$child]['rspouse'] != $spouse) && 
                                        ($people[$spouse]['rspouse'] == null) ) {
                                    $lchild = $child;
                                    $people[$child]['lspouse'] = $spouse;
                                    $people[$spouse]['rspouse'] = $child;
                                } else if (($rchild == null) && 
                                        ($people[$child]['rspouse'] == null) && 
                                        ($people[$child]['lspouse'] != $spouse) && 
                                        ($people[$spouse]['lspouse'] == null) ) {
                                    $rchild = $child;
                                    $people[$child]['rspouse'] = $spouse;
                                    $people[$spouse]['lspouse'] = $child;
                                }
                            }
                        }
                    }
                }
            }

            // Look for the next most deserving siblings to fill left or right child
            // Look for a deserving sibling to fill left or right child, starting with anyone who has spouse who is a child in a family
            foreach ($siblings as $child) {
                if (($lchild != $child) && ($rchild != $child)) { // Make sure we havent alread got them on left or right
                    foreach ($people[$child]['spouses'] as $spousedata) {
                        $spouse = $spousedata[0];
                        if (($people[$child]['lspouse'] != $spouse) && ($people[$child]['rspouse'] != $spouse)) {
                            // If we've got here this child is a good candidate for left or right child because of this spouse
                            // Make sure they are not shown already together and there is room for them
                            // Try left and then right
                            if (($lchild == null) && 
                                    ($people[$child]['lspouse'] == null) && 
                                    ($people[$child]['rspouse'] != $spouse) && 
                                    ($people[$spouse]['rspouse'] == null) ) {
                                $lchild = $child;
                                $people[$child]['lspouse'] = $spouse;
                                $people[$spouse]['rspouse'] = $child;
                            } else if (($rchild == null) && 
                                    ($people[$child]['rspouse'] == null) && 
                                    ($people[$child]['lspouse'] != $spouse) && 
                                    ($people[$spouse]['lspouse'] == null) ) {
                                $rchild = $child;
                                $people[$child]['rspouse'] = $spouse;
                                $people[$spouse]['lspouse'] = $child;
                            }
                        }
                    }
                }
            }

            // Thats the left and right child sorted and the l/r spouse has been set where necessary but
            // the other side of the left and right children have not had their spouses populated - neither
            // have the children who are not left and right. So display any other spouses possible. Note:
            // the spouses are already sorder by the number of children shared
            
            foreach ($siblings as $child) {
                foreach ($people[$child]['spouses'] as $spousedata) {
                    $spouse = $spousedata[0];
                    if (($people[$child]['lspouse'] != $spouse) && ($people[$child]['rspouse'] != $spouse)) {
                        if ( ($people[$child]['lspouse'] == null) && ($people[$spouse]['rspouse'] == null) ) {
                            $people[$child]['lspouse'] = $spouse;
                            $people[$spouse]['rspouse'] = $child;
                        } else if ( ($people[$child]['rspouse'] == null) && ($people[$spouse]['lspouse'] == null) ) {
                            $people[$child]['rspouse'] = $spouse;
                            $people[$spouse]['lspouse'] = $child;
                        }
                    }
                }
            }

            // Give each child their correct place and set up any stacking that's needed
            $ichild = 0;
            if ($lchild != null) {
                $people[$lchild]['place'] = $ichild;
                $ichild++;
            }
            $stackCnt = 0;
            foreach ($siblings as $child) {
                if (($child != $lchild) && ($child != $rchild) && (count($people[$child]['spouses']) == 0)) {
                    if ($stackCnt == 0) {
                        $people[$child]['show_me'] = 't';
                    } else {
                        $people[$child]['show_me'] = 'b';
                    }
                    $stackCnt++;
                    if ($stackCnt > $maxStack - 1) {
                        $stackCnt = 0;
                    }
                    $people[$child]['place'] = $ichild;
                    $ichild++;
                }
            }
            foreach ($siblings as $child) {
                if (($child != $lchild) && ($child != $rchild) && (count($people[$child]['spouses']) != 0)) {
                    $people[$child]['place'] = $ichild;
                    $ichild++;
                }
            }
            if ($rchild != null) {
                $people[$rchild]['place'] = $ichild;
                $ichild++;
            }
        }
    }

    foreach ($people as $indi) {
        update_assoc($mysqli, 'individual', $indi['id'], [
            'show_me' => $indi['show_me'],
            'lspouse' => $indi['lspouse'],
            'rspouse' => $indi['rspouse'],
            'place_in_family_sibling_list' => $indi['place']
        ]);
    }
}

// Source: http://php.net/manual/en/function.imagettfbbox.php#75407
function imagetextbox($size, $fontfile, $text)
{
    $bbox = imagettfbbox($size, 0, $fontfile, $text);
    $bbox2 = imagettfbbox($size, 0, $fontfile, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    $gap = $size * 0.1;
    return [
        't' => $bbox2[7] - $gap,
        'b' => $bbox2[1] + $gap,
        'l' => $bbox[0] - $gap,
        // 'r' => $bbox[2] + $gap, // - $bbox[0],
        'h' => abs($bbox2[1] - $bbox2[7]) + 2 * $gap,
        'w' => abs($bbox[2] - $bbox[0]) * 1.01 + 2 * $gap
    ];
}

function imagetextout($im, $xi, $yi, $fontData, $text, $centre = false)
{
    // $centre => true - xi coord is centre, false - xi coord is left edge
    // $yi is the coordinate ot the top of the box
    $fontFile = getFontFile($fontData);
    $size = $fontData['size'];
    $gap = $size * 0.1;
    $color = imagecolorallocate($im, $fontData['font_R'], $fontData['font_G'], $fontData['font_B']);
    $bbox = imagetextbox($size, $fontFile, $text);
    if ($centre) {
        $x = $xi - $bbox['l'] - $bbox['w'] / 2.0;
    } else {
        $x = $xi - $bbox['l'];
    }
    $y = $yi - $bbox['t'];
    $w = $bbox['w'] - 2 * $gap;
    imagettftext($im, $size, 0, $x, $y, $color, $fontFile, $text);
    if ($fontData['underline']) {
        imagefilledrectangle($im, $x, $y + $bbox['b'] * 0.25, $x + $w, $y + $bbox['b'] * 0.35, $color);
    }
    return $yi + $bbox['h']; // Returns the y coord of the next line
}

function getFontFile($font)
{
    $style = "";
    if ($font['bold']) {
        $style .= 'b';
    }
    if ($font['oblique']) {
        $style .= 'i';
    }
    $family = strtolower($font['style']);
    return TTF_FONTPATH . '/' . $family . $style . '.ttf';
}

/**
 * @Push and element onto the end of an array with associative key
 * @param array $array
 * @string $key
 * @mixed $value
 * @return array
 */
function RGBStr($r, $g, $b)
{
    $str = '0' . dechex(256 * (256 * $r + $g) + $b);
    $str = "#" . substr($str, strlen($str) - 6);
    return $str;
}

function aspect_ratios()
{
    return [
        [1.7778, "HD Landscape, 16 : 9"],
        [1.4142, "A-Series Landscape, &#8730;2 : 1"],
        [1.3333, "Traditional TV Landscape, 4 : 3"],
        [1.0, "Square, 1:1"],
        [1.0 / 1.3333, "Traditional TV Portrait, 3 : 4"],
        [1.0 / 1.4142, "A-Series Portrait, 1 : &#8730;2"],
        [1.0 / 1.7778, "HD Portrait, 9 : 16"]
    ];
}

function font_families()
{
    return [
        ["courier", "Courier"],
        ["helvetica", "Helvetica"],
        ["times", "Times Roman"]
    ];
}

function font_sizes()
{
    return [
        [6, "6"],
        [8, "8"],
        [10, "10"],
        [12, "12"],
        [14, "14"],
        [18, "18"],
        [24, "24"],
        [36, "36"],
        [48, "48"],
        [72, "72"],
        [96, "96"]
    ];
}

function size_str($size)
{
    $ret = "NK";
    if ($size < 1000) {
        $ret = round($size, 1) . 'Byte';
    } else if ($size < 1000000) {
        $ret = round($size / 1000, 1) . 'kB';
    } else {
        $ret = round($size / 1000000, 1) . 'MB';
    }
    return $ret;
}

function create_thumbnail($mysqli, $media_id, $max_w, $max_h, $treemedia_path)
{
    $thumbnail = null;
    if ($media_id !== null) {
        $object = read_assoc($mysqli, "media", $media_id);
        if ($object['content'] === null) {
            // Missing media
            $orig = imagecreatefromjpeg(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/img/Missing.jpg');
        } else {
            // echo 'format: ' . $object['format'] . '<br>';
            // echo 'format type: ' . gettype($object['format']) . '<br>';
            if (strpos("bmp.gif.jpeg.jpg", $object['format']) === false) {
                // Unsupported format 'No Image'
                // echo 'No image<br>';
                $orig = imagecreatefromjpeg(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/img/NoImage.jpg');
            } else {
                $orig = imagecreatefromjpeg($treemedia_path . $object['content']);
            }
        }
        $old_w = imagesx($orig);
        $old_h = imagesy($orig);
        $zoom = min($max_w / $old_w, $max_h / $old_h);
        $new_w = $zoom * $old_w;
        $new_h = $zoom * $old_h;
        $thumbnail = imagecreatetruecolor($new_w, $new_h);
        imagecopyresampled($thumbnail, $orig, 0, 0, 0, 0, $new_w, $new_h, $old_w, $old_h);
    }
    return $thumbnail;
}

function str_truncate($str, $nchar)
{
    $tmp = $str;
    if (strlen($tmp) > $nchar) {
        $tmp = substr($tmp, 0, $nchar);
    }
    return $tmp;
}

function array_push_assoc(&$array, $key, $value)
{
    $array[$key] = $value;
    return $array;
}

function individualDescription($array)
{
    $Name = "";
    if (strlen($array['name2']) == 0) {
        $Name = "... , ";
    } else {
        $Name = $array['name2'] . ", ";
    }

    if (strlen($array['name1']) == 0) {
        $Name .= "...";
    } else {
        $Name .= $array['name1'];
    }

    if (strlen($array['name3']) > 0) {
        $Name .= " " . $array['name3'];
    }
    return $Name;
}

function noteDescription($note)
{
    $ns = $note['note'];
    if (strlen($ns) > 90) {
        $ns = substr($ns, 0, 90) . "...";
    }
    if (strlen($ns) == 0) {
        $ns = "Blank note";
    }
    return $ns;
}

function SourceBriefDescriptor($array)
{
    $ns = $array['title'];
    if (strlen($ns) > 60) {
        $ns = substr($ns, 0, 60) . "...";
    }
    if (strlen($ns) == 0) {
        $ns = "Source without title";
    }
    return $ns;
}

function MediaBriefDescriptor($array)
{
    $ns = $array['title'];
    if (strlen($ns) > 60) {
        $ns = substr($ns, 0, 60) . "...";
    }
    if (strlen($ns) == 0) {
        $ns = "[No title]";
    }
    if ($array['content'] == null) {
        $ns .= " [Missing]";
    }
    // $ns .= " [Label: " . $array['label'] . "]";
    return $ns;
}

function AddLine(&$rtn, $newline, $separator)
{
    if ($newline != null) {
        if (strlen($newline) > 0) {
            if (strlen($rtn) > 0) {
                $rtn .= $separator;
            }
            $rtn .= $newline;
        }
    }
}

function CitationBriefDescription($mysqli, $array)
{
    $temp = $array['text_from_source'];
    if (strlen($temp) > 60) {
        $temp = substr($temp, 0, 60) . " ...";
    } else {
        if ($array['entry_recording_date_id'] != null) {
            $date = new CDateValue();
            $date->ReadDatabase($mysqli, $array['entry_recording_date_id']);
            $temp .= " " . $date->ShortStyle();
        }
    }
    return $temp;
}

function EventBriefDescription($mysqli, $array)
{
    $temp = "";
    if ($array['event_date_id'] != null) {
        $date = new CDateValue();
        $date->ReadDatabase($mysqli, $array['event_date_id']);
        $temp .= " " . $date->ShortStyle();
    }
    AddLine($temp, $array['argument'], ", ");
    AddLine($temp, $array['event_descriptor'], ", ");
    $temp = ExpandType($array['type']) . ': ' . $temp;
    return $temp;
}

function PlaceHTMLDescription($array)
{
    $temp = "";
    AddLine($temp, $array['place_value'], "<br>");
    AddLine($temp, $array['place_hierarchy'], "<br>");
    if (strlen($temp) == 0) {
        $temp = "Blank";
    }
    return $temp;
}

function AddressHTMLDescription($array)
{
    $temp = "";
    AddLine($temp, $array['line1'], "<br>");
    AddLine($temp, $array['line2'], "<br>");
    AddLine($temp, $array['line3'], "<br>");
    AddLine($temp, $array['city'], "<br>");
    AddLine($temp, $array['state'], "<br>");
    AddLine($temp, $array['postal_code'], "<br>");
    AddLine($temp, $array['country'], "<br>");
    AddLine($temp, $array['phone'], "<br>");
    if (strlen($temp) == 0) {
        $temp = "Blank";
    }
    return $temp;
}

function ExpandType($TypeIn)
{
    switch ($TypeIn) {
        case "ABBR":
            return "Abbreviation";
        case "ADDR":
            return "Address";
        case "ADR1":
            return "Address 1";
        case "ADR2":
            return "Address 2";
        case "ADOP":
            return "Adoption";
        case "AFN":
            return "Ancestral File Number";
        case "AGE":
            return "Age";
        case "AGNC":
            return "Agency";
        case "ALIA":
            return "Alias";
        case "ANCE":
            return "Ancestors";
        case "ANCI":
            return "Ancestor interest";
        case "ANUL":
            return "Annulment";
        case "ASSO":
            return "Associates";
        case "AUTH":
            return "Author";
        case "BAPL":
            return "Baptism (LDS)";
        case "BAPM":
            return "Baptism";
        case "BARM":
            return "Bar Mitzvah";
        case "BASM":
            return "Bas Mitzvah";
        case "BIRT":
            return "Birth";
        case "BLES":
            return "Blessing";
        case "BLOB":
            return "Binary Object";
        case "BURI":
            return "Burial";
        case "CALN":
            return "Call Number";
        case "CAST":
            return "Caste";
        case "CAUS":
            return "Cause";
        case "CENS":
            return "Cencus";
        case "CHAN":
            return "Change";
        case "CHAR":
            return "Character";
        case "CHIL":
            return "Child";
        case "CHR":
            return "Christening";
        case "CHRA":
            return "Adult Christening";
        case "CITY":
            return "City";
        case "CONF":
            return "Confirmation";
        case "CONL":
            return "Confirmation (LDS)";
        case "CONT":
            return "Continued";
        case "COPR":
            return "Copyright";
        case "CORP":
            return "Corporate";
        case "CREM":
            return "Cremation";
        case "CTRY":
            return "Country";
        case "DATA":
            return "Data";
        case "DATE":
            return "Date";
        case "DEAT":
            return "Death";
        case "DESC":
            return "Descendants";
        case "DESI":
            return "Descendants Interest";
        case "DEST":
            return "Destination";
        case "DIV":
            return "Divorce";
        case "DIVF":
            return "Divorce Filed";
        case "DSCR":
            return "Physical_Description";
        case "EDUC":
            return "Education";
        case "EMIG":
            return "Emigration";
        case "ENDL":
            return "Endowment";
        case "ENGA":
            return "Engagement";
        case "EVEN":
            return "General Event";
        case "FAM":
            return "Family";
        case "FAMC":
            return "Family Child";
        case "FAMF":
            return "Family File";
        case "FAMS":
            return "Family Spouce";
        case "FCOM":
            return "First Communion";
        case "FILE":
            return "File";
        case "FORM":
            return "Format";
        case "GEDC":
            return "GEDCOM";
        case "GIVN":
            return "Given Name";
        case "GRAD":
            return "Graduation";
        case "HEAD":
            return "Header";
        case "HUSB":
            return "Husband";
        case "IDNO":
            return "Identification Number";
        case "IMMI":
            return "Immigration";
        case "INDI":
            return "Individual";
        case "LANG":
            return "Language";
        case "LEGA":
            return "Legatee";
        case "MARB":
            return "Marriage Bann";
        case "MARC":
            return "Marriage Contract";
        case "MARL":
            return "Marriage Licence";
        case "MARR":
            return "Marriage";
        case "MARS":
            return "Marriage Settlement";
        case "MEDI":
            return "Media";
        case "NAME":
            return "Name";
        case "NATI":
            return "Nationality";
        case "NATU":
            return "Naturalization";
        case "NCHI":
            return "Children Count";
        case "NICK":
            return "Nickname";
        case "NMR":
            return "Marriage Count";
        case "NOTE":
            return "Note";
        case "NPFX":
            return "Name Prefix";
        case "NSFX":
            return "Name Suffix";
        case "OBJE":
            return "Object";
        case "OCCU":
            return "Occupation";
        case "ORDI":
            return "Ordinance";
        case "ORDN":
            return "Ordination";
        case "PAGE":
            return "Page";
        case "PEDI":
            return "Pedigree";
        case "PHON":
            return "Phone";
        case "PLAC":
            return "Place";
        case "POST":
            return "Postal Code";
        case "PROB":
            return "Probate";
        case "PROP":
            return "Property";
        case "PUBL":
            return "Publication";
        case "QUAY":
            return "Quality Of Data";
        case "REFN":
            return "Reference";
        case "RELA":
            return "Relationship";
        case "RELI":
            return "Religion";
        case "REPO":
            return "Repository";
        case "RESI":
            return "Residence";
        case "RESN":
            return "Restriction";
        case "RETI":
            return "Retirement";
        case "RFN":
            return "Record File Number";
        case "RIN":
            return "Record ID Number";
        case "ROLE":
            return "Role";
        case "SEX":
            return "Sex";
        case "SLGC":
            return "Sealing Child";
        case "SLGS":
            return "Sealing Spouse";
        case "SOUR":
            return "Source";
        case "SPFX":
            return "Surname Prefix";
        case "SSN":
            return "Social Security Number";
        case "STAE":
            return "State";
        case "STAT":
            return "Status";
        case "SUBM":
            return "Submitter";
        case "SUBN":
            return "Submission";
        case "SURN":
            return "Surname";
        case "TEMP":
            return "Temple";
        case "TEXT":
            return "Text";
        case "TIME":
            return "Time";
        case "TITL":
            return "Title";
        case "TRLR":
            return "Trailer";
        case "TYPE":
            return "Type";
        case "VERS":
            return "Version";
        case "WIFE":
            return "Wife";
        case "WILL":
            return "Will";
        default:
            return "Undefined event";
    }
}

function familyDescription($mysqli, $value)
{
    $famDesc = "";
    if ($value['husband'] == null) {
        $famDesc .= "[Unknown]: [Unknown] and ";
    } else {
        $row = read_assoc($mysqli, 'individual', $value['husband']);
        if (strlen($row['name2']) > 0) {
            $famDesc .= $row['name2'] . ": ";
        } else {
            $famDesc .= "[Unknown]: ";
        }
        if (strlen($row['name1']) > 0) {
            $famDesc .= $row['name1'] . " and ";
        } else {
            $famDesc .= "[Unknown] and ";
        }
    }
    if ($value['wife'] == null) {
        $famDesc .= "[Unknown]";
    } else {
        $row = read_assoc($mysqli, 'individual', $value['wife']);
        if (strlen($row['name1']) > 0) {
            $famDesc .= $row['name1'];
        } else {
            $famDesc .= "[Unknown]";
        }
        if (strlen($row['name2']) > 0) {
            $famDesc .= " (ne " . $row['name2'] . ")";
        }
    }
    $famDesc .= " with " . $value['nchild'];
    if ($value['nchild'] == 1) {
        $famDesc .= " child";
    } else {
        $famDesc .= " children";
    }
    return $famDesc;
}

function addDecriptionAndRedirectForObject($mysqli, &$line, $Object, $class)
{
    $refType = $Object['belongs_to_class'];
    $refId = $Object['belongs_to_id'];
    $array = read_assoc($mysqli, $refType, $refId);
    if ($refType == 'individual') {
        array_push($line, "Person: " . individualDescription($array));
    } else if ($refType == 'family') {
        array_push($line, "Family: " . familyDescription($mysqli, $array));
    } else if ($refType == 'note') {
        array_push($line, "Note: " . noteDescription($array));
    } else if ($refType == 'source') {
        array_push($line, "Source: " . sourceBriefDescriptor($array));
    } else if ($refType == 'event') {
        array_push($line, "Event: " . EventBriefDescription($mysqli, $array));
    } else if ($refType == 'citation') {
        array_push($line, "Citation: " . CitationBriefDescription($mysqli, $array));
    } else if ($refType == 'media') {
        array_push($line, "Media: " . MediaBriefDescriptor($array));
    }
    array_push($line, "/edit/edit_" . $refType . ".php?" . $class[$refType]["rtn"] . "=" . $array['id']);
}

function completeFALList($mysqli, $type, $id, $treeId, $class)
{
    $FALStrings = array();
    $control = [
        ["event", "event", false],
        ["note", "note_link", true],
        ["citation", "citation", false],
        ["submitter", "submitter_link", true],
        ["media", "media_link", true]
    ];
    foreach ($control as $cont) {
        if ($class[$type][$cont[0] . '_allowed']) {
            $objects = read_all_assoc_that_belong($mysqli, $cont[1], $treeId, $type, $id);
            if ($cont[0] == "event") {
                // put birth first and death next
                usort($objects, function ($a, $b) {
                    $type_a = $a['type'];
                    $na = 3;
                    if ($type_a == 'BIRT') {
                        $na = 1;
                    }
                    if ($type_a == 'DEAT') {
                        $na = 2;
                    }
                    $type_b = $b['type'];
                    $nb = 3;
                    if ($type_b == 'BIRT') {
                        $nb = 1;
                    }
                    if ($type_b == 'DEAT') {
                        $nb = 2;
                    }
                    return $na - $nb;
                });
            }
            foreach ($objects as $object) {
                if ($cont[2]) {
                    $fid = $object[$cont[0] . '_id'];
                    $final = read_assoc($mysqli, $cont[0], $fid);
                } else {
                    $fid = $object['id'];
                    $final = $object;
                }

                $desc = "";
                if ($cont[0] == "event") {
                    $desc = EventBriefDescription($mysqli, $final);
                } else if ($cont[0] == "note") {
                    $desc = "Note: " . noteDescription($final);
                } else if ($cont[0] == "citation") {
                    $desc = "Citation: " . CitationBriefDescription($mysqli, $final);
                } else if ($cont[0] == "submitter") {
                    $desc = "Submitter: " . $final['name'];
                } else if ($cont[0] == "media") {
                    $desc = "Media: " . MediaBriefDescriptor($final);
                }
                if (strlen($desc) == 0) {
                    $desc = "Blank";
                }
                $line = [
                    $desc,
                    '/edit/edit_' . $cont[0] . '.php?' . $class[$cont[0]]['rtn'] . '=' . $fid,
                    '/edit/delete_' . $cont[0] . '.php?' . $class[$cont[0]]['rtn'] . '=' . $fid .
                        '&type=' . $type . '&' . $class[$type]['rtn'] . '=' . $id
                ];
                array_push($FALStrings, $line);
            }
            $line = array(
                "New $cont[0] ...",
                "/edit/new_" . $cont[0] . ".php?type=" . $type . '&' . $class[$type]['rtn'] . '=' . $id,
                ''
            );
            array_push($FALStrings, $line);
        }
    }
    return $FALStrings;
}

function notNullStr($x, $maxlen = -1)
{
    $str = strval($x);
    if (($maxlen > 0) && (strlen($str) > $maxlen)) {
        $str = substr($str, 0, $maxlen);
    }
    return $str;
}

function notNullInputPost($x)
{
    $str = filter_input(INPUT_POST, $x);
    notNullStr($str);
    return $str;
}

function autoBoxText($mysqli, $treeId, $i)
{
    $BoxText = "";
    $l1 = strlen($i['name1']);
    $l2 = strlen($i['name2']);
    $l3 = strlen($i['name3']);
    if ($l1 + $l2 + $l3 == 0) {
        $BoxText = "Unknown";
    } else {
        if ($l1 > 0) {
            $BoxText = $i['name1'];
        }
        if ($l2 > 0) {
            if ($l1 > 0) {
                $BoxText = $BoxText . ' ';
            }
            $BoxText = $BoxText . $i['name2'];
        }
        if ($l3 > 0) {
            if ($l1 + $l2 > 0) {
                $BoxText = $BoxText . ' ';
            }
            $BoxText = $BoxText . $i['name3'];
        }
    }

    $BirthDate = get_birth($mysqli, $i['id'], $treeId);
    $DeathDate = get_death($mysqli, $i['id'], $treeId);

    if (($BirthDate != null) || ($DeathDate != null)) {
        $BoxText .= "\n(";
    }
    if ($BirthDate != null) {
        $BoxText .= "b. " . $BirthDate->ShortStyle();
    }
    if (($BirthDate != null) && ($DeathDate != null)) {
        $BoxText .= ", ";
    }
    if ($DeathDate != null) {
        $BoxText .= "d. " . $DeathDate->ShortStyle();
    }
    if (($BirthDate != null) || ($DeathDate != null)) {
        $BoxText .= ")";
    }
    return $BoxText;
}

function strToArray($OutText)
{
    $lines = [];
    $EndOfText = false;
    $StartOfNextLine = 0;
    $EndOfNextLine = 0;

    //Continue while there is more text
    while (!$EndOfText) {
        if ($StartOfNextLine >= strlen($OutText)) {
            $ThisLine = "";
            $EndOfText = true;
        } else {
            $nNext = strpos($OutText, chr(10), $StartOfNextLine);
            $rNext = strpos($OutText, chr(13), $StartOfNextLine);
            if (($nNext === false) && ($rNext === false)) {
                $ThisLine = substr($OutText, $StartOfNextLine);
                $EndOfText = true;
            } else {
                if ($nNext === false) {
                    $nNext = $rNext;
                }
                if ($rNext === false) {
                    $rNext = $nNext;
                }
                if ($nNext > $rNext + 1) {
                    $nNext = $rNext;
                }
                if ($rNext > $nNext + 1) {
                    $rNext = $nNext;
                }
                $EndOfNextLine = min($nNext, $rNext);
                $ThisLine = substr($OutText, $StartOfNextLine, $EndOfNextLine - $StartOfNextLine);
                $EndOfNextLine = max($nNext, $rNext);
            }
        }

        if (strlen($ThisLine) === 0) {
            $ThisLine = " ";
        }

        array_push($lines, $ThisLine);
        $StartOfNextLine = $EndOfNextLine + 1;
    }

    return $lines;
}
