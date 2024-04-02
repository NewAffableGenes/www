<?php

class GEDCOMExporter {

    var $mediaOption;
    var $UseExtras;
    var $class;

    function __construct($med, $extra, $class) {
        $this->mediaOption = $med;
        $this->UseExtras = $extra;
        $this->class = $class;
    }

    function writeHeader($mysqli, $treeId) {
        $data = read_assoc($mysqli, "tree", $treeId);
        $this->WriteLine(0, "", "HEAD", "", "");
        $this->WriteLine(1, "", "SOUR", "", "Approved System ID");
        $this->WriteLine(2, "", "VERS", "", "3.0.1");
        $this->WriteLine(2, "", "NAME", "", "AffableGenes (TM)");
        $this->WriteLine(2, "", "CORP", "", "AffableGenes.com");
        $this->WriteLine(1, "", "DATE", "", date("d M Y"));
        $this->WriteLine(2, "", "TIME", "", date("G:i:s"));
        if ($data['author'] !== null) {
            $subm = read_assoc($mysqli, "submitter", $data['author']);
            $this->WriteLine(1, "", "SUBM", $subm['label'], "");
        }
        $this->WriteLine(1, "", "GEDC", "", "");
        $this->WriteLine(2, "", "VERS", "", "5.5");
        $this->WriteLine(2, "", "FORM", "", "LINEAGE-LINKED");
        $this->WriteLine(1, "", "CHAR", "", "ANSEL");
        $this->WriteLine(1, "", "LANG", "", "English");

        if ($this->UseExtras) {
            $this->WriteLine(1, "", "_ASP", "", $this->DbltToText($data['aspect_ratio']));
            $this->WriteLine(1, "", "_SCX", "", $this->DbltToText($data['scroll_X']));
            $this->WriteLine(1, "", "_SCY", "", $this->DbltToText($data['scroll_Y']));
            $this->WriteLine(1, "", "_ORF", "", $this->FontToText($mysqli, $data['originator_font']));
            $this->WriteLine(1, "", "_OLF", "", $this->FontToText($mysqli, $data['other_line_font']));
            $this->WriteLine(1, "", "_TTF", "", $this->FontToText($mysqli, $data['title_font']));
            $this->WriteLine(1, "", "_FLF", "", $this->FontToText($mysqli, $data['first_line_font']));
            $this->WriteLine(1, "", "_JCOC", "", $this->RGBToText($data['connecting_R'], $data['connecting_G'], $data['connecting_B']));
            $this->WriteLine(1, "", "_JLTH", "", $this->IntToText($data['line_thickness']));
            $this->WriteLine(1, "", "_JMLHI", "", $this->IntToText($data['line_height']));
            $this->WriteLine(1, "", "_JMMGI", "", $this->IntToText($data['marriage_gap']));
            $this->WriteLine(1, "", "_JMIHI", "", $this->IntToText($data['min_indi_H']));
            $this->WriteLine(1, "", "_JMIWI", "", $this->IntToText($data['min_indi_W']));
            $this->WriteLine(1, "", "_JMSGI", "", $this->IntToText($data['sibling_gap']));
            $this->WriteLine(1, "", "_OTL", "", $this->BoolToYN($data['box_outline']));
            $this->WriteLine(1, "", "_JOLC", "", $this->RGBToText($data['outline_R'], $data['outline_G'], $data['outline_B']));
            $this->WriteLine(1, "", "_JOLT", "", $this->IntToText($data['outline_thickness']));
            $this->WriteLine(1, "", "_TTS", "", $data['title']);
            $this->WriteLine(1, "", "_JVZM", "", $this->IntToText($data['zoom']));
            $this->WriteLine(1, "", "_TNW", "", $this->IntToText($data['thumbnail_W']));
            $this->WriteLine(1, "", "_TNH", "", $this->IntToText($data['thumbnail_H']));
// $this->WriteLine(1, "", "_JWMF", "", watermarkFile);
// If Root exists
            if ($data['root'] !== null) {
                $root = read_assoc($mysqli, "individual", $data['root']);
                $this->WriteLine(0, "", "_ROOT", $root['label'], "");
            }
        }
    }

