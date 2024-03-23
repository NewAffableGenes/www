<?php

class GEDCOMLoader {

    var $numChunk;
    var $nextChunk;
    var $currentChunk;
    var $userId;
    var $loc;
    var $complete;
    var $Level;
    var $Line;
    var $LineCnt;
    var $Label;
    var $Type;
    var $XRef;
    var $Rest;
    var $LineLen;
    var $mysqli;
    var $treeId;
    var $lastloc;
    var $ag_file;

    function __construct($userId, $mysqli) {
        // Variables marked * are overwritten by calling function if we are continuing
        $this->userId = $userId;
        $this->mysqli = $mysqli;
        $this->loc = 0; // *
        $this->lastloc = 0;
        $this->complete = false;
        $this->Level = 0;
        $this->Label = "";
        $this->Type = "";
        $this->XRef = "";
        $this->Rest = "";
        $this->LineLen = 0;
        $this->numChunk = 1; // *, Will be overwrittent when chunk is read
        $this->nextChunk = 0; // *
        $this->currentChunk = ""; // *
        $this->ag_file = false;
        $this->LineCnt = 0;
        // do_log($this->mysqli, 'GEDCOMLoader constructed');      
    }

    private function readChunk() {
        $row = read_chunk($this->mysqli, $this->userId, $this->nextChunk);
        // Append the new data to the 'currentChunk'
        $this->currentChunk .= $row['content'];
        // Check only the first chunk to see if it starts with the utf-8 Byte Order Mark (239 187 191)
        // If it does ignore it!
        if ($this->nextChunk == 0) {
            if ((ord($this->currentChunk[0]) == 239) && (ord($this->currentChunk[1]) == 187) && (ord($this->currentChunk[2]) == 191)) {
                $this->loc = 3;
            }
        }
        // Now update the chunk counter
        $this->nextChunk++;
        $userdata = read_assoc($this->mysqli, "user", $this->userId);
        $this->numChunk = $userdata['upload_numchunk'];
    }

    private function readLine() {
        // The following code assumes longest line of GED file 
        // is < 1024 (> NNNNNN CONT <248> <CR/LF>.  From GEDCOM spec - 
        // "The total length of a GEDCOM line, including leading white space, 
        // level number, cross-reference number, tag, value, delimiters, and 
        // terminator, must not exceed 255 (wide) characters."      
        if ((strlen($this->currentChunk) - $this->loc < 1024) && ($this->nextChunk < $this->numChunk)) {
            $this->currentChunk = substr($this->currentChunk, $this->loc, strlen($this->currentChunk) - $this->loc);
            $this->loc = 0;
            $this->readChunk();
        }

        $str = "";
        $eof = ($this->loc >= strlen($this->currentChunk));
        while ((strlen($str) == 0) && !$eof) {
            $posnEOLn = strpos($this->currentChunk, "\n", $this->loc);
            if ($posnEOLn === false) {
                $posnEOLn = strlen($this->currentChunk);
            }
            $posnEOLr = strpos($this->currentChunk, "\r", $this->loc);
            if ($posnEOLr === false) {
                $posnEOLr = strlen($this->currentChunk);
            }
            $posnEOL = $posnEOLn;
            if ($posnEOLr < $posnEOL) {
                $posnEOL = $posnEOLr;
            }
            $str = trim(substr($this->currentChunk, $this->loc, $posnEOL - $this->loc));
            $this->lastloc = $this->loc;
            $this->loc = $posnEOL + 1;
            $eof = ($this->loc >= strlen($this->currentChunk));
        }
        if (($eof) && (strlen($str) == 0)) {
            $str = null;
        }
        // do_log($str);
        return $str;
    }

    function DecodeNextLine() {
        $Line1 = "";
        while (!$this->complete && (strlen($Line1) == 0)) {
            $Line1 = $this->readLine();
            $this->complete = ($Line1 === null);
        }

        $this->Line = $Line1;
        $this->LineCnt++;

        if (!$this->complete) {
            $this->LineLen = strlen($Line1);
            $PosnSp = strpos($Line1, " ");
            $levelStr = substr($Line1, 0, $PosnSp);
            if (ctype_digit($levelStr)) {
                $this->Level = intval($levelStr);
            } else {
                $this->Level = -1;
            }
            $Line2 = trim(substr($Line1, $PosnSp));
            $this->Label = "";
            if (substr($Line2, 0, 1) == "@") {
                $PosnAt = strpos($Line2, "@", 1);
                $this->Label = substr($Line2, 1, $PosnAt - 1);
                $Line2 = trim(substr($Line2, $PosnAt + 1));
            }
            $PosnSp2 = strpos($Line2, " ");
            $this->Rest = "";
            $this->XRef = "";
            if ($PosnSp2 === false) {
                $this->Type = strtoupper($Line2);
            } else {
                $this->Type = strtoupper(substr($Line2, 0, $PosnSp2));
                $Line2 = substr($Line2, $PosnSp2);
                $PosnAt = strpos($Line2, "@", 0);
                if ($PosnAt === false) {
                    $this->Rest = substr($Line2, 1);
                } else {
                    $PosnAt2 = strpos($Line2, "@", $PosnAt + 1);
                    if ($PosnAt2 !== false) {
                        $this->XRef = substr($Line2, $PosnAt + 1, $PosnAt2 - $PosnAt - 1);
                    }
                }
            }
            if ($this->Type == "TRLR") {
                $this->complete = true;
            }
        }
    }

