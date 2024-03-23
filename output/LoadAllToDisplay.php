<?php

// This class loads and holds a in temparary storage a record of everything that needs to be displayed
// Any individuals who are flagged not to be displayed are left out

class LoadAllToDisplay
{
    var $treeData;
    var $People; // All individuals
    var $Families; // All families with children show
    var $authorText;
    var $Title;
    var $title_font;
    var $originator_font;
    var $first_line_font;
    var $other_line_font;
    var $first_line_font_h;
    var $TruTreeCredit = "Created using AffableGenes.com (TM) ";
    var $AstriskExplanation = "* - means that a person is shown more than once on the tree ";
    var $Needs_Asterisk_Explanation;
    // var $tmpImg;    // TODO Check if we still need this as the text sizing for img doesn't seem to use it

    /** Creates a new instance of LoadAllToDisplay */
    function __construct($mysqli, $treeId, $media_path, $asPDF)
    {

        // Read the name of the author for later
        $this->treeData = read_assoc($mysqli, "tree", $treeId);

        if ($this->treeData['author'] == null) {
            $this->authorText = "Produced by: ... ";
        } else {
            $authorData = read_assoc($mysqli, "submitter", $this->treeData['author']);
            $this->authorText = "Produced by: " . $authorData['name'] . ' ';
        }

        // Read the tree title
        $this->Title = $this->treeData['title'];

        // Read the fonts and store local copy
        $this->title_font = read_assoc($mysqli, "font", $this->treeData['title_font']);
        // $this->title_font_file = getFontFile($this->title_font);
        $this->originator_font = read_assoc($mysqli, "font", $this->treeData['originator_font']);
        // $this->originator_font_file = getFontFile($this->originator_font);
        $this->first_line_font = read_assoc($mysqli, "font", $this->treeData['first_line_font']);
        // $this->first_line_font_file = getFontFile($this->first_line_font);
        $this->other_line_font = read_assoc($mysqli, "font", $this->treeData['other_line_font']);
        // $this->other_line_font_file = getFontFile($this->other_line_font);
        $this->first_line_font_h = $this->first_line_font['size'];  // TODO Check this is appropriate when drawing to an image - Originally written for PDF
        // Collect lists of all the families in this tree and store them in a local assoc_array
        $temp = read_all_assoc($mysqli, 'family', $treeId);
        $this->Families = [];
        foreach ($temp as $fam) {
            $this->Families[$fam['id']] = [
                "id" => $fam['id'],
                "wife" => $fam['wife'],
                "husband" => $fam['husband'],
                "pref_type" => $fam['pref_type'],
                "children" => [],
                "UpLinkDone" => false,
                "DnLinkDone" => false
            ];
        }

        // Collect lists of all the people in this tree and add them to
        // the families list as children if appropriate
        $temp = read_all_assoc($mysqli, 'individual', $treeId);
        $this->People = [];
        foreach ($temp as &$indi) {
            if ($indi['show_me'] != 'n') {
                $this->People[$indi['id']] = [
                    "id" => $indi['id'],
                    "sex" => $indi['sex'],
                    "show_me" => $indi['show_me'], // must be 't' (top) or 'b' (below)
                    "living" => $indi['living'],
                    "box_text" => $indi['box_text'],
                    "child_in_family" => $indi['child_in_family'],
                    "lspouse" => $indi['lspouse'],
                    "rspouse" => $indi['rspouse'],
                    "l_media_thumb" => create_thumbnail($mysqli, $indi['l_media_id'], $this->treeData['thumbnail_W'], $this->treeData['thumbnail_H'], $media_path),
                    "r_media_thumb" => create_thumbnail($mysqli, $indi['r_media_id'], $this->treeData['thumbnail_W'], $this->treeData['thumbnail_H'], $media_path),
                    "times_shown" => 0,
                    "shown_on_this_SSO" => false,
                    "box_size" => null
                ]; // box_size is populated in createOutput with the minimum size the box can be
                if ($indi['child_in_family'] != null) {
                    array_push($this->Families[$indi['child_in_family']]['children'], [intval($indi['place_in_family_sibling_list']), $indi['id']]);
                }
            }
        }

        // Check all left and right spouses exist - if not remove them in the temporary store
        foreach ($this->People as &$indi) {
            if (!$this->personExists($indi['lspouse']))
                $indi['lspouse'] = null;
            if (!$this->personExists($indi['rspouse']))
                $indi['rspouse'] = null;
        }

        foreach ($this->Families as &$fam) {
            // Now sort the children and discard the indexes
            $children = $fam['children'];
            usort($children, function ($a, $b) {
                return $a[0] > $b[0];
            });
            $fam['children'] = [];
            foreach ($children as $child) {
                array_push($fam['children'], $child[1]);
            }

            // Make sure first child is marked to show at the top
            if (count($fam['children']) > 0) $this->People[$fam['children'][0]]['show_me'] = 't';

            if (!$this->personExists($fam['husband']))
                $fam['husband'] = null;
            if (!$this->personExists($fam['wife']))
                $fam['wife'] = null;

            // Make sure that all the prefered parents in families are correct. If not, correct them
            if ($fam['pref_type'] == 'h') {
                if ($this->personExists($fam['husband'])) {
                    $fam['pref_type'] = 'h';
                } else {
                    if ($this->personExists($fam['wife'])) {
                        $fam['pref_type'] = 'w';
                    } else {
                        $fam['pref_type'] = 'n';
                    }
                }
            } else {
                if ($this->personExists($fam['wife'])) {
                    $fam['pref_type'] = 'w';
                } else {
                    if ($this->personExists($fam['husband'])) {
                        $fam['pref_type'] = 'h';
                    } else {
                        $fam['pref_type'] = 'n';
                    }
                }
            }
        }
    }

    // A function to check $Indi != null and the person in in the People list (i.e. showme != 'n')
    private function personExists($Indi)
    {
        if ($Indi === null) {
            return false;
        } else {
            if (!array_key_exists($Indi, $this->People)) {
                return false;
            }
        }
        return true;
    }

    function AnyShownMoreThanOnce()
    {
        // Check if any person is shown more than once
        $temp = false;
        foreach ($this->People as $indi) {
            $temp |= ($indi['times_shown'] > 1);
        }
        return $temp;
    }

    function clearShownOnThisSSO()
    {
        foreach ($this->People as &$indi) {
            $indi['shown_on_this_SSO'] = false;
        }
    }
}