    function writeTailer() {
        $this->WriteLine(0, "", "TRLR", "", "");
    }

    function DbltToText($v) {
        return strval($v);
    }

    function IntToText($v) {
        return strval($v);
    }

    function FontToText($mysqli, $f) {
        $font = read_assoc($mysqli, "font", $f);
        return $this->BoolToYN($font['underline']) . "|" .
                $this->BoolToYN($font['opaque_background']) . "|" .
                $this->RGBToText($font['font_R'], $font['font_G'], $font['font_B']) . "|" .
                $this->RGBToText($font['background_R'], $font['background_G'], $font['background_B']) . "|" .
                $font['style'] . "|" .
                $this->BoolToYN($font['oblique']) . "|" .
                $this->DecToHex($font['size']) . "|" .
                $this->BoolToYN($font['bold']);
    }

    function RGBToText($r, $g, $b) {
        return $this->DecToHex($r) . $this->DecToHex($g) . $this->DecToHex($b);
    }

    function DecToHex($i) {
        $h = "0" . dechex($i);
        $l = strlen($h);
        return substr($h, $l - 2, 2);
    }

    function BoolToYN($b) {
        if ($b == true) {
            return "Y";
        }
        return "N";
    }

    function WriteLine($ll, $l, $t, $x, $r, $MaxArgSize = 248) {
        $lf = "\n";

        $NeedCONT = false;
        $NeedCONC = false;
        $MoreToWrite = true;
        $LevelText = "";

        $Level = $ll;
        $Label = $l;
        $Type = $t;
        $XRef = $x;
        $Rest = $r; // May contain \n as EOL

        $Remainder = "";

        while ($MoreToWrite) {
            $NeedCONT = false;
            $NeedCONC = false;
            $LevelText = strval($Level);
            $Outline = $LevelText;
            if (strlen($Label) != 0) {
                $Outline .= " @" . $Label . "@";
            }
            $Outline .= " " . $Type;
            if (strlen($XRef) != 0) {
                $Outline .= " @" . $XRef . "@";
            }

            $LineBreak = strpos($Rest, "\n");
            $RestLen = strlen($Rest);
            if ($LineBreak === false) {
                if ($RestLen > $MaxArgSize) {
                    $NeedCONC = true;
                    $Split = $MaxArgSize - 1;
                    while (($Split > 0) && ((substr($Rest, $Split, 1) == ' ') || (substr($Rest, $Split + 1, 1) == ' '))) {
                        $Split--;
                    }
                    if ($Split == 0) {
                        $Split = $MaxArgSize - 1;
                    } // All spaces so they deserve a stupid answer!
                    $Remainder = substr($Rest, $Split);
                    $Rest = substr($Rest, 0, $Split);
                } else {
                    $Remainder = "";
                }
            } else {
                if ($LineBreak < $MaxArgSize) {
                    $NeedCONT = true;
                    $Remainder = substr($Rest, $LineBreak + 1);
                    $Rest = substr($Rest, 0, $LineBreak);
                } else {
                    $NeedCONC = true;
                    $Split = $MaxArgSize - 1;
                    while (($Split > 0) && ((substr($Rest, $Split, 1) == ' ') || (substr($Rest, $Split + 1, 1) == ' '))) {
                        $Split--;
                    }
                    if ($Split == 0) {
                        $Split = $MaxArgSize - 1;
                    } // All spaces so they deserve a stupid answer!
                    $Remainder = substr($Rest, $Split);
                    $Rest = substr($Rest, 0, $Split);
                }
            }

            if (strlen($Rest) != 0) {
                $Outline .= " " . $Rest;
            }

            $Outline .= $lf;

            echo $Outline;

            $MoreToWrite = false;

            if ($NeedCONC) {
                $Level = $ll + 1;
                $Label = "";
                $Type = "CONC";
                $XRef = "";
                $Rest = $Remainder;
                $MoreToWrite = true;
            }
            if ($NeedCONT) {
                $Level = $ll + 1;
                $Label = "";
                $Type = "CONT";
                $XRef = "";
                $Rest = $Remainder;
                $MoreToWrite = true;
            }
        }
    }