    function decode($treemedia_path) {
        // do_log($this->mysqli, "Starting decode");
        $ok = true;
        $this->complete = false;
        // Now run through file loading up the tree database
        $this->DecodeNextLine();
        $tend = time() + 1;
        while ((!$this->complete) && $ok && (time() < $tend)) {
            // if ((!$this->complete) && $ok) {
            $ok = (($this->Level >= 0) &&
                    (strlen($this->Type) > 0) &&
                    (strlen($this->Type) < 10) &&
                    ($this->LineLen <= 512)); // was 256 but failed
            if (!$ok) {
                do_log($this->mysqli, "Decode error: Level $this->Level, Type $this->Type, LineLen $this->LineLen, Line $this->LineCnt $this->Line");
                $this->DecodeNextLine();
                do_log($this->mysqli, "Next line: Level $this->Level, Type $this->Type, LineLen $this->LineLen, Line $this->LineCnt $this->Line");
            } else {
                // Act on it. Remember to update counts
                // do_log($this->mysqli, "Loading: " . $this->Type);
                switch ($this->Type) {
                    case "INDI":
                        $thisIndi = $this->getObject($this->Label, "individual");
                        $this->LoadIndividual($thisIndi);
                        break;
                    case "FAM":
                        $thisFamily = $this->getObject($this->Label, "family");
                        $this->LoadFamily($thisFamily);
                        break;
                    case "SUBM":
                        $submitter = $this->getObject($this->Label, "submitter");
                        $this->LoadSubmitter($submitter);
                        break;
                    case "NOTE":
                        $note = $this->getObject($this->Label, "note");
                        $this->LoadNote($note);
                        break;
                    case "SOUR":
                        $source = $this->getObject($this->Label, "source");
                        $this->LoadSource($source);
                        break;
                    case "OBJE":
                        $media = $this->getObject($this->Label, "media");
                        $this->LoadMedia($media, $treemedia_path);
                        break;
                    case "HEAD":
                        $this->LoadHeader();
                        break;
                    case "_ROOT":
                        if ($this->ag_file) {
                            $root = $this->getObject($this->XRef, "individual");
                            set_root($this->mysqli, $root, $this->treeId);
                        }
                        $this->DecodeNextLine();
                        break;
                    default:
                        $this->DecodeNextLine();
                        break;
                }
            }
        }
        return $ok;
    }

    function getObject($label, $objectType) {
        $id = get_object_id_from_label($this->mysqli, $this->treeId, $objectType, $label);
        if ($id == null) {
            switch ($objectType) {
                case("individual") :
                    $id = createDefaultIndividual($this->mysqli, $this->treeId, $label);
                    break;
                case("family") :
                    $id = createDefaultFamily($this->mysqli, $this->treeId, $label);
                    break;
                case("submitter") :
                    $id = createDefaultSubmitter($this->mysqli, $this->treeId, $label);
                    break;
                case("note") :
                    $id = createDefaultNote($this->mysqli, $this->treeId, $label);
                    break;
                case("source") :
                    $id = createDefaultSource($this->mysqli, $this->treeId, $label);
                    break;
                case("media") :
                    $id = createDefaultMedia($this->mysqli, $this->treeId, $label);
                    break;
            }
        }
        return $id;
    }

