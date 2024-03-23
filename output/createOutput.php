<?php
/*

createOutput is a class that take the tree with all the relationships and from them forms a map
of how the tree can be displayed in a map comprising a 2D array of 'slices'. Then the same class prepares the output ready to render onto
the screen or into a PDF file.

To output to a PDF the following calls are made:
    $ep = new createOutput($mysqli, $treeId, true, $media_path, false, $path . $backgrounds[$treeData['watermark_media_id']][1]);
    $ep->fpdf->Output('D', date('Y-m-d') . ' Affable Genes Family Tree.pdf');

To output to the screen the following calls are made:
    $ep = new createOutput($mysqli, $treeId, false, $media_path, false, $path . $backgrounds[intval($treeData['watermark_media_id'])][1]);
    $ep->writeScriptOutput();

Note: the 3rd argument passed to the constructor determines whether the created output will be destined for the
screen or a PDF fprmat

*/

class createOutput
{
    var $Target;

    var $pAll;      // A temporaty structure containg most of the data for this trww (used to be a class in c++ version)
    var $ConnectingLines; // of the class 'ConnectingLineList'
    var $Boxes;     // of the class 'BoxList'
    var $UIrSize;   // Size of the area to draw on - memebrs are height and width
    var $OutlineThickness;
    var $sideGap;
    var $fpdf;      // an FPDF object to draw a PDF
    var $bg_file;   // Background to show behind the tree
    var $map;       // An object containing a map of OutputMapSlices
    var $SSO;       // A list of all the SiblingSpouseObjects

    var $TitleSize;
    var $AuthorSize;