    function writeBody($mysqli, $treeId, $media_path) {
        $this->writeIndividuals($mysqli, $treeId);
        $this->writeFamilies($mysqli, $treeId);
        $this->writeNotes($mysqli, $treeId);
        $this->writeMedias($mysqli, $treeId, $media_path); // SIC
        $this->writeSources($mysqli, $treeId);
        $this->writeSubmitters($mysqli, $treeId);
    }

    function writeIndividuals($mysqli, $treeId) {
// Read individual & family lists
        $individual = read_all_assoc($mysqli, 'individual', $treeId);
// Write individuals
        foreach ($individual as $indi) {
            $this->WriteLine(0, $indi['label'], "INDI", "", "");
            // GEDFile . WriteLine(1, "", "RESN", "", RestrictionNotice);
            $Name1 = notNullStr($indi['name1']);
            $Name2 = notNullStr($indi['name2']);
            $Name3 = notNullStr($indi['name3']);
            $Sex = notNullStr($indi['sex']);
            if ((strlen($Name1) != 0) || (strlen($Name2) != 0)) {
                $tempname = $Name1 . "/" . $Name2 . "/";
                $this->WriteLine(1, "", "NAME", "", $tempname);
                if (strlen($Name3) != 0) {
                    $this->WriteLine(2, "", "NICK", "", $Name3);
                }
            }
            if (strlen($Sex) != 0) {
                $this->WriteLine(1, "", "SEX", "", strtoupper($Sex));
            }

            $thisFamily = $indi['child_in_family'];
            if ($thisFamily !== null) {
                $famData = read_assoc($mysqli, "family", $thisFamily);
                $this->WriteLine(1, "", "FAMC", $famData['label'], "");
            }

            $famSLabels = get_famSLabels($mysqli, $indi['id'], $treeId);
            foreach ($famSLabels as $famSLabel) {
                $this->WriteLine(1, "", "FAMS", $famSLabel, "");
            }

            $this->writeFAL($mysqli, 'individual', $indi['id'], $treeId, 1);

            if ($this->UseExtras) {
                if ($indi['living'] == 'a') {
                    $this->WriteLine(1, "", "_LIV", "", "Alive");
                }
                if ($indi['living'] == 'd') {
                    $this->WriteLine(1, "", "_LIV", "", "Dead");
                }
                $boxtext = notNullStr($indi['box_text']);
                if (strlen($boxtext) != 0) {
                    $this->WriteLine(1, "", "_BOX", "", $boxtext);
                }

                if ($indi['lspouse'] !== null) {
                    $spouse = read_assoc($mysqli, "individual", $indi['lspouse']);
                    $this->WriteLine(1, "", "_LSP", "", $spouse['label']);
                }

                if ($indi['rspouse'] !== null) {
                    $spouse = read_assoc($mysqli, "individual", $indi['rspouse']);
                    $this->WriteLine(1, "", "_RSP", "", $spouse['label']);
                }

                if ($indi['l_media_id'] !== null) {
                    $media = read_assoc($mysqli, "media", $indi['l_media_id']);
                    $this->WriteLine(1, "", "_LMED", "", $media['label']);
                }

                if ($indi['r_media_id'] !== null) {
                    $media = read_assoc($mysqli, "media", $indi['r_media_id']);
                    $this->WriteLine(1, "", "_RMED", "", $media['label']);
                }

                switch ($indi['show_me']) {
                    case "n":
                        $this->WriteLine(1, "", "_SHOW", "", "NoShow");
                        break;
                    case "b":
                        $this->WriteLine(1, "", "_SHOW", "", "BelowInStack");
                        break;
                    default: // TopOfStack;
                        $this->WriteLine(1, "", "_SHOW", "", "TopOfStack");
                        break;
                }
            }
        }
    }