    function LoadIndividual($thisIndi) {
        $data = read_assoc($this->mysqli, "individual", $thisIndi);
        $BaseLevel = $this->Level;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            switch ($this->Type) {
                case "RESN":
                    // Restriction notice
                    $this->DecodeNextLine();
                    break;
                case "NAME":
                    // Personal Name structure
                    $name = array(
                        'NPFX' => "",
                        'GIVN' => "",
                        'NICK' => "",
                        'SPFX' => "",
                        'SURN' => "",
                        'NSFX' => "");

                    // If NAME_PERSONAL exists convert it to Given, Surname & Suffix
                    if (strlen($this->Rest) > 0) {
                        $NamePersonal = $this->Rest;
                        $Slash1 = strpos($NamePersonal, '/', 0);
                        if ($Slash1 !== false) {
                            $name['GIVN'] = trim(substr($NamePersonal, 0, $Slash1));
                            $NamePersonal = substr($NamePersonal, $Slash1 + 1);
                        }
                        $Slash2 = strpos($NamePersonal, '/', 0);
                        if ($Slash2 !== false) {
                            $name['SURN'] = trim(substr($NamePersonal, 0, $Slash2));
                            $name['NSFX'] = trim(substr($NamePersonal, $Slash2 + 1));
                        } else {
                            $name['SURN'] = trim($NamePersonal);
                        }
                    }

                    // Read any Suplementary data
                    $SubBaseLevel = $this->Level;
                    $this->DecodeNextLine();
                    while (!$this->complete && ($this->Level > $SubBaseLevel)) {
                        if (($this->Type == 'NPFX') ||
                                ($this->Type == 'GIVN') ||
                                ($this->Type == 'SPFX') ||
                                ($this->Type == 'SURN') ||
                                ($this->Type == 'NICK') ||
                                ($this->Type == 'NSFX')) {
                            $name[$this->Type] = $this->Rest;
                        }
                        $this->DecodeNextLine();
                    }

                    // Store the given name and any prefix
                    $data['name1'] = $name['GIVN'];
                    if (strlen($name['NPFX']) > 0) {
                        if (strlen($name['GIVN']) > 0) {
                            $data['name1'] = $name['NPFX'] . ' ' . $name['GIVN'];
                        } else {
                            $data['name1'] = $name['NPFX'];
                        }
                    }

                    // Store the surname and any prefix
                    $data['name2'] = $name['SURN'];
                    if (strlen($name['SPFX']) > 0) {
                        if (strlen($name['SURN']) > 0) {
                            $data['name2'] = $name['SPFX'] . ' ' . $name['SURN'];
                        } else {
                            $data['name2'] = $name['SPFX'];
                        }
                    }

                    // Store the suffix and nickname
                    $data['name3'] = $name['NSFX'];
                    if (strlen($name['NICK']) > 0) {
                        if (strlen($name['NSFX']) > 0) {
                            $data['name3'] = $name['NSFX'] . ' (' . $name['NICK'] . ')';
                        } else {
                            $data['name3'] = '(' . $name['NICK'] . ')';
                        }
                    }
                    break;
                case "SEX":
                    $Sex = strtoupper($this->Rest);
                    switch ($Sex) {
                        case "M":
                        case "MALE":
                            $data['sex'] = 'm';
                            break;
                        case "F":
                        case "FEMALE":
                            $data['sex'] = 'f';
                            break;
                        default: // Unknown
                            $data['sex'] = 'u';
                            break;
                    }
                    $this->DecodeNextLine();
                    break;
                case "BIRT":
                case "CHR":
                case "DEAT":
                case "BURI":
                case "CREM":
                case "ADOP":
                case "BAPM":
                case "BARM":
                case "BASM":
                case "BLES":
                case "CHRA":
                case "CONF":
                case "FCOM":
                case "ORDN":
                case "NATU":
                case "EMIG":
                case "IMMI":
                case "CENS":
                case "PROB":
                case "WILL":
                case "GRAD":
                case "RETI":
                case "EVEN":
                case "CAST":
                case "DSCR":
                case "EDUC":
                case "IDNO":
                case "NATI":
                case "NCHI":
                case "NMR":
                case "OCCU":
                case "PROP":
                case "RELI":
                case "RESI":
                case "SSN":
                case "TITL":
                    // Create an INDIVIDUAL attribute/event record (both treated the same)
                    $this->AddEvent("individual", $thisIndi);
                    break;
                case "FAMC": {
                        // Create Child to Family Link
                        // Don't do this 
                        // I will rely on the CHIL in the FAM record
                        // TODO: Change to use this but have to do all the checks to make sure child is not added twice etc
                        $ll = $this->Level;
                        $this->DecodeNextLine();
                        while (!$this->complete && ($this->Level > $ll)) {
                            $this->DecodeNextLine();
                        }
                        break;
                    }
                case "FAMS": {
                        // Create Spouse to Family Link
                        // Don't do this 
                        // I will rely on the WIFE and HUSB in the FAM record
                        // TODO: Change to use this but have to do all the checks to make sure parents not added twice etc
                        $ll = $this->Level;
                        $this->DecodeNextLine();
                        while (!$this->complete && ($this->Level > $ll)) {
                            $this->DecodeNextLine();
                        }
                        break;
                    }
                case "SUBM":
                    // Create submitter XRef
                    // factsAndLinks.addSubmitterFromGEDCOM(GEDFile);
                    $this->addSubmitterLink("individual", $thisIndi, $this->XRef);
                    break;
                case "ALIA":
                    $this->DecodeNextLine();
                    break;
                case "ANCI":
                    $this->DecodeNextLine();
                    break;
                case "DESI":
                    $this->DecodeNextLine();
                    break;
                case "SOUR":
                    $this->addCitation("individual", $thisIndi);
                    break;
                case "OBJE":
                    $this->addMedialink("individual", $thisIndi, $this->XRef);
                    break;
                case "NOTE":
                    $this->addNoteLink("individual", $thisIndi, $this->XRef);
                    break;
                case "_BOX":
                    if ($this->ag_file) {
                        $data['box_text'] = $this->Rest;
                        $this->DecodeNextLine();
                        while (($this->Type == "CONC") || ($this->Type == "CONT")) {
                            if ($this->Type == "CONT") {
                                $data['box_text'] = $data['box_text'] . "\n";
                            }
                            $data['box_text'] = $data['box_text'] . $this->Rest;
                            $this->DecodeNextLine();
                        }
                        // do_log($this->mysqli, 'Box read: ' . $data['box_text']); // debug2
                    } else {
                        // do_log($this->mysqli, 'Box kept default'); // debug2
                        $this->DecodeNextLine();
                    }
                    break;
                case "_LSP":
                    if ($this->ag_file) {
                        $data['lspouse'] = $this->getObject($this->Rest, "individual");
                    }
                    $this->DecodeNextLine();
                    break;
                case "_RSP":
                    if ($this->ag_file) {
                        $data['rspouse'] = $this->getObject($this->Rest, "individual");
                    }
                    $this->DecodeNextLine();
                    break;
                case "_LIV":
                    if ($this->ag_file) {
                        switch ($this->Rest) {
                            case "Alive":
                                $data['living'] = 'a';
                                break;
                            case "Dead":
                                $data['living'] = 'd';
                                break;
                            default: // Unknown
                                $data['living'] = 'u';
                                break;
                        }
                    }
                    $this->DecodeNextLine();
                    break;
                case "_SHOW":
                    if ($this->ag_file) {
                        switch ($this->Rest) {
                            case "NoShow":
                                $data['show_me'] = 'n';
                                break;
                            case "BelowInStack":
                                $data['show_me'] = 'b';
                                break;
                            default: // TopOfStack;
                                $data['show_me'] = 't';
                                break;
                        }
                    }
                    $this->DecodeNextLine();
                    break;
                case "_LMED":
                    if ($this->ag_file) {
                        $data['l_media_id'] = $this->getObject($this->Rest, "media");
                    }
                    $this->DecodeNextLine();
                    break;
                case "_RMED":
                    if ($this->ag_file) {
                        $data['r_media_id'] = $this->getObject($this->Rest, "media");
                    }
                    $this->DecodeNextLine();
                    break;
                default:
                    $this->DecodeNextLine(); // Added
                    break;
            }
        }
        if (($data['box_text'] === null) || ($data['box_text'] === "") || ($data['box_text'] === "Your text here")) {
            update_assoc($this->mysqli, "individual", $thisIndi, $data);
            // var_dump($thisIndi);
            // var_dump($data);
            $data['box_text'] = autoBoxText($this->mysqli, $this->treeId, $data);
        }
        update_assoc($this->mysqli, "individual", $thisIndi, $data);
    }

    function LoadFamily($thisFamily) {
        $data = read_assoc($this->mysqli, "family", $thisFamily);
        $BaseLevel = $this->Level;
        $nChild = 0;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            switch ($this->Type) {
                case "HUSB":
                    $data['husband'] = $this->getObject($this->XRef, "individual");
                    $this->DecodeNextLine();
                    break;
                case "WIFE":
                    $data['wife'] = $this->getObject($this->XRef, "individual");
                    $this->DecodeNextLine();
                    break;
                case "CHIL":
                    $child = $this->getObject($this->XRef, "individual");
                    $childData = read_assoc($this->mysqli, "individual", $child);
                    $childData['child_in_family'] = $thisFamily;
                    $childData['place_in_family_sibling_list'] = $nChild++;
                    update_assoc($this->mysqli, "individual", $child, $childData);
                    $this->DecodeNextLine();
                    break;
                case "ANUL":
                case "CENS":
                case "DIV":
                case "DIVF":
                case "ENGA":
                case "MARR":
                case "MARB":
                case "MARC":
                case "MARL":
                case "MARS":
                case "EVEN":
                    $this->AddEvent("family", $thisFamily);
                    break;
                case "SUBM":
                    $this->addSubmitterLink("family", $thisFamily, $this->XRef);
                    break;
                case "SOUR":
                    $this->addCitation("family", $thisFamily);
                    break;
                case "OBJE":
                    $this->addMedialink("family", $thisFamily, $this->XRef);
                    break;
                case "NOTE":
                    $this->addNoteLink("family", $thisFamily, $this->XRef);
                    break;
                default:
                    $this->DecodeNextLine();
                    break;
            }
        }
        $data['nchild'] = $nChild;
        update_assoc($this->mysqli, "family", $thisFamily, $data);
    }

    function LoadSubmitter($submitter) {
        $data = read_assoc($this->mysqli, "submitter", $submitter);
        $BaseLevel = $this->Level;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            switch ($this->Type) {
                case "NAME":
                    $SubmitterName = $this->Rest;
                    if (strlen($SubmitterName) > 60) {
                        $SubmitterName = substr($SubmitterName, 0, 60);
                    }
                    $data['name'] = $SubmitterName;
                    $this->DecodeNextLine();
                    break;
                case "RFN":
                    $SubmitterRegisteredRFN = $this->Rest;
                    if (strlen($SubmitterRegisteredRFN) > 30) {
                        $SubmitterRegisteredRFN = substr($SubmitterRegisteredRFN, 0, 30);
                    }
                    $data['registered_RFN'] = $SubmitterRegisteredRFN;
                    $this->DecodeNextLine();
                    break;
                case "PHON":
                    $PhoneNumber = $this->Rest;
                    if (strlen($PhoneNumber) > 25) {
                        $PhoneNumber = substr($PhoneNumber, 0, 25);
                    }
                    $address = $data['address_id'];
                    if ($address == null) {
                        $address = createDefaultAddress($this->mysqli, $this->treeId);
                        $data['address_id'] = $address;
                    }
                    $addressData = read_assoc($this->mysqli, "address", $address);
                    $addressData['phone'] = $PhoneNumber;
                    update_assoc($this->mysqli, "address", $address, $addressData);
                    $this->DecodeNextLine();
                    break;
                case "ADDR":
                    $address = $data['address_id'];
                    if ($address == null) {
                        $address = createDefaultAddress($this->mysqli, $this->treeId);
                        $data['address_id'] = $address;
                    }
                    $this->LoadAddress($address);
                    break;
                default:
                    $this->DecodeNextLine();
                    break;
            }
        }
        update_assoc($this->mysqli, "submitter", $submitter, $data);
    }

    function LoadNote($note) {
        $tNote = $this->Rest;
        $data = read_assoc($this->mysqli, "note", $note);
        $BaseLevel = $this->Level;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            switch ($this->Type) {
                case "CONC":
                    $tNote .= $this->Rest;
                    $this->DecodeNextLine();
                    break;
                case "CONT":
                    $tNote .= "\n" . $this->Rest;
                    $this->DecodeNextLine();
                    break;
                case "SOUR":
                    $this->addCitation("note", $note);
                    break;
                default:
                    $this->DecodeNextLine();
                    break;
            }
        }
        $data['note'] = $tNote;
        update_assoc($this->mysqli, "note", $note, $data);
    }

    function LoadAddress($address) {
        $data = read_assoc($this->mysqli, "address", $address);
        $data['line1'] = $this->Rest;
        $BaseLevel = $this->Level;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            switch ($this->Type) {
                case "CONT":
                    $data['line1'] .= "\n" . $this->Rest;
                    break;
                case "ADR1":
                    $data['line2'] = str_truncate($this->Rest, 60);
                    break;
                case "ADR2":
                    $data['line3'] = str_truncate($this->Rest, 60);
                    break;
                case "CITY":
                    $data['city'] = str_truncate($this->Rest, 60);
                    break;
                case "STAE":
                    $data['state'] = str_truncate($this->Rest, 60);
                    break;
                case "POST":
                    $data['postal_code'] = str_truncate($this->Rest, 60);
                    break;
                case "CTRY":
                    $data['country'] = str_truncate($this->Rest, 60);
                    break;
            }
            $this->DecodeNextLine();
        }
        update_assoc($this->mysqli, "address", $address, $data);
    }

    function LoadSource($source) {
        $data = read_assoc($this->mysqli, "source", $source);
        $BaseLevel = $this->Level;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            switch ($this->Type) {
                case "AUTH":
                    $Originator = $this->Rest;
                    $this->DecodeNextLine();
                    while (($this->Type == "CONC") || ($this->Type == "CONT")) {
                        if ($this->Type == "CONT") {
                            $Originator .= "\n";
                        }
                        $Originator .= $this->Rest;
                        $this->DecodeNextLine();
                    }
                    $data['originator'] = $Originator;
                    break;
                case "TITL":
                    $DescriptiveTitle = $this->Rest;
                    $this->DecodeNextLine();
                    while (($this->Type == "CONC") || ($this->Type == "CONT")) {
                        if ($this->Type == "CONT") {
                            $DescriptiveTitle .= "\n";
                        }
                        $DescriptiveTitle .= $this->Rest;
                        $this->DecodeNextLine();
                    }
                    $data['title'] = $DescriptiveTitle;
                    break;
                case "ABBR":
                    $data['filed_by_entry'] = $this->Rest;
                    $this->DecodeNextLine();
                    break;
                case "PUBL":
                    $PublicationFacts = $this->Rest;
                    $this->DecodeNextLine();
                    while (($this->Type == "CONC") || ($this->Type == "CONT")) {
                        if ($this->Type == "CONT") {
                            $PublicationFacts .= "\n";
                        }
                        $PublicationFacts .= $this->Rest;
                        $this->DecodeNextLine();
                    }
                    $data['publication_facts'] = $PublicationFacts;
                    break;
                case "TEXT":
                    $TextFromSource = $this->Rest;
                    $this->DecodeNextLine();
                    while (($this->Type == "CONC") || ($this->Type == "CONT")) {
                        if ($this->Type == "CONT") {
                            $TextFromSource .= "\n";
                        }
                        $TextFromSource .= $this->Rest;
                        $this->DecodeNextLine();
                    }
                    $data['text'] = $TextFromSource;
                    break;
                case "OBJE":
                    $this->addMedialink("source", $source, $this->XRef);
                    break;
                case "NOTE":
                    $this->addNoteLink("source", $source, $this->XRef);
                    break;
                default:
                    $this->DecodeNextLine();
                    break;
            }
        }
        update_assoc($this->mysqli, "source", $source, $data);
    }

    function LoadMedia($media, $treemedia_path) {
        $data = read_assoc($this->mysqli, "media", $media);
        $content = "";
        $BaseLevel = $this->Level;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            switch ($this->Type) {
                case "FORM":
                    $data['format'] = $this->Rest;
                    $this->DecodeNextLine();
                    break;
                case "TITL":
                    $data['title'] = $this->Rest;
                    $this->DecodeNextLine();
                    break;
                case "BLOB":
                    $this->DecodeNextLine();
                    while ($this->Type == "CONT") {
                        $content .= $this->Rest;
                        $this->DecodeNextLine();
                    }
                    break;
                case "NOTE":
                    $this->addNoteLink("media", $media, $this->XRef);
                    break;
                case "SOUR":
                    $this->addCitation("media", $media);
                    break;
                default:
                    $this->DecodeNextLine();
                    break;
            }
        }
        if (strlen($content) > 0) {
            $data['content'] = sprintf('%08d', $data['id']) . '.' . $data['format'];
            $filename = $treemedia_path . $data['content'];
            $outfile = fopen($filename, 'wb');
            fwrite($outfile, base64_decode($content));
            fclose($outfile);
        } else {
            $data['content'] = null;
        }
        update_assoc($this->mysqli, "media", $media, $data);
    }

    function LoadFont($font) {
        $data = read_assoc($this->mysqli, "font", $font);
        $fontdata = explode("|", $this->Rest);
        $fontdata[2] = substr($fontdata[2], strlen($fontdata[2]) - 6, 6);
        $fontdata[3] = substr($fontdata[3], strlen($fontdata[3]) - 6, 6);
        $data['underline'] = ($fontdata[0] == "Y");
        $data['opaque_background'] = ($fontdata[1] == "Y");
        $data['font_R'] = hexdec(substr($fontdata[2], 0, 2));
        $data['font_G'] = hexdec(substr($fontdata[2], 2, 2));
        $data['font_B'] = hexdec(substr($fontdata[2], 4, 2));
        $data['background_R'] = hexdec(substr($fontdata[3], 0, 2));
        $data['background_G'] = hexdec(substr($fontdata[3], 2, 2));
        $data['background_B'] = hexdec(substr($fontdata[3], 4, 2));
        $data['style'] = $fontdata[4];
        $data['oblique'] = ($fontdata[5] == "Y");
        $data['size'] = hexdec($fontdata[6]);
        $data['bold'] = ($fontdata[7] == "Y");
        update_assoc($this->mysqli, "font", $font, $data);
        $this->DecodeNextLine();
    }

    function LoadHeader() {
        $data = read_assoc($this->mysqli, "tree", $this->treeId);
        $BaseLevel = $this->Level;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            switch ($this->Type) {
                case "SOUR":
                    $ll = $this->Level;
                    $this->DecodeNextLine();
                    while (!$this->complete && ($this->Level > $ll)) {
                        if ($this->Type == "CORP") {
                            do_log($this->mysqli, 'CORP found with: ' . $this->Rest);
                            $this->ag_file = (strtolower($this->Rest) == "affablegenes.com");
                            if($this->ag_file)
                                do_log($this->mysqli, 'This is a recognised Affable Genes file');
                            else
                                do_log($this->mysqli, 'This NOT a recognised Affable Genes file');
                        }
                        $this->DecodeNextLine();
                    }
                    break;
                case "SUBM":
                    $data['author'] = $this->getObject($this->XRef, "submitter");
                    $this->DecodeNextLine();
                    break;
                case "_ORF":
                    if ($this->ag_file) {
                        $this->LoadFont($data['originator_font']);
                    } else {
                        $this->DecodeNextLine();
                    }
                    break;
                case "_SCX":
                    if ($this->ag_file) {
                        $data['scroll_X'] = floatval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_SCY":
                    if ($this->ag_file) {
                        $data['scroll_Y'] = floatval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_ASP":
                    if ($this->ag_file) {
                        $data['aspect_ratio'] = floatval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_OLF":
                    if ($this->ag_file) {
                        $this->LoadFont($data['other_line_font']);
                    } else {
                        $this->DecodeNextLine();
                    }
                    break;
                case "_TTF":
                    if ($this->ag_file) {
                        $this->LoadFont($data['title_font']);
                    } else {
                        $this->DecodeNextLine();
                    }
                    break;
                case "_FLF":
                    if ($this->ag_file) {
                        $this->LoadFont($data['first_line_font']);
                    } else {
                        $this->DecodeNextLine();
                    }
                    break;
                case "_JCOC":
                    if ($this->ag_file) {
                        $str = $this->Rest;
                        $data['connecting_R'] = hexdec(substr($str, 0, 2));
                        $data['connecting_G'] = hexdec(substr($str, 2, 2));
                        $data['connecting_B'] = hexdec(substr($str, 4, 2));
                    }
                    $this->DecodeNextLine();
                    break;
                case "_JLTH":
                    if ($this->ag_file) {
                        $data['line_thickness'] = intval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_JMLHI":
                    if ($this->ag_file) {
                        $data['line_height'] = intval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_JMMGI":
                    if ($this->ag_file) {
                        $data['marriage_gap'] = intval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_JMIHI":
                    if ($this->ag_file) {
                        $data['min_indi_H'] = intval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_JMIWI":
                    if ($this->ag_file) {
                        $data['min_indi_W'] = intval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_JMSGI":
                    if ($this->ag_file) {
                        $data['sibling_gap'] = intval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_OTL":
                    if ($this->ag_file) {
                        $data['box_outline'] = ($this->Rest == "Y");
                    }
                    $this->DecodeNextLine();
                    break;
                case "_JOLC":
                    if ($this->ag_file) {
                        $str = $this->Rest;
                        $data['outline_R'] = hexdec(substr($str, 0, 2));
                        $data['outline_G'] = hexdec(substr($str, 2, 2));
                        $data['outline_B'] = hexdec(substr($str, 4, 2));
                    }
                    $this->DecodeNextLine();
                    break;
                case "_JOLT":
                    if ($this->ag_file) {
                        $data['outline_thickness'] = intval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_TTS":
                    if ($this->ag_file) {
                        $data['title'] = $this->Rest;
                    }
                    $this->DecodeNextLine();
                    break;
                case "_RZM":
                    if ($this->ag_file) {
                        $data['zoom'] = floatval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_TNW":
                    if ($this->ag_file) {
                        $data['thumbnail_W'] = intval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_TNH":
                    if ($this->ag_file) {
                        $data['thumbnail_H'] = intval($this->Rest);
                    }
                    $this->DecodeNextLine();
                    break;
                case "_WMK":
                    if ($this->ag_file) {
                        $watermarkFile = $this->getObject($this->Rest, "media");
                        $data['watermark_media_id'] = $watermarkFile;
                    }
                    $this->DecodeNextLine();
                    break;
                case "FILE":
                    $this->DecodeNextLine();
                    break;
                default:
                    $ll = $this->Level;
                    $this->DecodeNextLine();
                    while (!$this->complete && ($this->Level > $ll)) {
                        $this->DecodeNextLine();
                    }
                    break;
            }
        }
        update_assoc($this->mysqli, "tree", $this->treeId, $data);
    }

    function AddEvent($type, $id) {
        $eventId = createDefaultEvent($this->mysqli, $this->treeId, $type, $id);
        $this->LoadEvent($eventId);
    }

    function LoadEvent($event) {
        $data = read_assoc($this->mysqli, "event", $event);
        $evType = $this->Type;
        $data['type'] = $evType;
        $data['argument'] = $this->Rest;
        $BaseLevel = $this->Level;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            if (($evType == "ADOP") && ($this->Type == "FAMC")) {
                $data['adoptive_family_id'] = $this->getObject($this->XRef, "family");
                $this->DecodeNextLine();
            } else if (($evType == "ADOP") && ($this->Type == "ADOP")) {
                $data['adopted_by_which_parent'] = $this->Rest;
                $this->DecodeNextLine();
            } else if ($this->Type == "TYPE") {
                $data['event_descriptor'] = $this->Rest;
                $this->DecodeNextLine();
            } else if ($this->Type == "HUSB") {
                $ll = $this->Level;
                $this->DecodeNextLine();
                if ($this->Type == "AGE") {
                    $data['husband_age'] = $this->Rest;
                }
                while (!$this->complete && ($this->Level > $ll)) {
                    $this->DecodeNextLine();
                }
            } else if ($this->Type == "WIFE") {
                $ll = $this->Level;
                $this->DecodeNextLine();
                if ($this->Type == "AGE") {
                    $data['wife_age'] = $this->Rest;
                }
                while (!$this->complete && ($this->Level > $ll)) {
                    $this->DecodeNextLine();
                }
            } else if ($this->Type == "DATE") {
                $date = $data['event_date_id'];
                if ($date == null) {
                    $date = createDefaultComplexDate($this->mysqli, $this->treeId);
                    $data['event_date_id'] = $date;
                }
                $dateo = new CDateValue();
                $dateo->Interpret($this->Rest);
                $dateo->UpdateDatabase($this->mysqli, $date);
                $this->DecodeNextLine();
            } else if ($this->Type == "AGE") {
                $data['age_at_event'] = $this->Rest;
                $this->DecodeNextLine();
            } else if ($this->Type == "AGNC") {
                $data['responsible_agency'] = $this->Rest;
                $this->DecodeNextLine();
            } else if ($this->Type == "CAUS") {
                $data['cause_of_event'] = $this->Rest;
                $this->DecodeNextLine();
            } else if ($this->Type == "ADDR") {
                $address = $data['address_id'];
                if ($address == null) {
                    $address = createDefaultAddress($this->mysqli, $this->treeId);
                    $data['address_id'] = $address;
                }
                $this->LoadAddress($address);
            } else if ($this->Type == "PHON") {
                $PhoneNumber = $this->Rest;
                if (strlen($PhoneNumber) > 25) {
                    $PhoneNumber = substr($PhoneNumber, 0, 25);
                }
                $address = $data['address_id'];
                if ($address == null) {
                    $address = createDefaultAddress($this->mysqli, $this->treeId);
                    $data['address_id'] = $address;
                }
                $addressData = read_assoc($this->mysqli, "address", $address);
                $addressData['phone'] = $PhoneNumber;
                update_assoc($this->mysqli, "address", $address, $addressData);
                $this->DecodeNextLine();
            } else if ($this->Type == "PLAC") {
                $place = $data['place_id'];
                if ($place == null) {
                    $place = createDefaultPlace($this->mysqli, $this->treeId);
                    $data['place_id'] = $place;
                }
                $this->LoadPlace($place);
            } else if ($this->Type == "SOUR") {
                $this->addCitation("event", $event);
            } else if ($this->Type == "OBJE") {
                $this->addMedialink("event", $event, $this->XRef);
            } else if ($this->Type == "NOTE") {
                $this->addNoteLink("event", $event, $this->XRef);
            } else {
                $this->DecodeNextLine(); // ADDED !!!!!
            }
        }
        update_assoc($this->mysqli, "event", $event, $data);
    }

    function addSubmitterLink($type, $id, $XRefin) {
        $submitterId = $this->getObject($XRefin, "submitter");
        createDefaultSubmitterLink($this->mysqli, $this->treeId, $type, $id, $submitterId);
        $this->DecodeNextLine();
    }

    function addCitation($type, $id) {
        $citation = createDefaultCitation($this->mysqli, $this->treeId, $type, $id);
        $this->LoadCitation($citation);
    }

    function LoadCitation($citation) {
        $citationdata = read_assoc($this->mysqli, "citation", $citation);
        if (strlen($this->XRef) == 0) {
            // Old structure not using source records - So convert it!
            $source = createDefaultSource($this->mysqli, $this->treeId, nextLabel($this->mysqli, $this->treeId, "source"));
            $sourcedata = read_assoc($this->mysqli, "source", $source);
            $title = $this->Rest;
            $text = "";
            $BaseLevel = $this->Level;
            $this->DecodeNextLine();
            while (!$this->complete && ($this->Level > $BaseLevel)) {
                switch ($this->Type) {
                    case "CONC":
                        $title .= $this->Rest;
                        $this->DecodeNextLine();
                        break;
                    case "CONT":
                        $title .= "\n" . $this->Rest;
                        $this->DecodeNextLine();
                        break;
                    case "TEXT":
                        $text = $this->Rest;
                        $this->DecodeNextLine();
                        while (($this->Type == "CONC") || ($this->Type == "CONT")) {
                            if ($this->Type == "CONT") {
                                $text .= "\n";
                            }
                            $text .= $this->Rest;
                            $this->DecodeNextLine();
                        }
                        break;
                    case "NOTE":
                        $this->addNoteLink("citation", $citation, $this->XRef);
                        break;
                    case "DATA":
                        $this->DecodeNextLine();
                        break;
                    default:
                        // GEDFile.ReportUnused("Citation Object");
                        $this->DecodeNextLine(); // Added !!
                        break;
                }
            }
            $sourcedata['text'] = $text;
            $sourcedata['title'] = $title;
            update_assoc($this->mysqli, "source", $source, $sourcedata);
            $citationdata['source_id'] = $source;
        } else {
            $source = $this->getObject($this->XRef, "source");
            $citationdata['source_id'] = $source;
            $BaseLevel = $this->Level;
            $this->DecodeNextLine();
            while (!$this->complete && ($this->Level > $BaseLevel)) {
                switch ($this->Type) {
                    case "PAGE":
                        $citationdata['where_within_source'] = $this->Rest;
                        $this->DecodeNextLine();
                        break;
                    case "EVEN":
                        $citationdata['event_type'] = $this->Rest;
                        $this->DecodeNextLine();
                        break;
                    case "ROLE":
                        $citationdata['role_in_event'] = $this->Rest;
                        $this->DecodeNextLine();
                        break;
                    case "DATE":
                        $date = $citationdata['entry_recording_date_id'];
                        if ($date == null) {
                            $date = createDefaultComplexDate($this->mysqli, $this->treeId);
                            $citationdata['entry_recording_date_id'] = $date;
                        }
                        $dateo = new CDateValue();
                        $dateo->Interpret($this->Rest);
                        $dateo->UpdateDatabase($this->mysqli, $date);
                        $this->DecodeNextLine();
                        break;
                    case "TEXT":
                        $text = $this->Rest;
                        $this->DecodeNextLine();
                        while (($this->Type == "CONC") || ($this->Type == "CONT")) {
                            if ($this->Type == "CONT") {
                                $text .= "\n";
                            } else {
                                $text .= " ";
                            }
                            $text .= $this->Rest;
                            $this->DecodeNextLine();
                        }
                        $citationdata['text_from_source'] = $text;
                        break;
                    case "QUAY":
                        $citationdata['certainty_assessment'] = $this->Rest;
                        $this->DecodeNextLine();
                        break;
                    case "OBJE":
                        $this->addMedialink("citation", $citation, $this->XRef);
                        break;
                    case "NOTE":
                        $this->addNoteLink("citation", $citation, $this->XRef);
                        break;
                    case "DATA":
                        $this->DecodeNextLine();
                        break;
                    default:
                        $this->DecodeNextLine(); // Added !!
                        break;
                }
            }
        }
        update_assoc($this->mysqli, "citation", $citation, $citationdata);
    }

    function addMediaLink($type, $id, $XRefin) {
        $media = $this->getObject($XRefin, "media");
        createDefaultMediaLink($this->mysqli, $this->treeId, $type, $id, $media);
        $this->DecodeNextLine();
    }

    function addNoteLink($type, $id, $XRefin) {
        $note = $this->getObject($XRefin, "note");
        createDefaultNoteLink($this->mysqli, $this->treeId, $type, $id, $note);
        $this->DecodeNextLine();
    }

    function LoadPlace($place) {
        $data = read_assoc($this->mysqli, "place", $place);
        $BaseLevel = $this->Level;
        $data['place_hierarchy'] = "";
        $data['place_value'] = $this->Rest;
        $this->DecodeNextLine();
        while (!$this->complete && ($this->Level > $BaseLevel)) {
            switch ($this->Type) {
                case "FORM":
                    $data['place_hierarchy'] = $this->Rest;
                    $this->DecodeNextLine();
                    break;
                case "SOUR":
                    $ll = $this->Level;
                    while (!$this->complete && ($this->Level > $ll)) {
                        $this->DecodeNextLine();
                    }
                    break;
                case "NOTE":
                    $ll = $this->Level;
                    while (!$this->complete && ($this->Level > $ll)) {
                        $this->DecodeNextLine();
                    }
                    break;
                // GEDFile.ReportUnused("Place Object");
                default:
                    $this->DecodeNextLine(); // Added !!
                    break;
            }
        }
        update_assoc($this->mysqli, "place", $place, $data);
    }

}