    function __construct($mysqli, $treeId, $asPDF, $media_path, $withlinks, $bg_file_in)
    {
        // Make a note of the background file that will be used
        $this->bg_file = $bg_file_in;
        // Create a list of boxes and connecting lines which will let us
        // draw and trace back from screen coordinates to object
        $this->Boxes = new BoxList();
        $this->ConnectingLines = new ConnectingLineList();
        // Read all the data to a local store so I don't have to trouble the database afterward
        $this->pAll = new LoadAllToDisplay($mysqli, $treeId, $media_path, $asPDF);
        // Set the size of the border 
        $this->OutlineThickness = 20;
        $this->sideGap = $this->OutlineThickness * 3;
        // create a temporary pdf to load fonts so we can measure string sizes
        if ($asPDF)
            $this->fpdf = new FPDF('L', 'pt', array(10, 10));
        else
            $this->fpdf = null;
        // Compute the minimum size boxes for each individual
        $this->calcMinBoxSizes($asPDF);
        // Compute relative positions of everybody visible, starting at RootIndividual
        // but not making any determination of size & relative positioning just yet
        $this->prepOutputMap();
        // Work out how much space to leave for the credits in the bottom right
        $this->updateAuthorVars($asPDF);
        // Compute the sizes of borders etc.
        $minimumWidth = 700;
        $minimumWidth = max($minimumWidth, $this->AuthorSize['width'] + 2 * $this->sideGap);
        $minimumWidth = max($minimumWidth, $this->TitleSize['width'] + 2 * $this->sideGap);
        $aspectRatio = $this->pAll->treeData['aspect_ratio'];
        $bottomGap = $this->sideGap + $this->AuthorSize['height'];
        $topGap = $this->sideGap + $this->TitleSize['height'];
        // Work out sizes and relative positioning of everyone and the size of the final output canvas
        // And create a list of boxes and Connecting Lines which represent
        // everything to be displayed except the border, the title, the watermark
        // and the Author/Originator box in the bottom left
        $this->UIrSize = $this->Draw(
            $topGap,
            $bottomGap,
            $this->sideGap,
            $minimumWidth,
            $aspectRatio,
            $this->Boxes,
            $this->ConnectingLines
        );
        // Some convenient local copies of variables...
        $uw = $this->UIrSize['width'];
        $uh = $this->UIrSize['height'];
        // Add the title box
        $this->Boxes->AddBox("TitleType", null, ($uw - $this->TitleSize['width']) / 2, $this->sideGap, $this->TitleSize['width'], $this->TitleSize['height']);
        // Add the Author/Originator box
        $this->Boxes->AddBox("AuthorType", null, $uw - $this->sideGap - $this->AuthorSize['width'], $uh - $this->sideGap - $this->AuthorSize['height'], $this->AuthorSize['width'], $this->AuthorSize['height']);
        // Write the output to a fpdf if needed. Then it will be possible to just do fpdf->output
        if ($asPDF) {
            $this->fpdf = new FPDF('L', 'pt', array($this->UIrSize['width'], $this->UIrSize['height']));
            $this->fpdf->SetAutoPageBreak(false);
            $this->fpdf->SetAuthor('Affable Genes');
            $this->fpdf->SetTitle($this->pAll->treeData['title']);
            $this->fpdf->AddPage();
            $bg_size = getimagesize($this->bg_file);
            for ($x = 0; $x < $this->UIrSize['width']; $x += $bg_size[0]) {
                for ($y = 0; $y < $this->UIrSize['height']; $y += $bg_size[1]) {
                    $this->fpdf->Image($this->bg_file, $x, $y, $bg_size[0], $bg_size[1]);
                }
            }
            // Add the border
            $s2 = $this->sideGap / 2;
            $uw = $this->UIrSize['width'];
            $uh = $this->UIrSize['height'];
            $this->fpdf->SetLineWidth($this->sideGap);
            $this->fpdf->SetDrawColor(255, 255, 255);
            $this->fpdf->Line(0, $s2, $uw, $s2);
            $this->fpdf->Line(0, $uh - $s2, $uw, $uh - $s2);
            $this->fpdf->Line($s2, 0, $s2, $uh);
            $this->fpdf->Line($uw - $s2, 0, $uw - $s2, $uh);
            $this->fpdf->SetLineWidth($this->OutlineThickness);
            $this->fpdf->SetDrawColor(127, 127, 127);
            $this->fpdf->Line($s2, $s2, $uw - $s2, $s2);
            $this->fpdf->Line($s2, $uh - $s2, $uw - $s2, $uh - $s2);
            $this->fpdf->Line($s2, $s2, $s2, $uh - $s2);
            $this->fpdf->Line($uw - $s2, $s2, $uw - $s2, $uh - $s2);
            // Go through the ConnectionList and draw everything
            $this->ConnectingLines->drawAllToPDFCanvas($this->pAll, $this->fpdf);
            // Go through the Box list and draw everything
            $this->Boxes->drawAllToPDFCanvas($this->pAll, $this->fpdf, $withlinks);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Now the code that 0utputs the Java Script that will be passed to the browser (client)
    ////////////////////////////////////////////////////////////////////////////////////////////////

    function writeScriptOutput()
    {
        // Most of this is now done in JavaScrip - Only thing I have to do here
        // is populate the images for the boxes and write out the variable
        // the script will need using 'echo'
        // Done in JavaScrip: 'Add the border' and 'Go through the ConnectionList and draw everything'
        //
        // Output variables to describe Sheet size and outline
        echo "var side_gap = " . $this->sideGap . ";\n";
        echo "var outline_thickness = " . $this->OutlineThickness . ";\n";
        echo "var uw = " . $this->UIrSize['width'] . ";\n";
        echo "var uh = " . $this->UIrSize['height'] . ";\n";
        echo "var bg_image = JSON.parse('" . json_encode(base64_encode(file_get_contents($this->bg_file)), JSON_UNESCAPED_SLASHES) . "');\n";
        $bg_size = getimagesize($this->bg_file);
        echo "var bg_w = " . $bg_size[0] . ";\n";
        echo "var bg_h = " . $bg_size[1] . ";\n";
        // Output the data to draw the lines
        echo "var line_thickness = " . $this->pAll->treeData['line_thickness'] . ";\n";
        echo "var line_color = '" . RGBStr(
            $this->pAll->treeData['connecting_R'],
            $this->pAll->treeData['connecting_G'],
            $this->pAll->treeData['connecting_B']
        ) . "';\n";
        echo "var lines = JSON.parse('" . json_encode($this->ConnectingLines->list, JSON_UNESCAPED_SLASHES) . "');\n";
        // echo "window.alert('Lines read');\n";
        $this->Boxes->drawAllToImgCanvas($this->pAll);
        // Go through the Box list and draw everything
        echo "var boxes = JSON.parse('" . json_encode($this->Boxes->list, JSON_UNESCAPED_SLASHES) . "');\n";
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Now the code that supports the constructor
    ////////////////////////////////////////////////////////////////////////////////////////////////

    function prepOutputMap()
    {
        // 'Create the default map
        $this->map = new OutputMap();
        // Now scan the map, expanding where necessary - Until no more expansion happens!
        $SomeDone = true;
        while ($SomeDone) {
            $SomeDone = false;
            $nextRow = $this->map->TopLeft();
            while ($nextRow !== null) {
                $pSlice = $nextRow;
                $nextRow = $this->map->Below($nextRow);
                while ($pSlice !== null) {
                    // Does this Slice need expanding?
                    if (!$this->map->isComplete($pSlice)) {
                        $SomeDone = true;
                        switch ($this->map->Type($pSlice)) {
                            case UpSlice:
                                $this->DoUpLink($pSlice);
                                break;
                            case DnSlice:
                                $this->DoDnLink($pSlice);
                                break;
                            case SeedSlice:
                                $this->DoSeed($pSlice);
                                break;
                            default:
                                echo 'Error: There is either a new type of slice or a gap/cont is marked incomplete<br>';
                                die();
                        }
                    }
                    // Now move right
                    $pSlice = $this->map->Right($pSlice);
                }
            }
        }
        $this->map->ShowStructure("On completion of constructor");
    }

    function DoSeed($InSlice)
    {
        // Create the SiblingSpouseObject for the SeedIndividual
        // and put it at the start of the SSO array which holds all of the SSO
        // objects
        $this->SSO = [new SiblingSpouseObject($this->pAll, $this->pAll->treeData['root'], true)];
        $SSSeed = &$this->SSO[0];
        if (($SSSeed->nDnLink === 0) && ($SSSeed->nUpLink === 0)) {
            // No links so just attach the SSObject
            $this->map->setSSGroup($InSlice, 0); // Index into SSO array
            $this->map->setComplete($InSlice, true);
        } else {
            $iSlice = $InSlice;
            // Create a row of cont's long enough to contain all the uplinks and downlinks
            for ($i = 0; $i < ($SSSeed->nUpLink + $SSSeed->nDnLink) * 2 - 1; $i++) {
                $this->map->setType($iSlice, ContSlice);
                $this->map->GrowColumnToRightOf($iSlice);
                $iSlice = $this->map->Right($iSlice);
            }
            // Work through up links sequentially
            $iSlice = $InSlice;
            for ($i = 0; $i < $SSSeed->nUpLink; $i++) {
                $fam = $this->SSO[0]->apUpLink[$i]['LinkedFamily'];
                $this->map->createUpLink($iSlice, 0, $i, $fam, false);
                if ($fam !== null) $this->pAll->Families[$fam]['UpLinkDone'] = true;
                $iSlice = $this->map->Right($iSlice);
                $iSlice = $this->map->Right($iSlice);
            }
            // Work through down links sequentially
            for ($i = 0; $i < $SSSeed->nDnLink; $i++) {
                $fam = $this->SSO[0]->apDnLink[$i]['LinkedFamily'];
                $this->map->createDnLink($iSlice, 0, $i, $fam, false);
                if ($fam !== null) $this->pAll->Families[$fam]['DnLinkDone'] = true;
                $iSlice = $this->map->Right($iSlice);
                $iSlice = $this->map->Right($iSlice);
            }
        }
        $this->map->ShowStructure("Seed done");
    }

    function DoUpLink($InSlice)
    {
        // So this is an uplink and it must have a corresponding down link above it
        // from whence it was created. Find what is downlinked to:
        $SliceAbove = $this->map->Above($InSlice);
        $SSOAbove = $this->map->SSGroup($SliceAbove);
        $pDnLinkAbove = $this->map->pDnLink($SliceAbove);
        $DnFam = $this->SSO[$SSOAbove]->apDnLink[$pDnLinkAbove]['LinkedFamily'];

        if ($this->pAll->Families[$DnFam]['UpLinkDone']) {
            // If this uplink has been drawn before then don't create a new SSO
            // just forget I ever asked.
            $husband = $this->pAll->Families[$DnFam]['husband'];
            if ($husband !== null) {
                $this->pAll->People[$husband]['times_shown']++;
            }
            $wife = $this->pAll->Families[$DnFam]['wife'];
            if ($wife !== null) $this->pAll->People[$wife]['times_shown']++;
            $this->map->setType($InSlice, GapSlice);
            $this->map->setComplete($InSlice, true);
            $this->map->setComplete($SliceAbove, true);
        } else {
            $this->pAll->Families[$DnFam]['UpLinkDone'] = true;

            // Create an object to describe it
            array_push(
                $this->SSO,
                new SiblingSpouseObject(
                    $this->pAll,
                    $this->pAll->Families[$DnFam]['children'][0],
                    false
                )
            );
            $iSSSeed = count($this->SSO) - 1;
            $SSSeed = &$this->SSO[$iSSSeed];

            // So this is an uplink and it must have a corresponding down link above it
            // from whence it was created. i.e. don't need to create a row above
            // Lets find out which uplink in this SSObject matches the DnLink above
            $nMatch = -1;
            for ($i = 0; $i < $SSSeed->nUpLink; $i++) {
                if ($SSSeed->apUpLink[$i]['LinkedFamily'] === $DnFam) {
                    $nMatch = $i;
                }
            }

            if ($nMatch < 0) {
                echo 'nMatch < 0 in DoUpLink <br>';
                die();
            }

            // Are there any UpLinks to be draw to the Left?
            if ($nMatch > 0) {
                // If there are then look for somewhere to draw them
                $LinkLocation = $this->map->Left($InSlice);
                while (($this->map->TypeAbove($LinkLocation) != GapSlice) && ($this->map->TypeLeft($LinkLocation) == GapSlice)) {
                    $LinkLocation = $this->map->Left($LinkLocation);
                }
                $blind = !($this->map->TypeAbove($LinkLocation) == GapSlice);
                $LinkLocation = $this->map->Left($InSlice);
                if (!$blind) { 
                    while (($this->map->TypeAbove($LinkLocation) != GapSlice) && ($this->map->TypeLeft($LinkLocation) == GapSlice)) {
                        $this->map->setType($LinkLocation, ContSlice);
                        $LinkLocation = $this->map->Left($LinkLocation);
                    }
                } 
                for ($i = $nMatch - 1; $i >= 0; $i--) {                
                    if($blind) {
                        $this->SSO[$iSSSeed]->apUpLink[$i]['NoRoom'] = true;
                        $this->SSO[$iSSSeed]->apUpLink[$i]['LinkedFamily'] = null;
                    }
                    $this->map->GrowColumnToRightOf($LinkLocation);
                    $this->map->setTypeRight($LinkLocation, ContSlice);
                    $this->map->GrowColumnToLeftOf($LinkLocation);
                    $fam = $this->SSO[$iSSSeed]->apUpLink[$i]['LinkedFamily'];
                    if ($fam !== null) $this->pAll->Families[$fam]['UpLinkDone'] = true;
                    $this->map->createUpLink($LinkLocation, $iSSSeed, $i, $fam, false);
                    $LinkLocation = $this->map->Left($LinkLocation);
                }
            }
            $this->map->ShowStructure("DnUpLink: After left side UpLinks done");

            // Now deal with the matching UpLink
            $LinkLocation = $InSlice;
            $fam = $this->SSO[$iSSSeed]->apUpLink[$nMatch]['LinkedFamily'];
            $this->pAll->Families[$fam]['UpLinkDone'] = true;
            $this->map->createUpLink($LinkLocation, $iSSSeed, $nMatch, $fam, true);
            $this->map->ShowStructure("DnUpLink: After centre UpLinks done");

            // Are there any UpLinks to be draw to the Right?
            if ($nMatch + 1 < $SSSeed->nUpLink) {
                // If there are then look for somewhere to draw them
                $LinkLocation = $this->map->Right($LinkLocation);
                while (($this->map->TypeAbove($LinkLocation) != GapSlice) && ($this->map->TypeRight($LinkLocation) == GapSlice)) {
                    $LinkLocation = $this->map->Right($LinkLocation);
                }
                $blind = !($this->map->TypeAbove($LinkLocation) == GapSlice);
                $LinkLocation = $this->map->Right($LinkLocation);
                if (!$blind) { 
                    while (($this->map->TypeAbove($LinkLocation) != GapSlice) && ($this->map->TypeRight($LinkLocation) == GapSlice)) {
                        $this->map->setType($LinkLocation, ContSlice);
                        $LinkLocation = $this->map->Right($LinkLocation);
                    }
                }
                for ($i = $nMatch + 1; $i < $SSSeed->nUpLink; $i++) {   
                    if($blind) {
                        $this->SSO[$iSSSeed]->apUpLink[$i]['NoRoom'] = true;
                        $this->SSO[$iSSSeed]->apUpLink[$i]['LinkedFamily'] = null;
                    }
                    $this->map->GrowColumnToLeftOf($LinkLocation);
                    $this->map->setTypeLeft($LinkLocation, ContSlice);
                    $this->map->GrowColumnToRightOf($LinkLocation);
                    $fam = $this->SSO[$iSSSeed]->apUpLink[$i]['LinkedFamily'];
                    if ($fam !== null) $this->pAll->Families[$fam]['UpLinkDone'] = true;
                    $this->map->createUpLink($LinkLocation, $iSSSeed, $i, $fam, false);
                    $LinkLocation = $this->map->Right($LinkLocation);
                }
            }

            $this->map->ShowStructure("DoUpLink: After right side UpLinks done");

            // All the uplinks are done - Now do the downlinks
            // Are there any DnLinks to be draw?
            if ($SSSeed->nDnLink > 0) {
                // Make sure there is a row below
                if ($this->map->Below($InSlice) === null) $this->map->GrowRowOfGapsAtBottom();
                $PastEnd = false;
                $LinkLocation = $InSlice;
                do {
                    $TypeHere = $this->map->Type($LinkLocation);
                    $PastEnd = $PastEnd || ($TypeHere == GapSlice);
                    $CanGoHere = (($TypeHere == ContSlice) || ($TypeHere == GapSlice)) && ($this->map->TypeBelow($LinkLocation) == GapSlice);
                    $CanMoveLeft = (!$CanGoHere) && ((!$PastEnd) || ($this->map->TypeLeft($LinkLocation) == GapSlice));
                    if ($CanMoveLeft) $LinkLocation = $this->map->Left($LinkLocation);
                } while (!$CanGoHere && $CanMoveLeft);
                if ($CanGoHere) {
                    // Put any Cont's in if necessary
                    $tSlice = $this->map->Right($LinkLocation);
                    while ($this->map->Type($tSlice) == GapSlice) {
                        $this->map->setType($tSlice, ContSlice);
                        $tSlice = $this->map->Right($tSlice);
                    }

                    // Now we are over the first gap to the left
                    // Work through the necessary DnLinks
                    for ($i = $SSSeed->nDnLink - 1; $i >= 0; $i--) {
                        // Put a column to the right to separate this location from the last thing above
                        $this->map->GrowColumnToRightOf($LinkLocation);
                        $this->map->setTypeRight($LinkLocation, ContSlice);
                        // Put a column to the left although it may not be necessary
                        $this->map->GrowColumnToLeftOf($LinkLocation);
                        $fam = $this->SSO[$iSSSeed]->apDnLink[$i]['LinkedFamily'];
                        if($fam !== null) $this->pAll->Families[$fam]['DnLinkDone'] = true;
                        $this->map->createDnLink($LinkLocation, $iSSSeed, $i, $fam, false);
                        $LinkLocation = $this->map->Left($LinkLocation);
                    }

                    $this->map->ShowStructure("DnUpLink: Downlinks done on left");
                } else { // Look right instead
                    $PastEnd = false;
                    $LinkLocation = $InSlice;
                    do {
                        $TypeHere = $this->map->Type($LinkLocation);
                        $PastEnd = $PastEnd || ($TypeHere == GapSlice);
                        $CanGoHere = (($TypeHere == ContSlice) || ($TypeHere == GapSlice)) && ($this->map->TypeBelow($LinkLocation) == GapSlice);
                        $CanMoveLeft = (!$CanGoHere) && ((!$PastEnd) || ($this->map->TypeRight($LinkLocation) == GapSlice));
                        if ($CanMoveLeft) $LinkLocation = $this->map->Right($LinkLocation);
                    } while (!$CanGoHere && $CanMoveLeft);
                    $tSlice = $this->map->Left($LinkLocation);
                    if ($CanGoHere) {
                        // Put any Cont's in if necessary
                        while ($this->map->Type($tSlice) == GapSlice) {
                            $this->map->setType($tSlice, ContSlice);
                            $tSlice = $this->map->Left($tSlice);
                        }
                    }
                    // Now we are over the first gap to the left
                    // Work through the necessary DnLinks
                    for ($i = 0; $i < $SSSeed->nDnLink; $i++) {
                        if(!$CanGoHere) {
                            $this->SSO[$iSSSeed]->apDnLink[$i]['NoRoom'] = true;
                            $this->SSO[$iSSSeed]->apDnLink[$i]['LinkedFamily'] = null;
                        }
                        $this->map->GrowColumnToLeftOf($LinkLocation);
                        $this->map->setTypeLeft($LinkLocation, ContSlice);
                        $this->map->GrowColumnToRightOf($LinkLocation);
                        $fam = $this->SSO[$iSSSeed]->apDnLink[$i]['LinkedFamily'];
                        if($fam !== null) $this->pAll->Families[$fam]['DnLinkDone'] = true;
                        $this->map->createDnLink($LinkLocation, $iSSSeed, $i, $fam, false);
                        $LinkLocation = $this->map->Right($LinkLocation);
                    }
                    $this->map->ShowStructure("DoUpLink: Blind UpLinks done on right");
                }
            }
        }
    }

    function DoDnLink($InSlice)
    {
        // So this is an DnLink and it must have a corresponding up link below it
        // from whence it was created. Find what is uplinked from...
        $SliceBelow = $this->map->Below($InSlice);
        $SSOBelow = $this->map->SSGroup($SliceBelow);
        $pUpLinkBelow = $this->map->pUpLink($SliceBelow);
        $pF = $this->SSO[$SSOBelow]->apUpLink[$pUpLinkBelow]['LinkedFamily'];
        $pFData = $this->pAll->Families[$pF];

        // Set pIndo to the prefered parent
        if ($pFData['pref_type'] == 'n') {
            echo 'DoDnLink called when there are no parents to the family!<br>';
            die();
        }
        if ($pFData['pref_type'] == 'h')
            $pIndi = $pFData['husband'];
        else
            $pIndi = $pFData['wife'];

        // Create an object to describe the new SSO
        array_push($this->SSO, new SiblingSpouseObject($this->pAll, $pIndi, true));
        $iSSSeed = count($this->SSO) - 1;
        $SSSeed = &$this->SSO[$iSSSeed];

        if ($pFData['DnLinkDone']) {
            // If this DnLink has been marked as done then it just comprises parents
            // There can only be one down link in this limited SSObject and it must match the UpLink below
            $fam = $this->SSO[$iSSSeed]->apDnLink[0]['LinkedFamily'];
            $this->map->createDnLink($InSlice, $iSSSeed, 0, $fam, true);
            $this->map->ShowStructure("DoDnLink: After limited DnLink done");
        } else {
            $this->pAll->Families[$pF]['DnLinkDone'] = true;

            // So this is a down link and it must have a corresponding up link above it
            // from whence it was created. i.e. don't need to create a row below
            // Lets find out which down link in this SSObject matches the UpLink below
            $nMatch = -1;
            for ($i = 0; $i < $SSSeed->nDnLink; $i++) {
                if ($SSSeed->apDnLink[$i]['LinkedFamily'] === $pF) {
                    $nMatch = $i;
                }
            }

            if ($nMatch < 0) {
                echo 'nMatch < 0 in DoDnLink <br>';
                die();
            }

            // Are there any DnLinks to be draw to the Left?
            if ($nMatch > 0) {
                // If there are then look for somewhere to draw them
                $LinkLocation = $this->map->Left($InSlice);
                while (($this->map->TypeBelow($LinkLocation) != GapSlice) && ($this->map->TypeLeft($LinkLocation) == GapSlice)) {
                    $LinkLocation = $this->map->Left($LinkLocation);
                }
                $blind = !($this->map->TypeBelow($LinkLocation) == GapSlice);
                $LinkLocation = $this->map->Left($InSlice);
                if (!$blind) { 
                    while (($this->map->TypeBelow($LinkLocation) != GapSlice) && ($this->map->TypeLeft($LinkLocation) == GapSlice)) {
                        $this->map->setType($LinkLocation, ContSlice);
                        $LinkLocation = $this->map->Left($LinkLocation);
                    }
                }
                // Now we are above the first gap to the left, work through the necessary uplinks
                for ($i = $nMatch - 1; $i >= 0; $i--) {
                    if($blind) {
                        $this->SSO[$iSSSeed]->apDnLink[$i]['NoRoom'] = true;
                        $this->SSO[$iSSSeed]->apDnLink[$i]['LinkedFamily'] = null;
                    }
                    $this->map->GrowColumnToRightOf($LinkLocation);
                    $this->map->setTypeRight($LinkLocation, ContSlice);
                    $this->map->GrowColumnToLeftOf($LinkLocation);
                    $fam = $this->SSO[$iSSSeed]->apDnLink[$i]['LinkedFamily'];
                    if($fam !== null) $this->pAll->Families[$fam]['DnLinkDone'] = true;
                    $this->map->createDnLink($LinkLocation, $iSSSeed, $i, $fam, false);
                    $LinkLocation = $this->map->Left($LinkLocation);
                }
            }
            $this->map->ShowStructure("DoDnLink: After left side UpLinks done");

            // Now deal with the matching DnLink
            $LinkLocation = $InSlice;
            $fam = $this->SSO[$iSSSeed]->apDnLink[$nMatch]['LinkedFamily'];
            if($fam !== null) $this->pAll->Families[$fam]['DnLinkDone'] = true;
            $this->map->createDnLink($InSlice, $iSSSeed, $nMatch, $fam, true);
            $this->map->ShowStructure("DoDnLink: After centre UpLinks done");

            // Are there any DnLinks to be draw to the Right?
            if ($nMatch + 1 < $SSSeed->nDnLink) {
                // If there are then look for somewhere to draw them
                $LinkLocation = $this->map->Right($LinkLocation);
                while (($this->map->TypeBelow($LinkLocation) != GapSlice) && ($this->map->TypeRight($LinkLocation) == GapSlice)) {
                    $LinkLocation = $this->map->Right($LinkLocation);
                }
                $blind = !($this->map->TypeBelow($LinkLocation) == GapSlice);
                $LinkLocation = $this->map->Right($InSlice);
                if (!$blind) { 
                    while (($this->map->TypeBelow($LinkLocation) != GapSlice) && ($this->map->TypeRight($LinkLocation) == GapSlice)) {
                        $this->map->setType($LinkLocation, ContSlice);
                        $LinkLocation = $this->map->Right($LinkLocation);
                    }
                }
                for ($i = $nMatch + 1; $i < $SSSeed->nDnLink; $i++) {
                    if($blind) {
                        $this->SSO[$iSSSeed]->apDnLink[$i]['NoRoom'] = true;
                        $this->SSO[$iSSSeed]->apDnLink[$i]['LinkedFamily'] = null;
                    }
                    $this->map->GrowColumnToLeftOf($LinkLocation);
                    $this->map->setTypeLeft($LinkLocation, ContSlice);
                    // Put a column to the right although it may not be necessary
                    $this->map->GrowColumnToRightOf($LinkLocation);
                    $fam = $this->SSO[$iSSSeed]->apDnLink[$i]['LinkedFamily'];
                    if($fam !== null) $this->pAll->Families[$fam]['DnLinkDone'] = true;
                    $this->map->createDnLink($LinkLocation, $iSSSeed, $i, $fam, false);
                    $LinkLocation = $this->map->Right($LinkLocation);
                }
            }
            $this->map->ShowStructure("DoDnLink: After right side UpLinks done");

            // All the DnLinks are done. Are there any UpLinks to be drawn?
            if ($SSSeed->nUpLink > 0) {
                // Make sure there is a row above
                if ($this->map->Above($InSlice) === null) $this->map->GrowRowOfGapsAtTop();

                $PastEnd = false;
                $LinkLocation = $InSlice;
                do {
                    $TypeHere = $this->map->Type($LinkLocation);
                    $PastEnd = $PastEnd || ($TypeHere == GapSlice);
                    $CanGoHere = (($TypeHere == ContSlice) || ($TypeHere == GapSlice)) && ($this->map->TypeAbove($LinkLocation) == GapSlice);
                    $CanMoveLeft = (!$CanGoHere) && ((!$PastEnd) || ($this->map->TypeLeft($LinkLocation) == GapSlice));
                    if ($CanMoveLeft) $LinkLocation = $this->map->Left($LinkLocation);
                } while (!$CanGoHere && $CanMoveLeft);
                if ($CanGoHere) {
                    // Put any Cont's in if necessary
                    $tSlice = $this->map->Right($LinkLocation);
                    while ($this->map->Type($tSlice) == GapSlice) {
                        $this->map->setType($tSlice, ContSlice);
                        $tSlice = $this->map->Right($LinkLocation);
                    }

                    // Now we are over the first gap to the left
                    // Work through the necessary UpLinks
                    for ($i = $SSSeed->nUpLink - 1; $i >= 0; $i--) {
                        // Put a column to the right to separate this location from the last thing above
                        $this->map->GrowColumnToRightOf($LinkLocation);
                        $this->map->setTypeRight($LinkLocation, ContSlice);
                        // Put a column to the left although it may not be necessary
                        $this->map->GrowColumnToLeftOf($LinkLocation);
                        $fam = $this->SSO[$iSSSeed]->apUpLink[$i]['LinkedFamily'];
                        if ($fam !== null) $this->pAll->Families[$fam]['UpLinkDone'] = true;
                        $this->map->createUpLink($LinkLocation, $iSSSeed, $i, $fam, false);
                        $LinkLocation = $this->map->Left($LinkLocation);
                    }
                    $this->map->ShowStructure("DoDnLink: UpLinks done on left");
                } else { // Look right instead
                    $PastEnd = false;
                    $LinkLocation = $InSlice;
                    do {
                        $TypeHere = $this->map->Type($LinkLocation);
                        $PastEnd = $PastEnd || ($TypeHere == GapSlice);
                        $CanGoHere = (($TypeHere == ContSlice) || ($TypeHere == GapSlice)) && ($this->map->TypeAbove($LinkLocation) == GapSlice);
                        $CanMoveRight = (!$CanGoHere) && ((!$PastEnd) || ($this->map->TypeRight($LinkLocation) == GapSlice));
                        if ($CanMoveRight) $LinkLocation = $this->map->Right($LinkLocation);
                    } while (!$CanGoHere && $CanMoveRight);
                    $tSlice = $this->map->Left($LinkLocation);
                    if ($CanGoHere) {
                        while ($this->map->Type($tSlice) == GapSlice) {
                            $this->map->setType($tSlice, ContSlice);
                            $tSlice = $this->map->Left($tSlice);
                        }
                    }
                    for ($i = 0; $i < $SSSeed->nUpLink; $i++) {
                        if(!$CanGoHere) {
                            $this->SSO[$iSSSeed]->apUpLink[$i]['NoRoom'] = true;
                            $this->SSO[$iSSSeed]->apUpLink[$i]['LinkedFamily'] = null;
                        }
                        $this->map->GrowColumnToLeftOf($LinkLocation);
                        $this->map->setTypeLeft($LinkLocation, ContSlice);
                        $this->map->GrowColumnToRightOf($LinkLocation);
                        $fam = $this->SSO[$iSSSeed]->apUpLink[$i]['LinkedFamily'];
                        if ($fam !== null) $this->pAll->Families[$fam]['UpLinkDone'] = true;
                        $this->map->createUpLink($LinkLocation, $iSSSeed, $i, $fam, false);
                        $LinkLocation = $this->map->Right($LinkLocation);
                    }
                    $this->map->ShowStructure("DnUpLink: Upinks done on right");
                } 
            }
        }
    }

    function updateAuthorVars($asPDF)
    {
        $this->Needs_Asterisk_Explanation = $this->pAll->AnyShownMoreThanOnce();
        if ($asPDF) {
            $sLine1 = $this->textPDFSize($this->pAll->originator_font, $this->pAll->authorText);
            $sLine2 = $this->textPDFSize($this->pAll->originator_font, $this->pAll->TruTreeCredit);
            $sLine3 = $this->textPDFSize($this->pAll->originator_font, $this->pAll->AstriskExplanation);
        } else {
            $sLine1 = $this->textImgSize($this->pAll->originator_font, $this->pAll->authorText);
            $sLine2 = $this->textImgSize($this->pAll->originator_font, $this->pAll->TruTreeCredit);
            $sLine3 = $this->textImgSize($this->pAll->originator_font, $this->pAll->AstriskExplanation);
        }
        $this->AuthorSize = ['height' => $sLine1['height'] + $sLine2['height'], 'width' => max($sLine1['width'], $sLine2['width'])];
        if ($this->Needs_Asterisk_Explanation) {
            $this->AuthorSize = ['height' => $this->AuthorSize['height'] + $sLine3['height'], 'width' => max($this->AuthorSize['width'], $sLine3['width'])];
        }
        // Work out how much space to leave for the title at the top
        if ($asPDF) {
            $this->TitleSize = $this->textPDFSize($this->pAll->title_font, $this->pAll->Title);
        } else {
            $this->TitleSize = $this->textImgSize($this->pAll->title_font, $this->pAll->Title);
        }
    }

    function calcMinBoxSizes($asPDF)
    {
        // Set up the minimum individual sizes. These may be modified if the individual is shown in  stack
        foreach ($this->pAll->People as &$Indi) {
            $lines = strToArray($Indi['box_text']);
            $LineSize = ['height' => 0, 'width' => 0];
            $TotalSize = ['height' => 0, 'width' => 0];
            $FirstLine = true;
            for ($l = 0; $l < count($lines); $l++) {
                $ThisLine = $lines[$l];
                // Leave space for a symbol to mean drawn more than
                // once - will only be drawn if required
                if ($l == 0) {
                    $ThisLine .= " *";
                }
                if (strlen($ThisLine) === 0) {
                    $ThisLine = " ";
                }
                if ($asPDF) {
                    if ($l == 0) {
                        $LineSize = $this->textPDFSize($this->pAll->first_line_font, $ThisLine);
                    } else {
                        $LineSize = $this->textPDFSize($this->pAll->other_line_font, $ThisLine);
                    }
                } else {
                    if ($l == 0) {
                        $LineSize = $this->textImgSize($this->pAll->first_line_font, $ThisLine);
                    } else {
                        $LineSize = $this->textImgSize($this->pAll->other_line_font, $ThisLine);
                    }
                }
                $TotalSize['height'] += $LineSize['height'];
                $TotalSize['width'] = max($TotalSize['width'], $LineSize['width']);
            }
            // Look at media thumbnail sizes
            if ($Indi['l_media_thumb'] === null) {
                $lw = 0;
                $lh = 0;
            } else {
                $lw = imagesx($Indi['l_media_thumb']);
                $lh = imagesy($Indi['l_media_thumb']);
            }
            if ($Indi['r_media_thumb'] === null) {
                $rw = 0;
                $rh = 0;
            } else {
                $rw = imagesx($Indi['r_media_thumb']);
                $rh = imagesy($Indi['r_media_thumb']);
            }
            // Add some new space for the Left and Right media
            $TotalSize['width'] += $lw + $rw;
            $TotalSize['height'] = max($TotalSize['height'], $lh);
            $TotalSize['height'] = max($TotalSize['height'], $rh);
            if ($this->pAll->treeData['box_outline']) {
                $TotalSize['width'] += 2 * ($this->pAll->treeData['outline_thickness']);
                $TotalSize['height'] += 2 * ($this->pAll->treeData['outline_thickness']);
            }
            $TotalSize['width'] = max($TotalSize['width'], intval($this->pAll->treeData['min_indi_W']));
            $TotalSize['height'] = max($TotalSize['height'], intval($this->pAll->treeData['min_indi_H']));
            $Indi['box_size'] = $TotalSize;
        }
    }

    function textPDFSize($fontData, $text)
    {
        $this->fpdf->setAGFont($fontData);
        $width = $this->fpdf->GetStringWidth($text);
        return ["width" => $width, "height" => $fontData['size']];
    }

    function textImgSize($fontData, $text)
    {
        $fontFile = getFontFile($fontData);
        $bbox = imagetextbox($fontData['size'], $fontFile, $text);
        return ["width" => $bbox['w'], "height" => $bbox['h']];
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Now the code that the calculation of positioning each SSO horizontally and calculated the 
    // type of links that are necessary
    ////////////////////////////////////////////////////////////////////////////////////////////////

    function Draw(
        $topGap,
        $bottomGap,
        $sideGap,
        $minimumWidth,
        $aspectRatio,
        &$BoxListIn,
        &$ConListIn
    ) {
        // Variable used by calling function when shuffling the SSO about on the output
        $AbsX = [];
        $nWantToMove = [];
        $WantToMove = [];

        // Link has been broken into 2 arrays.
        $nLink = [];
        /* For both structure of xLink[i][j][k] is
         * i = Generation number counting from top to bottom
         * j = SSO in that generation counting from left to right
         * k = number 0 to n indexing several useful pieces of information
         */
        $iLink = []; // to hold the integers
        /*
        * k = 0: Link type
        *        0 = straight down
        *        1 = Siblings left of parents
        *        2 = Siblings right of parents
        *        3 = siblings below patents (??)
        * k = 1: Overlap count
        * k = 2: Ultimate overlap count
        * k = 3: index to SSO above in pSSO 
        * k = 4: index to SSO below in pSSO 
        */
        $fLink = []; // to hold the floats
        /*
         * k = 0: x absolute coordinate of downlink from generation above
         * k = 1: y absolute coordinate of bottom of downlink to siblings
         * k = 2: x absolute coordinate of leftmost downlink to a sibling
         * k = 3: x absolute coordinate of rightmost downlink to a sibling
         * k = 4: y absolute coordinate to the top of the link up to the parents above
         * k = 5: x relative coordinate of downlink from generation above 
         * k = 6: y relative coordinate of bottom of downlink to siblings 
         * k = 7: x relative coordinate of leftmost downlink to a sibling 
         * k = 8: x relative coordinate of rightmost downlink to a sibling 
        */

        $nSSO = [];      // Number of Spouse Sibling Objects (SSO) in a generation
        $pSSO = [];      // An array of pointers to the SSOs in the generation
        $SSOwidth = [];      // An array of pointers to the SSOs in the generation

        $QualityFactor = 1.02;
        // Count how many generations will be displayed and note the index of the top-left point on the map
        $nGen = $this->map->countGenerations();  // Number of generations (rows in the map)
        // Measure the total height of the output chart and the start position of each generation
        $TopLeft = $this->map->TopLeft();
        $nextRow = $TopLeft;
        $GenTop = [$topGap]; // Vertical position for the top of any perticular generation, Indexed by generation number

        $widest = 1;
        for ($iG = 0; $iG < $nGen; $iG++) {
            $width = 0;
            array_push($nSSO, 0);
            array_push($pSSO, []);
            array_push($AbsX, []);
            array_push($SSOwidth, []);
            array_push($nWantToMove, []);
            array_push($WantToMove, []);
            array_push($nLink, 0);
            array_push($iLink, []);
            array_push($fLink, []);
            $GenHeight = 0;
            $pSlice = $nextRow;
            $nextRow = $this->map->Below($nextRow);
            $lastSSObject = null;
            while ($pSlice !== null) {
                $iSSO = $this->map->SSGroup($pSlice);
                if (($iSSO !== null) && ($iSSO !== $lastSSObject)) {
                    $GenHeight = max($GenHeight, $this->SSO[$iSSO]->size['height']);
                    $nSSO[$iG]++;
                    array_push($pSSO[$iG], $iSSO);
                    array_push($AbsX[$iG], $width);
                    array_push($nWantToMove[$iG], 0);
                    array_push($WantToMove[$iG], 0);
                    array_push($SSOwidth[$iG], $this->SSO[$iSSO]->size['width']);
                    $width += $this->SSO[$iSSO]->size['width'];
                    $lastSSObject = $iSSO;
                }
                if ($this->map->Type($pSlice) == UpSlice) {
                    $iUp = $this->map->pUpLink($pSlice);
                    $nLink[$iG]++;
                    $aboveX = 0;
                    $aboveY = 0;
                    $sliceAboveIndex = null;
                    if ($this->SSO[$iSSO]->apUpLink[$iUp]['LinkedFamily'] !== null) {
                        $sliceAbove = $this->map->Above($pSlice);
                        $iSSOAbove = $this->map->SSGroup($sliceAbove);
                        $iDnAbove = $this->map->pDnLink($sliceAbove);
                        $sliceAboveIndex = array_search($iSSOAbove, $pSSO[$iG - 1]);
                        $aboveX = $this->SSO[$iSSOAbove]->apDnLink[$iDnAbove]['Px'];
                        $aboveY = $this->SSO[$iSSOAbove]->apDnLink[$iDnAbove]['Py'];
                    } 
                    array_push($iLink[$iG], [0, 0, 0, $sliceAboveIndex, $nSSO[$iG] - 1]);
                    array_push($fLink[$iG], [
                        0, 0, 0, 0, 0, $aboveX, $aboveY,
                        $this->SSO[$iSSO]->apUpLink[$iUp]['MinX'], $this->SSO[$iSSO]->apUpLink[$iUp]['MaxX']
                    ]);
                }
                $pSlice = $this->map->Right($pSlice);
            }
            if ($lastSSObject !== null) $widest = max($widest, $width);
            array_push($GenTop, $GenTop[$iG] + $GenHeight + (2.0 * $this->pAll->treeData['line_height']));
        }

        // Make a first guess at the size of the image to be displayed
        $imgW = max($minimumWidth, ($widest + 2 * $sideGap) * $QualityFactor);
        $imgH = $GenTop[$nGen] + $bottomGap; // for credits & notes 
        $imgW = max($imgW, $imgH * $aspectRatio);
        $imgH = max($imgH, $imgW / $aspectRatio);

        ////////////////////////////////////////////////////////////////////////
        // Now centre all of the x coordinates
        ////////////////////////////////////////////////////////////////////////
        $nextRow = $TopLeft;
        for ($iG = 0; $iG < $nGen; $iG++) {
            // Now shift the generation left/right to equally space them
            if ($nSSO[$iG] > 0) {
                $pLast = $nSSO[$iG] - 1;
                $shift = ($imgW - $AbsX[$iG][$pLast] - $SSOwidth[$iG][$pLast]) / ($nSSO[$iG] + 1);
                for ($iS = 0; $iS < $nSSO[$iG]; $iS++)
                    $AbsX[$iG][$iS] += $shift * ($iS + 1);
            }
        }

        ///////////////////////////////////////////////////////////////////////////
        // Iteratively move SSObjects left and right
        ///////////////////////////////////////////////////////////////////////////
        for ($Itt = 0; $Itt < 10; $Itt++) {
            if ($Itt > 0) { // If this isn't the the first iteration then move SSObjects if possible
                for ($iG = 0; $iG < $nGen; $iG++) {
                    for ($iS = 0; $iS < $nSSO[$iG] - 1; $iS++) {
                        // Look to the right to see if desired movement would cause overlap
                        if ($WantToMove[$iG][$iS] > 0) {
                            $shift = ($AbsX[$iG][$iS] + $WantToMove[$iG][$iS] +
                                $SSOwidth[$iG][$iS] - $AbsX[$iG][$iS + 1]);
                            if ($shift > 0) {
                                $WantToMove[$iG][$iS + 1] =
                                    ($shift + $WantToMove[$iG][$iS + 1] * $nWantToMove[$iG][$iS + 1]) /
                                    ($nWantToMove[$iG][$iS + 1] + 1);
                                $nWantToMove[$iG][$iS + 1]++;
                            }
                        }
                    }

                    for ($iS = $nSSO[$iG] - 1; $iS > 0; $iS--) {
                        // Look to the left to see if desired movement would cause overlap
                        if ($WantToMove[$iG][$iS] < 0) {
                            $shift = $AbsX[$iG][$iS] + $WantToMove[$iG][$iS] -
                                $SSOwidth[$iG][$iS - 1] + $AbsX[$iG][$iS];
                            if ($shift < 0) {
                                $WantToMove[$iG][$iS - 1] =
                                    ($shift + $WantToMove[$iG][$iS - 1] * $nWantToMove[$iG][$iS - 1]) /
                                    ($nWantToMove[$iG][$iS - 1] + 1);
                                $nWantToMove[$iG][$iS - 1]++;
                            }
                        }
                    }

                    // Move as far as the neighbours will let you
                    for ($iS = 0; $iS < $nSSO[$iG]; $iS++) {
                        $temp = $WantToMove[$iG][$iS];
                        if ($WantToMove[$iG][$iS] > 0)
                            $WantToMove[$iG][$iS] = floor($WantToMove[$iG][$iS]);
                        if ($WantToMove[$iG][$iS] < 0)
                            $WantToMove[$iG][$iS] = floor($WantToMove[$iG][$iS]);
                        $AbsX[$iG][$iS] += $WantToMove[$iG][$iS];
                        if ($iS > 0) {
                            $AbsX[$iG][$iS] =
                                max($AbsX[$iG][$iS], $AbsX[$iG][$iS - 1] + $SSOwidth[$iG][$iS - 1]);
                        } else {
                            $AbsX[$iG][$iS] = max($AbsX[$iG][$iS], $sideGap);
                        }
                        if ($iS < $nSSO[$iG] - 1) {
                            $AbsX[$iG][$iS] = min($AbsX[$iG][$iS], $AbsX[$iG][$iS + 1] - $SSOwidth[$iG][$iS]);
                        } else {
                            $AbsX[$iG][$iS] = min($AbsX[$iG][$iS], ($imgW - $sideGap - $SSOwidth[$iG][$iS]));
                        }
                    }
                }
            }

            // Reset the SSObjects desires to move left and right
            for ($iG = 0; $iG < $nGen; $iG++) {
                for ($iS = 0; $iS < $nSSO[$iG]; $iS++) {
                    $nWantToMove[$iG][$iS] = 0;
                    $WantToMove[$iG][$iS] = 0;
                }
            }

            // Run through the generations
            $nextRow = $TopLeft;
            for ($iG = 0; $iG < $nGen; $iG++) {
                for ($iL = 0; $iL < $nLink[$iG]; $iL++) {
                    $iSSO = $iLink[$iG][$iL][4];
                    $iSSOAbove = $iLink[$iG][$iL][3];
                    $fLink[$iG][$iL][2] = $AbsX[$iG][$iSSO] + $fLink[$iG][$iL][7];
                    $fLink[$iG][$iL][3] = $AbsX[$iG][$iSSO] + $fLink[$iG][$iL][8];
                    $fLink[$iG][$iL][4] = $GenTop[$iG];
                    if ($iSSOAbove !== null) {
                        $iLink[$iG][$iL][0] = 0;  // Link Type
                        $fLink[$iG][$iL][0] = $AbsX[$iG - 1][$iSSOAbove] + $fLink[$iG][$iL][5];
                        $fLink[$iG][$iL][1] = $GenTop[$iG - 1] + $fLink[$iG][$iL][6];
                        $dshift = $fLink[$iG][$iL][0] - ($fLink[$iG][$iL][2] + $fLink[$iG][$iL][3]) / 2;
                        $WantToMove[$iG - 1][$iSSOAbove] = (-$dshift / 2 + $WantToMove[$iG - 1][$iSSOAbove] * $nWantToMove[$iG - 1][$iSSOAbove]) /
                            ($nWantToMove[$iG - 1][$iSSOAbove] + 1);
                        $WantToMove[$iG][$iSSO] = ($dshift / 2 + $WantToMove[$iG][$iSSO] * $nWantToMove[$iG][$iSSO]) /
                            ($nWantToMove[$iG][$iSSO] + 1);
                        $nWantToMove[$iG - 1][$iSSOAbove]++;
                        $nWantToMove[$iG][$iSSO]++;
                    } else {
                        $iLink[$iG][$iL][0] = 3;  // Link Type
                    }
                }
            }
        }

        ///////////////////////////////////////////////////////////////////
        // Now find out what remaining overlaps exist and characterise them
        ///////////////////////////////////////////////////////////////////

        for ($iG = 0; $iG < $nGen; $iG++) {
            $MaxOverlap = 0;

            for ($iL = 0; $iL < $nLink[$iG]; $iL++) {
                $iLink[$iG][$iL][1] = 0;  // This is the overlap count
                $iLink[$iG][$iL][2] = 0;  // Ultimate overlap count
            }

            // Study the overlaps
            $lLinkEnd = 0;
            // 0 = direct up/down ie down link fall between up links MaxX and MinX
            // 1 = Siblings to left of parents
            // 2 = Siblings to right of parents
            // 3 = Blind uplink (ie a bar over siblings with no parents shown)

            $OverlapCount = 0;
            for ($iL = 0; $iL < $nLink[$iG]; $iL++) {
                // Look for extent so if there is an overlap we can deal with it
                if ($iLink[$iG][$iL][0] == 3) {
                    $LinkStart = min($fLink[$iG][$iL][2], $fLink[$iG][$iL][3]);
                    $LinkEnd = max($fLink[$iG][$iL][2], $fLink[$iG][$iL][3]);
                    $LinkType = 3;
                } else {
                    $LinkStart = min($fLink[$iG][$iL][0], min($fLink[$iG][$iL][2], $fLink[$iG][$iL][3]));
                    $LinkEnd = max($fLink[$iG][$iL][0], max($fLink[$iG][$iL][2], $fLink[$iG][$iL][3]));
                    if ($fLink[$iG][$iL][0] < $fLink[$iG][$iL][2]) {
                        $LinkType = 2;
                    } else if ($fLink[$iG][$iL][0] > $fLink[$iG][$iL][3]) {
                        $LinkType = 1;
                    } else {
                        $LinkType = 0;
                    }
                }

                $iLink[$iG][$iL][0] = $LinkType;
                $Overlap = (($iL > 0) && ($LinkStart - 3 * $this->pAll->treeData['line_thickness'] <= $lLinkEnd)); // SymSize
                if ($Overlap) {
                    $OverlapCount++;
                }
                $iLink[$iG][$iL][1] = $OverlapCount;
                $MaxOverlap = max($MaxOverlap, $OverlapCount);

                if (($OverlapCount > 0) && !$Overlap) {
                    for ($k = 0; $k <= $OverlapCount; $k++) {
                        $iLink[$iG][$iL - 1 - $k][2] = $OverlapCount;
                    }
                    $OverlapCount = 0;
                    $iLink[$iG][$iL][1] = 0;
                } else if (($OverlapCount > 0) && ($LinkType == 0)) {
                    for ($k = 0; $k <= $OverlapCount; $k++) {
                        $iLink[$iG][$iL - $k][2] = $OverlapCount;
                    }
                    $OverlapCount = 0;
                } else if (($OverlapCount > 0) && ($iL == $nLink[$iG] - 1)) {
                    for ($k = 0; $k <= $OverlapCount; $k++) {
                        $iLink[$iG][$iL - $k][2] = $OverlapCount;
                    }
                    $OverlapCount = 0;
                }
                $lLinkEnd = $LinkEnd;
            }

            // Move this generation and all following generations down by MaxOverlap * LineHeight
            $dy = ($MaxOverlap * $this->pAll->treeData['line_height']);
            for ($ii = $iG; $ii < $nGen; $ii++) {
                $GenTop[$ii] += $dy;
                for ($ll = 0; $ll < $nLink[$ii]; $ll++) {
                    if ($ii != $iG) {
                        $fLink[$ii][$ll][1] += $dy;
                    }
                    $fLink[$ii][$ll][4] += $dy;
                }
            }
            $GenTop[$nGen] += $dy;
        }

        /////////////////////////////////////////////
        // Check whether the overlaps have moved the bottom generation below the image bottom
        /////////////////////////////////////////////
        if ($GenTop[$nGen] + $bottomGap > $imgH) {
            // So we have to increase the image width to maintain the aspect ratio
            $imgH = $GenTop[$nGen] + $bottomGap; // for credits & notes
            $newImgW = ($imgH * $aspectRatio);
            $xShift = ($newImgW - $imgW) / 2;
            $imgW = $newImgW;

            for ($iG = 0; $iG < $nGen; $iG++) {
                for ($iS = 0; $iS < $nSSO[$iG]; $iS++) {
                    $AbsX[$iG][$iS] += $xShift;
                }
                for ($iL = 0; $iL < $nLink[$iG]; $iL++) {
                    $fLink[$iG][$iL][0] += $xShift;
                    $fLink[$iG][$iL][2] += $xShift;
                    $fLink[$iG][$iL][3] += $xShift;
                }
            }
        }

        /////////////////////////////////////////////
        // Move all generations down so that they are central
        /////////////////////////////////////////////

        $shift = ($imgH - $bottomGap + $topGap - $GenTop[0] - $GenTop[$nGen]) / 2;
        for ($iG = 0; $iG <= $nGen; $iG++) {
            $GenTop[$iG] += $shift;
            if ($iG < $nGen) {
                for ($iL = 0; $iL < $nLink[$iG]; $iL++) {
                    $fLink[$iG][$iL][1] += $shift;
                    $fLink[$iG][$iL][4] += $shift;
                }
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////
        // Finally the code that outputs all the links and boxes ready to be rendered
        ////////////////////////////////////////////////////////////////////////////////////////////////

        for ($iG = 0; $iG < $nGen; $iG++) {
            // Draw the SSObjects
            for ($iS = 0; $iS < $nSSO[$iG]; $iS++)
                $this->SSO[$pSSO[$iG][$iS]]->drawSSO(
                    $this->pAll,
                    $BoxListIn,
                    $ConListIn,
                    $GenTop[$iG],
                    $AbsX[$iG][$iS]
                ); // Now draw the links
            $MidLevel = 0;

            for ($iL = 0; $iL < $nLink[$iG]; $iL++) {
                if ($iLink[$iG][$iL][0] != 3) {
                    if ($iLink[$iG][$iL][0] == 0) { // Straight down
                        $ConListIn->AddConnectingLine('v', $fLink[$iG][$iL][0], $fLink[$iG][$iL][1], $fLink[$iG][$iL][4] - $fLink[$iG][$iL][1]);
                    } else {
                        if ($iLink[$iG][$iL][0] == 1) { // Type 1 (ie siblings to left of parents)
                            $MidLevel = ($fLink[$iG][$iL][4] -
                                ($iLink[$iG][$iL][2] - $iLink[$iG][$iL][1]) * $this->pAll->treeData['line_height']); //  / (Link[iG][iL][7]+1));
                        } else if ($iLink[$iG][$iL][0] == 2) { // Type 2 (ie siblings to right of parents)
                            $MidLevel = ($fLink[$iG][$iL][4] -
                                $iLink[$iG][$iL][1] * $this->pAll->treeData['line_height']); //  /	(Link[iG][iL][7]+1));
                        }
                        $ConListIn->AddConnectingLine('v', $fLink[$iG][$iL][0], $fLink[$iG][$iL][1], $MidLevel - $fLink[$iG][$iL][1]);
                        $ConListIn->AddConnectingLine('h', $fLink[$iG][$iL][0], $MidLevel, ($fLink[$iG][$iL][2] + $fLink[$iG][$iL][3]) / 2 - $fLink[$iG][$iL][0]);
                        $ConListIn->AddConnectingLine('v', ($fLink[$iG][$iL][2] + $fLink[$iG][$iL][3]) / 2, $MidLevel, $fLink[$iG][$iL][4] - $MidLevel);
                    }
                }
            }
        }

        // Finally report the size of image canvas required
        return ['width' => $imgW, 'height' => $imgH];
    }
}