    function writeFamilies($mysqli, $treeId) {
// Write families
        $family = read_all_assoc($mysqli, 'family', $treeId);
        foreach ($family as $famData) {
            $this->WriteLine(0, $famData['label'], "FAM", "", "");
            if ($famData['husband'] !== null) {
                $indi = read_assoc($mysqli, "individual", $famData['husband']);
                $this->WriteLine(1, "", "HUSB", $indi['label'], "");
            }
            if ($famData['wife'] !== null) {
                $indi = read_assoc($mysqli, "individual", $famData['wife']);
                $this->WriteLine(1, "", "WIFE", $indi['label'], "");
            }

            $children = get_children($mysqli, $famData['id'], $treeId);
            foreach ($children as $child) {
                $this->WriteLine(1, "", "CHIL", $child['label'], "");
            }
            $this->writeFAL($mysqli, 'family', $famData['id'], $treeId, 1);
        }
    }

    function writeNotes($mysqli, $treeId) {
// Write Notes
        $notes = read_all_assoc($mysqli, 'note', $treeId);
        foreach ($notes as $note) {
            $this->WriteLine(0, $note['label'], "NOTE", "", $note['note'], 248);
            $this->writeFAL($mysqli, 'note', $note['id'], $treeId, 1);
        }
    }

    function writeMedias($mysqli, $treeId, $media_path) { // Spelling SIC
        $medias = read_all_assoc($mysqli, 'media', $treeId);
        foreach ($medias as $media) {
            $format = notNullStr($media['format']);
            $title = notNullStr($media['title']);
            $this->WriteLine(0, $media['label'], "OBJE", "", "");
            // $this->WriteLine(1, "", "FILE", "", "");
            if ($format !== "") {
                $this->WriteLine(1, "", "FORM", "", $format);
            }
            if ($title !== "") {
                $this->WriteLine(1, "", "TITL", "", $title);
            }
            $this->writeFAL($mysqli, 'media', $media['id'], $treeId, 1);
            if (($this->mediaOption == 'f') && ($media['content'] !== null) && (file_exists($media_path.$media['content'])) && (!is_DIR($media_path.$media['content']))) {
                $this->WriteLine(1, "", "BLOB", "", "");
                // Note: PHP Base64 Encode uses "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"
                // and GEDCOM wants "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"
                // I'm outputting Base64 for ease
                $lines = str_split(base64_encode(
                                file_get_contents($media_path . $media['content'])
                        ), 248);
                foreach ($lines as $line) {
                    $this->WriteLine(2, "", "CONT", "", $line);
                }
            }
        }
    }

    function writeSources($mysqli, $treeId) {
// Write Sources
        $sources = read_all_assoc($mysqli, 'source', $treeId);
        foreach ($sources as $source) {
            $this->WriteLine(0, $source['label'], "SOUR", "", "");
            $Originator = notNullStr($source['originator']);
            $DescriptiveTitle = notNullStr($source['title']);
            $FiledByEntry = notNullStr($source['filed_by_entry']);
            $PublicationFacts = notNullStr($source['publication_facts']);
            $TextFromSource = notNullStr($source['text']);

            if (strlen($Originator) != 0) {
                $this->WriteLine(1, "", "AUTH", "", $Originator, 248);
            }

            if (strlen($DescriptiveTitle) != 0) {
                $this->WriteLine(1, "", "TITL", "", $DescriptiveTitle, 248);
            }

            if (strlen($FiledByEntry) != 0) {
                $this->WriteLine(1, "", "ABBR", "", $FiledByEntry);
            }

            if (strlen($PublicationFacts) != 0) {
                $this->WriteLine(1, "", "PUBL", "", $PublicationFacts);
            }

            if (strlen($TextFromSource) != 0) {
                $this->WriteLine(1, "", "TEXT", "", $TextFromSource);
            }
            $this->writeFAL($mysqli, 'source', $source['id'], $treeId, 1);
        }
    }

