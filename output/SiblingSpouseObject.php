<?php
/*

A SiblingSpouseObject (aka SSO) describes a block on a family tree that describes a group of people who
are all in one generation and are siblings or related by marriage to siblings.

The constructor is called with a 'seed' individual. New SSOs are created from 3 places in createOutput.php. These are:
- When expanding the 'Root' individual.
- From DoUpLink where the first child in a family is specified. DoUpLink is called when an unexpanded
  UpLink is found. The unexpanded UpLink will have been put there when a down
- From DoDownLink when the DownLink. In this case the constructor is called referencing the prefered spouse

*/

class SiblingSpouseObject
{
    var $size;      // The size of this SSO assoc array with elements height and width
    var $apDnLink;  // elements of the apDnLink are; Px, Py, LinkedFamily and NoRoom
    var $apUpLink;  // elements of the apUpLink are; MaxX, MinX, LinkedFamily and NoRoom
    var $pSSIndi;   // The seed individual on creation of this SSO
    var $nDnLink;   // Number of elements in $apDnLink
    var $nUpLink;   // Number of elements in $apUpLink

    // Output ready to go    
    var $stored_boxes;
    var $stored_con;

    // This is where most of the work
    function __construct(
        &$pAll,             // A structure that contains all of the relevant tree data in a temporary store
        $pIndiIn,           // The individual to be expanded arround
        $comingFromBelow    // See below
    )
    // If $comingFromBelow is true then the link has come from below and therefore this person
    // must be at the top of a stack containing only one person. This is so there
    // is a clear run for a link from below
    {
        $this->size = ['width' => 1, 'height' => 1];
        $this->apDnLink = [];
        $this->apUpLink = [];
        $keyIndis = [];
        $this->pSSIndi = $pIndiIn;
        $this->nDnLink = 0;
        $this->nUpLink = 0;

        // If $comingFromBelow is true then force this person be at the top of a stack containing only one person
        // which means the next child (if there is one) must also be at the top
        if ($comingFromBelow) {
            $pAll->People[$pIndiIn]['show_me'] = 't';
            $FamC = $pAll->People[$pIndiIn]['child_in_family'];
            if ($FamC !== null) {
                $Children = $pAll->Families[$FamC]['children'];
                $loc = array_search($pIndiIn, $Children);
                if ($loc < count($Children) - 1)
                    $pAll->People[$Children[$loc + 1]]['show_me'] = 't';
            }
        }

        // Check continuity going right and make adjustments so that several families can be shown
        // next to each other linked by marriages
        $pAll->clearShownOnThisSSO();
        $RightmostIsOnSSO = false;
        $thisIndi = $this->pSSIndi;
        $done = false;
        while (!$done) {
            // If the person we are currently on is a child in a family move on to the rightmost child
            $FamC = $pAll->People[$thisIndi]['child_in_family'];
            if ($FamC !== null) {
                $Children = $pAll->Families[$FamC]['children'];
                $nChildren = count($Children);
                for ($i = 0; $i < $nChildren; $i++)
                    $pAll->People[$Children[$i]]['shown_on_this_SSO'] = true;
                $thisIndi = $Children[$nChildren - 1]; // Rightmost child in family
            }
            // Does this rightmost sibling have a spouse on the right?
            if ($pAll->People[$thisIndi]['rspouse'] !== null) {
                // If so remember we found them
                $thisIndi = $pAll->People[$thisIndi]['rspouse'];
                array_push($keyIndis, $thisIndi);
                if ($pAll->People[$thisIndi]['shown_on_this_SSO']) {
                    // If this person has already been shown on this SSO we will show them but expand
                    // no further
                    $RightmostIsOnSSO = true;
                    $done = true;
                } else {
                    // If this person has a spouse to be drawn to the right then make that spouse the leftmost child
                    // in their family and at the top of their own stack
                    $pAll->People[$thisIndi]['shown_on_this_SSO'] = true;
                    $FamC = $pAll->People[$thisIndi]['child_in_family'];
                    if ($FamC !== null) {
                        $Children = $pAll->Families[$FamC]['children'];
                        $loc = array_search($thisIndi, $Children);
                        array_splice($Children, $loc, 1);
                        array_unshift($Children, $thisIndi);
                        $pAll->Families[$FamC]['children'] = $Children;
                        $pAll->People[$thisIndi]['show_me'] = 't';
                        if (count($Children) > 1) $pAll->People[$Children[1]]['show_me'] = 't';
                    }
                }
            } else {
                // If there is no right spouse we have gone s far as we can
                $done = true;
            }
        }

        // Check continuity going left and make adjustments so that several families can be shown
        // next to each other linked by marriages
        $LeftmostIsOnSSO = false;
        $thisIndi = $this->pSSIndi;
        $done = false;
        while (!$done) {
            // If the person we are currently on is a child in a family move on to the leftmost child
            $FamC = $pAll->People[$thisIndi]['child_in_family'];
            if ($FamC !== null) {
                $Children = $pAll->Families[$FamC]['children'];
                $nChildren = count($Children);
                for ($i = 0; $i < $nChildren; $i++) {
                    $pAll->People[$Children[$i]]['shown_on_this_SSO'] = true;
                }
                $thisIndi = $Children[0]; // Leftmost child
            }
            array_unshift($keyIndis, $thisIndi);
            // Does the leftmost person have a left spouse
            if ($pAll->People[$thisIndi]['lspouse'] !== null) {
                $thisIndi = $pAll->People[$thisIndi]['lspouse'];
                if ($pAll->People[$thisIndi]['shown_on_this_SSO']) {
                    // If this person has already been shown on this SSO we will show them but expand
                    // no further
                    $LeftmostIsOnSSO = true;
                    $done = true;
                } else {
                    // If this person has a spouse to be drawn to the left then make that spouse the rightmost child
                    // in their family and at the top of their own stack
                    $pAll->People[$thisIndi]['shown_on_this_SSO'] = true;
                    $FamC = $pAll->People[$thisIndi]['child_in_family'];
                    if ($FamC !== null) {
                        $Children = $pAll->Families[$FamC]['children'];
                        $loc = array_search($thisIndi, $Children);
                        array_splice($Children, $loc, 1);
                        array_push($Children, $thisIndi);
                        $pAll->Families[$FamC]['children'] = $Children;
                        $pAll->People[$thisIndi]['show_me'] = 't';
                    }
                }
            } else {
                // If there is no left spouse we have gone s far as we can
                $done = true;
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////////////
        // $keyIndis is now a complete list of people who are either first child in the families
        // going from left to right or they are in the SSO but not siblings in a family. 
        // $RightmostIsOnSSO is true if the rightmost person the keyIndis list should not be expanded
        // $LeftmostIsOnSSO is true if the leftmost person the keyIndis list should not be expanded
        ////////////////////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////////////
        $nKeyIndi = count($keyIndis);

        // Look at each of the key individuals and describe the siblings around them
        $siblings = [];
        for ($i = 0; $i < $nKeyIndi; $i++) {
            $FamC = $pAll->People[$keyIndis[$i]]['child_in_family'];
            // If this is the first or last and that one is marked to not expand just have the one person
            // Also if the individual is not a child in a family just have the one person
            if (
                ($LeftmostIsOnSSO && ($i == 0)) ||
                ($RightmostIsOnSSO && ($i == $nKeyIndi - 1)) ||
                ($FamC == null)
            ) {
                $siblingSet = [[$keyIndis[$i]]];
            } else {
                $siblingSet = [];
                $stack = [$keyIndis[$i]];
                $Children = $pAll->Families[$FamC]['children']; // Key indi will be [0]
                for ($j = 1; $j < count($Children); $j++) {
                    if ($pAll->People[$Children[$j]]['show_me'] == 't') {
                        array_push($siblingSet, $stack);
                        $stack = [$Children[$j]];
                    } else {
                        array_push($stack, $Children[$j]);
                    }
                }
                array_push($siblingSet, $stack);
            }
            array_push($siblings, $siblingSet);
        }

        // Look for stacks of more than 1 person then make all the widths the same
        for ($i = 0; $i < $nKeyIndi; $i++) {
            $nStacks = count($siblings[$i]);
            for ($j = 0; $j < $nStacks; $j++) {
                $stack = $siblings[$i][$j];
                $stackLen = count($stack);
                if ($stackLen > 1) {
                    $w = 0;
                    // $h = 0;
                    for ($k = 0; $k < $stackLen; $k++) {
                        $w = max($w, $pAll->People[$stack[$k]]['box_size']['width']);
                    }
                    for ($k = 0; $k < $stackLen; $k++) {
                        $pAll->People[$stack[$k]]['box_size']['width'] = $w;
                    }
                }
            }
        }

        // Look at every stack and see whether we can show spouses left or right. To be able to show spouses the stack has to have a 
        // length of 1. Cant show any more left if I'm already on the left of a sibling list because that one will already be
        // there in the next block of of siblings. Same working right.
        for ($i = 0; $i < $nKeyIndi; $i++) {
            $nStacks = count($siblings[$i]);
            $rightSpouses[$i][$nStacks - 1] = [];
            for ($j = 0; $j < $nStacks - 1; $j++) {
                if (count($siblings[$i][$j]) == 1) {
                    $thisIndi = $siblings[$i][$j][0];
                    $sl = [];
                    $done = false;
                    while (!$done) {
                        $sp = $pAll->People[$thisIndi]['rspouse'];
                        if ($sp == null) {
                            $done = true;
                        } else {
                            array_push($sl, $sp);
                            if ($pAll->People[$sp]['shown_on_this_SSO']) {
                                $done = true;
                            }
                            $pAll->People[$sp]['shown_on_this_SSO'] = true;
                        }
                        $thisIndi = $sp;
                    }
                    $rightSpouses[$i][$j] = $sl;
                }
            }
            // Look for spouses left
            $leftSpouses[$i][0] = [];
            for ($j = 1; $j < $nStacks; $j++) {
                if (count($siblings[$i][$j]) == 1) {
                    $thisIndi = $siblings[$i][$j][0];
                    $sl = [];
                    $done = false;
                    while (!$done) {
                        $sp = $pAll->People[$thisIndi]['lspouse'];
                        if ($sp == null) {
                            $done = true;
                        } else {
                            array_unshift($sl, $sp);
                            if ($pAll->People[$sp]['shown_on_this_SSO']) {
                                $done = true;
                            }
                            $pAll->People[$sp]['shown_on_this_SSO'] = true;
                        }
                        $thisIndi = $sp;
                    }
                    $leftSpouses[$i][$j] = $sl;
                }
            }
        }

        // Create some box and connector arrays. These will have positions relative to the top left of
        // the SSO. When it comes to write the SSO these just get copied with the offsets applied.
        $this->stored_boxes = new BoxList();
        $this->stored_con = new ConnectingLineList();

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        // Work from left to right filling in the BoxList, ConnectingLineList, size(width, height), 
        // up links (apUpLink, nUpLink) and down links (apDnLink, nDnLink)
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        // Calculate where the top of the text will be
        $TextTop = $pAll->treeData['line_height'];
        // Keep a running tally on the height and width
        $Width = 0;
        $Height = 0;

        for ($i = 0; $i < $nKeyIndi; $i++) {
            // Calculate where the left side of the top bar will be
            if (count($siblings[$i][0]) == 1) {
                $LHS = $Width + $pAll->People[$siblings[$i][0][0]]['box_size']['width'] / 2;
            } else {
                $LHS = $Width + $pAll->treeData['line_thickness'] / 2;
            }
            $RHS = $LHS;

            // Work through the children in this family in stacks
            $nStack = count($siblings[$i]);
            for ($j = 0; $j < $nStack; $j++) {
                // For this set of siblings is either of the parents able to be drawn as parents
                $FamC = $pAll->People[$siblings[$i][$j][0]]['child_in_family'];
                if ($FamC == null) {
                    $ParentsShown = false;
                } else {
                    $Husband = $pAll->Families[$FamC]['husband'];
                    $Wife = $pAll->Families[$FamC]['wife'];
                    $ParentsShown = (
                        (($Husband != null) && ($this->topOfOwnStack($pAll, $Husband))) ||
                        (($Wife != null) && ($this->topOfOwnStack($pAll, $Wife))));
                }
                $DrawFullUpright = ($nStack > 1) || $ParentsShown;

                // Is this a stack
                $nStacked = count($siblings[$i][$j]);
                if ($nStacked > 1) {
                    // If it is output the stack (all idividuals have the same width). No down links
                    // Is the stack the last thing in this list? If it is draw the bar on the right side
                    $barRight = (($j == $nStack - 1) && ($j != 0));
                    $IndiSize = $pAll->People[$siblings[$i][$j][0]]['box_size'];
                    $indiW = $IndiSize['width'];
                    // Calculate the vertical line position, short horiz left/right and individual center
                    if ($barRight) {
                        $vertX = $Width + $indiW + $pAll->treeData['sibling_gap'] + $pAll->treeData['line_thickness'] / 2;
                        $shortL = $Width + $indiW;
                        $leftX = $Width;
                    } else {
                        $vertX = $Width + $pAll->treeData['line_thickness'] / 2;
                        $shortL = $vertX;
                        $leftX = $Width + $pAll->treeData['line_thickness'] + $pAll->treeData['sibling_gap'];
                    }
                    $RHS = max($RHS, $vertX);
                    $LineY = $TextTop;
                    for ($k = 0; $k < $nStacked; $k++) {
                        $IndiSize = $pAll->People[$siblings[$i][$j][$k]]['box_size'];
                        $this->stored_boxes->AddBox(
                            'individual',
                            $siblings[$i][$j][$k],
                            $leftX,
                            $LineY,
                            $IndiSize['width'],
                            $IndiSize['height']
                        );
                        $pAll->People[$siblings[$i][$j][$k]]['times_shown']++;
                        // Draw the small horizontal
                        $this->stored_con->AddConnectingLine('h', $shortL, $LineY + $IndiSize['height'] / 2, $pAll->treeData['sibling_gap'] + $pAll->treeData['line_thickness'] / 2);
                        $LineY += $IndiSize['height'] + max(1, $pAll->treeData['sibling_gap'] / 2);
                    }
                    // Draw the vertical & update the height
                    if ($DrawFullUpright) {
                        $this->stored_con->AddConnectingLine('v', $vertX, 0, $LineY - $IndiSize['height'] / 2 - max(1, $pAll->treeData['sibling_gap'] / 2));
                    } else {
                        $this->stored_con->AddConnectingLine('v', $vertX, $TextTop, $LineY - $IndiSize['height'] / 2 - max(1, $pAll->treeData['sibling_gap'] / 2) - $TextTop);
                    }
                    $Height = max($Height, $LineY - max(1, $pAll->treeData['sibling_gap'] / 2));
                    // Move Right
                    $Width += $IndiSize['width'] + $pAll->treeData['line_thickness'] + 2 * $pAll->treeData['sibling_gap'];
                } else {
                    // If this is not a stack...
                    // Draw spouses to the left and put in down links on the individual and the marriage
                    // Note: We know there will not be any for the leftmost child in a family
                    $lSpouses = $leftSpouses[$i][$j];
                    $nlSpouses = count($lSpouses);
                    for ($k = 0; $k < $nlSpouses; $k++) {
                        $IndiSize = $pAll->People[$lSpouses[$k]]['box_size'];
                        $this->stored_boxes->AddBox(
                            'individual',
                            $lSpouses[$k],
                            $Width,
                            $TextTop,
                            $IndiSize['width'],
                            $IndiSize['height']
                        );
                        $pAll->People[$lSpouses[$k]]['times_shown']++;
                        $Height = max($Height, $IndiSize['height']);
                        $this->CreateSingleParentDownLinks(
                            $pAll,
                            $lSpouses[$k],
                            $Width,
                            $IndiSize['width'],
                            $TextTop + $IndiSize['height']
                        );
                        $Width += $IndiSize['width'];
                        if ($k == $nlSpouses - 1) {
                            $otherSpouse = $siblings[$i][$j][0];
                        } else {
                            $otherSpouse = $lSpouses[$k + 1];
                        }
                        $this->CheckDownLink(
                            $pAll,
                            $otherSpouse,
                            $lSpouses[$k],
                            $Width,
                            $TextTop,
                            $this->stored_boxes,
                            $this->stored_con
                        );
                        $Width += $pAll->treeData['sibling_gap'] + $pAll->treeData['marriage_gap'];
                    }

                    // Drow the individual and any down link not associated with spouses either side
                    $IndiSize = $pAll->People[$siblings[$i][$j][0]]['box_size'];
                    $this->stored_boxes->AddBox(
                        'individual',
                        $siblings[$i][$j][0],
                        $Width,
                        $TextTop,
                        $IndiSize['width'],
                        $IndiSize['height']
                    );
                    $pAll->People[$siblings[$i][$j][0]]['times_shown']++;
                    $Height = max($Height, $IndiSize['height']);
                    $this->CreateSingleParentDownLinks(
                        $pAll,
                        $siblings[$i][$j][0],
                        $Width,
                        $IndiSize['width'],
                        $TextTop + $IndiSize['height']
                    );
                    $RHS = max($RHS, $Width + $IndiSize['width'] / 2);
                    if ($DrawFullUpright) $this->stored_con->AddConnectingLine('v', $RHS, 0, $TextTop);
                    $Width += $IndiSize['width'];

                    // Draw spouses to the right and put in down links on the individual and the marriage
                    // Note: We know there will not be any for the rightmost child in a family
                    $rSpouses = $rightSpouses[$i][$j];
                    $nrSpouses = count($rSpouses);
                    for ($k = 0; $k < $nrSpouses; $k++) {
                        $IndiSize = $pAll->People[$rSpouses[$k]]['box_size'];
                        if ($k == 0) {
                            $otherSpouse = $siblings[$i][$j][0];
                        } else {
                            $otherSpouse = $rSpouses[$k - 1];
                        }
                        $this->CheckDownLink(
                            $pAll,
                            $otherSpouse,
                            $rSpouses[$k],
                            $Width,
                            $TextTop,
                            $this->stored_boxes,
                            $this->stored_con
                        );
                        $Width += $pAll->treeData['sibling_gap'] + $pAll->treeData['marriage_gap'];
                        $this->stored_boxes->AddBox(
                            'individual',
                            $rSpouses[$k],
                            $Width,
                            $TextTop,
                            $IndiSize['width'],
                            $IndiSize['height']
                        );
                        $pAll->People[$rSpouses[$k]]['times_shown']++;
                        $Height = max($Height, $IndiSize['height']);
                        $this->CreateSingleParentDownLinks(
                            $pAll,
                            $rSpouses[$k],
                            $Width,
                            $IndiSize['width'],
                            $TextTop + $IndiSize['height']
                        );
                        $Width += $IndiSize['width'];
                    }
                    if ($j < $nStack - 1) $Width += $pAll->treeData['sibling_gap'];
                }
            }
            // Complete the uplink
            if ($nStack > 1) $this->stored_con->AddConnectingLine('h', $LHS, 0, $RHS - $LHS);

            if ($ParentsShown) {
                array_push($this->apUpLink, [
                    'MaxX' => $RHS,
                    'MinX' => $LHS,
                    'LinkedFamily' => $FamC,
                    'NoRoom' => false
                ]);
                $this->nUpLink++;
            }
            // If there will be another set of siblings to the right there must be a marriage, so add the symbol and down link it                
            if ($i < $nKeyIndi - 1) {
                $this->CheckDownLink(
                    $pAll,
                    $siblings[$i][$nStack - 1][0],
                    $siblings[$i + 1][0][0],
                    $Width,
                    $TextTop,
                    $this->stored_boxes,
                    $this->stored_con
                );
                $Width += $pAll->treeData['sibling_gap'] + $pAll->treeData['marriage_gap'];
            }
            // Thats it! Go for the next spouse right it there is one
        }
        $Width += $pAll->treeData['sibling_gap'];
        $this->size = ['width' => $Width, 'height' => $Height];
    }

    function drawSSO(&$pAll, &$BoxListIn, &$ConListIn, $top, $left)
    {
        // Put the stored boxes and connecting lines into the lists, rembering to add the offset
        for ($i = 0; $i < count($this->stored_boxes->list); $i++)
            $BoxListIn->copyBox($this->stored_boxes->list[$i], $top, $left);
        for ($i = 0; $i < count($this->stored_con->list); $i++)
            $ConListIn->copyLine($this->stored_con->list[$i], $top, $left);
        for ($i = 0; $i < $this->nDnLink; $i++) {
            // elements of the apDnLink are; Px, Py, LinkedFamily and NoRoom
            if ($this->apDnLink[$i]['NoRoom']) {
                $r = 0.4*($pAll->treeData['sibling_gap']+$pAll->treeData['marriage_gap']);
                $BoxListIn->AddBox(
                    'missingDnLink',
                    $this->apDnLink[$i]['LinkedFamily'],
                    $left + $this->apDnLink[$i]['Px'] - $r,
                    $top + $this->apDnLink[$i]['Py'], //  + $pAll->first_line_font_h,
                    2*$r,
                    2*$r
                );
            }
        }
        for ($i = 0; $i < $this->nUpLink; $i++) {
            // elements of the apUpLink are; MaxX, MinX, LinkedFamily and NoRoom
            if ($this->apUpLink[$i]['NoRoom']) {
                $r = 0.8*($pAll->first_line_font_h);
                $BoxListIn->AddBox(
                    'missingUpLink',
                    $this->apUpLink[$i]['LinkedFamily'],
                    $left + ($this->apUpLink[$i]['MaxX'] + $this->apUpLink[$i]['MinX'])/2 - $r,
                    $top - $r,
                    2*$r,
                    2*$r
                );
            }
        }
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Supporting functions
    //////////////////////////////////////////////////////////////////////////////////////////////////////

    private function CheckDownLink(
        &$pAll,
        // $Draw, // Only create the down link if !Draw otherwise just return the Family link
        $Spouse1,
        $Spouse2,
        $PxIn,
        $PyIn,
        &$BoxListIn,
        &$ConListIn
    ) {
        // Does this couple have any children to display?
        // First find which family they are
        $SharedFamily = null;
        foreach ($pAll->Families as $famData) {
            if (
                (($Spouse1 === $famData['wife']) && ($Spouse2 === $famData['husband'])) ||
                (($Spouse2 === $famData['wife']) && ($Spouse1 === $famData['husband']))
            ) {
                $SharedFamily = $famData['id'];
                $ChildrenShown = (count($famData['children']) > 0);
                $DnLinkDoneAlready = false;
                for ($g = 0; $g < $this->nDnLink; $g++)
                    $DnLinkDoneAlready = ($DnLinkDoneAlready || ($this->apDnLink[$g]['LinkedFamily'] === $SharedFamily));

                // if children are shown then create a new down link
                if ($ChildrenShown && !$DnLinkDoneAlready) {
                    array_push($this->apDnLink, [
                        'Px' => $PxIn + ($pAll->treeData['sibling_gap'] + $pAll->treeData['marriage_gap']) / 2,
                        'Py' => $PyIn + $pAll->first_line_font_h + $pAll->treeData['outline_thickness'],
                        'LinkedFamily' => $SharedFamily,
                        'NoRoom' => false
                    ]);
                    $this->nDnLink++;
                }
            }
        }
        $xh = $pAll->first_line_font_h;
        $BoxListIn->AddBox(
            'family',
            $SharedFamily,
            $PxIn,
            $PyIn,
            (float) $pAll->treeData['sibling_gap'] + (float) $pAll->treeData['marriage_gap'],
            (float) $xh
        );
        $sleft = $PxIn + $pAll->treeData['sibling_gap'] / 2;
        $swidth = $pAll->treeData['marriage_gap'];
        $ConListIn->AddConnectingLine('h', $sleft, $PyIn + 0.25 * $xh, $swidth);
        $ConListIn->AddConnectingLine('h', $sleft, $PyIn + 0.75 * $xh, $swidth);
    }

    private function CreateSingleParentDownLinks(
        &$pAll,
        $sIndi,
        $PxIn,
        $PwIn,
        $PyIn
    ) {
        // Create somewhere to put a list of the families which are below this individual but wont
        // be shown below a marriage symbol
        $SPfams = [];
        // Does this individual have any children to display that are not linked to the
        // displayed spouses
        $myLSpouse = $pAll->People[$sIndi]['lspouse'];
        $myRSpouse = $pAll->People[$sIndi]['rspouse'];
        foreach ($pAll->Families as $famData) {
            $fam = $famData['id'];
            $sSpouse = null;
            $inFam = false;
            if ($famData['wife'] == $sIndi) {
                $inFam = true;
                $sSpouse = $famData['husband'];
            } else if ($famData['husband'] == $sIndi) {
                $inFam = true;
                $sSpouse = $famData['wife'];
            }
            if ($inFam) {
                if (($sSpouse === null) || (!$this->topOfOwnStack($pAll, $sSpouse)) ||
                    (($sSpouse !== $myLSpouse) && ($sSpouse !== $myRSpouse))
                ) {
                    // Now we've determined this spouse isnt shown or doesn't exist - check for children
                    // which are to be shown
                    $ChildrenShown = (count($famData['children']) > 0);
                    $DnLinkDoneAlready = false;
                    for ($g = 0; $g < $this->nDnLink; $g++) {
                        $DnLinkDoneAlready = ($DnLinkDoneAlready || ($this->apDnLink[$g]['LinkedFamily'] === $fam));
                    }

                    // if children are shown then create a new down link
                    if ($ChildrenShown && !$DnLinkDoneAlready) array_push($SPfams, $fam);
                }
            }
        }
        $nFam = count($SPfams);
        for ($i = 0; $i < $nFam; $i++) {
            array_push($this->apDnLink, [
                'Px' => $PxIn + $PwIn * ($i + 1) / ($nFam + 1),
                'Py' => $PyIn,
                'LinkedFamily' => $SPfams[$i],
                'NoRoom' => false
            ]);
            $this->nDnLink++;
        }
    }

    // This function returns true ifthe person:
    // - is at the top of a stack
    // - is either the only child in the family or is the last child or the next child is also at the top
    private function topOfOwnStack(&$pAll, $indiId)
    {
        if ($pAll->People[$indiId]['show_me'] != 't') {
            return false;
        } else {
            $famC = $pAll->People[$indiId]['child_in_family'];
            if ($famC === null) {
                return true;
            } else {
                $children = $pAll->Families[$famC]['children'];
                $child = array_search($indiId, $children);
                if (count($children) <= $child + 1) {
                    return true;
                } else {
                    return ($pAll->People[$children[$child + 1]]['show_me'] == 't');
                }
            }
        }
    }
}