    function writeSubmitters($mysqli, $treeId) {
// Write Submitters
        $submitters = read_all_assoc($mysqli, 'submitter', $treeId);
        foreach ($submitters as $submitter) {
            $this->WriteLine(0, $submitter['label'], "SUBM", "", "");
            $name = notNullStr($submitter['name']);
            $RFN = notNullStr($submitter['registered_RFN']);
            if (strlen($name) != 0) {
                $this->WriteLine(1, "", "NAME", "", $name);
            }
            if (strlen($RFN) != 0) {
                $this->WriteLine(1, "", "FRN", "", $RFN);
            }
            $this->writeAddress($mysqli, 1, $submitter['address_id']);
        }
    }

    function writeAddress($mysqli, $baselevel, $id) {
        $address = read_assoc($mysqli, "address", $id);
        $line1 = notNullStr($address['line1']);
        $line2 = notNullStr($address['line2']);
        $line3 = notNullStr($address['line3']);
        $city = notNullStr($address['city']);
        $state = notNullStr($address['state']);
        $postalcode = notNullStr($address['postal_code']);
        $country = notNullStr($address['country']);
        $phone = notNullStr($address['phone']);
        if (($line1 !== "") ||
                ($line2 !== "") ||
                ($line3 !== "") ||
                ($city !== "") ||
                ($state !== "") ||
                ($postalcode !== "") ||
                ($country !== "") ||
                ($line2 !== "")) {
            $this->WriteLine($baselevel, "", "ADDR", "", $line1, 60);
            if (strlen($line2) != 0) {
                $this->WriteLine($baselevel + 1, "", "ADR1", "", $line2);
            }
            if (strlen($line3) != 0) {
                $this->WriteLine($baselevel + 1, "", "ADR2", "", $line3);
            }
            if (strlen($city) != 0) {
                $this->WriteLine($baselevel + 1, "", "CITY", "", $city);
            }
            if (strlen($state) != 0) {
                $this->WriteLine($baselevel + 1, "", "STAE", "", $state);
            }
            if (strlen($postalcode) != 0) {
                $this->WriteLine($baselevel + 1, "", "POST", "", $postalcode);
            }
            if (strlen($country) != 0) {
                $this->WriteLine($baselevel + 1, "", "CTRY", "", $country);
            }
        }
        if (strlen($phone) != 0) {
            $this->WriteLine($baselevel, "", "PHON", "", $phone);
        }
    }

    function writePlace($mysqli, $baselevel, $id, $treeId) {
        $place = read_assoc($mysqli, "place", $id);
        $this->WriteLine($baselevel, "", "PLAC", "", $place['place_value']);
        if ($place['place_hierarchy'] != null) {
            $this->WriteLine($baselevel + 1, "", "FORM", "", $place['place_hierarchy']);
        }
        $this->writeFAL($mysqli, "place", $id, $treeId, $baselevel + 1);
    }

    function writeFAL($mysqli, $type, $id, $treeId, $baselevel) {
        $FALStrings = [];
        $control = [
            ["event", "event", false],
            ["note", "note_link", true, "NOTE"],
            ["citation", "citation", false],
            ["submitter", "submitter_link", true, "SUBM"],
            ["media", "media_link", true, "OBJE"]];

        foreach ($control as $cont) {
            if ($this->class[$type][$cont[0] . '_allowed']) {
                $objects = read_all_assoc_that_belong($mysqli, $cont[1], $treeId, $type, $id);
                foreach ($objects as $object) {
                    if ($cont[2]) {
                        $fid = $object[$cont[0] . '_id'];
                        $final = read_assoc($mysqli, $cont[0], $fid);
                        $this->WriteLine($baselevel, "", $cont[3], $final['label'], "");
                    } else {
                        $fid = $object['id'];
                        $final = $object;
                        if ($cont[0] == "event") {
                            $evtype = notNullStr($object['type']);
                            if (strlen($evtype) == 0) {
                                $evtype = 'EVEN';
                            }
                            $argument = notNullStr($object['argument']);
                            $descriptor = notNullStr($object['event_descriptor']);
                            $age = notNullStr($object['age_at_event']);
                            $agency = notNullStr($object['responsible_agency']);
                            $cause = notNullStr($object['cause_of_event']);
                            $this->WriteLine($baselevel, "", $evtype, "", $argument);
                            if ($descriptor !== "") {
                                $this->WriteLine($baselevel + 1, "", "TYPE", "", $descriptor);
                            }
                            $date_id = $object['event_date_id'];
                            if ($date_id !== null) {
                                $date = new CDateValue();
                                $date->ReadDatabase($mysqli, $date_id);
                                if (!$date->IsBlank()) {
                                    $this->WriteLine($baselevel + 1, "", "DATE", "", $date->GEDCOMStyle());
                                }
                            }
                            $this->writePlace($mysqli, $baselevel + 1, $object['place_id'], $treeId);
                            $this->writeAddress($mysqli, $baselevel + 1, $object['address_id']);
                            if ($age !== "") {
                                $this->WriteLine($baselevel + 1, "", "AGE", "", $age);
                            }
                            if ($agency !== "") {
                                $this->WriteLine($baselevel + 1, "", "AGNC", "", $agency);
                            }
                            if ($cause !== "") {
                                $this->WriteLine($baselevel + 1, "", "CAUS", "", $cause);
                            }
                            $this->writeFAL($mysqli, "event", $object['id'], $treeId, $baselevel + 1);
                        } else if ($cont[0] == "citation") {
                            $SourceXRef = "";
                            $fid = $object['source_id'];
                            if ($fid !== null) {
                                $final = read_assoc($mysqli, 'source', $fid);
                                $SourceXRef = $final['label'];
                            }
                            $this->WriteLine($baselevel, "", "SOUR", $SourceXRef, "");
                            $where = notNullStr($object['where_within_source']);
                            $role = notNullStr($object['role_in_event']);
                            $date_id = $object['entry_recording_date_id'];
                            $evtype = notNullStr($object['event_type']);
                            $text = notNullStr($object['text_from_source']);
                            $quay = notNullStr($object['certainty_assessment']);
                            if ($where !== "") {
                                $this->WriteLine($baselevel + 1, "", "PAGE", "", $where);
                            }
                            if (($evtype !== "") || ($role !== "")) {
                                $this->WriteLine($baselevel + 1, "", "EVEN", "", $evtype);
                                if ($role !== "") {
                                    $this->WriteLine($baselevel + 1, "", "ROLE", "", $role);
                                }
                            }

                            $datestr = "";
                            if ($date_id !== null) {
                                $date = new CDateValue();
                                $date->ReadDatabase($mysqli, $date_id);
                                if (!$date->IsBlank()) {
                                    $datestr = $date->GEDCOMStyle();
                                }
                            }

                            if (($datestr !== "") || ($text !== "")) {
                                $this->WriteLine($baselevel + 1, "", "DATA", "", "");
                                if ($datestr !== "")
                                    $this->WriteLine($baselevel + 2, "", "DATE", "", $datestr);
                                if ($text !== "")
                                    $this->WriteLine($baselevel + 2, "", "TEXT", "", $text, 248);
                            }

                            if ($quay !== "") {
                                $this->WriteLine($baselevel + 1, "", "QUAY", "", $quay);
                            }

                            $this->writeFAL($mysqli, "citation", $object['id'], $treeId, $baselevel + 1);
                        }
                    }
                }
            }
        }
    }

}
